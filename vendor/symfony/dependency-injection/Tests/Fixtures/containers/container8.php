<?php

namespace _PhpScoper5ea00cc67502b;

use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder;
use _PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
$container = new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ContainerBuilder(new \_PhpScoper5ea00cc67502b\Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['foo' => '%baz%', 'baz' => 'bar', 'bar' => 'foo is %%foo bar', 'escape' => '@escapeme', 'values' => [\true, \false, null, 0, 1000.3, 'true', 'false', 'null'], 'null string' => 'null', 'string of digits' => '123', 'string of digits prefixed with minus character' => '-123', 'true string' => 'true', 'false string' => 'false', 'binary number string' => '0b0110', 'numeric string' => '-1.2E2', 'hexadecimal number string' => '0xFF', 'float string' => '10100.1', 'positive float string' => '+10100.1', 'negative float string' => '-10100.1']));
return $container;
