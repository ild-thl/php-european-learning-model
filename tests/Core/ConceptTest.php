<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConceptTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'language' => [
                [
                    "id" => "http://publications.europa.eu/resource/authority/language/ENG",
                    "type" => "Concept",
                    "inScheme" => [
                        "id" => "http://publications.europa.eu/resource/authority/language",
                        "type" => "ConceptScheme"
                    ],
                    "prefLabel" => ["en" => [ "English" ]],
                    "notation" => "language",
                    "definition" => ["en" => ["The English language"]],
                ]
            ],
            'only id' => [
                [
                    "id" => "http://publications.europa.eu/resource/authority/language/ENG",
                    'type' => 'Concept',
                ]
            ],
            'skill' => [
                [
                    "id" => "http://data.europa.eu/esco/skill/326809fc-238d-40c2-881e-40042f7f2f0d",
                    "type" => "Concept",
                    "inScheme" => [
                        "id" => "http://data.europa.eu/esco/concept-scheme/skills",
                        "type" => "ConceptScheme"
                    ],
                    "prefLabel" => ["en" => ["establish collaborative relations"]],
                    "notation" => "Skill",
                    "definition" => ["en" => ["Establish a connection between organisations..."]],
                ]
            ]
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'empty notation' => [
                [
                    "id" => "http://publications.europa.eu/resource/authority/language/ENG",
                    'type' => 'Concept',
                    "notation" => ""
                ],
                InvalidCredentialException::class
            ],
            'invalid inScheme type' => [
                [
                    "id" => "http://publications.europa.eu/resource/authority/language/ENG",
                    'type' => 'Concept',
                    "inScheme" => "invalid type"
                ],
                \TypeError::class
            ]
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughArrayDeserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [Concept::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughJsonEncoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [Concept::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function testInvalidTargetsAreRejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        Concept::fromArray($invalid);
    }
}
