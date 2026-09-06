<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanDigitalCredentials\Address;
use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\Identifier;
use IsyThl\EuropeanDigitalCredentials\LearningAchievementSpecification;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use IsyThl\EuropeanDigitalCredentials\Location;
use IsyThl\EuropeanDigitalCredentials\Note;
use IsyThl\EuropeanDigitalCredentials\Organisation;
use IsyThl\EuropeanDigitalCredentials\Qualification;
use IsyThl\EuropeanDigitalCredentials\WebResource;
use IsyThl\EuropeanLearningModel\Loq\LearningOpportunity;
use IsyThl\EuropeanLearningModel\Loq\LearningOpportunityDocument;
use IsyThl\EuropeanLearningModel\Loq\LoqDatasetDocument;
use IsyThl\EuropeanLearningModel\Loq\QualificationReference;
use IsyThl\EuropeanLearningModel\Loq\QualificationDocument;
use IsyThl\EuropeanLearningModel\Core\PeriodOfTime;
use PHPUnit\Framework\TestCase;

final class LoqTest extends TestCase {

    public function testQualificationReferencePreservesPersistentIdentity(): void {
        $reference = new QualificationReference(
            new Identifier('qualification-id', '123', 'example-qualifications'),
            'https://example.test/datasets/qualifications',
        );

        self::assertSame([
            'type' => 'QualificationReference',
            'identifier' => [
                'id' => 'urn:epass:identifier:qualification-id',
                'type' => 'Identifier',
                'notation' => '123',
                'schemeName' => 'example-qualifications',
            ],
            'datasetNamespace' => 'https://example.test/datasets/qualifications',
        ], $reference->toArray());
    }

    public function testIncompleteQualificationReferenceFailsBeforeSerialization(): void {
        $this->expectException(InvalidCredentialException::class);

        new QualificationReference(null);
    }

    public function testDocumentRejectsNonLearningOpportunityRoots(): void {
        $this->expectException(InvalidCredentialException::class);

        new LearningOpportunityDocument([['type' => 'LearningOpportunity']]);
    }

    public function testDatasetRejectsMalformedRootsBeforeSerialization(): void {
        $this->expectException(InvalidCredentialException::class);

        new LoqDatasetDocument([['type' => 'Qualification']]);
    }

    public function testDatasetSerializesMixedTypedRootsDeterministically(): void {
        $qualification = new Qualification(
            'https://example.test/qualification/1',
            new LocalizedString(['en' => 'Qualification']),
            eqfLevel: $this->concept(ElmVocabularySchemes::EQF, 'https://example.test/eqf/4'),
            nqfLevels: [$this->concept(ElmVocabularySchemes::QDR_BASE, 'https://example.test/nqf/4')],
            learningOutcomes: [new \IsyThl\EuropeanDigitalCredentials\LearningOutcome(
                'https://example.test/outcome/1',
                new LocalizedString(['en' => 'Outcome']),
            )],
            educationSubjects: [$this->concept(ElmVocabularySchemes::ISCED_F, 'https://example.test/isced/1')],
        );
        $opportunity = $this->opportunity();
        $dataset = new LoqDatasetDocument([$qualification, $opportunity]);

        self::assertSame($dataset->toJson(), $dataset->toJson());
        self::assertCount(2, $dataset->toArray()['@graph']);
    }

    public function testLearningOpportunityAcceptsEmbeddedQualification(): void {
        $qualification = new Qualification(
            'https://example.test/qualification/embedded',
            new LocalizedString(['en' => 'Embedded qualification']),
            eqfLevel: $this->concept(ElmVocabularySchemes::EQF, 'https://example.test/eqf/4'),
            nqfLevels: [$this->concept(ElmVocabularySchemes::QDR_BASE, 'https://example.test/nqf/4')],
            learningOutcomes: [new \IsyThl\EuropeanDigitalCredentials\LearningOutcome(
                'https://example.test/outcome/embedded',
                new LocalizedString(['en' => 'Embedded outcome']),
            )],
            educationSubjects: [$this->concept(ElmVocabularySchemes::ISCED_F, 'https://example.test/isced/1')],
        );

        $opportunity = $this->opportunityWithSpecification($qualification);

        self::assertSame(
            $qualification->toArray(),
            $opportunity->toArray()['learningAchievementSpecification'],
        );
    }

    public function testLearningOpportunitySerializesTemporalCoverageInUtc(): void {
        $opportunity = $this->opportunityWithTemporal(new PeriodOfTime(
            new \DateTimeImmutable('2026-01-01T12:00:00+02:00'),
            new \DateTimeImmutable('2026-01-02T12:00:00+02:00'),
        ));

        self::assertSame([
            'type' => 'PeriodOfTime',
            'startDate' => '2026-01-01T10:00:00Z',
            'endDate' => '2026-01-02T10:00:00Z',
        ], $opportunity->toArray()['temporal']);
    }

    public function testPeriodOfTimeRejectsReverseDates(): void {
        $this->expectException(InvalidCredentialException::class);

        new PeriodOfTime(
            new \DateTimeImmutable('2026-01-02T00:00:00Z'),
            new \DateTimeImmutable('2026-01-01T00:00:00Z'),
        );
    }

    public function testLearningOpportunitySerializesProviderLearningScheduleConcept(): void {
        $schedule = new Concept(
            'https://example.test/schedule/evenings',
            new LocalizedString(['en' => 'Evenings']),
            new ConceptScheme('https://example.test/vocabulary/learning-schedule'),
            'evenings',
        );

        $opportunity = $this->opportunityWithSchedule($schedule);

        self::assertSame($schedule->toArray(), $opportunity->toArray()['learningSchedule']);
    }

    public function testQualificationDocumentRejectsMissingProfileFields(): void {
        $qualification = new Qualification(
            'qualification-1',
            new LocalizedString(['en' => 'Incomplete qualification']),
        );

        $this->expectException(InvalidCredentialException::class);

        new QualificationDocument([$qualification]);
    }

    public function testLoqRootsSerializeOptionalPublisher(): void {
        $publisher = new Organisation(
            'publisher-1',
            new Location('publisher-location', new Address(
                'publisher-address',
                $this->countryConcept(),
                new Note('publisher-note', new LocalizedString(['en' => 'Brussels'])),
            )),
            new LocalizedString(['en' => 'Example publisher']),
        );
        $qualification = new Qualification(
            'https://example.test/qualification/1',
            new LocalizedString(['en' => 'Qualification']),
            eqfLevel: $this->concept(ElmVocabularySchemes::EQF, 'https://example.test/eqf/4'),
            nqfLevels: [$this->concept(ElmVocabularySchemes::QDR_BASE, 'https://example.test/nqf/4')],
            learningOutcomes: [new \IsyThl\EuropeanDigitalCredentials\LearningOutcome(
                'https://example.test/outcome/1',
                new LocalizedString(['en' => 'Outcome']),
            )],
            educationSubjects: [$this->concept(ElmVocabularySchemes::ISCED_F, 'https://example.test/isced/1')],
            publisher: $publisher,
        );

        self::assertSame($publisher->toArray(), $qualification->toArray()['publisher']);
    }

    private function countryConcept(): Concept {
        return new Concept(
            'http://publications.europa.eu/resource/authority/country/BEL',
            new LocalizedString(['en' => 'Belgium']),
            new ConceptScheme(ElmVocabularySchemes::COUNTRY),
            'BEL',
        );
    }

    private function concept(string $scheme, string $id): Concept {
        return new Concept($id, new LocalizedString(['en' => $id]), new ConceptScheme($scheme));
    }

    private function opportunity(): LearningOpportunity {
        return $this->opportunityWithSpecification(new LearningAchievementSpecification(
            'https://example.test/specification/1',
            new LocalizedString(['en' => 'Specification']),
        ));
    }

    private function opportunityWithSpecification(
        LearningAchievementSpecification|Qualification $specification,
    ): LearningOpportunity {
        return $this->opportunityWithSpecificationAndTemporal($specification, null);
    }

    private function opportunityWithTemporal(
        PeriodOfTime $temporal,
    ): LearningOpportunity {
        return $this->opportunityWithSpecificationAndTemporal(
            new LearningAchievementSpecification(
                'https://example.test/specification/1',
                new LocalizedString(['en' => 'Specification']),
            ),
            $temporal,
        );
    }

    private function opportunityWithSchedule(Concept $schedule): LearningOpportunity {
        return $this->opportunityWithSpecificationAndSchedule(
            new LearningAchievementSpecification(
                'https://example.test/specification/1',
                new LocalizedString(['en' => 'Specification']),
            ),
            $schedule,
        );
    }

    private function opportunityWithSpecificationAndTemporal(
        LearningAchievementSpecification|Qualification $specification,
        ?PeriodOfTime $temporal,
    ): LearningOpportunity {
        return $this->opportunityWithSpecificationTemporalAndSchedule($specification, $temporal, null);
    }

    private function opportunityWithSpecificationTemporalAndSchedule(
        LearningAchievementSpecification|Qualification $specification,
        ?PeriodOfTime $temporal,
        ?Concept $schedule,
    ): LearningOpportunity {
        $language = $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en');
        $country = $this->countryConcept();
        $provider = new Organisation(
            'provider-1',
            new Location('location-1', new Address(
                'address-1',
                $country,
                new Note('address-note', new LocalizedString(['en' => 'Brussels'])),
            )),
            new LocalizedString(['en' => 'Example Provider']),
        );

        return new LearningOpportunity(
            'https://example.test/opportunity/1',
            new LocalizedString(['en' => 'Opportunity']),
            $language,
            new WebResource('homepage-1', 'https://example.test/opportunity/1'),
            [$provider],
            $specification,
            temporal: $temporal,
            learningSchedule: $schedule,
        );
    }

    private function opportunityWithSpecificationAndSchedule(
        LearningAchievementSpecification|Qualification $specification,
        Concept $schedule,
    ): LearningOpportunity {
        return $this->opportunityWithSpecificationTemporalAndSchedule($specification, null, $schedule);
    }

    public function testDocumentSerializationIsDeterministic(): void {
        $language = new Concept(
            'http://publications.europa.eu/resource/authority/language/ENG',
            new LocalizedString(['en' => 'English']),
            new ConceptScheme(ElmVocabularySchemes::LANGUAGE),
            'ENG',
        );
        $country = new Concept(
            'http://publications.europa.eu/resource/authority/country/BEL',
            new LocalizedString(['en' => 'Belgium']),
            new ConceptScheme(ElmVocabularySchemes::COUNTRY),
            'BEL',
        );
        $provider = new Organisation(
            'provider-1',
            new Location('location-1', new Address('address-1', $country, new Note('address-note', new LocalizedString([
                'en' => 'Brussels',
            ])))),
            new LocalizedString(['en' => 'Example Provider']),
        );
        $specification = new LearningAchievementSpecification(
            'https://example.test/specifications/1',
            new LocalizedString(['en' => 'Example qualification']),
        );
        $opportunity = new LearningOpportunity(
            'https://example.test/opportunity/123',
            new LocalizedString(['en' => 'Example opportunity']),
            $language,
            new WebResource('homepage-1', 'https://example.test/opportunity/123'),
            [$provider],
            $specification,
        );
        $document = new LearningOpportunityDocument([
            $opportunity,
        ]);

        self::assertSame($document->toJson(), $document->toJson());
        self::assertStringContainsString('"@graph"', $document->toJson());
    }
}
