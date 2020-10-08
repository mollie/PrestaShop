# Decimal

[![Build Status](https://api.travis-ci.org/PrestaShop/decimal.svg?branch=master)](https://travis-ci.org/PrestaShop/decimal)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/985899efeb83453babcc507def66c90e)](https://www.codacy.com/app/PrestaShop/decimal?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=PrestaShop/decimal&amp;utm_campaign=Badge_Grade)
[![Codacy Badge](https://api.codacy.com/project/badge/Coverage/985899efeb83453babcc507def66c90e)](https://www.codacy.com/app/PrestaShop/decimal?utm_source=github.com&utm_medium=referral&utm_content=PrestaShop/decimal&utm_campaign=Badge_Coverage)
[![Total Downloads](https://img.shields.io/packagist/dt/prestashop/decimal.svg?style=flat-square)](https://packagist.org/packages/prestashop/decimal)

An object-oriented [BC Math extension](http://php.net/manual/en/book.bc.php) wrapper/shim.

**Decimal** offers a stateless, fluent object-oriented implementation of basic arbitrary-precision arithmetic, using BC Math if available.

You can find out more about floating point precision [here](http://php.net/float).

Example:
```php
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\Decimal\Operation\Rounding;

echo (new DecimalNumber('0.1'))
    ->plus(new DecimalNumber('0.7'))
    ->times(new DecimalNumber('10'))
    ->round(0, Rounding::ROUND_FLOOR)
  
// echoes '8'
```

## Install

Via Composer

``` bash
$ composer require prestashop/decimal
```

## Usage reference

Quick links:
- [Instantiation](#instantiation)
- [Addition](#addition)
- [Subtraction](#subtraction)
- [Multiplication](#multiplication)
- [Division](#division)
- [Comparison](#comparison)
- [Fixed precision](#fixed-precision)
- [Rounding](#rounding)
- [Dot shifting](#dot-shifting)
- [Useful methods](#useful-methods)

### Instantiation
Creates a new Decimal number.
```php
public __construct ( string $number [, int $exponent = null ] ): DecimalNumber
```
There are two ways to instantiate a Decimal\DecimalNumber:
``` php
// create a number from string
$number = new PrestaShop\Decimal\DecimalNumber('123.456');
echo $number; // echoes '123.456'
```
``` php
// exponent notation
$number = new PrestaShop\Decimal\DecimalNumber('123456', -3);
echo $number; // echoes '123.456'
```

### Addition
Returns the computed result of adding another number to the current one.
```php
public DecimalNumber::plus ( DecimalNumber $addend ): DecimalNumber
```
Examples:
```php
$a = new PrestaShop\Decimal\DecimalNumber('123.456');
$b = new PrestaShop\Decimal\DecimalNumber('654.321');

echo $a->plus($b); // echoes '777.777'
```

### Subtraction
Returns the computed result of subtracting another number to the current one.
```php
public DecimalNumber::minus ( DecimalNumber $subtrahend ): DecimalNumber
```
Examples:
```php
$a = new PrestaShop\Decimal\DecimalNumber('777.777');
$b = new PrestaShop\Decimal\DecimalNumber('654.321');

echo $a->minus($b); // echoes '123.456'
```

### Multiplication
Returns the computed result of multiplying the current number with another one.
```php
public DecimalNumber::times ( DecimalNumber $factor ): DecimalNumber
```
Examples:
```php
$a = new PrestaShop\Decimal\DecimalNumber('777.777');
$b = new PrestaShop\Decimal\DecimalNumber('654.321');

echo $a->times($b); // echoes '508915.824417'
```

### Division
Returns the computed result of dividing  the current number by another one, with up to a certain number of decimal positions (6 by default).
```php
public DecimalNumber::dividedBy ( DecimalNumber $divisor [, int $precision = Operation\Division::DEFAULT_PRECISION ] )
```
Examples:
```php
$a = new PrestaShop\Decimal\DecimalNumber('777.777');
$b = new PrestaShop\Decimal\DecimalNumber('654.321');

echo $a->dividedBy($b, 0);  // echoes '1'
echo $a->dividedBy($b, 5);  // echoes '1.18867'
echo $a->dividedBy($b, 10); // echoes '1.1886780341'
echo $a->dividedBy($b, 15); // echoes '1.188678034175886'
```

### Comparison
Returns the result of the comparison assertion.
```php
$a = new PrestaShop\Decimal\DecimalNumber('777.777');
$b = new PrestaShop\Decimal\DecimalNumber('654.321');

$a->equals($b);                // returns false
$a->isLowerThan($b);           // returns false
$a->isLowerOrEqualThan($b);    // returns false
$a->isGreaterThan($b);         // returns true
$a->isGreaterOrEqualThan($b);  // returns true

// shortcut methods
$a->equalsZero();               // returns false
$a->isLowerThanZero();          // returns false 
$a->isLowerOrEqualThanZero();   // returns false
$a->isGreaterThanZero();        // returns true
$a->isGreaterOrEqualThanZero(); // returns true
```

### Fixed precision
Returns the number as a string, optionally rounded, with an exact number of decimal positions.
```php
public DecimalNumber::toPrecision ( int $precision [, string $roundingMode = Rounding::ROUND_TRUNCATE ] ): string
```
Examples:
```php
$a = new PrestaShop\Decimal\DecimalNumber('123.456');
$a = new PrestaShop\Decimal\DecimalNumber('-123.456');

// truncate / pad
$a->toPrecision(0); // '123'
$a->toPrecision(1); // '123.4'
$a->toPrecision(2); // '123.45'
$a->toPrecision(3); // '123.456'
$a->toPrecision(4); // '123.4560'
$b->toPrecision(0); // '-123'
$b->toPrecision(1); // '-123.4'
$b->toPrecision(2); // '-123.45'
$b->toPrecision(3); // '-123.456'
$b->toPrecision(4); // '-123.4560'

// ceil (round up)
$a->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL); // '124'
$a->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL); // '123.5'
$a->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL); // '123.46'
$b->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL); // '-122'
$b->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL); // '-123.3'
$b->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_CEIL); // '-123.44'

// floor (round down)
$a->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_FLOOR); // '123'
$a->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_FLOOR); // '123.4'
$a->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_FLOOR); // '123.45'
$b->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_FLOOR); // '-124'
$b->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_FLOOR); // '-123.5'
$b->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_FLOOR); // '-123.46'

// half-up (symmetric half-up)
$a->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP); // '123'
$a->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP); // '123.5'
$a->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP); // '123.46'
$b->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP); // '-123'
$b->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP); // '-123.5'
$b->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_UP); // '-123.46'

// half-down (symmetric half-down)
$a->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_DOWN); // '123'
$a->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_DOWN); // '123.4'
$a->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_DOWN); // '123.46'
$a->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_DOWN); // '-123'
$a->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_DOWN); // '-123.4'
$a->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_DOWN); // '-123.46'

// half-even
$a->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN); // '123'
$a->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN); // '123.4'
$a->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN); // '123.46'
$a->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN); // '-123'
$a->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN); // '-123.4'
$a->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN); // '-123.46'

$a = new PrestaShop\Decimal\DecimalNumber('1.1525354556575859505');
$a->toPrecision(0, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1'
$a->toPrecision(1, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.2'
$a->toPrecision(2, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.15'
$a->toPrecision(3, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.152'
$a->toPrecision(4, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.1525'
$a->toPrecision(5, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.15255'
$a->toPrecision(6, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.152535'
$a->toPrecision(7, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.1525354'
$a->toPrecision(8, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.15253546'
$a->toPrecision(9, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN);  // '1.152535456'
$a->toPrecision(10, PrestaShop\Decimal\Operation\Rounding::ROUND_HALF_EVEN); // '1.1525354556'
```

### Rounding
Rounding behaves like `toPrecision`, but provides "up to" a certain number of decimal positions
(it does not add trailing zeroes).
```php
public DecimalNumber::round ( int $maxDecimals [, string $roundingMode = Rounding::ROUND_TRUNCATE ] ): string
```
Examples:
```php
$a = new PrestaShop\Decimal\DecimalNumber('123.456');
$a = new PrestaShop\Decimal\DecimalNumber('-123.456');

// truncate / pad
$a->round(0); // '123'
$a->round(1); // '123.4'
$a->round(2); // '123.45'
$a->round(3); // '123.456'
$a->round(4); // '123.456'
$b->round(0); // '-123'
$b->round(1); // '-123.4'
$b->round(2); // '-123.45'
$b->round(3); // '-123.456'
$b->round(4); // '-123.456'
```

### Dot shifting
Creates a new copy of this number multiplied by 10^exponent
```php
public DecimalNumber::toMagnitude ( int $exponent ): DecimalNumber
```
Examples:
```php
$a = new PrestaShop\Decimal\DecimalNumber('123.456789');

// shift 3 digits to the left
$a->toMagnitude(-3); // 0.123456789

// shift 3 digits to the right
$a->toMagnitude(3); // 123456.789
```

### Useful methods
```php
$number = new PrestaShop\Decimal\DecimalNumber('123.45');
$number->getIntegerPart();    // '123'
$number->getFractionalPart(); // '45'
$number->getPrecision();      // '2' (number of decimals)
$number->getSign();           // '' ('-' if the number was negative)
$number->getExponent();       // '2' (always positive)
$number->getCoefficient();    // '123456'
$number->isPositive();        // true
$number->isNegative();        // false
$number->invert();            // new Decimal\DecimalNumber('-123.45')
```

## Testing

``` bash
$ composer install
$ vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [All Contributors](https://github.com/prestashop/decimal/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
