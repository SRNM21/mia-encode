<?php

namespace App\Services;

use App\Core\Facades\Auth;
use App\Models\Setting;
use App\Models\User;
use Exception;

class SettingsService
{
    public function updateProfile(User $user, string $username, string $email): void
    {
        User::update(['id' => $user->id], [
            'username' => $username,
            'email' => $email,
        ]);
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword, string $confirmPassword): void
    {
        $matchCurrentPassword = $user->checkPassword($currentPassword);

        if (!$matchCurrentPassword)
        {
            throw new Exception('Current password does not match.', 401);
        }

        if ($newPassword != $confirmPassword)
        {
            throw new Exception('New password does not match.', 400);
        }
            
        // Attempt to change password if current password is matched
        $success = Auth::changePassword($newPassword);

        if (!$success) 
        {
            throw new Exception('Change password has failed.', 401);
        }
    }

    public function updateTheme(User $user, string $theme): void
    {
        if (!\in_array($theme, ['dark', 'light', 'system'])) {
            throw new Exception('The selected theme is invalid.', 400);
        }

        $existingSetting = Setting::where('user_id', '=', $user->id)->first();

        if ($existingSetting) 
        {
            Setting::update(
                ['user_id' => $user->id], 
                ['preference' => $theme]
            );
        } 
        else 
        {
            Setting::create([
                'user_id' => $user->id,
                'preference' => $theme
            ]);
        }
    }
}
