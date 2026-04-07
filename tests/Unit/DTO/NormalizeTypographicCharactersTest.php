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

namespace Mollie\Tests\Unit\DTO;

use Mollie\DTO\OrderData;
use Mollie\DTO\PaymentData;
use Mollie\Tests\Unit\BaseTestCase;

class NormalizeTypographicCharactersTest extends BaseTestCase
{
    /**
     * @dataProvider typographicCharactersProvider
     */
    public function testOrderDataNormalizesTypographicCharacters($input, $expected)
    {
        $orderData = $this->createOrderData();

        $result = $this->invokePrivateMethod($orderData, 'cleanUpInput', [$input]);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider typographicCharactersProvider
     */
    public function testPaymentDataNormalizesTypographicCharacters($input, $expected)
    {
        $paymentData = $this->createPaymentData();

        $result = $this->invokePrivateMethod($paymentData, 'cleanUpInput', [$input]);
        $this->assertSame($expected, $result);
    }

    public function typographicCharactersProvider()
    {
        return [
            // === REPORTED BUG: U+2018 LEFT SINGLE QUOTATION MARK ===
            "left single quote in family name (reported bug)" => [
                "D\u{2018} Urso",
                "D' Urso",
            ],

            // === SMART QUOTES (single) ===
            "right single quote (curly apostrophe)" => [
                "O\u{2019}Brien",
                "O'Brien",
            ],
            "single low-9 quote" => [
                "test\u{201A}value",
                "test'value",
            ],
            "single left-pointing angle quote" => [
                "test\u{2039}value",
                "test'value",
            ],
            "single right-pointing angle quote" => [
                "test\u{203A}value",
                "test'value",
            ],

            // === SMART QUOTES (double) ===
            "left double quote" => [
                "Company \u{201C}Test\u{201D}",
                'Company "Test"',
            ],
            "double low-9 quote" => [
                "Company \u{201E}Test\u{201D}",
                'Company "Test"',
            ],
            "left-pointing double angle (guillemet)" => [
                "\u{00AB}Company\u{00BB}",
                '"Company"',
            ],

            // === DASHES AND HYPHENS ===
            "en dash" => [
                "Hans\u{2013}Peter",
                "Hans-Peter",
            ],
            "em dash" => [
                "Hans\u{2014}Peter",
                "Hans-Peter",
            ],
            "unicode hyphen" => [
                "Hans\u{2010}Peter",
                "Hans-Peter",
            ],
            "non-breaking hyphen" => [
                "Hans\u{2011}Peter",
                "Hans-Peter",
            ],
            "minus sign" => [
                "Hans\u{2212}Peter",
                "Hans-Peter",
            ],

            // === SPACES ===
            "non-breaking space" => [
                "John\u{00A0}Doe",
                "John Doe",
            ],
            "em space" => [
                "John\u{2003}Doe",
                "John Doe",
            ],
            "thin space" => [
                "John\u{2009}Doe",
                "John Doe",
            ],

            // === AMPERSAND ===
            "ampersand in company name" => [
                "Smith & Sons",
                "Smith and Sons",
            ],

            // === ELLIPSIS ===
            "horizontal ellipsis" => [
                "Test\u{2026}",
                "Test...",
            ],

            // === ACCENTED CHARACTERS (must be preserved) ===
            "german umlaut in street name" => [
                "Blümleacker 13",
                "Blümleacker 13",
            ],
            "french accented characters" => [
                "François Müller",
                "François Müller",
            ],
            "spanish characters" => [
                "José García",
                "José García",
            ],
            "polish characters" => [
                "Łódź Świętokrzyska",
                "Łódź Świętokrzyska",
            ],
            "czech characters" => [
                "Jiří Čapek",
                "Jiří Čapek",
            ],
            "nordic characters" => [
                "Ångström Øresund",
                "Ångström Øresund",
            ],

            // === EDGE CASES ===
            "empty string returns default" => [
                "",
                "N/A",
            ],
            "null returns default" => [
                null,
                "N/A",
            ],
            "whitespace only returns default" => [
                "   ",
                "N/A",
            ],
            "plain ASCII unchanged" => [
                "John Doe",
                "John Doe",
            ],
            "leading whitespace trimmed" => [
                "  John Doe",
                "John Doe",
            ],
            "regular apostrophe preserved" => [
                "O'Brien",
                "O'Brien",
            ],
            "regular hyphen preserved" => [
                "Hans-Peter",
                "Hans-Peter",
            ],
            "regular double quote preserved" => [
                'Company "Test"',
                'Company "Test"',
            ],

            // === COMBINED CASES ===
            "multiple typographic chars in one string" => [
                "D\u{2019}Urso \u{2013} Blümleacker",
                "D'Urso - Blümleacker",
            ],
            "smart quotes around company with ampersand" => [
                "\u{201C}Smith & Sons\u{201D}",
                '"Smith and Sons"',
            ],
            "non-breaking space with curly apostrophe" => [
                "O\u{2019}Brien\u{00A0}Jr.",
                "O'Brien Jr.",
            ],

            // === REGEX SAFETY NET: exotic characters stripped ===
            "emoji stripped" => [
                "John 😀 Doe",
                "John  Doe",
            ],
            "trademark symbol stripped" => [
                "Company™",
                "Company",
            ],
            "copyright symbol stripped" => [
                "Company©",
                "Company",
            ],
            "registered symbol stripped" => [
                "Company®",
                "Company",
            ],
            "bullet point stripped" => [
                "Test•Value",
                "TestValue",
            ],

            // === TRUNCATION (100 char limit) ===
            "long string truncated to 100 chars" => [
                str_repeat("A", 150),
                str_repeat("A", 100),
            ],

            // === ADDRESS PUNCTUATION PRESERVED ===
            "slash in address" => [
                "Apt 1/2",
                "Apt 1/2",
            ],
            "hash in address" => [
                "Suite #200",
                "Suite #200",
            ],
            "parentheses in address" => [
                "Building (North)",
                "Building (North)",
            ],
            "comma in address" => [
                "Street 1, Floor 3",
                "Street 1, Floor 3",
            ],
            "colon in address" => [
                "Block A: Room 5",
                "Block A: Room 5",
            ],
            "period in address" => [
                "St. Peter",
                "St. Peter",
            ],
            "plus in address" => [
                "Floor +1",
                "Floor +1",
            ],
        ];
    }

    private function createOrderData()
    {
        $amount = $this->mock(\Mollie\DTO\Object\Amount::class);

        return new OrderData($amount, 'https://example.com/redirect', 'https://example.com/webhook');
    }

    private function createPaymentData()
    {
        $amount = $this->mock(\Mollie\DTO\Object\Amount::class);

        return new PaymentData($amount, 'test', 'https://example.com/redirect', 'https://example.com/webhook');
    }

    private function invokePrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
