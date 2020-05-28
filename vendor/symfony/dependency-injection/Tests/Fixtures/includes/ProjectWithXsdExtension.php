<?php

namespace _PhpScoper5ece82d7231e4;

class ProjectWithXsdExtension extends \_PhpScoper5ece82d7231e4\ProjectExtension
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
\class_alias('_PhpScoper5ece82d7231e4\\ProjectWithXsdExtension', 'ProjectWithXsdExtension', \false);
