<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'string',
                'min:12',
                'confirmed',
                function ($value, $fail) {
                    if (!preg_match('/[a-z]/', $value)) {
                        $fail(__('auth.password_rule_lowercase'));
                    }
                },
                function ($value, $fail) {
                    if (!preg_match('/[A-Z]/', $value)) {
                        $fail(__('auth.password_rule_uppercase'));
                    }
                },
                function ($value, $fail) {
                    if (!preg_match('/\d/', $value)) {
                        $fail(__('auth.password_rule_number'));
                    }
                },
                function ($value, $fail) {
                    if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                        $fail(__('auth.password_rule_symbol'));
                    }
                },
            ],
        ], [
            'password.min' => __('auth.password_rule_length'),
            'password.confirmed' => __('auth.password_match_no'),
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Utilisateur $user) use ($request) {
                $user->forceFill([
                    'mdp' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
