<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:2', 'max:100',
                          'regex:/^[\pL\s\-]+$/u'],
            'email'   => ['required', 'string', 'max:254',
                          'regex:/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex'     => 'El nombre solo puede contener letras, espacios y guiones.',
            'name.min'       => 'El nombre debe tener al menos 2 caracteres.',
            'email.regex'    => 'Ingresa un correo electrónico válido.',
            'message.min'    => 'El mensaje debe tener al menos 10 caracteres.',
            'message.max'    => 'El mensaje no puede superar 2000 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'    => trim(preg_replace('/\s+/', ' ', $this->name ?? '')),
            'email'   => strtolower(trim($this->email ?? '')),
            'message' => trim($this->message ?? ''),
        ]);
    }
}
