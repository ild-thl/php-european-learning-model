<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Edc\LearningAchievement;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LearningAchievementTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
                    'id' => 'urn:epass:learningAchievement:1',
                    'type' => 'LearningAchievement',
                    'title' => [
                        'en' => ['Digital micro-credential creation'],
                    ],
                    'awardedBy' => [
                        'id' => 'urn:epass:awardingProcess:1',
                        'type' => 'AwardingProcess',
                        'awardingBody' => [[
                            'id' => 'urn:epass:org:1',
                            'type' => 'Organisation',
                            'legalName' => [
                                'en' => ['Learning Provider'],
                            ],
                            'location' => [[
                                'id' => 'urn:epass:location:1',
                                'type' => 'Location',
                                'address' => [[
                                    'id' => 'urn:epass:address:1',
                                    'type' => 'Address',
                                    'countryCode' => [
                                        'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                        'type' => 'Concept',
                                    ],
                                ]],
                            ]],
                        ]],
                    ],
                    'identifier' => [
                        [
                            'id' => 'urn:epass:identifier:1',
                            'type' => 'Identifier',
                            'notation' => 'LA1234',
                            'schemeName' => 'Learning Achievement ID',
                        ],
                    ],
                    'specifiedBy' => [
                        'id' => 'urn:epass:qualification:1',
                        'type' => 'Qualification',
                        'title' => [
                            'en' => ['Digital micro-credential creation'],
                        ],
                    ],
                    'dcType' => [[
                        'id' => 'http://publications.europa.eu/resource/authority/achievement-type/COMPETENCE',
                        'type' => 'Concept',
                    ]],
                    'description' => [
                        'en' => ['Description of the digital micro-credential creation'],
                    ],
                    'additionalNote' => [[
                        'id' => 'urn:epass:note:1',
                        'type' => 'Note',
                        'noteLiteral' => [
                            'en' => ['This is an additional note for the learning achievement.'],
                        ],
                    ]],
                    'supplementaryDocument' => [[
                        'id' => 'urn:epass:webresource:1',
                        'type' => 'WebResource',
                        'contentUrl' => 'http://example.com/supplementary-document',
                    ]],
                    'influencedBy' => [[
                        'id' => 'urn:epass:activity:1',
                        'type' => 'LearningActivity',
                        'title' => [
                            'en' => ['EDC building training course'],
                        ],
                        'awardedBy' => [
                            'id' => 'urn:epass:awardingProcess:1',
                            'type' => 'AwardingProcess',
                            'awardingBody' => [[
                                'id' => 'urn:epass:org:1',
                                'type' => 'Organisation',
                                'legalName' => [
                                    'en' => ['Learning Provider'],
                                ],
                                'location' => [[
                                    'id' => 'urn:epass:location:1',
                                    'type' => 'Location',
                                    'address' => [[
                                        'id' => 'urn:epass:address:1',
                                        'type' => 'Address',
                                        'countryCode' => [
                                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                            'type' => 'Concept',
                                        ],
                                    ]],
                                ]],
                            ]],
                        ],
                    ]],
                    'provenBy' => [[
                        'id' => 'urn:epass:learningAssessment:1',
                        'type' => 'LearningAssessment',
                        'title' => [
                            'en' => ['Digital artefact assessment'],
                        ],
                        'grade' => [
                            'id' => 'urn:epass:note:3',
                            'type' => 'Note',
                            'noteLiteral' => [
                                'en' => ['Pass'],
                            ],
                        ],
                        'awardedBy' => [
                            'id' => 'urn:epass:awardingProcess:1',
                            'type' => 'AwardingProcess',
                            'awardingBody' => [[
                                'id' => 'urn:epass:org:1',
                                'type' => 'Organisation',
                                'legalName' => [
                                    'en' => ['Learning Provider'],
                                ],
                                'location' => [[
                                    'id' => 'urn:epass:location:1',
                                    'type' => 'Location',
                                    'address' => [[
                                        'id' => 'urn:epass:address:1',
                                        'type' => 'Address',
                                        'countryCode' => [
                                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                            'type' => 'Concept',
                                        ],
                                    ]],
                                ]],
                            ]],
                        ],
                    ]],
                    'entitlesTo' => [[
                        'id' => 'urn:epass:learningEntitlement:1',
                        'type' => 'LearningEntitlement',
                        'title' => [
                            'en' => ['Access to additional programs'],
                        ],
                        'awardedBy' => [
                            'id' => 'urn:epass:awardingProcess:1',
                            'type' => 'AwardingProcess',
                            'awardingBody' => [[
                                'id' => 'urn:epass:org:1',
                                'type' => 'Organisation',
                                'legalName' => [
                                    'en' => ['Learning Provider'],
                                ],
                                'location' => [[
                                    'id' => 'urn:epass:location:1',
                                    'type' => 'Location',
                                    'address' => [[
                                        'id' => 'urn:epass:address:1',
                                        'type' => 'Address',
                                        'countryCode' => [
                                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                            'type' => 'Concept',
                                        ],
                                    ]],
                                ]],
                            ]],
                        ],
                    ]],
                    'hasPart' => [[
                        'id' => 'urn:epass:learningAchievement:2',
                        'type' => 'LearningAchievement',
                        'title' => [
                            'en' => ['Another Learning Achievement'],
                        ],
                        'awardedBy' => [
                            'id' => 'urn:epass:awardingProcess:1',
                            'type' => 'AwardingProcess',
                            'awardingBody' => [[
                                'id' => 'urn:epass:org:1',
                                'type' => 'Organisation',
                                'legalName' => [
                                    'en' => ['Learning Provider'],
                                ],
                                'location' => [[
                                    'id' => 'urn:epass:location:1',
                                    'type' => 'Location',
                                    'address' => [[
                                        'id' => 'urn:epass:address:1',
                                        'type' => 'Address',
                                        'countryCode' => [
                                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                            'type' => 'Concept',
                                        ],
                                    ]],
                                ]],
                            ]],
                        ],
                    ]],
                    'isPartOf' => [[
                        'id' => 'urn:epass:learningAchievement:3',
                        'type' => 'LearningAchievement',
                        'title' => [
                            'en' => ['Yet Another Learning Achievement'],
                        ],
                        'awardedBy' => [
                            'id' => 'urn:epass:awardingProcess:1',
                            'type' => 'AwardingProcess',
                            'awardingBody' => [[
                                'id' => 'urn:epass:org:1',
                                'type' => 'Organisation',
                                'legalName' => [
                                    'en' => ['Learning Provider'],
                                ],
                                'location' => [[
                                    'id' => 'urn:epass:location:1',
                                    'type' => 'Location',
                                    'address' => [[
                                        'id' => 'urn:epass:address:1',
                                        'type' => 'Address',
                                        'countryCode' => [
                                            'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                            'type' => 'Concept',
                                        ],
                                    ]],
                                ]],
                            ]],
                        ],
                    ]],
                    'creditReceived' => [[
                        'id' => 'urn:epass:credit:1',
                        'type' => 'CreditPoint',
                        'framework' => [
                            'id' => 'http://data.europa.eu/snb/education-credit/6fcec5c5af',
                            'type' => 'Concept',
                            'inScheme' => [
                                'id' => 'http://data.europa.eu/snb/education-credit/25831c2',
                                'type' => 'ConceptScheme',
                            ],
                            'prefLabel' => [
                                'en' => ['European Credit Transfer System'],
                            ],
                        ],
                        'point' => '5',
                    ]],
                    'order' => 1,
                ],
            ],
            'minimal' => [
                [
                    'id' => 'urn:epass:learningAchievement:1',
                    'type' => 'LearningAchievement',
                    'title' => [
                        'en' => ['Digital micro-credential creation'],
                    ],
                    'awardedBy' => [
                        'id' => 'urn:epass:awardingProcess:1',
                        'type' => 'AwardingProcess',
                        'awardingBody' => [[
                            'id' => 'urn:epass:org:1',
                            'type' => 'Organisation',
                            'legalName' => [
                                'en' => ['Learning Provider'],
                            ],
                            'location' => [[
                                'id' => 'urn:epass:location:1',
                                'type' => 'Location',
                                'address' => [[
                                    'id' => 'urn:epass:address:1',
                                    'type' => 'Address',
                                    'countryCode' => [
                                        'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                        'type' => 'Concept',
                                    ],
                                ]],
                            ]],
                        ]],
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing type' => [
                ['id' => 'urn:epass:learningAchievement:1'],
                InvalidCredentialException::class,
            ],
            'missing title' => [
                [
                    'id' => 'urn:epass:learningAchievement:1',
                    'type' => 'LearningAchievement',
                    'awardedBy' => [
                        'id' => 'urn:epass:awardingProcess:1',
                        'type' => 'AwardingProcess',
                        'awardingBody' => [[
                            'id' => 'urn:epass:org:1',
                            'type' => 'Organisation',
                            'legalName' => [
                                'en' => ['Learning Provider'],
                            ],
                            'location' => [[
                                'id' => 'urn:epass:location:1',
                                'type' => 'Location',
                                'address' => [[
                                    'id' => 'urn:epass:address:1',
                                    'type' => 'Address',
                                    'countryCode' => [
                                        'id' => 'http://publications.europa.eu/resource/authority/country/IRL',
                                        'type' => 'Concept',
                                    ],
                                ]],
                            ]],
                        ]],
                    ],
                ],
                InvalidCredentialException::class,
            ],
            'missing awardedBy' => [
                [
                    'id' => 'urn:epass:learningAchievement:1',
                    'type' => 'LearningAchievement',
                    'title' => [
                        'en' => ['Digital micro-credential creation'],
                    ],
                ],
                InvalidCredentialException::class,
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_array_deserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [LearningAchievement::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_json_encoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [LearningAchievement::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function test_invalid_targets_are_rejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        LearningAchievement::fromArray($invalid);
    }
}
