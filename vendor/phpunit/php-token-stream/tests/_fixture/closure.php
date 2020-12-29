<?php

namespace MolliePrefix;

$function1 = function ($foo, $bar) use($var) {
};
$function2 = function (\MolliePrefix\Foo $foo, $bar) use($var) {
};
$function3 = function ($foo, $bar, $baz) {
};
$function4 = function (\MolliePrefix\Foo $foo, $bar, $baz) {
};
$function5 = function () {
};
$function6 = function () {
};
