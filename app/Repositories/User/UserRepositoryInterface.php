<?php

namespace App\Repositories\User;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Find a user by email.
     *
     * @param  string  $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by ID.
     *
     * @param  int  $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Update a user.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function update(User $user, array $data): User;
}

