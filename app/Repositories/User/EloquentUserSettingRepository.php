<?php

namespace App\Repositories\User;

use App\Models\UserSetting;

class EloquentUserSettingRepository implements UserSettingRepositoryInterface
{
    /**
     * Find user settings by user ID.
     */
    public function findByUserId(int $userId): ?UserSetting
    {
        return UserSetting::where('user_id', $userId)->first();
    }

    /**
     * Create or update user settings.
     */
    public function updateOrCreate(array $attributes, array $values): UserSetting
    {
        return UserSetting::updateOrCreate($attributes, $values);
    }
}
