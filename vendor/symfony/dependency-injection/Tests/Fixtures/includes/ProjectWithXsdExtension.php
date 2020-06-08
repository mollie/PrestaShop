<?php

namespace _PhpScoper5eddef0da618a;

class ProjectWithXsdExtension extends \_PhpScoper5eddef0da618a\ProjectExtension
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
\class_alias('_PhpScoper5eddef0da618a\\ProjectWithXsdExtension', 'ProjectWithXsdExtension', \false);
