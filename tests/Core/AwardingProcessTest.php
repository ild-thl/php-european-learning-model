<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\AwardingProcess;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AwardingProcessTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
                    'id' => 'urn:epass:awardingProcess:1',
                    'type' => 'AwardingProcess',
                    'educationalSystemNote' => [
                        'id' => 'urn:epass:concept:educationalSystemNote:1',
                        'type' => 'Concept',
                        'prefLabel' => [
                            'en' => [ 'Educational System XY' ]
                        ]
                    ],
                    'awardingDate' => '2024-06-05T00:00:00Z',
                    'description' => ['en' => ['Description']],
                    'location' => [
                        'id' => 'urn:epass:location:1',
                        'type' => 'Location',
                        'address' => [ [
                            'id' => 'urn:epass:address:1',
                            'type' => 'Address',
                            'countryCode' => [
                                'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                'type' => 'Concept',
                                'inScheme' => [
                                    'id' => 'http://publications.europa.eu/resource/authority/country',
                                    'type' => 'ConceptScheme'
                                ],
                                'prefLabel' => [
                                    'en' => [ 'Ireland' ]
                                ],
                                'notation' => 'country'
                            ],
                        ] ],
                    ],
                    'additionalNote' => [[
                        'id' => 'urn:epass:note:1',
                        'type' => 'Note',
                        'noteLiteral' => ['de' => ['Zusätzliche Information']]
                    ]],
                    'awardingBody' => [
                        [
                            'id' => 'urn:epass:org:1',
                            'type' => 'Organisation',
                            'legalName' => [
                                'en' => [ 'Research Alliance' ]
                            ],
                            'location' => [[
                                'id' => 'urn:epass:location:1',
                                'type' => 'Location',
                                'address' => [ [
                                    'id' => 'urn:epass:address:1',
                                    'type' => 'Address',
                                    'countryCode' => [
                                        'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                        'type' => 'Concept',
                                        'inScheme' => [
                                            'id' => 'http://publications.europa.eu/resource/authority/country',
                                            'type' => 'ConceptScheme'
                                        ],
                                        'prefLabel' => [
                                            'en' => [ 'Ireland' ]
                                        ],
                                        'notation' => 'country'
                                    ],
                                ] ],
                            ] ],
                        ],
                        [
                            'id' => 'urn:epass:person:1',
                            'type' => 'Person',
                        ]
                    ],
                    'order' => 1,
                ]
            ],
            'minimal' => [
                [
                    'id' => 'urn:epass:awardingProcess:1',
                    'type' => 'AwardingProcess',
                ]
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing tyoe' => [
                [
                    'id' => 'urn:epass:awardingProcess:1',
                    'awardingBody' => [],
                ],
                InvalidCredentialException::class
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughArrayDeserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [AwardingProcess::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughJsonEncoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [AwardingProcess::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function testInvalidTargetsAreRejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        AwardingProcess::fromArray($invalid);
    }
}
