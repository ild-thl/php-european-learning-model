<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConceptSchemeTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'language' => [
                [
                    "id" => "http://publications.europa.eu/resource/authority/language",
                    "type" => "ConceptScheme"
                ]
            ],
            'skill' => [
                [
                    "id" => "http://data.europa.eu/esco/concept-scheme/skills",
                    "type" => "ConceptScheme"
                ]
            ]
        ];
    }

    /** @return array<string, array{0: array<string, mixed>, 1: class-string<\Throwable>}> */
    public static function invalidTargets(): array {
        return [
            'empty id' => [
                [
                    "id" => "",
                    "type" => "ConceptScheme"
                ],
                InvalidCredentialException::class
            ],
            'missing type' => [
                [
                    "id" => "http://data.europa.eu/esco/concept-scheme/skills"
                ],
                InvalidCredentialException::class
            ]
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughArrayDeserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [ConceptScheme::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughJsonEncoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [ConceptScheme::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function testInvalidTargetsAreRejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        ConceptScheme::fromArray($invalid);
    }
}
