<?php

namespace App\Repositories\User;

use App\Models\UserSetting;

interface UserSettingRepositoryInterface
{
    /**
     * Find user settings by user ID.
     */
    public function findByUserId(int $userId): ?UserSetting;

    /**
     * Create or update user settings.
     */
    public function updateOrCreate(array $attributes, array $values): UserSetting;
}
