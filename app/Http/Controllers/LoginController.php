<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Core\Facades\Auth;
use App\Http\Request\Request;
use Exception;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        $this->view('auth.login');
    }

    public function login(Request $request)
    {
        $success = Auth::attempt([
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ]);
        
        if (!$success) 
        {
            return $this->responseJson([
                'title' => 'Wrong Credentials.',
                'message' => 'Invalid username or password.'
            ], 401);
        }

        return $this->responseJson([
            'redirect' => 'dashboard'
        ]);
    }

    public function logout()
    {
        try 
        {
            Auth::logout();

            return $this->responseJson([
                'redirect' => 'login'
            ]);
        }
        catch (Exception $e)
        {
            return $this->responseJson([
                'title' => 'Logout Failed.',
                'message' => 'Failed to logout.',
                'error' => $e->getMessage()
            ], 500);
        }

    }
}