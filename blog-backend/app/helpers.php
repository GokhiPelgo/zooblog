<?php

if (! function_exists('apiError')) {
    function apiError(string $message, int $status = 400): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }
}
