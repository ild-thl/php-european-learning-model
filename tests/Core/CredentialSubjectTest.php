<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Edc\CredentialSubject;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CredentialSubjectTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
                    'id' => 'urn:epass:person:1',
                    'type' => 'Person',
                    'fullName' => ['en' => ['Ana Andromeda']],
                    'familyName' => ['en' => ['Andromeda']],
                    'givenName' => ['en' => ['Ana']],
                    'birthName' => [['en' => ['Birthname']]],
                    'patronymicName' => [['en' => ['PatronymicName']]],
                    'identifier' => [[
                        'id' => 'urn:epass:identifier:4',
                        'type' => 'Identifier',
                        'notation' => '87654321',
                        'schemeName' => 'Student Card ID',
                    ]],
                    'nationalID' => [
                        'id' => 'urn:epass:legalIdentifier:3',
                        'type' => 'LegalIdentifier',
                        'notation' => 'IT-12345678',
                        'spatial' => [
                            'id' => 'http://publications.europa.eu/resource/authority/country/ITA',
                            'type' => 'Concept',
                            'inScheme' => [
                                'id' => 'http://publications.europa.eu/resource/authority/country',
                                'type' => 'ConceptScheme',
                            ],
                            'prefLabel' => ['en' => ['Italy']],
                            'notation' => 'country',
                        ],
                    ],
                    'dateOfBirth' => '1999-10-02T00:00:00Z',
                    'placeOfBirth' => [
                        'id' => 'urn:epass:location:2',
                        'type' => 'Location',
                        'address' => [[
                            'id' => 'urn:epass:address:4',
                            'type' => 'Address',
                            'countryCode' => [
                                'id' => 'http://publications.europa.eu/resource/authority/country/ITA',
                                'type' => 'Concept',
                                'inScheme' => [
                                    'id' => 'http://publications.europa.eu/resource/authority/country',
                                    'type' => 'ConceptScheme',
                                ],
                                'prefLabel' => ['en' => ['Italy']],
                                'notation' => 'country',
                            ],
                            'fullAddress' => [
                                'id' => 'urn:epass:note:8',
                                'type' => 'Note',
                                'noteLiteral' => ['en' => ['Rome']],
                            ],
                        ]],
                    ],
                    'gender' => [
                        'id' => 'http://publications.europa.eu/resource/authority/human-sex/FEMALE',
                        'type' => 'Concept',
                        'inScheme' => [
                            'id' => 'http://publications.europa.eu/resource/authority/human-sex',
                            'type' => 'ConceptScheme',
                        ],
                        'prefLabel' => ['en' => ['female']],
                        'notation' => 'human-sex',
                    ],
                    'location' => [
                        'id' => 'urn:epass:location:3',
                        'type' => 'Location',
                        'address' => [[
                            'id' => 'urn:epass:address:5',
                            'type' => 'Address',
                            'countryCode' => [
                                'id' => 'http://publications.europa.eu/resource/authority/country/ITA',
                                'type' => 'Concept',
                            ],
                        ]],
                    ],
                    'contactPoint' => [[
                        'id' => 'urn:epass:contactPoint:3',
                        'type' => 'ContactPoint',
                        'address' => [[
                            'id' => 'urn:epass:address:3',
                            'type' => 'Address',
                            'countryCode' => [
                                'id' => 'http://publications.europa.eu/resource/authority/country/ITA',
                                'type' => 'Concept',
                                'inScheme' => [
                                    'id' => 'http://publications.europa.eu/resource/authority/country',
                                    'type' => 'ConceptScheme',
                                ],
                                'prefLabel' => ['en' => ['Italy']],
                                'notation' => 'country',
                            ],
                            'fullAddress' => [
                                'id' => 'urn:epass:note:7',
                                'type' => 'Note',
                                'noteLiteral' => ['en' => ['Via da Vinci, 12, Rome']],
                            ],
                        ]],
                        'emailAddress' => [[
                            'id' => 'mailto:ana.andromeda@email.com',
                            'type' => 'Mailbox',
                        ]],
                    ]],
                    'memberOf' => [[
                        'id' => 'urn:epass:organisation:1',
                        'type' => 'Organisation',
                        'legalName' => ['en' => ['Example Organisation']],
                        'location' => [[
                            'id' => 'urn:epass:location:1',
                            'type' => 'Location',
                            'address' => [[
                                'id' => 'urn:epass:address:1',
                                'type' => 'Address',
                                'countryCode' => [
                                    'id' => 'http://publications.europa.eu/resource/authority/country/ITA',
                                    'type' => 'Concept',
                                ],
                            ]],
                        ]],
                    ]],
                    'citizenshipCountry' => [[
                        'id' => 'http://publications.europa.eu/resource/authority/country/ITA',
                        'type' => 'Concept',
                        'inScheme' => [
                            'id' => 'http://publications.europa.eu/resource/authority/country',
                            'type' => 'ConceptScheme',
                        ],
                        'prefLabel' => ['en' => ['Italy']],
                        'notation' => 'country',
                    ]],
                    // TODO: hasCredntial
                    'hasFamilyRelationship' => [[
                        'id' => 'http://data.europa.eu/snb/family-relationship/c_2eae4440',
                        'type' => 'Concept',
                    ]],
                    'order' => 1,
                    'modified' => '2024-06-01T12:00:00Z',
                    'hasClaim' => [[
                        'id' => 'urn:epass:learningAchievement:1',
                        'type' => 'LearningAchievement',
                        'title' => ['en' => ['Example Learning Achievement']],
                        'awardedBy' => [
                            'id' => 'urn:epass:awardingProcess:1',
                            'type' => 'AwardingProcess',
                        ],
                        'specifiedBy' => [
                            'id' => 'urn:epass:learningAchievementSpecification:1',
                            'type' => 'LearningAchievementSpecification',
                            'title' => ['en' => ['Example Learning Specification']],
                        ],
                    ]],
                ],
            ],
            'minimal' => [
                [
                    'id' => 'urn:epass:person:1',
                    'type' => 'Person',
                    'hasClaim' => [[
                        'id' => 'urn:epass:learningAchievement:1',
                        'type' => 'LearningAchievement',
                        'title' => ['en' => ['Example Learning Achievement']],
                        'awardedBy' => [
                            'id' => 'urn:epass:awardingProcess:1',
                            'type' => 'AwardingProcess',
                        ],
                        'specifiedBy' => [
                            'id' => 'urn:epass:learningAchievementSpecification:1',
                            'type' => 'LearningAchievementSpecification',
                            'title' => ['en' => ['Example Learning Specification']],
                        ],
                    ]],
                ],
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing type' => [
                ['id' => 'urn:epass:person:1'],
                InvalidCredentialException::class,
            ],
            'missing hasClaim' => [
                [
                    'id' => 'urn:epass:person:1',
                    'type' => 'Person',
                ],
                InvalidCredentialException::class,
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughArrayDeserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [CredentialSubject::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughJsonEncoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [CredentialSubject::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function testInvalidTargetsAreRejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        CredentialSubject::fromArray($invalid);
    }
}
