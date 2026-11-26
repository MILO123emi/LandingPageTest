<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Support\SecurityLog;
use SensitiveParameter;

class SetPassword
{
    public function handle(User $user, #[SensitiveParameter] string $password, bool $mustChange, ?string $keepSessionId = null): void
    {
        $user->forceFill(['password' => $password, 'must_change_password' => $mustChange])->save();

        app(CloseSessions::class)->handle($user, $keepSessionId);
        SecurityLog::record('password.changed', $user, ['temporary' => $mustChange]);
    }
}
