<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email']));
        $user->password = Hash::make($request->validated('password'));
        $user->role = Role::User->value;
        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['email' => 'Alamat email ini sudah terdaftar. Silakan masuk.']);
        }

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        return redirect()->route($user->dashboardRoute());
    }
}
