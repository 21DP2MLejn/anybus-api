<?php

namespace App\Repositories\User;

use App\Models\UserProfile;

interface UserProfileRepositoryInterface
{
    /**
     * Create a new user profile.
     */
    public function create(array $data): UserProfile;

    /**
     * Find user profile by user ID.
     */
    public function findByUserId(int $userId): ?UserProfile;

    /**
     * Update user profile.
     */
    public function update(UserProfile $profile, array $data): UserProfile;

    /**
     * Delete user profile.
     */
    public function delete(UserProfile $profile): bool;
}
