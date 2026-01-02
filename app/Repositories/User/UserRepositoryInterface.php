<?php

namespace App\Repositories\User;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User;

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by ID.
     */
    public function findById(int $id): ?User;

    /**
     * Update a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User;
}
