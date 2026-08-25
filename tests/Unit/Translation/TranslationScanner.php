<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Tests\Unit\Translation;

/**
 * Resolves every translatable string in the module against translations/*.php the same way
 * PrestaShop does at runtime, and reports what each locale is missing.
 *
 * Deliberately free of PHPUnit and PrestaShop so both the guard test and the baseline
 * regeneration script can use it.
 */
class TranslationScanner
{
    const MODULE_NAME = 'mollie';

    /** @var string */
    private $moduleDir;

    public function __construct(string $moduleDir)
    {
        $this->moduleDir = $moduleDir;
    }

    /**
     * @return array<string, string[]> locale => sorted "source :: string" entries, locales sorted
     */
    public function missingByLocale(): array
    {
        $used = $this->usedStrings();

        $missing = [];
        foreach ($this->localeFiles() as $locale => $path) {
            $table = self::loadLocale($path);
            $gaps = [];
            foreach ($used as $source => $strings) {
                foreach (array_keys($strings) as $string) {
                    if (!self::resolves($table, $source, (string) $string)) {
                        $gaps[] = $source . ' :: ' . $string;
                    }
                }
            }
            sort($gaps);
            $missing[$locale] = $gaps;
        }
        ksort($missing);

        return $missing;
    }

    /**
     * @return array<string, string> locale => absolute path
     */
    public function localeFiles(): array
    {
        $files = [];
        foreach ((array) glob($this->moduleDir . '/translations/*.php') as $path) {
            $locale = basename($path, '.php');
            if ('index' === $locale) {
                continue;
            }
            $files[$locale] = $path;
        }
        ksort($files);

        return $files;
    }

    /**
     * The files declare `global $_MODULE`, so the include lands in $GLOBALS. Clearing it first
     * keeps a file that defines nothing from inheriting the previously loaded locale.
     */
    public static function loadLocale(string $path): array
    {
        unset($GLOBALS['_MODULE']);

        $_MODULE = [];
        include $path;

        if (isset($GLOBALS['_MODULE']) && is_array($GLOBALS['_MODULE']) && [] !== $GLOBALS['_MODULE']) {
            return $GLOBALS['_MODULE'];
        }

        return is_array($_MODULE) ? $_MODULE : [];
    }

    /**
     * Mirrors Translate::getModuleTranslation(): the template-scoped key first, then the
     * module-scoped fallback. The theme-scoped variants never apply to these files.
     */
    private static function resolves(array $table, string $source, string $string): bool
    {
        $hash = md5($string);
        $keys = [
            '<{' . self::MODULE_NAME . '}prestashop>' . $source . '_' . $hash,
            '<{' . self::MODULE_NAME . '}prestashop>' . self::MODULE_NAME . '_' . $hash,
        ];

        foreach ($keys as $key) {
            if (isset($table[$key]) && '' !== $table[$key]) {
                return true;
            }
        }

        return false;
    }

    /**
     * The translation source is the template basename for Smarty and the explicit second
     * argument for PHP, matching how the keys are written into translations/*.php.
     *
     * @return array<string, array<string, true>> source => set of raw string literals
     */
    private function usedStrings(): array
    {
        $used = [];

        foreach (self::filesIn($this->moduleDir . '/views/templates', 'tpl') as $path) {
            $source = strtolower(basename($path, '.tpl'));
            $contents = (string) file_get_contents($path);
            $patterns = ["/\{l\s+s='((?:[^'\\\\]|\\\\.)*)'/", '/\{l\s+s="((?:[^"\\\\]|\\\\.)*)"/'];
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $contents, $matches)) {
                    foreach ($matches[1] as $string) {
                        $used[$source][$string] = true;
                    }
                }
            }
        }

        $phpFiles = [$this->moduleDir . '/' . self::MODULE_NAME . '.php'];
        foreach (['src', 'controllers', 'subscription', 'upgrade'] as $dir) {
            $phpFiles = array_merge($phpFiles, self::filesIn($this->moduleDir . '/' . $dir, 'php'));
        }

        foreach ($phpFiles as $path) {
            if (!is_file($path)) {
                continue;
            }
            $contents = (string) file_get_contents($path);
            if (!preg_match_all("/->l\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*(?:,\s*([^)]*?))?\)/s", $contents, $matches, PREG_SET_ORDER)) {
                continue;
            }

            $fileNameConst = null;
            if (preg_match("/FILE_NAME\s*=\s*'([^']+)'/", $contents, $constMatch)) {
                $fileNameConst = $constMatch[1];
            }
            $fallbackSource = strtolower(basename($path, '.php'));

            foreach ($matches as $match) {
                $sourceArg = isset($match[2]) ? trim($match[2]) : '';

                if ('' === $sourceArg) {
                    $source = $fallbackSource;
                } elseif (preg_match("/^'([^']+)'$/", $sourceArg, $literal)) {
                    $source = strtolower($literal[1]);
                } elseif (null !== $fileNameConst && false !== strpos($sourceArg, 'FILE_NAME')) {
                    $source = strtolower($fileNameConst);
                } else {
                    $source = $fallbackSource;
                }

                $used[$source][$match[1]] = true;
            }
        }

        ksort($used);

        return $used;
    }

    /**
     * @return string[]
     */
    private static function filesIn(string $dir, string $extension): array
    {
        if (!is_dir($dir)) {
            return [];
        }

        $paths = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (!$file->isDir() && $extension === $file->getExtension()) {
                $paths[] = $file->getPathname();
            }
        }
        sort($paths);

        return $paths;
    }
}
