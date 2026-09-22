<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\Organisation;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OrganisationTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
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
                    'identifier' => [[
                        'id' => 'urn:epass:identifier:1',
                        'type' => 'Identifier',
                        'notation' => 'Res1818',
                        'schemeName' => 'Research Alliance ID'
                    ]],
                    'altLabel' => [
                        'en' => [ 'RA' ]
                    ],
                    'eidasLegalIdentifier' => [
                        'id' => 'urn:epass:legalIdentifier:1',
                        'type' => 'LegalIdentifier',
                        'notation' => '0000',
                        'spatial' => [
                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                            'type' => 'Concept',
                        ],
                    ],
                    'subOrganizationOf' => [
                        'id' => 'urn:epass:org:2',
                        'type' => 'Organisation',
                        'legalName' => [ 'en' => [ 'Parent Organization' ] ],
                        'location' => [[
                            'id' => 'urn:epass:location:1',
                            'type' => 'Location',
                            'address' => [ [
                                'id' => 'urn:epass:address:1',
                                'type' => 'Address',
                                'countryCode' => [
                                    'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                    'type' => 'Concept',
                                ],
                            ] ],
                        ]],
                    ],
                    'registration' => [
                        'id' => 'urn:epass:legalIdentifier:2',
                        'type' => 'LegalIdentifier',
                        'notation' => '0000',
                        'spatial' => [
                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                            'type' => 'Concept',
                        ],
                    ],
                    'logo' => [
                        'id' => 'urn:epass:mediaObject:1',
                        'type' => 'MediaObject',
                        'content' => '...',
                        'contentEncoding' => [
                            'id' => 'http://data.europa.eu/snb/encoding/6146cde7dd',
                            'type' => 'Concept',
                        ],
                        'contentType' => [
                            'id' => 'http://publications.europa.eu/resource/authority/file-type/PNG',
                            'type' => 'Concept',
                        ],
                    ],
                    'dcType' => [[
                        'id' => ' http://publications.europa.eu/resource/authority/organization-type/COMPANY',
                        'type' => 'Concept',
                    ]],
                    'additionalNote' => [[
                        'id' => 'urn:epass:note:1',
                        'type' => 'Note',
                        'noteLiteral' => ['en' => ['This is an additional note.']],
                    ]],
                    'homepage' => [[
                        'id' => 'urn:epass:webResource:1',
                        'type' => 'WebResource',
                        'contentUrl' => 'https://learningprovider.edu',
                    ]],
                    'hasSubOrganization' => [[
                        'id' => 'urn:epass:org:3',
                        'type' => 'Organisation',
                        'legalName' => [
                            'en' => [ 'Sub Organization' ]
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
                                ],
                            ] ],
                        ]],
                    ]],
                    'taxIdentifier' => [[
                        'id' => 'urn:epass:legalIdentifier:3',
                        'type' => 'LegalIdentifier',
                        'notation' => '0000',
                        'spatial' => [
                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                            'type' => 'Concept',
                        ],
                    ]],
                    'contactPoint' => [[
                        'id' => 'urn:epass:contactPoint:1',
                        'type' => 'ContactPoint',
                    ]],
                    'vatIdentifier' => [[
                        'id' => 'urn:epass:legalIdentifier:3',
                        'type' => 'LegalIdentifier',
                        'notation' => '0000',
                        'spatial' => [
                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                            'type' => 'Concept',
                        ],
                    ]],
                    'accreditation' => [[
                        'id' => 'urn:epass:accreditation:1',
                        'type' => 'Accreditation',
                        'title' => [
                            'en' => [ 'Accreditation Title' ]
                        ],
                        'accreditingAgent' => [
                            'id' => 'urn:epass:org:1',
                            'type' => 'Organisation',
                            'legalName' => [
                                'en' => [ 'Accrediting Agent Legal Name' ]
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
                                    ],
                                ] ],
                            ] ],
                        ],
                        'dcType' => [
                            'id' => 'http://data.europa.eu/snb/accreditation/003293d2ce',
                            'type' => 'Concept',
                        ],
                    ]],
                    'order' => 1,
                    'modified' => '2024-06-01T12:00:00Z',
                ]
            ],
            'minimal' => [
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
                            ],
                        ] ],
                    ] ],
                ]
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing legalName' => [
                [
                    'id' => 'urn:epass:org:1',
                    'type' => 'Organisation',
                    'location' => [[
                        'id' => 'urn:epass:location:1',
                        'type' => 'Location',
                        'address' => [ [
                            'id' => 'urn:epass:address:1',
                            'type' => 'Address',
                            'countryCode' => [
                                'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                'type' => 'Concept',
                            ],
                        ] ],
                    ] ],
                ],
                InvalidCredentialException::class
            ],
            'missing location' => [
                [
                    'id' => 'urn:epass:org:1',
                    'type' => 'Organisation',
                    'legalName' => [
                        'en' => [ 'Research Alliance' ]
                    ],
                ],
                InvalidCredentialException::class
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughArrayDeserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [Organisation::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughJsonEncoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [Organisation::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function testInvalidTargetsAreRejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        Organisation::fromArray($invalid);
    }
}
