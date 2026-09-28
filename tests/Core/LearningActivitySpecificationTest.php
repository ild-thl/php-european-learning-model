<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\LearningActivitySpecification;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LearningActivitySpecificationTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
                    'id' => 'urn:epass:learningActivitySpecification:1',
                    'type' => 'LearningActivitySpecification',
                    'title' => ['en' => ['EDC building training course']],
                    'identifier' => [[
                        'id' => 'urn:epass:identifier:1',
                        'type' => 'Identifier',
                        'notation' => 'Las1234',
                        'schemeName' => 'Learning Achievement Specification Identifier',
                    ]],
                    'altLabel' => [[
                        'en' => ['EBTC'],
                    ]],
                    'status' => 'not-published-anymore',
                    'volumeOfLearning' => 'PT4H',
                    'dcType' => [[
                        'id' => 'http://data.europa.eu/snb/learning-activity/bf2e3a7bae',
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
                        'noteLiteral' => ['en' => ['European Digital Credentials for Learning (EDC) are electronically sealed, EU standard-compliant digital records issued to a person to certify the learning they have undertaken. They can be awarded for formal education, non-formal training, online courses, volunteering experiences and more. Education and training providers can freely use the EDC Platform to award degrees, diplomas, certificates of participation or other credentials to their learners. In turn, learners are empowered to share their multilingual temper-evident verifiable credentials with chosen third parties, e.g. prospective employers, to demonstrate their knowledge, skills, competences and qualifications, irrespective of the source of their acquisition. EDCs can be sent to learners by email or by direct deposit into their standard-compliant (e.g. Europass) wallets.']],
                    ]],
                    'supplementaryDocument' => [[
                        'id' => 'urn:epass:webresource:1',
                        'type' => 'WebResource',
                        'contentUrl' => 'https://europa.eu/europass/digital-credentials/issuer/#/credential-builder',
                    ]],
                    'generalisationOf' => [[
                        'id' => 'urn:epass:learningActivitySpecification:2',
                        'type' => 'LearningActivitySpecification',
                        'title' => ['en' => ['Another more specialized learningActivitySpecification']],
                    ]],
                    'specialisationOf' => [[
                        'id' => 'urn:epass:learningActivitySpecification:3',
                        'type' => 'LearningActivitySpecification',
                        'title' => ['en' => ['Another broader learningActivitySpecification']],
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
                        'id' => 'urn:epass:learningActivitySpecification:3',
                        'type' => 'LearningActivitySpecification',
                        'title' => ['en' => ['Another learningActivitySpecification']],
                    ]],
                    'isPartOf' => [[
                        'id' => 'urn:epass:learningActivitySpecification:3',
                        'type' => 'LearningActivitySpecification',
                        'title' => ['en' => ['Another learningActivitySpecification']],
                    ]],
                    'mode' => [[
                        'id' => 'http://data.europa.eu/snb/learning-assessment/e92d221e4d',
                        'type' => 'Concept',
                    ]],
                    'homepage' => [[
                        'id' => 'urn:epass:webresource:2',
                        'type' => 'WebResource',
                        'contentUrl' => 'http://example.com/las/1',
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
                ],
            ],
            'minimal' => [
                [
                    'id' => 'urn:epass:learningActivitySpecification:1',
                    'type' => 'LearningActivitySpecification',
                    'title' => [
                        'en' => ['LearniEDC building training coursengActivity'],
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing type' => [
                ['id' => 'urn:epass:learningActivitySpecification:1'],
                \InvalidArgumentException::class,
            ],
            'missing title' => [
                [
                    'id' => 'urn:epass:learningActivitySpecification:1',
                    'type' => 'LearningActivitySpecification',
                ],
                InvalidCredentialException::class,
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_array_deserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [LearningActivitySpecification::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_json_encoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [LearningActivitySpecification::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function test_invalid_targets_are_rejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        LearningActivitySpecification::fromArray($invalid);
    }
}
