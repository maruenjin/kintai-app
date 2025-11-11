<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

       
        $target = ($user && method_exists($user, 'isAdmin') && $user->isAdmin())
            ? route('admin.attendance.list')   
            : url('/');             

        return redirect()->intended($target);
    }
}
