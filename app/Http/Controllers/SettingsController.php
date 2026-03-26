<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Core\Facades\Auth;
use App\Http\Request\PasswordRequest;
use App\Http\Request\ProfileRequest;
use App\Http\Request\Request;
use App\Models\User;
use Throwable;

class SettingsController extends Controller
{
    public function show(Request $request) 
    {
        $this->view('settings');
    }

    private function checkAuthenticated(): User
    {
        $user = Auth::user();

        if (!$user)
        {
            $this->responseJson([
                'title' => 'Authentication Error',
                'message' => 'No user authenticated on this session.'
            ], 401);
        }

        return $user;
    }

    public function updateProfile(ProfileRequest $request) 
    {
        $error = get_error();

        if ($error)
        {
            return $this->responseJson([
                'title' => 'Error Occured',
                'errors' => $error
            ], 400);
        }

        $user = $this->checkAuthenticated();

        try
        {
            $username = $request->input('username');
            $email = $request->input('email');

            User::update(['id' => $user->id], [
                'username' => $username,
                'email' => $email,
            ]);

            $this->responseJson([
                'title' => 'Saved Successfully',
                'message' => 'Changes saved successfully.'
            ]);
        }
        catch (Throwable $e)
        {
            $this->responseJson([
                'title' => 'Unknown Error',
                'message' => 'An unknown error occured ['. $e->getCode() .'].'
            ], 500);
        }
    }

    public function updatePassword(PasswordRequest $request) 
    {
        $error = get_error();

        if ($error)
        {
            return $this->responseJson([
                'title' => 'Error Occured',
                'errors' => $error
            ], 400);
        }

        $user = $this->checkAuthenticated();

        try
        {
            $currentPassword = $request->input('current_password');
            $newPassword = $request->input('new_password');
            $confirmPassword = $request->input('confirm_password');

            $matchCurrentPassword = $user->checkPassword($currentPassword);

            if (!$matchCurrentPassword)
            {
                $this->responseJson([
                    'title' => 'Does not Match',
                    'message' => 'Current password does not match.'
                ], 401);
            }

            if ($newPassword != $confirmPassword)
            {
                $this->responseJson([
                    'title' => 'Does not Match',
                    'message' => 'New password does not match.'
                ], 400);
            }
                
            // Attempt to change password if current password is matched
            $success = Auth::changePassword($newPassword);

            if (!$success) 
            {
                $this->responseJson([
                    'title' => 'Update Failed',
                    'message' => 'Change password has failed.'
                ], 401);
            }

            $this->responseJson([
                'title' => 'Password Changed',
                'message' => 'Password changed successfully.'
            ]);
        }
        catch (Throwable $e)
        {
            $this->responseJson([
                'title' => 'Unknown Error',
                'message' => 'An unknown error occured ['. $e->getCode() .'].'
            ], 500);
        }
    }
}