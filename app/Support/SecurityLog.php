<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class SecurityLog
{
    /** @param array<string, mixed> $context */
    public static function record(string $event, User $user, array $context = []): void
    {
        Log::channel('security')->info($event, [
            'user_id' => $user->id,
            'origin' => app()->runningInConsole() ? 'console' : 'web',
            'ip' => request()->ip(),
            ...$context,
        ]);
    }
}
