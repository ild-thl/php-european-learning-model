<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\LearningOpportunity;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LearningOpportunityTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
                    'id' => 'urn:epass:opportunity:1',
                    'type' => 'LearningOpportunity',
                    'title' => [
                        'en' => ['EDC building training course'],
                    ],
                    'providedBy' => [[
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
                    'identifier' => [
                        [
                            'id' => 'urn:epass:identifier:1',
                            'type' => 'Identifier',
                            'notation' => 'LA1234',
                            'schemeName' => 'Learning Opportunity ID',
                        ],
                    ],
                    'duration' => 'PT4H',
                    'status' => 'not-published-anymore',
                    'learningSchedule' => [
                        'id' => 'http://data.europa.eu/snb/learning-schedule/67395e6b5a',
                        'type' => 'Concept',
                    ],
                    'temporal' => [
                        'id' => 'urn:epass:temporal:1',
                        'type' => 'PeriodOfTime',
                        'startDate' => '2025-03-01T12:00:00Z',
                    ],
                    'bannerImage' => [
                        'id' => 'urn:epass:mediaobject:1',
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
                    'scheduleInformation' => [
                        'id' => 'urn:epass:note:1',
                        'type' => 'Note',
                        'noteLiteral' => [
                            'en' => ['From 1st March 2025 UTC until 31st June 2025. Weekly sessions on Mondays and Wednesdays 12:00 PM - 2:00 PM UTC.'],
                        ],
                    ],
                    'admissionProcedure' => [
                        'id' => 'urn:epass:note:2',
                        'type' => 'Note',
                        'noteLiteral' => [
                            'en' => ['Applicants must submit their previous academic transcripts and a motivation letter.'],
                        ],
                    ],
                    'learningAchievementSpecification' => [
                        'id' => 'urn:epass:learningAchievementSpecification:1',
                        'type' => 'LearningAchievementSpecification',
                        'title' => [
                            'en' => ['Digital micro-credential creation'],
                        ],
                    ],
                    'learningActivitySpecification' => [
                        'id' => 'urn:epass:learningActivitySpec:1',
                        'type' => 'LearningActivitySpecification',
                        'title' => [
                            'en' => ['EDC building training course'],
                        ],
                    ],
                    'dcType' => [[
                        'id' => 'http://data.europa.eu/snb/learning-opportunity/05053c1cbe',
                        'type' => 'Concept',
                    ]],
                    'description' => [
                        'en' => ['This online course allows the learner to familiarise themselves with the process of creating ELM standard compliant digital micro-credentials that contain data on all mandatory elements specified in Annex 1 of the Proposal for a Council Recommendation on a European approach to micro-credentials for lifelong learning and employability.'],
                    ],
                    'descriptionHtml' => [
                        '<p>This online course ...</p>',
                    ],
                    'additionalNote' => [[
                        'id' => 'urn:epass:note:1',
                        'type' => 'Note',
                        'noteLiteral' => [
                            'en' => ['European Digital Credentials for Learning (EDC) are electronically sealed, EU standard-compliant digital records issued to a person to certify the learning they have undertaken. They can be awarded for formal education, non-formal training, online courses, volunteering experiences and more. Education and training providers can freely use the EDC Platform to award degrees, diplomas, certificates of participation or other credentials to their learners. In turn, learners are empowered to share their multilingual temper-evident verifiable credentials with chosen third parties, e.g. prospective employers, to demonstrate their knowledge, skills, competences and qualifications, irrespective of the source of their acquisition. EDCs can be sent to learners by email or by direct deposit into their standard-compliant (e.g. Europass) wallets.'],
                        ],
                    ]],
                    'supplementaryDocument' => [[
                        'id' => 'urn:epass:webresource:1',
                        'type' => 'WebResource',
                        'contentUrl' => 'http://example.com/supplementary-document',
                    ]],
                    'defaultLanguage' => [
                        'id' => 'http://publications.europa.eu/resource/authority/language/ENG',
                        'type' => 'Concept',
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
                    'grant' => [[
                        'id' => 'urn:epass:grant:1',
                        'type' => 'Grant',
                        'title' => [
                            'en' => ['Grant Title'],
                        ],
                    ]],
                    'homepage' => [[
                        'id' => 'urn:epass:webresource:2',
                        'type' => 'WebResource',
                        'contentUrl' => 'http://example.com/las/1',
                    ]],
                    'priceDetail' => [[
                        'id' => 'urn:epass:pricedetail:1',
                        'type' => 'PriceDetail',
                        'prefLabel' => [
                            'en' => ['free'],
                        ],
                    ]],
                    'hasPart' => [[
                        'id' => 'urn:epass:opportunity:2',
                        'type' => 'LearningOpportunity',
                        'title' => [
                            'en' => ['EDC building training course: Part 1'],
                        ],
                        'providedBy' => [[
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
                    ]],
                    'isPartOf' => [[
                        'id' => 'urn:epass:opportunity:3',
                        'type' => 'LearningOpportunity',
                        'title' => [
                            'en' => ['EDC building programme'],
                        ],
                        'providedBy' => [[
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
                    ]],
                    'applicationDeadline' => ['2024-12-31T23:59:59Z'],
                    'mode' => [[
                        'id' => 'http://data.europa.eu/snb/learning-assessment/920fbb3cbe',
                        'type' => 'Concept',
                    ]],
                    'order' => 1,
                    'modified' => '2024-01-01T00:00:00Z',
                ],
            ],
            'minimal' => [
                [
                    'id' => 'urn:epass:opportunity:1',
                    'type' => 'LearningOpportunity',
                    'title' => [
                        'en' => ['EDC building training course'],
                    ],
                    'providedBy' => [[
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
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing type' => [
                ['id' => 'urn:epass:opportunity:1'],
                InvalidCredentialException::class,
            ],
            'missing title' => [
                [
                    'id' => 'urn:epass:opportunity:1',
                    'type' => 'LearningOpportunity',
                    'providedBy' => [[
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
                InvalidCredentialException::class,
            ],
            'missing providedBy' => [
                [
                    'id' => 'urn:epass:opportunity:1',
                    'type' => 'LearningOpportunity',
                    'title' => [
                        'en' => ['EDC building training course'],
                    ],
                ],
                InvalidCredentialException::class,
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_array_deserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [LearningOpportunity::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_json_encoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [LearningOpportunity::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function test_invalid_targets_are_rejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        LearningOpportunity::fromArray($invalid);
    }
}
