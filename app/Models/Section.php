<?php

namespace App\Models;

use App\Enums\SectionType;
use App\Models\Concerns\BelongsToSite;
use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $site_id
 * @property SectionType $type
 * @property int $position
 * @property bool $is_visible
 * @property array<string, mixed> $content
 * @property array<string, mixed>|null $draft_content
 * @property int|null $draft_position
 * @property bool|null $draft_is_visible
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['site_id', 'type', 'position', 'is_visible', 'content', 'draft_content', 'draft_position', 'draft_is_visible'])]
class Section extends Model
{
    use BelongsToSite;

    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => SectionType::class,
            'is_visible' => 'boolean',
            'content' => 'array',
            'draft_content' => 'array',
            'draft_is_visible' => 'boolean',
        ];
    }

    /** @param Builder<Section> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_visible', true)->orderBy('position')->orderBy('id');
    }

    /** @return list<int> */
    public function mediaIds(): array
    {
        $ids = [];

        foreach ($this->type->mediaFields() as $field) {
            $ids[] = $this->content[$field] ?? null;
        }

        if ($this->type === SectionType::Gallery) {
            foreach ((array) ($this->content['items'] ?? []) as $item) {
                $ids[] = is_array($item) ? ($item['media_id'] ?? null) : null;
            }
        }

        return array_values(array_map('intval', array_filter($ids, 'is_numeric')));
    }
}
