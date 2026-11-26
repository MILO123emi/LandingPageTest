<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Users\SetPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SecurityController extends Controller
{
    /**
     * Show the user's security settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Security', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $request, SetPassword $setPassword): RedirectResponse
    {
        $setPassword->handle($request->user(), $request->validated('password'), false, $request->session()->getId());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Contraseña actualizada. Cerramos tus otras sesiones.']);

        return back();
    }
}
