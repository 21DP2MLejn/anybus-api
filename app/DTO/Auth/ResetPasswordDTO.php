<?php

namespace App\DTO\Auth;

readonly class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
        public string $passwordConfirmation,
    ) {
    }

    /**
     * Create DTO from array.
     *
     * @param  array<string, mixed>  $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            token: $data['token'],
            password: $data['password'],
            passwordConfirmation: $data['password_confirmation'] ?? $data['password'],
        );
    }

    /**
     * Convert DTO to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'token' => $this->token,
            'password' => $this->password,
            'password_confirmation' => $this->passwordConfirmation,
        ];
    }
}

