<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Core\Facades\Auth;
use App\Http\Request\PasswordRequest;
use App\Http\Request\ProfileRequest;
use App\Http\Request\Request;
use App\Models\User;
use App\Services\SettingsService;
use Exception;
use Throwable;

class SettingsController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

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

            $this->settingsService->updateProfile($user, $username, $email);

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

            $this->settingsService->updatePassword(
                $user, 
                $currentPassword, 
                $newPassword, 
                $confirmPassword
            );

            $this->responseJson([
                'title' => 'Password Changed',
                'message' => 'Password changed successfully.'
            ]);
        }
        catch (Exception $e)
        {
            $this->responseJson([
                'title' => 'Update Failed',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
        catch (Throwable $e)
        {
            $this->responseJson([
                'title' => 'Unknown Error',
                'message' => 'An unknown error occured ['. $e->getCode() .'].'
            ], 500);
        }
    }

    public function updateTheme(Request $request) 
    {
        $user = $this->checkAuthenticated();

        try {
            $theme = $request->input('theme');

            $this->settingsService->updateTheme($user, $theme);

            $this->responseJson([
                'title' => 'Theme Updated',
                'message' => 'Your appearance preference has been saved successfully.'
            ]);

        } catch (Exception $e) {
            $this->responseJson([
                'title' => 'Update Failed',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        } catch (Throwable $e) {
            $this->responseJson([
                'title' => 'Unknown Error',
                'message' => 'An unknown error occurred [' . $e->getCode() . '].'
            ], 500);
        }
    }
}