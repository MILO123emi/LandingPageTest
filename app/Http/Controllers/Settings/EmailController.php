<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Users\ChangeEmail;
use App\Enums\CodePurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\EmailChangeRequest;
use App\Http\Requests\Settings\EmailConfirmRequest;
use App\Mail\VerificationCodeMail;
use App\Support\MailGuard;
use App\Support\SecurityLog;
use App\Support\VerificationCodes;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EmailController extends Controller
{
    public function edit(Request $request, VerificationCodes $codes): Response
    {
        return Inertia::render('settings/Email', [
            'pendingEmail' => $codes->pending($request->user(), CodePurpose::EmailChange)?->new_email,
        ]);
    }

    public function store(EmailChangeRequest $request, VerificationCodes $codes): RedirectResponse
    {
        MailGuard::ensureDeliverable();
        $email = $request->validated('email');
        $code = $codes->issue($request->user(), CodePurpose::EmailChange, $email);
        Mail::to($email)->send(new VerificationCodeMail($code, CodePurpose::EmailChange));
        SecurityLog::record('email.code_sent', $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => "Te mandamos un código a {$email}."]);

        return back();
    }

    public function update(EmailConfirmRequest $request, VerificationCodes $codes, ChangeEmail $changeEmail): RedirectResponse
    {
        $record = $codes->verify($request->user(), CodePurpose::EmailChange, $request->validated('code'));

        try {
            $changeEmail->handle($request->user(), (string) $record->new_email, $request->session()->getId());
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['code' => $e->getMessage()]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Correo actualizado.']);

        return to_route('email.edit');
    }
}
