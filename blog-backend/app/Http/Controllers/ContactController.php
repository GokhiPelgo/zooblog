<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactConfirmationMail;
use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * GET /api/contact-messages
     *
     * Lista los mensajes recibidos (más recientes primero), paginados.
     * Protegido: requiere el header X-Admin-Token con el valor de
     * ADMIN_API_TOKEN. Son datos personales, así que no puede ser público.
     */
    public function index(Request $request): JsonResponse
    {
        $token = config('services.admin.token');

        if (! $token || ! hash_equals($token, (string) $request->header('X-Admin-Token'))) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $messages = ContactMessage::query()
            ->latest()              // ordena por created_at desc
            ->paginate(20);

        return response()->json($messages);
    }

    /** POST /api/contact */
    public function store(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Persiste en base de datos
        ContactMessage::create($data);

        // Notificación al administrador — fallo silencioso para no bloquear la respuesta
        try {
            Mail::to(config('services.contact.admin_email'))
                ->send(new ContactMessageMail(
                    senderName:  $data['name'],
                    senderEmail: $data['email'],
                    messageBody: $data['message'],
                ));
        } catch (\Throwable $e) {
            Log::error('ContactMessageMail failed: ' . $e->getMessage());
        }

        // Confirmación al remitente. OJO: Resend solo envía a correos ajenos
        // (no tu cuenta) si tienes un DOMINIO VERIFICADO. Fallo silencioso.
        try {
            Mail::to($data['email'])
                ->send(new ContactConfirmationMail(senderName: $data['name']));
        } catch (\Throwable $e) {
            Log::error('ContactConfirmationMail failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Mensaje recibido. ¡Gracias por contactarnos!',
        ], 201);
    }
}
