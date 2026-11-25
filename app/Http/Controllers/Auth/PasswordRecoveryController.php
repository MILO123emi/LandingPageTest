<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Users\SetPassword;
use App\Enums\CodePurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RecoverPasswordRequest;
use App\Http\Requests\Auth\SendRecoveryCodeRequest;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Support\MailGuard;
use App\Support\SecurityLog;
use App\Support\VerificationCodes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordRecoveryController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/ForgotPassword');
    }

    public function store(SendRecoveryCodeRequest $request, VerificationCodes $codes): RedirectResponse
    {
        MailGuard::ensureDeliverable();
        $email = $request->validated('email');
        $user = User::query()->where('email', $email)->first();

        if ($user !== null) {
            $code = $codes->issue($user, CodePurpose::PasswordRecovery);
            Mail::to($user->email)->send(new VerificationCodeMail($code, CodePurpose::PasswordRecovery));
            SecurityLog::record('password.recovery_code_sent', $user);
        }

        $request->session()->put('recovery.email', $email);

        return to_route('password.recovery.edit');
    }

    public function edit(Request $request): Response|RedirectResponse
    {
        if (! $request->session()->has('recovery.email')) {
            return to_route('password.recovery.create');
        }

        return Inertia::render('auth/ResetPassword', ['email' => $request->session()->get('recovery.email')]);
    }

    public function update(RecoverPasswordRequest $request, VerificationCodes $codes, SetPassword $setPassword): RedirectResponse
    {
        $email = $request->session()->get('recovery.email');
        $user = is_string($email) ? User::query()->where('email', $email)->first() : null;

        if ($user === null) {
            throw ValidationException::withMessages(['code' => 'El código venció o ya no es válido. Pedí uno nuevo.']);
        }

        $codes->verify($user, CodePurpose::PasswordRecovery, $request->validated('code'));
        $setPassword->handle($user, $request->validated('password'), false, null);
        SecurityLog::record('password.recovered', $user);
        $request->session()->forget('recovery.email');

        return to_route('login')->with('status', 'Listo. Ya podés entrar con tu contraseña nueva.');
    }
}
