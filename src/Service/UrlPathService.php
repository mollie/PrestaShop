<?php

namespace Mollie\Service;

use Mollie;
use Tools;

class UrlPathService
{

    /**
     * @var Mollie
     */
    private $module;

    public function __construct(Mollie $module)
    {
        $this->module = $module;
    }

    /**
     * Retrieve recursively all classes in a directory and its subdirectories
     *
     * @param string $path Relative path from root to the directory
     *
     * @return array
     *
     * @since 3.3.0
     */
    public function getClassesFromDir($path)
    {
        $classes = [];
        $rootDir = $this->normalizeDirectory(_PS_ROOT_DIR_);

        foreach (scandir($rootDir . $path) as $file) {
            if ($file[0] != '.') {
                if (is_dir($rootDir . $path . $file)) {
                    $classes = array_merge($classes, $this->getClassesFromDir($path . $file . '/'));
                } elseif (Tools::substr($file, -4) == '.php') {
                    $content = Tools::file_get_contents($rootDir . $path . $file);

                    $namespacePattern = '[\\a-z0-9_]*[\\]';
                    $pattern = '#\W((abstract\s+)?class|interface)\s+(?P' . $this->module->display(__FILE__, 'views/templates/front/classname.tpl') . basename($file, '.php') . '(?:Core)?)' . '(?:\s+extends\s+' . $namespacePattern . '[a-z][a-z0-9_]*)?(?:\s+implements\s+' . $namespacePattern . '[a-z][\\a-z0-9_]*(?:\s*,\s*' . $namespacePattern . '[a-z][\\a-z0-9_]*)*)?\s*\{#i';

                    if (preg_match($pattern, $content, $m)) {
                        $classes[$m['classname']] = [
                            'path' => $path . $file,
                            'type' => trim($m[1]),
                            'override' => true,
                        ];

                        if (Tools::substr($m['classname'], -4) == 'Core') {
                            $classes[Tools::substr($m['classname'], 0, -4)] = [
                                'path' => '',
                                'type' => $classes[$m['classname']]['type'],
                                'override' => true,
                            ];
                        }
                    }
                }
            }
        }

        return $classes;
    }

    /**
     * Normalize directory
     *
     * @param string $directory
     *
     * @return string
     *
     * @since 3.3.0
     */
    private function normalizeDirectory($directory)
    {
        return rtrim($directory, '/\\') . DIRECTORY_SEPARATOR;
    }
}