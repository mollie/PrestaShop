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

use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\MollieApiInputSanitizer;

class NormalizeTypographicCharactersTest extends BaseTestCase
{
    /**
     * @dataProvider sanitizeProvider
     */
    public function testSanitize($input, $expected)
    {
        $this->assertSame($expected, MollieApiInputSanitizer::sanitize($input));
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalizeTypographicCharacters($input, $expected)
    {
        $this->assertSame($expected, MollieApiInputSanitizer::normalizeTypographicCharacters($input));
    }

    public function sanitizeProvider()
    {
        return [
            // === REPORTED BUG ===
            "left single quote in family name (reported bug)" => [
                "D\u{2018} Urso", "D' Urso",
            ],

            // === SMART QUOTES (single) ===
            "right single quote (curly apostrophe)" => ["O\u{2019}Brien", "O'Brien"],
            "single low-9 quote" => ["test\u{201A}value", "test'value"],
            "single left-pointing angle quote" => ["test\u{2039}value", "test'value"],
            "single right-pointing angle quote" => ["test\u{203A}value", "test'value"],

            // === SMART QUOTES (double) ===
            "left/right double quotes" => ["Company \u{201C}Test\u{201D}", 'Company "Test"'],
            "double low-9 + right double quote" => ["Company \u{201E}Test\u{201D}", 'Company "Test"'],
            "guillemets" => ["\u{00AB}Company\u{00BB}", '"Company"'],

            // === DASHES ===
            "en dash" => ["Hans\u{2013}Peter", "Hans-Peter"],
            "em dash" => ["Hans\u{2014}Peter", "Hans-Peter"],
            "unicode hyphen" => ["Hans\u{2010}Peter", "Hans-Peter"],
            "non-breaking hyphen" => ["Hans\u{2011}Peter", "Hans-Peter"],
            "minus sign" => ["Hans\u{2212}Peter", "Hans-Peter"],

            // === SPACES ===
            "non-breaking space" => ["John\u{00A0}Doe", "John Doe"],
            "em space" => ["John\u{2003}Doe", "John Doe"],
            "thin space" => ["John\u{2009}Doe", "John Doe"],

            // === AMPERSAND ===
            "ampersand in company name" => ["Smith & Sons", "Smith and Sons"],

            // === ELLIPSIS ===
            "horizontal ellipsis" => ["Test\u{2026}", "Test..."],

            // === ACCENTED CHARACTERS PRESERVED ===
            "german umlaut" => ["Blümleacker 13", "Blümleacker 13"],
            "french accents" => ["François Müller", "François Müller"],
            "spanish accents" => ["José García", "José García"],
            "polish characters" => ["Łódź Świętokrzyska", "Łódź Świętokrzyska"],
            "czech characters" => ["Jiří Čapek", "Jiří Čapek"],
            "nordic characters" => ["Ångström Øresund", "Ångström Øresund"],

            // === EDGE CASES ===
            "empty string returns default" => ["", "N/A"],
            "null returns default" => [null, "N/A"],
            "whitespace only returns default" => ["   ", "N/A"],
            "plain ASCII unchanged" => ["John Doe", "John Doe"],
            "leading whitespace trimmed" => ["  John Doe", "John Doe"],
            "regular apostrophe preserved" => ["O'Brien", "O'Brien"],
            "regular hyphen preserved" => ["Hans-Peter", "Hans-Peter"],
            "regular double quote preserved" => ['Company "Test"', 'Company "Test"'],

            // === COMBINED ===
            "multiple typographic chars" => [
                "D\u{2019}Urso \u{2013} Blümleacker", "D'Urso - Blümleacker",
            ],
            "smart quotes + ampersand" => [
                "\u{201C}Smith & Sons\u{201D}", '"Smith and Sons"',
            ],

            // === REGEX SAFETY NET ===
            "emoji stripped" => ["John \xF0\x9F\x98\x80 Doe", "John  Doe"],
            "trademark stripped" => ["Company\xE2\x84\xA2", "Company"],
            "copyright stripped" => ["Company\xC2\xA9", "Company"],

            // === ADDRESS PUNCTUATION PRESERVED ===
            "slash" => ["Apt 1/2", "Apt 1/2"],
            "hash" => ["Suite #200", "Suite #200"],
            "parentheses" => ["Building (North)", "Building (North)"],
            "comma" => ["Street 1, Floor 3", "Street 1, Floor 3"],
            "colon" => ["Block A: Room 5", "Block A: Room 5"],
            "period" => ["St. Peter", "St. Peter"],
            "plus" => ["Floor +1", "Floor +1"],
            "at sign" => ["test@email.com", "test@email.com"],
            "semicolon" => ["Line; next", "Line; next"],

            // === TRUNCATION ===
            "truncated to 100 chars" => [str_repeat("A", 150), str_repeat("A", 100)],

            // === REAL-WORLD SCENARIOS ===
            "full name from bug report" => ["Rosemarie D\u{2018} Urso", "Rosemarie D' Urso"],
            "french brand name" => ["L\u{2019}Oréal", "L'Oréal"],
            "german double-barrel with en dash" => ["Müller\u{2013}Schmidt", "Müller-Schmidt"],
            "company with nbsp + ampersand" => [
                "Käsmayr\u{00A0}GmbH\u{00A0}&\u{00A0}Co.", "Käsmayr GmbH and Co.",
            ],
        ];
    }

    public function normalizeProvider()
    {
        return [
            "leaves plain ASCII unchanged" => ["Hello World", "Hello World"],
            "replaces curly apostrophe" => ["O\u{2019}Brien", "O'Brien"],
            "replaces em dash" => ["A\u{2014}B", "A-B"],
            "replaces non-breaking space" => ["A\u{00A0}B", "A B"],
            "strips emoji" => ["Hi \xF0\x9F\x98\x80", "Hi "],
            "preserves accented chars" => ["Blümleacker", "Blümleacker"],
        ];
    }
}
