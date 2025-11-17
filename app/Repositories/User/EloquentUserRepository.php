<?php

namespace App\Repositories\User;

use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Find a user by email.
     *
     * @param  string  $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find a user by ID.
     *
     * @param  int  $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Update a user.
     *
     * @param  User  $user
     * @param  array<string, mixed>  $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }
}

