<?php

namespace MolliePrefix;

class TestGeneratorMaker
{
    public function create($array = [])
    {
        foreach ($array as $key => $value) {
            (yield $key => $value);
        }
    }
}
\class_alias('MolliePrefix\\TestGeneratorMaker', 'TestGeneratorMaker', \false);
