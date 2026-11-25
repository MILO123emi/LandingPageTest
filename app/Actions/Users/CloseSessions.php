<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CloseSessions
{
    public function handle(User $user, ?string $exceptSessionId = null): void
    {
        DB::table('sessions')->where('user_id', $user->id)
            ->when($exceptSessionId !== null, fn ($query) => $query->where('id', '!=', $exceptSessionId))
            ->delete();

        $user->forceFill(['remember_token' => Str::random(60)])->save();
    }
}
