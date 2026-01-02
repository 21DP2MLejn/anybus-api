<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $sessionId = $this->input('session_id');
        $sessionData = Cache::get($sessionId);

        if (! $sessionData || ! isset($sessionData['expires_at']) || now()->isAfter($sessionData['expires_at'])) {
            // Invalid or expired session
            return;
        }

        $this->merge([
            'email' => $sessionData['email'],
            'token' => $sessionData['token'],
        ]);

        // Clear the cache data after use for security
        Cache::forget($sessionId);
    }
}
