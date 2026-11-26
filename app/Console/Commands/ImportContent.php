<?php

namespace App\Console\Commands;

use App\Enums\SectionType;
use App\Enums\SiteStatus;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Section;
use App\Models\Site;
use App\Support\CurrentSite;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Loads a shop from content/{folder}/import.json (PLAN §5.1, F7b). content/ is outside git,
 * so real names, texts and photos never reach the repository. Running it twice replaces the
 * previous import without duplicating media, categories or products.
 */
class ImportContent extends Command
{
    protected $signature = 'vitrina:import-content {folder : Carpeta dentro de content/} {--publish : Publicar de una vez en vez de dejarlo como borrador}';

    protected $description = 'Cargar una tienda desde content/{carpeta}/import.json';

    private string $baseDir = '';

    public function handle(CurrentSite $current): int
    {
        $folder = (string) $this->argument('folder');

        if (preg_match('/^[a-z0-9-]+$/', $folder) !== 1) {
            $this->error('La carpeta solo puede tener minúsculas, números y guiones.');

            return self::FAILURE;
        }

        $root = realpath((string) config('vitrina.content_path'));
        $file = $root !== false ? realpath($root.'/'.$folder.'/import.json') : false;

        if ($root === false || $file === false) {
            $this->error("No encontré content/{$folder}/import.json.");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($file), true);

        if (! is_array($data) || ! is_array($data['site'] ?? null) || ! is_array($data['sections'] ?? null)) {
            $this->error('El import.json no tiene "site" y "sections".');

            return self::FAILURE;
        }

        $this->baseDir = dirname($file);
        $publish = (bool) $this->option('publish');

        try {
            $site = DB::transaction(fn (): Site => $this->import($data, $publish, $current));
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            $current->clear();
        }

        $this->info(($publish ? 'Publicado: ' : 'Cargado como borrador: ').$site->name.' en /'.$site->path);

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $data */
    private function import(array $data, bool $publish, CurrentSite $current): Site
    {
        /** @var array<string, mixed> $info */
        $info = $data['site'];
        $site = Site::query()->firstOrNew(['slug' => (string) $info['slug']]);
        $site->fill(['name' => (string) $info['name'], 'path' => (string) $info['path']]);

        if (! $site->exists) {
            $site->status = SiteStatus::Draft;
            $site->settings = [];
        }

        if ($publish) {
            $site->status = SiteStatus::from((string) ($info['status'] ?? 'live'));
        }

        $site->save();
        $current->set($site);

        $settings = is_array($data['settings'] ?? null) ? $data['settings'] : [];
        $settings['logo_media_id'] = $this->media($site, ['file' => $settings['logo'] ?? null, 'alt' => '']);
        unset($settings['logo']);
        $settings = array_filter($settings, fn (mixed $value): bool => $value !== null);
        $theme = is_array($data['theme'] ?? null) ? $data['theme'] : [];

        $site->fill($publish
            ? ['settings' => $settings, 'theme' => $theme, 'draft_settings' => null, 'draft_theme' => null, 'published_at' => now()]
            : ['draft_settings' => $settings, 'draft_theme' => $theme]);
        $site->save();

        $this->sections($site, $data['sections'], $publish);
        $this->catalog($site, $data);

        return $site;
    }

    /** @param array<int, mixed> $sections */
    private function sections(Site $site, array $sections, bool $publish): void
    {
        Section::query()->delete();

        foreach (array_values($sections) as $position => $row) {
            if (! is_array($row) || SectionType::tryFrom((string) ($row['type'] ?? '')) === null) {
                throw new RuntimeException('Sección desconocida en la posición '.($position + 1).'.');
            }

            $content = $this->resolveMedia($site, is_array($row['content'] ?? null) ? $row['content'] : []);
            $visible = (bool) ($row['visible'] ?? true);

            Section::create($publish
                ? ['type' => $row['type'], 'position' => $position + 1, 'is_visible' => $visible, 'content' => $content]
                : ['type' => $row['type'], 'position' => $position + 1, 'is_visible' => false, 'content' => [],
                    'draft_content' => $content, 'draft_position' => $position + 1, 'draft_is_visible' => $visible]);
        }
    }

    /** @param array<string, mixed> $data */
    private function catalog(Site $site, array $data): void
    {
        $categoryIds = [];

        foreach (array_values((array) ($data['categories'] ?? [])) as $position => $row) {
            $category = Category::query()->firstOrNew(['slug' => (string) $row['slug']]);
            $category->fill([
                'name' => (string) $row['name'],
                'description' => $row['description'] ?? null,
                'media_id' => $this->media($site, ['file' => $row['image'] ?? null, 'alt' => (string) $row['name']]),
                'position' => $position + 1,
                'is_visible' => true,
            ])->save();
            $categoryIds[(string) $row['slug']] = $category->id;
        }

        $productSlugs = [];

        foreach (array_values((array) ($data['products'] ?? [])) as $position => $row) {
            $categoryId = $categoryIds[(string) ($row['category'] ?? '')] ?? null;

            if ($categoryId === null) {
                throw new RuntimeException("El producto {$row['slug']} usa una categoría que no existe.");
            }

            Product::query()->firstOrNew(['slug' => (string) $row['slug']])->fill([
                'category_id' => $categoryId,
                'name' => (string) $row['name'],
                'description' => $row['description'] ?? null,
                'media_id' => $this->media($site, ['file' => $row['image'] ?? null, 'alt' => (string) $row['name']]),
                'is_featured' => (bool) ($row['featured'] ?? false),
                'is_visible' => true,
                'position' => $position + 1,
            ])->save();
            $productSlugs[] = (string) $row['slug'];
        }

        Product::query()->whereNotIn('slug', $productSlugs)->delete();
        Category::query()->whereNotIn('id', array_values($categoryIds))->delete();
    }

    /**
     * Replaces {"file": "...", "alt": "..."} references with media ids, at any depth.
     *
     * @param  array<array-key, mixed>  $content
     * @return array<array-key, mixed>
     */
    private function resolveMedia(Site $site, array $content): array
    {
        foreach ($content as $key => $value) {
            if (is_array($value) && array_key_exists('file', $value)) {
                $content[$key] = $this->media($site, $value);
            } elseif (is_array($value)) {
                $content[$key] = $this->resolveMedia($site, $value);
            }
        }

        return $content;
    }

    /** @param array<string, mixed> $ref */
    private function media(Site $site, array $ref): ?int
    {
        $file = $ref['file'] ?? null;

        if (! is_string($file) || $file === '') {
            return null;
        }

        $path = realpath($this->baseDir.'/'.$file);
        $root = (string) realpath((string) config('vitrina.content_path'));

        if ($path === false || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR)) {
            throw new RuntimeException("La foto {$file} no existe o está fuera de content/.");
        }

        $size = getimagesize($path);

        if ($size === false || ! in_array($size['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException("{$file} no es una imagen JPG, PNG o WebP.");
        }

        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$size['mime']];
        $stored = "sites/{$site->id}/media/".substr((string) sha1_file($path), 0, 16).'/original.'.$extension;
        $disk = Storage::disk('public');

        if (! $disk->exists($stored)) {
            $disk->put($stored, (string) file_get_contents($path));
        }

        $media = Media::query()->firstOrNew(['path_original' => $stored]);
        $media->fill([
            'disk' => 'public',
            'width' => $size[0],
            'height' => $size[1],
            'mime' => $size['mime'],
            'size_bytes' => (int) filesize($path),
        ]);

        // The same photo can be reused (logo, hero, gallery): keep the first description given.
        if ((string) $media->getAttribute('alt') === '') {
            $media->alt = mb_substr((string) ($ref['alt'] ?? ''), 0, 200);
        }

        $media->save();

        return $media->id;
    }
}
