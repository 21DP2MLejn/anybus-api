<?php

namespace App\Services\User;

use App\Models\UserSetting;
use App\Repositories\User\UserSettingRepositoryInterface;

class UserSettingService
{
    public function __construct(
        private readonly UserSettingRepositoryInterface $userSettingRepository
    ) {}

    /**
     * Get user settings by user ID.
     */
    public function getUserSettings(int $userId): ?UserSetting
    {
        return $this->userSettingRepository->findByUserId($userId);
    }

    /**
     * Store or update user settings using updateOrCreate.
     */
    public function storeUserSettings(int $userId, array $data): UserSetting
    {
        $settings = $data['settings'] ?? [];

        return $this->userSettingRepository->updateOrCreate(
            ['user_id' => $userId],
            ['settings' => $settings]
        );
    }
}
