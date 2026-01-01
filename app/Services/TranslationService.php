<?php

namespace App\Services;

class TranslationService
{
    protected string $basePath;

    public function __construct()
    {
        $this->basePath = resource_path('lang');
    }

    /**
     * Get translations for locale.
     */
    public function get(string $locale): array
    {
        $file = "$this->basePath/$locale/app.php";
        if (!file_exists($file)) {
            return [];
        }
        return include $file;
    }

    /**
     * Update translations for locale (merge & overwrite provided keys)
     */
    public function update(string $locale, array $translations): array
    {
        $current = $this->get($locale);
        $updated = array_merge($current, $translations);
        ksort($updated);
        $this->writeFile($locale, $updated);
        return $updated;
    }

    protected function writeFile(string $locale, array $data): void
    {
        $path = "$this->basePath/$locale";
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $file = "$path/app.php";
        $export = var_export($data, true);
        $content = "<?php\n\nreturn $export;\n";
        file_put_contents($file, $content);
    }
} 