<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\Qualification;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class QualificationTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
                    'id' => 'urn:epass:qualification:1',
                    'type' => 'Qualification',
                    'title' => ['en' => ['Digital micro-credential creation']],
                    'identifier' => [[
                        'id' => 'urn:epass:identifier:1',
                        'type' => 'Identifier',
                        'notation' => 'Las1234',
                        'schemeName' => 'Learning Achievement Specification Identifier',
                    ]],
                    'altLabel' => [[
                        'en' => ['DMCA'],
                    ]],
                    'learningOutcomeSummary' => [
                        'id' => 'urn:epass:note:1',
                        'type' => 'Note',
                        'noteLiteral' => ['en' => [
                            'The learner will be able to create European Digital Credentials using the EDC Building Blocks.',
                        ]],
                    ],
                    'volumeOfLearning' => 'P2DT12H',
                    'entryRequirement' => [
                        'id' => 'urn:epass:note:6',
                        'type' => 'Note',
                        'noteLiteral' => ['en' => [
                            'The learner needs to have an EDC Online Credential Builder account, that requires an EU Login ID.',
                        ]],
                    ],
                    'learningSetting' => [
                        'id' => 'http://data.europa.eu/snb/learning-setting/e207a81fc7',
                        'type' => 'Concept',
                    ],
                    'status' => 'not-published-anymore',
                    'maximumDuration' => 'P1M',
                    'dcType' => [[
                        'id' => 'http://data.europa.eu/snb/learning-opportunity/74a4a268e8',
                        'type' => 'Concept',
                    ]],
                    'language' => [[
                        'id' => 'http://publications.europa.eu/resource/authority/language/ENG',
                        'type' => 'Concept',
                    ]],
                    'description' => [
                        'en' => ['Description'],
                    ],
                    'additionalNote' => [[
                        'id' => 'urn:epass:note:2',
                        'type' => 'Note',
                        'noteLiteral' => ['en' => ['More Information']],
                    ]],
                    'supplementaryDocument' => [[
                        'id' => 'urn:epass:webresource:1',
                        'type' => 'WebResource',
                        'contentUrl' => 'http://example.com/supplementary-document',
                    ]],
                    'generalisationOf' => [[
                        'id' => 'urn:epass:qualification:2',
                        'type' => 'Qualification',
                        'title' => ['en' => ['Another more specialized qualification']],
                    ]],
                    'specialisationOf' => [[
                        'id' => 'urn:epass:qualification:3',
                        'type' => 'Qualification',
                        'title' => ['en' => ['Another broader qualification']],
                    ]],
                    'creditPoint' => [[
                        'id' => 'urn:epass:creditPoint:1',
                        'type' => 'CreditPoint',
                        'framework' => [
                            'id' => 'http://data.europa.eu/snb/education-credit/6fcec5c5af',
                            'type' => 'Concept',
                        ],
                        'point' => '5',
                    ]],
                    'hasPart' => [[
                        'id' => 'urn:epass:qualification:3',
                        'type' => 'Qualification',
                        'title' => ['en' => ['Another aualification']],
                    ]],
                    'isPartOf' => [[
                        'id' => 'urn:epass:qualification:3',
                        'type' => 'Qualification',
                        'title' => ['en' => ['Another qualification']],
                    ]],
                    'mode' => [[
                        'id' => 'http://data.europa.eu/snb/learning-assessment/e92d221e4d',
                        'type' => 'Concept',
                    ]],
                    'homepage' => [[
                        'id' => 'urn:epass:webresource:2',
                        'type' => 'WebResource',
                        'contentUrl' => 'http://example.com/qualification/1',
                    ]],
                    'category' => ['Digital Educational Technologies'],
                    'targetGroup' => [[
                        'id' => 'http://publications.europa.eu/resource/authority/target-audience/EDU',
                        'type' => 'Concept',
                    ]],
                    'awardingOpportunity' => [[
                        'id' => 'urn:epass:awardingOpportunity:1',
                        'type' => 'AwardingOpportunity',
                    ]],
                    'educationSubject' => [[
                        'id' => 'http://data.europa.eu/snb/isced-f/068',
                        'type' => 'Concept',
                    ]],
                    'learningOutcome' => [
                        [
                            'id' => 'urn:epass:learningOutcome:1',
                            'type' => 'LearningOutcome',
                            'title' => ['en' => ['Communication, collaboration and creativity']],
                            'relatedSkill' => [[
                                'id' => 'http://data.europa.eu/snb/dcf/u69o196gu6',
                                'type' => 'Concept',
                            ]],
                            'relatedESCOSkill' => [[
                                'id' => 'http://data.europa.eu/esco/skill/326809fc-238d-40c2-881e-40042f7f2f0d',
                                'type' => 'Concept',
                            ], [
                                'id' => 'http://data.europa.eu/esco/skill/80f308e6-0e09-404a-80cd-ec5f50f6f304',
                                'type' => 'Concept',
                            ], [
                                'id' => 'http://data.europa.eu/esco/skill/ccdf3597-5c80-410f-896d-e1bdfa223de9',
                                'type' => 'Concept',
                            ]],
                        ],
                    ],
                    'influencedBy' => [[
                        'id' => 'urn:epass:learningActivitySpecification:2',
                        'type' => 'LearningActivitySpecification',
                        'title' => ['en' => ['A learning activity specification']],
                    ]],
                    'provenBy' => [[
                        'id' => 'urn:epass:learningAssessmentSpecification:2',
                        'type' => 'LearningAssessmentSpecification',
                        'title' => ['en' => ['A learning assessment specification']],
                    ]],
                    'ISCEDFCode' => [[
                        'id' => 'http://data.europa.eu/snb/isced-f/068',
                        'type' => 'Concept',
                    ]],
                    'entitlesTo' => [[
                        'id' => 'urn:epass:learningEntitlementSpecification:1',
                        'type' => 'LearningEntitlementSpecification',
                        'title' => ['en' => ['Entitlement']],
                        'dcType' => [
                            'id' => 'http://data.europa.eu/snb/entitlement/64aad92881',
                            'type' => 'Concept',
                        ],
                        'entitlementStatus' => [
                            'id' => 'http://data.europa.eu/snb/entitlement-status/5b8d6b34fb',
                            'type' => 'Concept',
                        ],
                    ]],
                    'educationLevel' => [[
                        'id' => 'https://w3id.org/kim/educationalLevel/level_C',
                        'type' => 'Concept',
                    ]],
                    'order' => 1,
                    'modified' => '2024-06-05T12:00:00Z',
                    'isPartialQualification' => true,
                    'qualificationCode' => [[
                        'id' => 'http://example.com/nqf-qualification/1',
                        'type' => 'Concept',
                    ]],
                    'NQFLevel' => [[
                        'id' => 'http://data.europa.eu/snb/qdr/c_670e54c7',
                        'type' => 'Concept',
                    ]],
                    'EQFLevel' => [
                        'id' => 'http://data.europa.eu/snb/eqf/6',
                        'type' => 'Concept',
                    ],
                    'accreditation' => [[
                        'id' => 'urn:epass:accreditation:1',
                        'type' => 'Accreditation',
                        'title' => [
                            'en' => ['Accreditation Title'],
                        ],
                        'accreditingAgent' => [
                            'id' => 'urn:epass:org:1',
                            'type' => 'Organisation',
                            'legalName' => [
                                'en' => ['Accrediting Agent Legal Name'],
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
                        ],
                        'dcType' => [
                            'id' => 'http://data.europa.eu/snb/accreditation/003293d2ce',
                            'type' => 'Concept',
                        ],
                    ]],
                ],
            ],
            'minimal' => [
                [
                    'id' => 'urn:epass:qualification:1',
                    'type' => 'Qualification',
                    'title' => [
                        'en' => ['Digital micro-credential creation'],
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing type' => [
                ['id' => 'urn:epass:qualification:1'],
                InvalidCredentialException::class,
            ],
            'missing title' => [
                [
                    'id' => 'urn:epass:qualification:1',
                    'type' => 'Qualification',
                ],
                InvalidCredentialException::class,
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_array_deserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [Qualification::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_json_encoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [Qualification::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function test_invalid_targets_are_rejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        Qualification::fromArray($invalid);
    }
}
