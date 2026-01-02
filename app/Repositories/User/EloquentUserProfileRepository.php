<?php

namespace App\Repositories\User;

use App\Models\UserProfile;

class EloquentUserProfileRepository implements UserProfileRepositoryInterface
{
    /**
     * Create a new user profile.
     */
    public function create(array $data): UserProfile
    {
        return UserProfile::create($data);
    }

    /**
     * Find user profile by user ID.
     */
    public function findByUserId(int $userId): ?UserProfile
    {
        return UserProfile::where('user_id', $userId)->first();
    }

    /**
     * Update user profile.
     */
    public function update(UserProfile $profile, array $data): UserProfile
    {
        $profile->update($data);

        return $profile->fresh();
    }

    /**
     * Delete user profile.
     */
    public function delete(UserProfile $profile): bool
    {
        return $profile->delete();
    }
}
