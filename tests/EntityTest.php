<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanDigitalCredentials\Entity;
use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\Claim;
use IsyThl\EuropeanDigitalCredentials\Address;
use IsyThl\EuropeanDigitalCredentials\Accreditation;
use IsyThl\EuropeanDigitalCredentials\AwardingProcess;
use IsyThl\EuropeanDigitalCredentials\ContactPoint;
use IsyThl\EuropeanDigitalCredentials\CreditPoint;
use IsyThl\EuropeanDigitalCredentials\Credential;
use IsyThl\EuropeanDigitalCredentials\CredentialSubject;
use IsyThl\EuropeanDigitalCredentials\CredentialDocumentValidator;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\DisplayParameter;
use IsyThl\EuropeanDigitalCredentials\DisplayDetail;
use IsyThl\EuropeanDigitalCredentials\IndividualDisplay;
use IsyThl\EuropeanDigitalCredentials\Identifier;
use IsyThl\EuropeanDigitalCredentials\Issuer;
use IsyThl\EuropeanDigitalCredentials\LegalIdentifier;
use IsyThl\EuropeanDigitalCredentials\MediaObject;
use IsyThl\EuropeanDigitalCredentials\Note;
use IsyThl\EuropeanDigitalCredentials\EmailAddress;
use IsyThl\EuropeanDigitalCredentials\Location;
use IsyThl\EuropeanDigitalCredentials\LearningAchievement;
use IsyThl\EuropeanDigitalCredentials\LearningActivity;
use IsyThl\EuropeanDigitalCredentials\LearningActivitySpecification;
use IsyThl\EuropeanDigitalCredentials\LearningEntitlement;
use IsyThl\EuropeanDigitalCredentials\LearningEntitlementSpecification;
use IsyThl\EuropeanDigitalCredentials\LearningAssessment;
use IsyThl\EuropeanDigitalCredentials\LearningAssessmentSpecification;
use IsyThl\EuropeanDigitalCredentials\LearningAchievementSpecification;
use IsyThl\EuropeanDigitalCredentials\LearningOutcome;
use IsyThl\EuropeanDigitalCredentials\Organisation;
use IsyThl\EuropeanDigitalCredentials\Qualification;
use IsyThl\EuropeanDigitalCredentials\GradingScheme;
use IsyThl\EuropeanDigitalCredentials\WebResource;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase {

    public function testSerializationIsDeterministicAndPreservesUnicode(): void {
        $entity = new class ('urn:test:credential') extends Entity {
            public function toArray(): array {
                return ['id' => $this->id, 'label' => ['en' => ['Zoë']]];
            }
        };

        $expected = '{"id":"urn:test:credential","label":{"en":["Zoë"]}}';

        self::assertSame($expected, $entity->toJson());
        self::assertSame($expected, $entity->toJson());
    }

    public function testGeneratedIdentifiersHaveCredentialUrnFormat(): void {
        $entity = new class extends Entity {
            public function toArray(): array {
                return ['id' => $this->id];
            }
        };

        self::assertMatchesRegularExpression('/^urn:credential:[0-9a-f]{32}$/', $entity->id);
    }

    public function testLocalizedValuesKeepLanguageMapAndArrayShape(): void {
        $localized = new LocalizedString(['en' => 'Course completion', 'de' => ['Kursabschluss']]);

        self::assertSame([
            'en' => ['Course completion'],
            'de' => ['Kursabschluss'],
        ], $localized->toArray());
    }

    public function testLocalizedValuesRejectMalformedLanguages(): void {
        $this->expectException(InvalidCredentialException::class);

        new LocalizedString(['english' => 'Course completion']);
    }

    public function testConceptRoundTripsItsJsonLdShape(): void {
        $concept = new Concept(
            'http://example.test/concept/one',
            new LocalizedString(['en' => 'One']),
            new ConceptScheme('http://example.test/scheme'),
            'one',
        );

        self::assertSame($concept->toArray(), Concept::fromArray($concept->toArray())->toArray());
    }

    public function testConceptRejectsMissingProfileFields(): void {
        $this->expectException(InvalidCredentialException::class);

        Concept::fromArray(['id' => 'http://example.test/concept/one']);
    }

    public function testCredentialIncludesTheGenericProfileByDefault(): void {
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ada']),
            new LocalizedString(['en' => 'Lovelace']),
            new LocalizedString(['en' => 'Ada Lovelace']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array {
                    return ['id' => $this->id, 'type' => 'Claim'];
                }
            }],
        );
        $language = new Concept(
            'http://publications.europa.eu/resource/authority/language/ENG',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
            'language',
        );
        $credential = new Credential(
            'credential-1',
            $subject,
            new DisplayParameter('display-1', $language, $language, new LocalizedString(['en' => 'Title'])),
            new \DateTimeImmutable('2024-01-01T00:00:00+01:00'),
        );

        self::assertSame(
            'http://data.europa.eu/snb/credential/e34929035b',
            $credential->toArray()['credentialProfiles'][0]['id'],
        );
        (new CredentialDocumentValidator())->validateJson($credential->toJson());
    }

    public function testCredentialRejectsProfileFromAnotherScheme(): void {
        $this->expectException(\IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException::class);

        new Credential(
            'credential-1',
            new CredentialSubject(
                'subject-1',
                new LocalizedString(['en' => 'Ada']),
                new LocalizedString(['en' => 'Lovelace']),
                new LocalizedString(['en' => 'Ada Lovelace']),
                [new class ('claim-1') extends Claim {
                    public function toArray(): array {
                        return ['id' => $this->id, 'type' => 'Claim'];
                    }
                }],
            ),
            new DisplayParameter(
                'display-1',
                new Concept(
                    'http://publications.europa.eu/resource/authority/language/ENG',
                    new LocalizedString(['en' => 'English']),
                    new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
                    'language',
                ),
                new Concept(
                    'http://publications.europa.eu/resource/authority/language/ENG',
                    new LocalizedString(['en' => 'English']),
                    new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
                    'language',
                ),
                new LocalizedString(['en' => 'Title']),
            ),
            new \DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            credentialProfile: new Concept(
                'http://example.test/profile',
                new \IsyThl\EuropeanDigitalCredentials\LocalizedString(['en' => 'Profile']),
                new ConceptScheme('http://example.test/wrong-scheme'),
            ),
        );
    }

    public function testCredentialDocumentValidatorRejectsJsonArrays(): void {
        $this->expectException(InvalidCredentialException::class);

        (new CredentialDocumentValidator())->validateJson('[]');
    }

    public function testAchievementSpecificationSerializesControlledConceptFields(): void {
        $concept = static function (string $id, string $scheme): Concept {
            return new Concept(
                'http://example.test/' . $id,
                new LocalizedString(['en' => $id]),
                new ConceptScheme($scheme),
            );
        };
        $specification = new LearningAchievementSpecification(
            'specification-1',
            new LocalizedString(['en' => 'Course']),
            type: $concept('course', ElmVocabularySchemes::LEARNING_OPPORTUNITY),
            learningSetting: $concept('formal', ElmVocabularySchemes::LEARNING_SETTING),
            mode: $concept('online', ElmVocabularySchemes::ASSESSMENT),
            status: $concept('active', ElmVocabularySchemes::ACCREDITATION_STATUS),
            targetGroups: [$concept('adult', ElmVocabularySchemes::TARGET_GROUP)],
        );

        $data = $specification->toArray();

        self::assertSame('http://example.test/course', $data['dcType']['id']);
        self::assertSame('http://example.test/formal', $data['learningSetting']['id']);
        self::assertSame('http://example.test/online', $data['mode']['id']);
        self::assertSame('http://example.test/active', $data['status']['id']);
        self::assertSame('http://example.test/adult', $data['targetGroup'][0]['id']);
    }

    public function testActivitySpecificationUsesTheLearningActivityTypeScheme(): void {
        $activityType = new Concept(
            'http://example.test/workshop',
            new LocalizedString(['en' => 'Workshop']),
            new ConceptScheme(ElmVocabularySchemes::LEARNING_ACTIVITY),
        );

        $specification = new LearningActivitySpecification(
            'activity-1',
            new LocalizedString(['en' => 'Workshop']),
            type: $activityType,
        );

        self::assertSame('http://example.test/workshop', $specification->toArray()['dcType']['id']);
    }

    public function testLearningOutcomeUsesTheDcfSkillsScheme(): void {
        $skill = new Concept(
            'http://example.test/skill',
            new LocalizedString(['en' => 'Skill']),
            new ConceptScheme(ElmVocabularySchemes::DCF_SKILLS),
        );

        $outcome = new LearningOutcome(
            'outcome-1',
            new LocalizedString(['en' => 'Outcome']),
            relatedSkills: [$skill],
        );

        self::assertSame('http://example.test/skill', $outcome->toArray()['relatedSkills'][0]['id']);
    }

    public function testLearningOutcomeRejectsSkillsFromAnotherScheme(): void {
        $this->expectException(InvalidCredentialException::class);

        new LearningOutcome(
            'outcome-1',
            new LocalizedString(['en' => 'Outcome']),
            relatedSkills: [new Concept(
                'http://example.test/skill',
                new LocalizedString(['en' => 'Skill']),
                new ConceptScheme(ElmVocabularySchemes::ESCO_SKILLS),
            )],
        );
    }

    public function testAccreditationSerializesControlledConceptFields(): void {
        $concept = static function (string $id, string $scheme): Concept {
            return new Concept(
                'http://example.test/' . $id,
                new LocalizedString(['en' => $id]),
                new ConceptScheme($scheme),
            );
        };
        $agent = new Organisation(
            'agent-1',
            new Location(
                'location-1',
                new Address(
                    'address-1',
                    $concept('nl', ElmVocabularySchemes::COUNTRY),
                    new Note('note-1', new LocalizedString(['en' => 'The Hague'])),
                ),
            ),
            new LocalizedString(['en' => 'Quality authority']),
        );
        $accreditation = new Accreditation(
            'accreditation-1',
            new LocalizedString(['en' => 'Accredited course']),
            $agent,
            accreditedForEqfLevels: [$concept('eqf-6', ElmVocabularySchemes::EQF)],
            accreditedForThematicAreas: [$concept('computer-science', ElmVocabularySchemes::ISCED_F)],
            accreditedInJurisdictions: [$concept('nl', ElmVocabularySchemes::ATU)],
            decision: $concept('approved', ElmVocabularySchemes::ACCREDITATION_DECISION),
            limitCredentialTypes: [$concept('qualification', ElmVocabularySchemes::CREDENTIAL)],
            status: $concept('active', ElmVocabularySchemes::ACCREDITATION_STATUS),
        );

        $data = $accreditation->toArray();

        self::assertSame('http://example.test/eqf-6', $data['accreditedForEQFLevel'][0]['id']);
        self::assertSame('http://example.test/computer-science', $data['accreditedForThematicArea'][0]['id']);
        self::assertSame('http://example.test/nl', $data['accreditedInJurisdiction'][0]['id']);
        self::assertSame('http://example.test/approved', $data['decision']['id']);
        self::assertSame('http://example.test/qualification', $data['limitCredentialType'][0]['id']);
        self::assertSame('http://example.test/active', $data['status']['id']);
    }

    public function testAccreditationRejectsDecisionFromAnotherScheme(): void {
        $agent = new Organisation(
            'agent-1',
            new Location(
                'location-1',
                new Address(
                    'address-1',
                    new Concept(
                        'http://example.test/nl',
                        new LocalizedString(['en' => 'Netherlands']),
                        new ConceptScheme(ElmVocabularySchemes::COUNTRY),
                    ),
                    new Note('note-1', new LocalizedString(['en' => 'The Hague'])),
                ),
            ),
            new LocalizedString(['en' => 'Quality authority']),
        );

        $this->expectException(InvalidCredentialException::class);

        new Accreditation(
            'accreditation-1',
            new LocalizedString(['en' => 'Accredited course']),
            $agent,
            decision: new Concept(
                'http://example.test/wrong',
                new LocalizedString(['en' => 'Wrong']),
                new ConceptScheme(ElmVocabularySchemes::ASSESSMENT),
            ),
        );
    }

    public function testDisplayParameterRejectsNonLanguageConcepts(): void {
        $concept = new Concept(
            'http://example.test/concept',
            new LocalizedString(['en' => 'Concept']),
            new ConceptScheme('http://example.test/scheme'),
        );

        $this->expectExceptionMessage('language must use vocabulary scheme');

        new DisplayParameter('display-1', $concept, $concept, new LocalizedString(['en' => 'Title']));
    }

    public function testAddressRejectsNonCountryConcepts(): void {
        $concept = new Concept(
            'http://example.test/concept',
            new LocalizedString(['en' => 'Concept']),
            new ConceptScheme('http://example.test/scheme'),
        );

        $this->expectExceptionMessage('countryCode must use vocabulary scheme');

        new Address('address-1', $concept, new Note('note-1', new LocalizedString(['en' => 'Address'])));
    }

    public function testLearningEntitlementRejectsNonEntitlementType(): void {
        $concept = new Concept(
            'http://example.test/concept',
            new LocalizedString(['en' => 'Concept']),
            new ConceptScheme('http://example.test/scheme'),
        );

        $this->expectExceptionMessage('type must use vocabulary scheme');

        new LearningEntitlementSpecification(
            'entitlement-1',
            new LocalizedString(['en' => 'Access']),
            $concept,
        );
    }

    public function testCredentialDocumentValidatorRejectsProfileFromAnotherScheme(): void {
        $document = [
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'http://data.europa.eu/snb/model/context/edc-ap',
            ],
            'type' => ['VerifiableCredential', 'EuropeanDigitalCredential'],
            'credentialProfiles' => [[
                'id' => 'http://example.test/profile',
                'type' => 'Concept',
                'inScheme' => ['id' => 'http://example.test/wrong-scheme'],
                'prefLabel' => ['en' => ['Profile']],
            ]],
            'credentialSchema' => [
                'id' => 'http://data.europa.eu/snb/model/ap/edc-generic-full',
                'type' => 'ShaclValidator2017',
            ],
            'credentialSubject' => ['id' => 'subject-1', 'type' => 'Person'],
            'displayParameter' => ['id' => 'display-1', 'type' => 'DisplayParameter'],
            'validFrom' => '2026-01-01T00:00:00Z',
        ];

        $this->expectExceptionMessage('ELM credential profile scheme');

        (new CredentialDocumentValidator())->validate($document);
    }

    public function testCredentialDocumentValidatorReportsMissingRequiredField(): void {
        $this->expectExceptionMessage('credentialProfiles');

        (new CredentialDocumentValidator())->validate([
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'http://data.europa.eu/snb/model/context/edc-ap',
            ],
            'type' => ['VerifiableCredential', 'EuropeanDigitalCredential'],
        ]);
    }

    public function testCredentialDocumentValidatorRejectsMalformedDates(): void {
        $this->expectExceptionMessage('validFrom');

        (new CredentialDocumentValidator())->validate([
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'http://data.europa.eu/snb/model/context/edc-ap',
            ],
            'type' => ['VerifiableCredential', 'EuropeanDigitalCredential'],
            'credentialProfiles' => [[
                'id' => 'http://data.europa.eu/snb/credential/e34929035b',
                'type' => 'Concept',
                'inScheme' => ['id' => 'http://data.europa.eu/snb/credential/25831c2'],
                'prefLabel' => ['en' => ['Generic']],
            ]],
            'credentialSchema' => [
                'id' => 'http://data.europa.eu/snb/model/ap/edc-generic-full',
                'type' => 'ShaclValidator2017',
            ],
            'credentialSubject' => ['id' => 'subject-1', 'type' => 'Person'],
            'displayParameter' => ['id' => 'display-1', 'type' => 'DisplayParameter'],
            'validFrom' => '2026-01-01',
        ]);
    }

    public function testCredentialDocumentValidatorRejectsInvalidNestedEntityTypes(): void {
        $this->expectExceptionMessage('credentialSubject');

        (new CredentialDocumentValidator())->validate([
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'http://data.europa.eu/snb/model/context/edc-ap',
            ],
            'type' => ['VerifiableCredential', 'EuropeanDigitalCredential'],
            'credentialProfiles' => [[
                'id' => 'http://data.europa.eu/snb/credential/e34929035b',
                'type' => 'Concept',
                'inScheme' => ['id' => 'http://data.europa.eu/snb/credential/25831c2'],
                'prefLabel' => ['en' => ['Generic']],
            ]],
            'credentialSchema' => [
                'id' => 'http://data.europa.eu/snb/model/ap/edc-generic-full',
                'type' => 'ShaclValidator2017',
            ],
            'credentialSubject' => ['id' => 'subject-1', 'type' => 'Organisation'],
            'displayParameter' => ['id' => 'display-1', 'type' => 'DisplayParameter'],
            'validFrom' => '2026-01-01T00:00:00Z',
        ]);
    }

    public function testCredentialDocumentValidatorRejectsMalformedIssuer(): void {
        $this->expectExceptionMessage('issuer');

        (new CredentialDocumentValidator())->validate([
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'http://data.europa.eu/snb/model/context/edc-ap',
            ],
            'type' => ['VerifiableCredential', 'EuropeanDigitalCredential'],
            'credentialProfiles' => [[
                'id' => 'http://data.europa.eu/snb/credential/e34929035b',
                'type' => 'Concept',
                'inScheme' => ['id' => 'http://data.europa.eu/snb/credential/25831c2'],
                'prefLabel' => ['en' => ['Generic']],
            ]],
            'credentialSchema' => [
                'id' => 'http://data.europa.eu/snb/model/ap/edc-generic-full',
                'type' => 'ShaclValidator2017',
            ],
            'credentialSubject' => ['id' => 'subject-1', 'type' => 'Person'],
            'displayParameter' => ['id' => 'display-1', 'type' => 'DisplayParameter'],
            'issuer' => ['id' => 'issuer-1', 'type' => 'Person'],
            'validFrom' => '2026-01-01T00:00:00Z',
        ]);
    }

    public function testRepresentativeFixtureSharesStableElmProfileFields(): void {
        $fixture = json_decode(
            (string) file_get_contents(__DIR__ . '/../resources/profile/AA-Annex1-MC-unsigned.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $credential = $fixture['credential'];

        self::assertSame(['VerifiableCredential', 'EuropeanDigitalCredential'], $credential['type']);
        self::assertSame(
            'http://data.europa.eu/snb/model/ap/edc-generic-full',
            $credential['credentialSchema'][0]['id'],
        );
        self::assertSame('ShaclValidator2017', $credential['credentialSchema'][0]['type']);
        self::assertSame('Person', $credential['credentialSubject']['type']);
        self::assertSame('DisplayParameter', $credential['displayParameter']['type']);
        self::assertSame('2024-01-01T00:00:00+01:00', $credential['validFrom']);
        self::assertArrayNotHasKey('@context', $credential);
    }

    public function testSpecificationSerializesEducationConcepts(): void {
        $level = new Concept(
            'http://example.test/education-level/6',
            new LocalizedString(['en' => 'Bachelor']),
            new ConceptScheme('http://example.test/education-levels'),
        );
        $subject = new Concept(
            'http://example.test/education-subject/computing',
            new LocalizedString(['en' => 'Computing']),
            new ConceptScheme('http://data.europa.eu/snb/isced-f/25831c2'),
        );
        $specification = new LearningAchievementSpecification(
            'specification-1',
            new LocalizedString(['en' => 'Digital skills']),
            null,
            [],
            null,
            [],
            null,
            null,
            [],
            [],
            [],
            [$level],
            [$subject],
        );

        self::assertSame('Bachelor', $specification->toArray()['educationLevel'][0]['prefLabel']['en'][0]);
        self::assertSame('Computing', $specification->toArray()['educationSubject'][0]['prefLabel']['en'][0]);
    }

    public function testLearningAchievementSerializesProvenByAssessment(): void {
        $country = new Concept(
            'http://example.test/country/DE',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
        );
        $organisation = new Organisation(
            'organisation-1',
            new Location(
                'location-1',
                new Address(
                    'address-1',
                    $country,
                    new Note('note-1', new LocalizedString(['en' => 'Berlin'])),
                ),
            ),
            new LocalizedString(['en' => 'Example Authority']),
        );
        $assessmentType = new Concept(
            'http://example.test/assessment/exam',
            new LocalizedString(['en' => 'Exam']),
            new ConceptScheme('http://data.europa.eu/snb/assessment/25831c2'),
        );
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
        );
        $verification = new Concept(
            'http://example.test/verification/identity',
            new LocalizedString(['en' => 'Identity verification']),
            new ConceptScheme('http://data.europa.eu/snb/supervision-verification/25831c2'),
        );
        $specification = new LearningAssessmentSpecification(
            'assessment-spec-1',
            new LocalizedString(['en' => 'Digital artefact assessment']),
            $assessmentType,
            new GradingScheme(
                'grading-1',
                new LocalizedString(['en' => 'Pass or fail']),
                new LocalizedString(['en' => 'Simple grading']),
            ),
            $language,
            $assessmentType,
        );
        $assessment = new LearningAssessment(
            'assessment-1',
            new AwardingProcess('awarding-2', $organisation),
            new LocalizedString(['en' => 'Digital artefact assessment']),
            new Note('grade-1', new LocalizedString(['en' => 'Pass'])),
            $verification,
            $specification,
            [new LearningAssessment(
                'assessment-part-1',
                new AwardingProcess('awarding-2', $organisation),
                new LocalizedString(['en' => 'Evidence review']),
                new Note('grade-part-1', new LocalizedString(['en' => 'Pass'])),
                $verification,
                $specification,
            )],
        );
        $achievement = new LearningAchievement(
            'achievement-1',
            new LocalizedString(['en' => 'Digital Skills']),
            new AwardingProcess('awarding-1', $organisation),
            new LearningAchievementSpecification('specification-1', new LocalizedString(['en' => 'Digital Skills'])),
            null,
            [$assessment],
        );

        self::assertSame('LearningAssessment', $achievement->toArray()['provenBy'][0]['type']);
        self::assertSame('Pass', $achievement->toArray()['provenBy'][0]['grade']['noteLiteral']['en'][0]);
        self::assertSame(
            'urn:epass:learningAssessment:assessment-part-1',
            $achievement->toArray()['provenBy'][0]['hasPart'][0]['id'],
        );
    }

    public function testLearningAchievementSerializesIdentifiers(): void {
        $country = new Concept(
            'http://example.test/country/DE',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
        );
        $organisation = new Organisation(
            'organisation-1',
            new Location(
                'location-1',
                new Address(
                    'address-1',
                    $country,
                    new Note('note-1', new LocalizedString(['en' => 'Berlin'])),
                ),
            ),
            new LocalizedString(['en' => 'Example Authority']),
        );
        $achievement = new LearningAchievement(
            'achievement-1',
            new LocalizedString(['en' => 'Digital Skills']),
            new AwardingProcess('awarding-1', $organisation),
            new LearningAchievementSpecification('specification-1', new LocalizedString(['en' => 'Digital Skills'])),
            null,
            [],
            [new Identifier('achievement-code-1', 'DS-001', 'Achievement registry')],
        );

        self::assertSame('Identifier', $achievement->toArray()['identifier'][0]['type']);
        self::assertSame('DS-001', $achievement->toArray()['identifier'][0]['notation']);
    }

    public function testLearningAchievementSerializesInfluencingActivity(): void {
        $country = new Concept(
            'http://example.test/country/DE',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
        );
        $organisation = new Organisation(
            'organisation-1',
            new Location(
                'location-1',
                new Address(
                    'address-1',
                    $country,
                    new Note('note-1', new LocalizedString(['en' => 'Berlin'])),
                ),
            ),
            new LocalizedString(['en' => 'Example Authority']),
        );
        $activity = new LearningActivity(
            'activity-1',
            new LocalizedString(['en' => 'Digital micro-credential creation']),
            new AwardingProcess('awarding-1', $organisation),
            new LearningActivitySpecification(
                'activity-spec-1',
                new LocalizedString(['en' => 'Digital micro-credential creation']),
            ),
            null,
            [new LearningActivity(
                'activity-part-1',
                new LocalizedString(['en' => 'Preparation']),
                new AwardingProcess('awarding-1', $organisation),
                new LearningActivitySpecification(
                    'activity-spec-part-1',
                    new LocalizedString(['en' => 'Preparation']),
                ),
            )],
        );
        $achievement = new LearningAchievement(
            'achievement-1',
            new LocalizedString(['en' => 'Digital Skills']),
            new AwardingProcess('awarding-2', $organisation),
            new LearningAchievementSpecification('specification-1', new LocalizedString(['en' => 'Digital Skills'])),
            null,
            [],
            [],
            [$activity],
        );

        self::assertSame('LearningActivity', $achievement->toArray()['influencedBy'][0]['type']);
        self::assertSame('urn:epass:activity:activity-1', $achievement->toArray()['influencedBy'][0]['id']);
        self::assertSame(
            'urn:epass:activity:activity-part-1',
            $achievement->toArray()['influencedBy'][0]['hasPart'][0]['id'],
        );
    }

    public function testLearningAchievementSerializesEntitlement(): void {
        $country = new Concept(
            'http://example.test/country/DE',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
        );
        $organisation = new Organisation(
            'organisation-2',
            new Location(
                'location-2',
                new Address('address-2', $country, new Note('note-2', new LocalizedString(['en' => 'Berlin']))),
            ),
            new LocalizedString(['en' => 'Example Authority']),
        );
        $entitlement = new LearningEntitlement(
            'entitlement-1',
            new LocalizedString(['en' => 'Right to join the organisation']),
            new AwardingProcess('awarding-3', $organisation),
            null,
            [],
            new \DateTimeImmutable('2025-01-01T12:00:00+02:00'),
            null,
            new LearningEntitlementSpecification(
                'entitlement-spec-1',
                new LocalizedString(['en' => 'Membership entitlement']),
                new Concept(
                    'http://example.test/entitlement-type/membership',
                    new LocalizedString(['en' => 'Membership']),
                    new ConceptScheme('http://data.europa.eu/snb/entitlement/25831c2'),
                ),
            ),
        );

        self::assertSame('LearningEntitlement', $entitlement->toArray()['type']);
        self::assertSame('2025-01-01T10:00:00Z', $entitlement->toArray()['issued']);
        self::assertSame(
            'LearningEntitlementSpecification',
            $entitlement->toArray()['specifiedBy']['type'],
        );
    }

    public function testCredentialSerializesOptionalDatesInUtcAndOmitsUnsetDates(): void {
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ada']),
            new LocalizedString(['en' => 'Lovelace']),
            new LocalizedString(['en' => 'Ada Lovelace']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array {
                    return ['id' => $this->id, 'type' => 'Claim'];
                }
            }],
        );
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
        );
        $credential = new Credential(
            'credential-1',
            $subject,
            new DisplayParameter('display-1', $language, $language, new LocalizedString(['en' => 'Title'])),
            new \DateTimeImmutable('2024-01-01T00:00:00+01:00'),
            null,
            null,
            new \DateTimeImmutable('2024-01-02T01:00:00+02:00'),
            new \DateTimeImmutable('2024-01-03T03:00:00+03:00'),
            new \DateTimeImmutable('2024-01-04T04:00:00+04:00'),
        );

        $data = $credential->toArray();
        self::assertSame('2023-12-31T23:00:00Z', $data['validFrom']);
        self::assertSame('2024-01-01T23:00:00Z', $data['issuanceDate']);
        self::assertSame('2024-01-03T00:00:00Z', $data['issued']);
        self::assertSame('2024-01-04T00:00:00Z', $data['validUntil']);
        self::assertArrayNotHasKey('expirationDate', $data);
    }

    public function testDisplayDescriptionIsOptionalAndLocalized(): void {
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
        );
        $display = new DisplayParameter(
            'display-1',
            $language,
            $language,
            new LocalizedString(['en' => 'Title']),
            new LocalizedString(['en' => 'Description', 'de' => 'Beschreibung']),
        );

        self::assertSame([
            'en' => ['Description'],
            'de' => ['Beschreibung'],
        ], $display->toArray()['description']);
        self::assertArrayNotHasKey(
            'description',
            (new DisplayParameter(
                'display-2',
                $language,
                $language,
                new LocalizedString(['en' => 'Title']),
            ))->toArray(),
        );
    }

    public function testDisplayMediaObjectsSerializeAsNestedProfileObjects(): void {
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
        );
        $encoding = new Concept(
            'http://data.europa.eu/snb/encoding/6146cde7dd',
            new LocalizedString(['en' => 'base64']),
            new ConceptScheme('http://data.europa.eu/snb/encoding/25831c2'),
        );
        $contentType = new Concept(
            'http://publications.europa.eu/resource/authority/file-type/PNG',
            new LocalizedString(['en' => 'PNG']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/file-type'),
            'file-type',
        );
        $media = new MediaObject('media-1', 'aGVsbG8=', $encoding, $contentType);
        $display = new IndividualDisplay(
            'individual-1',
            $language,
            [new DisplayDetail('detail-1', 1, $media)],
        );
        $parameter = new DisplayParameter(
            'display-1',
            $language,
            $language,
            new LocalizedString(['en' => 'Title']),
            null,
            [$display],
        );

        self::assertSame(
            'MediaObject',
            $parameter->toArray()['individualDisplay'][0]['displayDetail'][0]['image']['type'],
        );
        self::assertSame(
            'aGVsbG8=',
            $parameter->toArray()['individualDisplay'][0]['displayDetail'][0]['image']['content'],
        );
    }

    public function testDisplayDetailRejectsNonPositivePages(): void {
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
        );
        $encoding = new Concept(
            'http://example.test/encoding/base64',
            new LocalizedString(['en' => 'Base64']),
            new ConceptScheme('http://data.europa.eu/snb/encoding/25831c2'),
        );
        $contentType = new Concept(
            'http://publications.europa.eu/resource/authority/file-type/PNG',
            new LocalizedString(['en' => 'PNG']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/file-type'),
        );
        $media = new MediaObject('media-1', 'aGVsbG8=', $encoding, $contentType);

        $this->expectException(InvalidCredentialException::class);
        new DisplayDetail('detail-1', 0, $media);
    }

    public function testCredentialSubjectSerializesOptionalBirthDataInUtc(): void {
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ada']),
            new LocalizedString(['en' => 'Lovelace']),
            new LocalizedString(['en' => 'Ada Lovelace']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array {
                    return ['id' => $this->id, 'type' => 'Claim'];
                }
            }],
            new LocalizedString(['en' => 'Augusta Ada King']),
            new \DateTimeImmutable('1815-12-10T00:00:00+01:00'),
        );

        $data = $subject->toArray();
        self::assertSame(['en' => ['Augusta Ada King']], $data['birthName']);
        self::assertSame('1815-12-09T23:00:00Z', $data['dateOfBirth']);
    }

    public function testCredentialSubjectSerializesTypedIdentifiersWithProfileShapes(): void {
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/ITA',
            new LocalizedString(['en' => 'Italy']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
            'country',
        );
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ana']),
            new LocalizedString(['en' => 'Andromeda']),
            new LocalizedString(['en' => 'Ana Andromeda']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array {
                    return ['id' => $this->id, 'type' => 'Claim'];
                }
            }],
            null,
            null,
            new Identifier('identifier-1', '87654321', 'Student Card ID'),
            new LegalIdentifier('legal-1', 'IT-12345678', $country),
        );

        $data = $subject->toArray();
        self::assertSame('87654321', $data['identifier'][0]['notation']);
        self::assertSame('LegalIdentifier', $data['nationalID']['type']);
        self::assertSame('ITA', substr($data['nationalID']['spatial']['id'], -3));
    }

    public function testSubjectContactPointSerializesAddressAndMailboxArrays(): void {
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/ITA',
            new LocalizedString(['en' => 'Italy']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
            'country',
        );
        $contact = new ContactPoint(
            'contact-1',
            new Address('address-1', $country, new Note('note-1', new LocalizedString(['en' => 'Via da Vinci, 12']))),
            new EmailAddress('ana.andromeda@example.com'),
        );
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ana']),
            new LocalizedString(['en' => 'Andromeda']),
            new LocalizedString(['en' => 'Ana Andromeda']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array {
                    return ['id' => $this->id, 'type' => 'Claim'];
                }
            }],
            null,
            null,
            null,
            null,
            $contact,
        );

        $data = $subject->toArray();
        self::assertSame('ContactPoint', $data['contactPoint'][0]['type']);
        self::assertSame('ana.andromeda@example.com', substr($data['contactPoint'][0]['emailAddress'][0]['id'], 7));
        self::assertSame(
            ['en' => ['Via da Vinci, 12']],
            $data['contactPoint'][0]['address'][0]['fullAddress']['noteLiteral'],
        );
    }

    public function testCredentialSerializesIssuerWithRegistrationAndRawIssuerId(): void {
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/DEU',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
            'country',
        );
        $address = new Address(
            'address-1',
            $country,
            new Note('note-1', new LocalizedString(['en' => 'Berlin'])),
        );
        $issuer = new Issuer(
            'did:example:issuer',
            new Location('location-1', $address),
            new LocalizedString(['en' => 'Example Authority']),
            new LegalIdentifier('legal-1', 'DE-123', $country),
        );

        self::assertSame('did:example:issuer', $issuer->toArray()['id']);
        self::assertSame('DE-123', $issuer->toArray()['registration']['notation']);
    }

    public function testLearningAchievementSerializesRequiredClaimStructure(): void {
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/DEU',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
        );
        $organisation = new Organisation(
            'organisation-1',
            new Location(
                'location-1',
                new Address(
                    'address-1',
                    $country,
                    new Note('note-1', new LocalizedString(['en' => 'Berlin'])),
                ),
            ),
            new LocalizedString(['en' => 'Example Authority']),
        );
        $title = new LocalizedString(['en' => 'Digital Skills']);
        $achievement = new LearningAchievement(
            'achievement-1',
            $title,
            new AwardingProcess('awarding-1', $organisation),
            new LearningAchievementSpecification('specification-1', $title, new LocalizedString(['en' => 'A course.'])),
        );

        $data = $achievement->toArray();
        self::assertSame('LearningAchievement', $data['type']);
        self::assertSame('Organisation', $data['awardedBy']['awardingBody']['type']);
        self::assertSame(['en' => ['A course.']], $data['specifiedBy']['description']);
    }

    public function testAchievementSpecificationSerializesCreditPointArray(): void {
        $framework = new Concept(
            'http://data.europa.eu/snb/education-credit/6fcec5c5af',
            new LocalizedString(['en' => 'European Credit Transfer System']),
            new ConceptScheme('http://data.europa.eu/snb/education-credit/25831c2'),
        );
        $specification = new LearningAchievementSpecification(
            'specification-1',
            new LocalizedString(['en' => 'Digital skills']),
            null,
            [new CreditPoint('credit-1', $framework, '2')],
        );

        self::assertSame('2', $specification->toArray()['creditPoint'][0]['point']);
        self::assertSame('Concept', $specification->toArray()['creditPoint'][0]['framework']['type']);
    }

    public function testAchievementSpecificationSerializesLanguageCategoriesAndDurations(): void {
        $language = new Concept(
            'http://publications.europa.eu/resource/authority/language/ENG',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/language'),
            'language',
        );
        $specification = new LearningAchievementSpecification(
            'specification-1',
            new LocalizedString(['en' => 'Digital skills']),
            null,
            [],
            $language,
            ['micro-module'],
            'P1M',
            'PT4H',
        );

        $data = $specification->toArray();
        self::assertSame('ENG', substr($data['language'][0]['id'], -3));
        self::assertSame(['micro-module'], $data['category']);
        self::assertSame('P1M', $data['maximumDuration']);
        self::assertSame('PT4H', $data['volumeOfLearning']);
    }

    public function testAchievementSpecificationRejectsMalformedDuration(): void {
        $this->expectException(\InvalidArgumentException::class);

        new LearningAchievementSpecification(
            'specification-1',
            new LocalizedString(['en' => 'Digital skills']),
            null,
            [],
            null,
            [],
            'one month',
        );
    }

    public function testQualificationUsesQualificationProfileTypeAndIdentifier(): void {
        $qualificationCode = new Concept(
            'http://example.test/qualification/code',
            new LocalizedString(['en' => 'Example qualification']),
            new ConceptScheme('http://example.test/qualification'),
        );
        $qualification = new Qualification(
            'qualification-1',
            new LocalizedString(['en' => 'Digital micro-credential creation']),
            null,
            [],
            null,
            [],
            null,
            null,
            false,
            [$qualificationCode],
        );

        $data = $qualification->toArray();
        self::assertSame('Qualification', $data['type']);
        self::assertSame('urn:epass:qualification:qualification-1', $data['id']);
        self::assertFalse($data['isPartialQualification']);
        self::assertSame('Example qualification', $data['qualificationCodes'][0]['prefLabel']['en'][0]);
    }

    public function testSpecificationSerializesLearningOutcomeAndRelatedSkills(): void {
        $skill = new Concept(
            'http://example.test/skill/one',
            new LocalizedString(['en' => 'Problem solving']),
            new ConceptScheme(ElmVocabularySchemes::DCF_SKILLS),
        );
        $specification = new LearningAchievementSpecification(
            'specification-1',
            new LocalizedString(['en' => 'Digital skills']),
            null,
            [],
            null,
            [],
            null,
            null,
            [new LearningOutcome('outcome-1', new LocalizedString(['en' => 'Can solve problems']), [$skill])],
        );

        $data = $specification->toArray();
        self::assertSame('LearningOutcome', $data['learningOutcome'][0]['type']);
        self::assertSame('Problem solving', $data['learningOutcome'][0]['relatedSkills'][0]['prefLabel']['en'][0]);
    }

    public function testQualificationSerializesEqfAndNqfLevels(): void {
        $eqf = new Concept(
            'http://data.europa.eu/snb/eqf/level-4',
            new LocalizedString(['en' => 'Level 4']),
            new ConceptScheme('http://data.europa.eu/snb/eqf/25831c2'),
        );
        $nqf = new Concept(
            'http://example.test/nqf/level-4',
            new LocalizedString(['en' => 'National level 4']),
            new ConceptScheme('http://data.europa.eu/snb/qdr/c_ef113b94'),
        );
        $qualificationCode = new Concept(
            'http://example.test/qualification-framework/code-123',
            new LocalizedString(['en' => 'Example qualification code']),
            new ConceptScheme('http://example.test/qualification-framework'),
            'code-123',
        );
        $qualification = new Qualification(
            'qualification-1',
            new LocalizedString(['en' => 'Qualification']),
            null,
            [],
            null,
            [],
            null,
            null,
            null,
            qualificationCodes: [$qualificationCode],
            eqfLevel: $eqf,
            nqfLevels: [$nqf],
        );

        $data = $qualification->toArray();
        self::assertSame('http://data.europa.eu/snb/eqf/level-4', $data['eqfLevel']['id']);
        self::assertSame('http://example.test/nqf/level-4', $data['nqfLevel'][0]['id']);
        self::assertSame(
            'http://example.test/qualification-framework',
            $data['qualificationCodes'][0]['inScheme']['id'],
        );
    }

    public function testLearningOutcomeAndSpecificationSerializeAdditionalNotes(): void {
        $note = new Note('note-1', new LocalizedString(['en' => 'Additional context']));
        $outcome = new LearningOutcome('outcome-1', new LocalizedString(['en' => 'Can apply skills']), [], [$note]);
        $specification = new LearningAchievementSpecification(
            'specification-1',
            new LocalizedString(['en' => 'Digital skills']),
            null,
            [],
            null,
            [],
            null,
            null,
            [],
            [$note],
        );

        self::assertSame('Additional context', $outcome->toArray()['additionalNote'][0]['noteLiteral']['en'][0]);
        self::assertSame('Note', $specification->toArray()['additionalNote'][0]['type']);
    }

    public function testSpecificationSerializesSupplementaryWebResources(): void {
        $resource = new WebResource(
            'resource-1',
            'https://example.test/course',
            new LocalizedString(['en' => 'Course details']),
        );
        $specification = new LearningAchievementSpecification(
            'specification-1',
            new LocalizedString(['en' => 'Digital skills']),
            null,
            [],
            null,
            [],
            null,
            null,
            [],
            [],
            [$resource],
        );

        $document = $specification->toArray()['supplementaryDocument'][0];
        self::assertSame('urn:epass:webResource:resource-1', $document['id']);
        self::assertSame('https://example.test/course', $document['contentURL']);
        self::assertSame(['Course details'], $document['title']['en']);
    }

    public function testWebResourceRejectsNonHttpUrl(): void {
        $this->expectException(\InvalidArgumentException::class);

        new WebResource('resource-1', 'javascript:alert(1)');
    }

    public function testQualificationSerializesAccreditations(): void {
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/IRL',
            new LocalizedString(['en' => 'Ireland']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
        );
        $organisation = new Organisation(
            'organisation-1',
            new Location(
                'location-1',
                new Address(
                    'address-1',
                    $country,
                    new Note('note-1', new LocalizedString(['en' => 'Dublin'])),
                ),
            ),
            new LocalizedString(['en' => 'Quality Authority']),
            new LegalIdentifier('registration-1', '123', $country),
        );
        $accreditation = new Accreditation(
            'accreditation-1',
            new LocalizedString(['en' => 'Quality assured']),
            $organisation,
        );
        $qualification = new Qualification(
            'qualification-1',
            new LocalizedString(['en' => 'Qualification']),
            null,
            [],
            null,
            [],
            null,
            null,
            null,
            [],
            null,
            [],
            [$accreditation],
        );

        self::assertSame('Accreditation', $qualification->toArray()['accreditation'][0]['type']);
        self::assertSame(
            'Quality Authority',
            $qualification->toArray()['accreditation'][0]['accreditingAgent']['legalName']['en'][0],
        );
    }

    public function testLearningAchievementSerializesReceivedCredit(): void {
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/DEU',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
        );
        $organisation = new Organisation(
            'organisation-1',
            new Location(
                'location-1',
                new Address('address-1', $country, new Note('note-1', new LocalizedString(['en' => 'Berlin']))),
            ),
            new LocalizedString(['en' => 'Example Authority']),
        );
        $credit = new CreditPoint(
            'credit-1',
            new Concept(
                'http://data.europa.eu/snb/ects',
                new LocalizedString(['en' => 'ECTS']),
                new ConceptScheme('http://data.europa.eu/snb/education-credit/25831c2'),
            ),
            '3',
        );
        $achievement = new LearningAchievement(
            'achievement-1',
            new LocalizedString(['en' => 'Digital Skills']),
            new AwardingProcess('awarding-1', $organisation),
            new LearningAchievementSpecification('specification-1', new LocalizedString(['en' => 'Digital Skills'])),
            $credit,
        );

        self::assertSame('CreditPoint', $achievement->toArray()['creditReceived']['type']);
        self::assertSame('3', $achievement->toArray()['creditReceived']['point']);
    }

    public function testQualificationRejectsInvalidAccreditationType(): void {
        $this->expectException(InvalidCredentialException::class);

        new Qualification(
            'qualification-1',
            new LocalizedString(['en' => 'Qualification']),
            null,
            [],
            null,
            [],
            null,
            null,
            null,
            [],
            null,
            [],
            [new \stdClass()],
        );
    }
}
