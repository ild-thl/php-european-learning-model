<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\Identifier;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IdentifierTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
                    'id' => 'urn:epass:identifier:1',
                    'type' => 'Identifier',
                    'notation' => 'Res1818',
                    'schemeAgency' => 'https://research-alliance.example',
                    'schemeName' => 'Research Alliance ID',
                    'creator' => 'https://research-alliance.example',
                    'schemeVersion' => '1.0',
                    'schemeId' => 'https://research-alliance.example/schemes/identifiers',
                    'issued' => '2024-06-01T12:00:00Z',
                    'dcType' => [[
                        'id' => 'http://data.europa.eu/snb/accreditation/003293d2ce',
                        'type' => 'Concept',
                    ]],
                    'order' => 1,
                ],
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing notation' => [
                [
                    'id' => 'urn:epass:identifier:1',
                    'type' => 'Identifier',
                ],
                InvalidCredentialException::class
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughArrayDeserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [Identifier::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughJsonEncoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [Identifier::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function testInvalidTargetsAreRejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        Identifier::fromArray($invalid);
    }
}
