<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanDigitalCredentials\Entity;
use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\Claim;
use IsyThl\EuropeanDigitalCredentials\Address;
use IsyThl\EuropeanDigitalCredentials\AwardingProcess;
use IsyThl\EuropeanDigitalCredentials\ContactPoint;
use IsyThl\EuropeanDigitalCredentials\CreditPoint;
use IsyThl\EuropeanDigitalCredentials\Credential;
use IsyThl\EuropeanDigitalCredentials\CredentialSubject;
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
use IsyThl\EuropeanDigitalCredentials\LearningAchievementSpecification;
use IsyThl\EuropeanDigitalCredentials\LearningOutcome;
use IsyThl\EuropeanDigitalCredentials\Organisation;
use IsyThl\EuropeanDigitalCredentials\Qualification;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase
{
    public function testSerializationIsDeterministicAndPreservesUnicode(): void
    {
        $entity = new class ('urn:test:credential') extends Entity {
            public function toArray(): array
            {
                return ['id' => $this->id, 'label' => ['en' => ['Zoë']]];
            }
        };

        $expected = '{"id":"urn:test:credential","label":{"en":["Zoë"]}}';

        self::assertSame($expected, $entity->toJson());
        self::assertSame($expected, $entity->toJson());
    }

    public function testGeneratedIdentifiersHaveCredentialUrnFormat(): void
    {
        $entity = new class extends Entity {
            public function toArray(): array
            {
                return ['id' => $this->id];
            }
        };

        self::assertMatchesRegularExpression('/^urn:credential:[0-9a-f]{32}$/', $entity->id);
    }

    public function testLocalizedValuesKeepLanguageMapAndArrayShape(): void
    {
        $localized = new LocalizedString(['en' => 'Course completion', 'de' => ['Kursabschluss']]);

        self::assertSame([
            'en' => ['Course completion'],
            'de' => ['Kursabschluss'],
        ], $localized->toArray());
    }

    public function testLocalizedValuesRejectMalformedLanguages(): void
    {
        $this->expectException(InvalidCredentialException::class);

        new LocalizedString(['english' => 'Course completion']);
    }

    public function testConceptRoundTripsItsJsonLdShape(): void
    {
        $concept = new Concept(
            'http://example.test/concept/one',
            new LocalizedString(['en' => 'One']),
            new ConceptScheme('http://example.test/scheme'),
            'one',
        );

        self::assertSame($concept->toArray(), Concept::fromArray($concept->toArray())->toArray());
    }

    public function testConceptRejectsMissingProfileFields(): void
    {
        $this->expectException(InvalidCredentialException::class);

        Concept::fromArray(['id' => 'http://example.test/concept/one']);
    }

    public function testCredentialIncludesTheGenericProfileByDefault(): void
    {
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ada']),
            new LocalizedString(['en' => 'Lovelace']),
            new LocalizedString(['en' => 'Ada Lovelace']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array
                {
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
    }

    public function testCredentialSerializesOptionalDatesInUtcAndOmitsUnsetDates(): void
    {
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ada']),
            new LocalizedString(['en' => 'Lovelace']),
            new LocalizedString(['en' => 'Ada Lovelace']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array
                {
                    return ['id' => $this->id, 'type' => 'Claim'];
                }
            }],
        );
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://example.test/languages'),
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

    public function testDisplayDescriptionIsOptionalAndLocalized(): void
    {
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://example.test/languages'),
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
            (new DisplayParameter('display-2', $language, $language, new LocalizedString(['en' => 'Title'])))->toArray(),
        );
    }

    public function testDisplayMediaObjectsSerializeAsNestedProfileObjects(): void
    {
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://example.test/languages'),
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

        self::assertSame('MediaObject', $parameter->toArray()['individualDisplay'][0]['displayDetail'][0]['image']['type']);
        self::assertSame('aGVsbG8=', $parameter->toArray()['individualDisplay'][0]['displayDetail'][0]['image']['content']);
    }

    public function testDisplayDetailRejectsNonPositivePages(): void
    {
        $language = new Concept(
            'http://example.test/language/en',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme('http://example.test/languages'),
        );
        $media = new MediaObject('media-1', 'aGVsbG8=', $language, $language);

        $this->expectException(InvalidCredentialException::class);
        new DisplayDetail('detail-1', 0, $media);
    }

    public function testCredentialSubjectSerializesOptionalBirthDataInUtc(): void
    {
        $subject = new CredentialSubject(
            'subject-1',
            new LocalizedString(['en' => 'Ada']),
            new LocalizedString(['en' => 'Lovelace']),
            new LocalizedString(['en' => 'Ada Lovelace']),
            [new class ('claim-1') extends Claim {
                public function toArray(): array
                {
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

    public function testCredentialSubjectSerializesTypedIdentifiersWithProfileShapes(): void
    {
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
                public function toArray(): array
                {
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

    public function testSubjectContactPointSerializesAddressAndMailboxArrays(): void
    {
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
                public function toArray(): array
                {
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
        self::assertSame(['en' => ['Via da Vinci, 12']], $data['contactPoint'][0]['address'][0]['fullAddress']['noteLiteral']);
    }

    public function testCredentialSerializesIssuerWithRegistrationAndRawIssuerId(): void
    {
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/DEU',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
            'country',
        );
        $address = new Address('address-1', $country, new Note('note-1', new LocalizedString(['en' => 'Berlin'])));
        $issuer = new Issuer(
            'did:example:issuer',
            new Location('location-1', $address),
            new LocalizedString(['en' => 'Example Authority']),
            new LegalIdentifier('legal-1', 'DE-123', $country),
        );

        self::assertSame('did:example:issuer', $issuer->toArray()['id']);
        self::assertSame('DE-123', $issuer->toArray()['registration']['notation']);
    }

    public function testLearningAchievementSerializesRequiredClaimStructure(): void
    {
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/DEU',
            new LocalizedString(['en' => 'Germany']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/country'),
        );
        $organisation = new Organisation(
            'organisation-1',
            new Location('location-1', new Address('address-1', $country, new Note('note-1', new LocalizedString(['en' => 'Berlin'])))),
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

    public function testAchievementSpecificationSerializesCreditPointArray(): void
    {
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

    public function testAchievementSpecificationSerializesLanguageCategoriesAndDurations(): void
    {
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

    public function testAchievementSpecificationRejectsMalformedDuration(): void
    {
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

    public function testQualificationUsesQualificationProfileTypeAndIdentifier(): void
    {
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

    public function testSpecificationSerializesLearningOutcomeAndRelatedSkills(): void
    {
        $skill = new Concept(
            'http://example.test/skill/one',
            new LocalizedString(['en' => 'Problem solving']),
            new ConceptScheme('http://example.test/skills'),
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
}