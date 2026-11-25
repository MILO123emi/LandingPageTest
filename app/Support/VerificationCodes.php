<?php

namespace App\Support;

use App\Enums\CodePurpose;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class VerificationCodes
{
    public const TTL_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public function issue(User $user, CodePurpose $purpose, ?string $newEmail = null): string
    {
        return DB::transaction(function () use ($user, $purpose, $newEmail): string {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $user->verificationCodes()->where('purpose', $purpose)->delete();

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->verificationCodes()->create([
                'purpose' => $purpose,
                'code_hash' => Hash::make($code),
                'new_email' => $newEmail === null ? null : Str::lower(trim($newEmail)),
                'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            ]);

            return $code;
        });
    }

    public function verify(User $user, CodePurpose $purpose, #[SensitiveParameter] string $code): VerificationCode
    {
        $result = DB::transaction(function () use ($user, $purpose, $code): VerificationCode|ValidationException {
            $record = $user->verificationCodes()->where('purpose', $purpose)->lockForUpdate()->first();

            if ($record === null || $record->expires_at->lte(now()) || $record->attempts >= self::MAX_ATTEMPTS) {
                return ValidationException::withMessages(['code' => 'El código venció o ya no es válido. Pedí uno nuevo.']);
            }

            if (! Hash::check($code, $record->code_hash)) {
                $record->increment('attempts');

                return ValidationException::withMessages(['code' => 'El código no es correcto.']);
            }

            $record->delete();

            return $record;
        });

        // Throw after committing so an incorrect code cannot roll back its attempt.
        if ($result instanceof ValidationException) {
            throw $result;
        }

        return $result;
    }

    public function pending(User $user, CodePurpose $purpose): ?VerificationCode
    {
        return $user->verificationCodes()
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->first();
    }
}
