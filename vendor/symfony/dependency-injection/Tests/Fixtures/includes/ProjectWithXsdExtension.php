<?php

namespace _PhpScoper5ea00cc67502b;

use function class_alias;

class ProjectWithXsdExtension extends ProjectExtension
{
    public function getXsdValidationBasePath()
    {
        return __DIR__ . '/schema';
    }
    public function getNamespace()
    {
        return 'http://www.example.com/schema/projectwithxsd';
    }
    public function getAlias()
    {
        return 'projectwithxsd';
    }
}
class_alias('_PhpScoper5ea00cc67502b\\ProjectWithXsdExtension', 'ProjectWithXsdExtension', false);
