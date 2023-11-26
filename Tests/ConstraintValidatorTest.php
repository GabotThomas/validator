<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Tests\Fixtures\TestEnum;

class ConstraintValidatorTest extends TestCase
{
    use IcuCompatibilityTrait;

    /**
     * @dataProvider formatValueProvider
     */
    public function testFormatValue(string $expected, $value, int $format = 0)
    {
        \Locale::setDefault('en');

        $this->assertSame($expected, (new TestFormatValueConstraintValidator())->formatValueProxy($value, $format));
    }

    public static function formatValueProvider(): array
    {
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Moscow'); // GMT+3

        $data = [
            ['true', true],
            ['false', false],
            ['null', null],
            ['resource', fopen('php://memory', 'r')],
            ['"foo"', 'foo'],
            ['array', []],
            ['object', $toString = new TestToStringObject()],
            ['ccc', $toString, ConstraintValidator::OBJECT_TO_STRING],
            ['object', $dateTime = new \DateTimeImmutable('1971-02-02T08:00:00UTC')],
            [class_exists(\IntlDateFormatter::class) ? static::normalizeIcuSpaces("Oct 4, 2019, 11:02\u{202F}AM") : '2019-10-04 11:02:03', new \DateTimeImmutable('2019-10-04T11:02:03+09:00'), ConstraintValidator::PRETTY_DATE],
            [class_exists(\IntlDateFormatter::class) ? static::normalizeIcuSpaces("Feb 2, 1971, 8:00\u{202F}AM") : '1971-02-02 08:00:00', $dateTime, ConstraintValidator::PRETTY_DATE],
            [class_exists(\IntlDateFormatter::class) ? static::normalizeIcuSpaces("Jan 1, 1970, 6:00\u{202F}AM") : '1970-01-01 06:00:00', new \DateTimeImmutable('1970-01-01T06:00:00Z'), ConstraintValidator::PRETTY_DATE],
            [class_exists(\IntlDateFormatter::class) ? static::normalizeIcuSpaces("Jan 1, 1970, 3:00\u{202F}PM") : '1970-01-01 15:00:00', (new \DateTimeImmutable('1970-01-01T23:00:00'))->setTimezone(new \DateTimeZone('America/New_York')), ConstraintValidator::PRETTY_DATE],
        ];

        if (\PHP_VERSION_ID >= 80100) {
            $data[] = ['FirstCase', TestEnum::FirstCase];
        }

        date_default_timezone_set($defaultTimezone);

        return $data;
    }
}

final class TestFormatValueConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
    }

    public function formatValueProxy($value, int $format): string
    {
        return $this->formatValue($value, $format);
    }
}

final class TestToStringObject
{
    public function __toString()
    {
        return 'ccc';
    }
}
