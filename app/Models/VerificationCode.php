<?php

namespace App\Models;

use App\Enums\CodePurpose;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property CodePurpose $purpose
 * @property string $code_hash
 * @property string|null $new_email
 * @property int $attempts
 * @property Carbon $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['purpose', 'code_hash', 'new_email', 'attempts', 'expires_at'])]
#[Hidden(['code_hash'])]
class VerificationCode extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'purpose' => CodePurpose::class,
            'expires_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
