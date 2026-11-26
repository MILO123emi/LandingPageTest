<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Position and slug helpers shared by categories and products. */
final class Ordering
{
    /**
     * Moves a row one place up or down inside its group. Positions are renumbered
     * 1..n only where they differ, so after the first move a swap touches two rows.
     *
     * @param  Builder<covariant Model>  $group
     */
    public static function move(Builder $group, Model $model, string $direction): void
    {
        /** @var list<int> $ids */
        $ids = $group->clone()->orderBy('position')->orderBy('id')->pluck('id')->all();
        $index = array_search($model->getKey(), $ids, true);
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || ! isset($ids[$target])) {
            return;
        }

        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];

        $current = $group->clone()->pluck('position', 'id');

        foreach ($ids as $i => $id) {
            if ((int) $current[$id] !== $i + 1) {
                $group->clone()->whereKey($id)->update(['position' => $i + 1]);
            }
        }
    }

    /** @param Builder<covariant Model> $group */
    public static function next(Builder $group): int
    {
        return (int) $group->clone()->max('position') + 1;
    }

    /**
     * URL slug unique inside the current site (the global site scope applies).
     *
     * @param  Builder<covariant Model>  $query
     */
    public static function uniqueSlug(Builder $query, string $name, int $max): string
    {
        $base = Str::limit(Str::slug($name), $max - 4, '') ?: 'item';
        $base = rtrim($base, '-');
        $slug = $base;

        for ($i = 2; $query->clone()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
