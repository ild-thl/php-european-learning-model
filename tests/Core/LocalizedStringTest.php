<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\JsonLdEncoder;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LocalizedStringTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, string|list<string>>}> */
    public static function validTargets(): array {
        return [
            'course completion' => [
                ['en' => ['Course completion'], 'de' => ['Kursabschluss']],
            ],
            'multiple values' => [
                ['en' => ['Hello', 'Hi']],
            ],
            'three-letter language tag' => [
                ['eng' => ['Course completion']],
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>, 1: class-string<\Throwable>}> */
    public static function invalidTargets(): array {
        return [
            'empty translations' => [[], InvalidCredentialException::class],
            'missing language key' => [['Course completion'], \TypeError::class],
            'malformed language tag' => [['english' => ['Course completion']], InvalidCredentialException::class],
            'empty value' => [['en' => ['']], InvalidCredentialException::class],
            'non-string value' => [['en' => [123]], InvalidCredentialException::class],
        ];
    }

    /** @param array<string, string|list<string>> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughArrayDeserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [LocalizedString::class, 'fromArray']);
    }

    /** @param array<string, string|list<string>> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughJsonEncoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [LocalizedString::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function testInvalidTargetsAreRejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        LocalizedString::fromArray($invalid);
    }

    public function testMultipleValuesAndFallbackArePreserved(): void {
        $localized = new LocalizedString(['en' => ['Hello', 'Hi'], 'de' => 'Hallo']);

        self::assertSame(['Hello', 'Hi'], $localized->toArray()['en']);
        self::assertSame('Hallo', $localized->value('fr', ['de']));
        self::assertSame('Hello', $localized->value('fr'));
    }
}
