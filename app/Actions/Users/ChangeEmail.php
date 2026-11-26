<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Support\SecurityLog;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChangeEmail
{
    public function handle(User $user, string $email, ?string $keepSessionId = null): void
    {
        $email = Str::lower(trim($email));

        DB::transaction(function () use ($user, $email): void {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (User::query()->where('email', $email)->whereKeyNot($user->id)->lockForUpdate()->exists()) {
                throw new DomainException('Ese correo ya lo usa otra cuenta.');
            }

            $user->forceFill(['email' => $email])->save();
            $user->verificationCodes()->delete();
        });

        app(CloseSessions::class)->handle($user, $keepSessionId);
        SecurityLog::record('email.changed', $user);
    }
}
