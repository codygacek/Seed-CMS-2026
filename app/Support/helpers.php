<?php

declare(strict_types=1);

use App\Models\NavMenu;
use App\Models\Setting;
use App\Models\WidgetContainer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

//
// ---------------------------------------------------------
// Image resize (legacy, on-demand + cached)
// ---------------------------------------------------------
// Keeps the same public URL behavior using /cache/... under public/.
//
// Requirements:
// - ImageMagick "convert" available on server (PATH_TO_CONVERT)
// - public/cache and public/cache/remote writable
//

if (! function_exists('resize')) {
    function resize(string $imagePath, ?array $opts = null): string|false
    {
        $imagePath = urldecode(trim($imagePath));

        $defaults = [
            'crop' => false,
            'scale' => false,
            'thumbnail' => false,
            'maxOnly' => false,
            'canvas-color' => 'transparent',
            'output-filename' => false,
            'quality' => 90,
            'cache_http_minutes' => 20,
            'cacheFolder' => public_path('cache') . DIRECTORY_SEPARATOR,
            'remoteFolder' => public_path('cache/remote') . DIRECTORY_SEPARATOR,
        ];

        $opts = array_merge($defaults, $opts ?? []);

        File::ensureDirectoryExists($opts['cacheFolder']);
        File::ensureDirectoryExists($opts['remoteFolder']);

        $pathToConvert = env('PATH_TO_CONVERT', '/usr/bin/convert');

        $toPublicUrl = function (string $localPath): string {
            $relative = str_replace(public_path(), '', $localPath);
            $relative = '/' . ltrim(str_replace(DIRECTORY_SEPARATOR, '/', $relative), '/');
            return url($relative);
        };

        // ---- 1) Remote image support (http/https) ----
        $purl = parse_url($imagePath);
        if (! empty($purl['scheme']) && in_array($purl['scheme'], ['http', 'https'], true)) {
            $basename = basename(parse_url($imagePath, PHP_URL_PATH) ?: 'remote');
            $localFilepath = $opts['remoteFolder'] . $basename;

            $download = true;
            if (File::exists($localFilepath)) {
                $ageMinutes = (time() - File::lastModified($localFilepath)) / 60;
                if ($ageMinutes < (int) $opts['cache_http_minutes']) {
                    $download = false;
                }
            }

            if ($download) {
                $resp = Http::timeout(15)->get($imagePath);
                if (! $resp->ok()) {
                    return false;
                }
                File::put($localFilepath, $resp->body());
            }

            $imagePath = $localFilepath;
        } else {
            // Strip any accidental domain that was passed in (rare but happens)
            $imagePath = preg_replace('#^https?://[^/]+#', '', $imagePath) ?? $imagePath;
        }

        // ---- 2) Normalize common legacy/public formats ----
        // Accept:
        //  - "/storage/media/foo.jpg"
        //  - "storage/media/foo.jpg"
        //  - "media/foo.jpg"
        //  - "foo.jpg" (legacy media_assets.file)
        //  - absolute paths like "/Users/.../public/storage/media/foo.jpg"
        $normalized = trim($imagePath);

        // If absolute path already under public/, use it as-is
        if (str_starts_with($normalized, public_path())) {
            $localPath = $normalized;
        } else {
            $normalized = '/' . ltrim($normalized, '/'); // ensure leading slash for comparisons

            if (str_starts_with($normalized, '/storage/')) {
                // public URL -> local path
                $localPath = public_path(ltrim($normalized, '/')); // "storage/..."
            } elseif (str_starts_with($normalized, '/media/')) {
                // old/new "media/..." style, treat as public disk under /storage
                $localPath = public_path('storage' . $normalized); // "storage/media/..."
            } elseif ($normalized === '/' . basename($normalized)) {
                // It's just "file.jpg" (no slashes) -> old CMS media folder
                $localPath = public_path('storage/media/' . ltrim($normalized, '/'));
            } else {
                // Generic relative path -> try under public/ first
                $localPath = public_path(ltrim($normalized, '/'));
            }
        }

        // ---- 3) If still missing, try smart remaps (public/storage + old "/media" dir) ----
        if (! File::exists($localPath)) {
            // If they passed "media/foo.jpg" without /storage, try storage
            $tryStorage = public_path('storage/' . ltrim($imagePath, '/'));
            if (File::exists($tryStorage)) {
                $localPath = $tryStorage;
            } else {
                // If someone used public_path().'/media/foo.jpg' historically
                // try mapping it to public/storage/media/foo.jpg
                $relativeFromPublic = ltrim(str_replace(public_path(), '', $localPath), '/');
                $tryStorage2 = public_path('storage/' . $relativeFromPublic);
                if (File::exists($tryStorage2)) {
                    $localPath = $tryStorage2;
                } else {
                    // Final fallback: return the original *public URL* form,
                    // but NEVER return a URL containing a filesystem path.
                    if (str_starts_with($imagePath, public_path())) {
                        return $toPublicUrl($imagePath);
                    }

                    $publicish = '/' . ltrim($imagePath, '/');

                    // If it looks like legacy "/storage/...", return it.
                    if (str_starts_with($publicish, '/storage/')) {
                        return url($publicish);
                    }

                    // If it looks like "media/...", return "/storage/media/..."
                    if (str_starts_with($publicish, '/media/')) {
                        return url('/storage' . $publicish);
                    }

                    // If it's just "file.jpg", assume legacy media folder
                    if ($publicish === '/' . basename($publicish)) {
                        return url('/storage/media/' . ltrim($publicish, '/'));
                    }

                    // Otherwise return a sane absolute URL to that relative path
                    return url($publicish);
                }
            }
        }

        // ---- 4) If no size requested, return original (as public URL) ----
        $w = $opts['w'] ?? null;
        $h = $opts['h'] ?? null;

        if (empty($w) && empty($h)) {
            return $toPublicUrl($localPath);
        }

        $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION) ?: '');
        if ($ext === '') {
            return $toPublicUrl($localPath);
        }

        // ---- 5) Build cache path ----
        $filenameHash = md5_file($localPath);

        if ($opts['output-filename'] !== false) {
            $newPath = (string) $opts['output-filename'];
        } else {
            if (! empty($w) && ! empty($h)) {
                $newPath = $opts['cacheFolder']
                    . $filenameHash
                    . '_w' . (int) $w
                    . '_h' . (int) $h
                    . ($opts['crop'] ? '_cp' : '')
                    . ($opts['scale'] ? '_sc' : '')
                    . '.' . $ext;
            } elseif (! empty($w)) {
                $newPath = $opts['cacheFolder'] . $filenameHash . '_w' . (int) $w . '.' . $ext;
            } else {
                $newPath = $opts['cacheFolder'] . $filenameHash . '_h' . (int) $h . '.' . $ext;
            }
        }

        // If convert isn't installed, just return original image URL
        if (! File::exists($pathToConvert)) {
            return $toPublicUrl($localPath);
        }

        // ---- 6) Generate resized cache image if needed ----
        $shouldCreate = ! File::exists($newPath) || File::lastModified($newPath) < File::lastModified($localPath);

        if ($shouldCreate) {
            if (! empty($w) && ! empty($h)) {
                [$width, $height] = getimagesize($localPath);

                $resize = ($width > $height) ? (string) $w : ('x' . (string) $h);
                if ($opts['crop']) {
                    $resize = ($width > $height) ? ('x' . (string) $h) : (string) $w;
                }

                if ($opts['scale']) {
                    $cmd = $pathToConvert . ' ' . escapeshellarg($localPath)
                        . ' -resize ' . escapeshellarg($resize)
                        . ' -quality ' . escapeshellarg((string) $opts['quality'])
                        . ' ' . escapeshellarg($newPath);
                } else {
                    $cmd = $pathToConvert . ' ' . escapeshellarg($localPath)
                        . ' -resize ' . escapeshellarg($resize)
                        . ' -size ' . escapeshellarg(((int) $w) . 'x' . ((int) $h))
                        . ' xc:' . escapeshellarg((string) $opts['canvas-color'])
                        . ' +swap -gravity center -composite'
                        . ' -quality ' . escapeshellarg((string) $opts['quality'])
                        . ' ' . escapeshellarg($newPath);
                }
            } else {
                $thumb = (! empty($h) ? 'x' : '') . (int) $w;

                $cmd = $pathToConvert . ' ' . escapeshellarg($localPath)
                    . ' -thumbnail ' . $thumb
                    . ($opts['maxOnly'] ? '\>' : '')
                    . ' -quality ' . escapeshellarg((string) $opts['quality'])
                    . ' ' . escapeshellarg($newPath);
            }

            exec($cmd, $output, $code);

            if ($code !== 0 || ! File::exists($newPath)) {
                // If resizing fails, fall back to original
                return $toPublicUrl($localPath);
            }
        }

        // Return public URL to cached resized image
        return $toPublicUrl($newPath);
    }
}

//
// ---------------------------------------------------------
// Settings + theming helpers (backwards compatible)
// ---------------------------------------------------------
// Assumes you have a "themes" disk pointing to public/themes
//

if (! function_exists('get_setting')) {
    function get_setting(string $key): mixed
    {
        return Setting::query()->where('key', $key)->value('value');
    }
}

if (! function_exists('get_theme')) {
    function get_theme(): string
    {
        return get_setting('theme') ?: 'default';
    }
}

if (! function_exists('theme_uri')) {
    function theme_uri(string $path = ''): string
    {
        $path = ltrim($path, '/');

        if (get_setting('theme') && Storage::disk('themes')->exists(get_theme() . '/' . $path)) {
            return '/themes/' . get_theme() . '/' . $path;
        }

        return '/themes/default/' . $path;
    }
}

if (! function_exists('layouts_uri')) {
    function layouts_uri(string $path = ''): string
    {
        $path = str_replace('.', '/', $path);

        if (Storage::disk('themes')->exists(get_theme() . '/layouts/' . $path . '.blade.php')) {
            return get_theme() . '/layouts/' . $path;
        }

        return 'default/layouts/' . $path;
    }
}

if (! function_exists('get_layouts')) {
    function get_layouts(): array
    {
        $defaultFiles = Storage::disk('themes')->files('default/layouts');
        $themeFiles = Storage::disk('themes')->files(get_theme() . '/layouts');

        $layoutFiles = array_unique(array_merge($defaultFiles, $themeFiles));

        $available = [];

        foreach ($layoutFiles as $file) {
            $fullPath = public_path('themes/' . ltrim($file, '/'));

            if (! File::exists($fullPath)) {
                continue;
            }

            // Scan for: "Template Name: X --}}"
            foreach (File::lines($fullPath) as $line) {
                if (preg_match('/(?<=Template Name: ).*(?= --}})/i', $line, $match)) {
                    $identifier = pathinfo($file, PATHINFO_FILENAME);
                    $identifier = explode('.', $identifier)[0];

                    $available[$identifier] ??= $match[0];
                    break;
                }
            }
        }

        ksort($available);

        return $available;
    }
}

//
// ---------------------------------------------------------
// Navigation menus (HTML output like legacy CMS)
// ---------------------------------------------------------
//

if (! function_exists('navigation_menu')) {
    function navigation_menu(?string $identifier = null, string $menu_class = 'main-menu'): string
    {
        $navMenu = NavMenu::query()->where('slug', $identifier)->first();

        if (! $navMenu) {
            return '<div style="background-color:#f8fafc;padding:1rem;">You have not created the menu for this location yet.</div>';
        }

        return menu_loop($navMenu->top_level_items, $menu_class);
    }
}

if (! function_exists('menu_loop')) {
    function menu_loop($items, string $class = 'main-menu'): string
    {
        $menu = '<ul class="' . e($class) . '">';

        foreach ($items as $menu_item) {
            $children = $menu_item->children;
            $hasChildren = $children && $children->count();

            $menu .= '<li class="nav-item">';
            $menu .= '<a class="nav-item-link" href="' . e($menu_item->link) . '"';
            if ($menu_item->new_window) {
                $menu .= ' target="_blank" rel="noopener noreferrer"';
            }
            $menu .= '>' . e($menu_item->label);

            if ($hasChildren) {
                $menu .= '<span class="menu-drop-icon"><i class="fas fa-angle-down fa-fw"></i></span>';
            }

            $menu .= '</a>';

            if ($hasChildren) {
                $menu .= menu_loop($children, 'sub-menu');
            }

            $menu .= '</li>';
        }

        $menu .= '</ul>';

        return $menu;
    }
}

//
// ---------------------------------------------------------
// Widgets (renders widget partials; falls back to frontend widgets)
// ---------------------------------------------------------
//

if (! function_exists('widgets')) {
    function widgets(?string $identifier = null): string
    {
        $container = WidgetContainer::query()->with('widgets')->where('slug', $identifier)->first();

        if (! $container) {
            return '';
        }

        $options = is_array($container->options) ? $container->options : json_decode((string) $container->options, true) ?? [];

        $containerClass = $options['container_class'] ?? '';
        $showTitle = (bool) ($options['show_title'] ?? false);
        $titleClass = $options['title_class'] ?? '';

        $html = '<div class="widgets-container ' . e($containerClass) . '">';

        if ($showTitle) {
            $html .= '<div class="' . e($titleClass) . '">' . e($container->title) . '</div>';
        }

        foreach ($container->widgets ?? [] as $widget) {
            $widgetOptions = is_array($widget->options) ? $widget->options : json_decode((string) $widget->options, true) ?? [];

            $html .= view()->first([
                get_theme() . '.widgets.' . $widget->type,
                'frontend.widgets.' . $widget->type,
            ])->with([
                'widget' => $widget,
                'options' => (object) $widgetOptions,
            ])->render();
        }

        $html .= '</div>';

        return $html;
    }
}

//
// ---------------------------------------------------------
// Legacy get_members() external API logic
// (Kept for compatibility; modernized curl -> Http client)
// ---------------------------------------------------------
//

if (! function_exists('get_members')) {
    function get_members(?string $group = null, $page = null): array
    {
        if (! $group) {
            return [];
        }

        $type = 'lost';
        $slug = $page->slug ?? '';

        if (str_contains($slug, 'email')) {
            $type = 'no_email';
        } elseif (str_contains($slug, 'cell')) {
            $type = 'no_cell';
        }

        $url = 'https://gms.fmgtucson.com/donor_advantage/api/member.jsp?filter=in_chapter=1';

        $token = (string) env('MEMBER_TOKEN', '');
        $resp = Http::timeout(20)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])
            ->get($url);

        if (! $resp->ok()) {
            return [];
        }

        $list = $resp->json();
        if (! is_array($list)) {
            return [];
        }

        usort($list, 'date_compare');

        // Move blank dates to end
        $dateType = (string) env('CLIENT_DATE_TYPE', 'init_date');
        foreach ($list as $k => $item) {
            if (($item[$dateType] ?? '') === '') {
                unset($list[$k]);
                $list[] = $item;
            }
        }
        $list = array_values($list);

        $list = array_filter($list, 'alumni_only');

        return array_values(array_filter($list, $type));
    }
}

if (! function_exists('alumni_only')) {
    function alumni_only(array $record): bool
    {
        return ($record['code'] ?? null) === 'A';
    }
}

if (! function_exists('lost')) {
    function lost(array $record): bool
    {
        return ($record['status'] ?? null) === 'L';
    }
}

if (! function_exists('no_email')) {
    function no_email(array $record): bool
    {
        $status = $record['status'] ?? null;
        $hasEmail = array_key_exists('email', $record);

        return in_array($status, ['A', 'L'], true) && ! $hasEmail;
    }
}

if (! function_exists('no_cell')) {
    function no_cell(array $record): bool
    {
        $status = $record['status'] ?? null;

        $phones = $record['phone'] ?? [];
        $types = is_array($phones) ? Arr::pluck($phones, 'type') : [];

        $hasCell = in_array('Cell', $types, true);

        return in_array($status, ['A', 'L'], true) && (! is_array($phones) || empty($phones) || ! $hasCell);
    }
}

if (! function_exists('date_compare')) {
    function date_compare(array $a, array $b): int
    {
        $dateType = (string) env('CLIENT_DATE_TYPE', 'init_date');

        $date1 = (string) ($a[$dateType] ?? '');
        $date2 = (string) ($b[$dateType] ?? '');

        $y1 = (int) (explode('-', $date1)[0] ?? 0);
        $y2 = (int) (explode('-', $date2)[0] ?? 0);

        return $y1 <=> $y2;
    }
}

if (! function_exists('two_digit_year_from_date_string')) {
    function two_digit_year_from_date_string(string $year): string
    {
        if (strlen($year) <= 2) {
            return $year;
        }

        $y = explode('-', $year)[0] ?? $year;

        return substr($y, 2, 2);
    }
}

//
// ---------------------------------------------------------
// SweetAlert builder helper (your current pattern)
// ---------------------------------------------------------
//

if (! function_exists('swal')) {
    function swal(): \App\Support\SwalBuilder
    {
        return new \App\Support\SwalBuilder();
    }
}