<?php

namespace App\Services\User;

use App\Models\UserProfile;
use App\Repositories\User\UserProfileRepositoryInterface;

class UserProfileService
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $userProfileRepository
    ) {}

    /**
     * Get user profile by user ID.
     */
    public function getUserProfile(int $userId): ?UserProfile
    {
        return $this->userProfileRepository->findByUserId($userId);
    }

    /**
     * Create a new user profile.
     */
    public function createUserProfile(int $userId, array $data): UserProfile
    {
        $data['user_id'] = $userId;

        return $this->userProfileRepository->create($data);
    }

    /**
     * Update user profile.
     */
    public function updateUserProfile(int $userId, array $data): UserProfile
    {
        $profile = $this->userProfileRepository->findByUserId($userId);

        if (! $profile) {
            throw new \Exception('User profile not found.');
        }

        return $this->userProfileRepository->update($profile, $data);
    }

    /**
     * Delete user profile.
     */
    public function deleteUserProfile(int $userId): bool
    {
        $profile = $this->userProfileRepository->findByUserId($userId);

        if (! $profile) {
            throw new \Exception('User profile not found.');
        }

        return $this->userProfileRepository->delete($profile);
    }
}
