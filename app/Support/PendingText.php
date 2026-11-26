<?php

namespace App\Support;

/**
 * Splits plain text into lines and segments so Blade can print it escaped and still
 * highlight "[PENDIENTE: …]" markers (PLAN §5.1) without ever using raw output.
 */
final class PendingText
{
    /** @return list<list<array{pending: bool, text: string}>> lines of segments */
    public static function lines(?string $text): array
    {
        $lines = [];

        foreach (preg_split('/\R/u', trim((string) $text)) ?: [] as $line) {
            $lines[] = self::segments($line);
        }

        return $lines;
    }

    /** @return list<array{pending: bool, text: string}> */
    public static function segments(string $line): array
    {
        $parts = preg_split('/(\[PENDIENTE:[^\]]*\])/u', $line, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) ?: [];

        return array_map(
            fn (string $part): array => ['pending' => str_starts_with($part, '[PENDIENTE:'), 'text' => $part],
            $parts,
        );
    }

    public static function isPending(?string $text): bool
    {
        return $text !== null && str_contains($text, '[PENDIENTE:');
    }
}
