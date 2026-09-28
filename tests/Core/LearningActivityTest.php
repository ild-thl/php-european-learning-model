<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\LearningActivity;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LearningActivityTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
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
                    'identifier' => [
                        [
                            'id' => 'urn:epass:identifier:1',
                            'type' => 'Identifier',
                            'notation' => 'LA1234',
                            'schemeName' => 'Learning Activity ID',
                        ],
                    ],
                    // TODO: learningOpportunity
                    'workload' => 'PT4H',
                    'specifiedBy' => [
                        'id' => 'urn:epass:learningActivitySpec:1',
                        'type' => 'LearningActivitySpecification',
                        'title' => [
                            'en' => ['EDC building training course'],
                        ],
                    ],
                    'levelOfCompletion' => 80,
                    'dcType' => [[
                        'id' => 'http://data.europa.eu/snb/learning-activity/bf2e3a7bae',
                        'type' => 'Concept',
                    ]],
                    'description' => [
                        'en' => ['This online course allows the learner to familiarise themselves with the process of creating ELM standard compliant digital micro-credentials that contain data on all mandatory elements specified in Annex 1 of the Proposal for a Council Recommendation on a European approach to micro-credentials for lifelong learning and employability.'],
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
                    'hasPart' => [[
                        'id' => 'urn:epass:activity:2',
                        'type' => 'LearningActivity',
                        'title' => [
                            'en' => ['EDC building training course: Part 1'],
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
                        'id' => 'urn:epass:activity:3',
                        'type' => 'LearningActivity',
                        'title' => [
                            'en' => ['EDC building programme'],
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
                    'temporal' => [[
                        'id' => 'urn:epass:temporal:1',
                        'type' => 'PeriodOfTime',
                        'startDate' => '2025-03-01T12:00:00Z',
                    ]],
                    'directedBy' => [
                        [
                            'id' => 'urn:epass:person:1',
                            'type' => 'Person',
                        ],
                        [
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
                        ],
                    ],
                    'influences' => [[
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
                    ]],
                    'order' => 1,
                ],
            ],
            'minimal' => [
                [
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
                ],
            ],
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing type' => [
                ['id' => 'urn:epass:activity:1'],
                InvalidCredentialException::class,
            ],
            'missing title' => [
                [
                    'id' => 'urn:epass:activity:1',
                    'type' => 'LearningActivity',
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
                    'id' => 'urn:epass:activity:1',
                    'type' => 'LearningActivity',
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
        $this->assertArrayRoundTrip($expected, [LearningActivity::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function test_each_valid_target_round_trips_through_json_encoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [LearningActivity::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function test_invalid_targets_are_rejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        LearningActivity::fromArray($invalid);
    }
}
