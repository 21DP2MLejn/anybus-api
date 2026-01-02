<?php

namespace App\DTO\Auth;

readonly class RegisterDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $role = 'customer',
    ) {}

    /**
     * Create DTO from array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            role: $data['role'] ?? 'customer',
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
            'password' => $this->password,
            'role' => $this->role,
        ];
    }
}
