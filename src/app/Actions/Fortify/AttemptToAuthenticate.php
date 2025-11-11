<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;

class AttemptToAuthenticate
{
    
    public function __invoke(LoginRequest $request)
    {
        
        $credentials = $request->only(Fortify::username(), 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return Auth::user();  
        }

        return null; 
    }
}

