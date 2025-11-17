<?php

namespace App\DTO\Auth;

readonly class ForgotPasswordDTO
{
    public function __construct(
        public string $email,
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
        ];
    }
}

