<?php

namespace MolliePrefix;

$globalString = 'Hello';
$globalIntTruthy = 1;
$globalIntFalsey = 0;
$globalFloat = 1.123;
$globalBoolTrue = \true;
$globalBoolFalse = \false;
$globalNull = null;
$globalArray = ['foo'];
$globalNestedArray = [['foo']];
$globalObject = (object) ['foo' => 'bar'];
$globalObjectWithBackSlashString = (object) ['foo' => 'MolliePrefix\\back\\slash'];
$globalObjectWithDoubleBackSlashString = (object) ['foo' => 'MolliePrefix\\back\\slash'];
