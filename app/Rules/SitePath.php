<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use RuntimeException;

final class SitePath implements ValidationRule
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public static function isWellFormed(string $value): bool
    {
        return strlen($value) <= 40 && preg_match(self::PATTERN, $value) === 1;
    }

    /** @return list<string> */
    public static function reserved(): array
    {
        /** @var list<string> $reserved */
        $reserved = config('vitrina.reserved_paths', []);

        return [...$reserved, (string) config('vitrina.admin_path')];
    }

    public static function isAllowed(string $value): bool
    {
        return self::isWellFormed($value) && ! in_array($value, self::reserved(), true);
    }

    public static function assertValidAdminPath(string $value): void
    {
        /** @var list<string> $reserved */
        $reserved = config('vitrina.reserved_paths', []);

        if (! self::isWellFormed($value) || in_array($value, $reserved, true)) {
            throw new RuntimeException('ADMIN_PATH inválido: usá minúsculas, números y guiones, y que no sea una ruta reservada.');
        }
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::isAllowed($value)) {
            $fail('La ruta no es válida o está reservada.');
        }
    }
}
