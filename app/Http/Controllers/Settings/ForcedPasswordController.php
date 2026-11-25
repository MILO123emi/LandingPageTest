<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Users\SetPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ForcedPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForcedPasswordController extends Controller
{
    public function edit(Request $request): Response|RedirectResponse
    {
        if (! $request->user()->must_change_password) {
            return to_route('dashboard');
        }

        return Inertia::render('auth/ChangePassword', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    public function update(ForcedPasswordRequest $request, SetPassword $setPassword): RedirectResponse
    {
        $setPassword->handle($request->user(), $request->validated('password'), false, $request->session()->getId());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Contraseña actualizada.']);

        return to_route('dashboard');
    }
}
