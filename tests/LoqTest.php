<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanDigitalCredentials\Address;
use IsyThl\EuropeanDigitalCredentials\AwardingProcess;
use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\Identifier;
use IsyThl\EuropeanDigitalCredentials\LearningAchievementSpecification;
use IsyThl\EuropeanDigitalCredentials\LearningActivity;
use IsyThl\EuropeanDigitalCredentials\LearningActivitySpecification;
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
use IsyThl\EuropeanLearningModel\Core\PriceDetail;
use IsyThl\EuropeanLearningModel\Core\Amount;
use IsyThl\EuropeanLearningModel\Core\Grant;
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

    public function testLearningOpportunitySerializesLocationAndAddress(): void {
        $location = new Location(
            'location-2',
            new Address(
                'address-2',
                $this->countryConcept(),
                new Note('address-note-2', new LocalizedString(['en' => 'Antwerp'])),
            ),
        );

        $opportunity = $this->opportunityWithLocation($location);

        self::assertSame($location->toArray(), $opportunity->toArray()['location']);
    }

    public function testLearningOpportunitySerializesPriceDetail(): void {
        $priceDetail = new PriceDetail(
            new LocalizedString(['en' => 'Course fee']),
            new LocalizedString(['en' => 'The fee for the course']),
            [new Note('price-note', new LocalizedString(['en' => 'Scholarships available']))],
        );

        $opportunity = $this->opportunityWithPriceDetail($priceDetail);

        self::assertSame($priceDetail->toArray(), $opportunity->toArray()['priceDetail']);
    }

    public function testLearningOpportunitySerializesDurationAndMode(): void {
        $mode = new Concept(
            'https://example.test/mode/blended',
            new LocalizedString(['en' => 'Blended learning']),
            new ConceptScheme('https://example.test/vocabulary/modes'),
        );
        $opportunity = new LearningOpportunity(
            'https://example.test/opportunity/duration',
            new LocalizedString(['en' => 'Opportunity with duration']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-duration', 'https://example.test/opportunity/duration'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/duration',
                new LocalizedString(['en' => 'Specification']),
            ),
            duration: 'P3M',
            mode: $mode,
        );

        self::assertSame('P3M', $opportunity->toArray()['duration']);
        self::assertSame($mode->toArray(), $opportunity->toArray()['mode']);
    }

    public function testLearningOpportunityRejectsMalformedDuration(): void {
        $this->expectException(InvalidCredentialException::class);

        new LearningOpportunity(
            'https://example.test/opportunity/invalid-duration',
            new LocalizedString(['en' => 'Invalid duration']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-invalid-duration', 'https://example.test/opportunity/invalid-duration'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/invalid-duration',
                new LocalizedString(['en' => 'Specification']),
            ),
            duration: 'three months',
        );
    }

    public function testLearningOpportunitySerializesSupplementaryDocumentsAndAdditionalNotes(): void {
        $document = new WebResource('supplementary-document', 'https://example.test/course-guide.pdf');
        $note = new Note('opportunity-note', new LocalizedString(['en' => 'Bring a laptop.']));
        $opportunity = new LearningOpportunity(
            'https://example.test/opportunity/resources',
            new LocalizedString(['en' => 'Opportunity with resources']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-resources', 'https://example.test/opportunity/resources'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/resources',
                new LocalizedString(['en' => 'Specification']),
            ),
            supplementaryDocuments: [$document],
            additionalNotes: [$note],
        );

        self::assertSame([$document->toArray()], $opportunity->toArray()['supplementaryDocument']);
        self::assertSame([$note->toArray()], $opportunity->toArray()['additionalNote']);
    }

    public function testLearningOpportunityRejectsInvalidSupplementaryDocument(): void {
        $this->expectException(InvalidCredentialException::class);

        new LearningOpportunity(
            'https://example.test/opportunity/invalid-document',
            new LocalizedString(['en' => 'Invalid document']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-invalid-document', 'https://example.test/opportunity/invalid-document'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/invalid-document',
                new LocalizedString(['en' => 'Specification']),
            ),
            supplementaryDocuments: [new Note('wrong-document', new LocalizedString(['en' => 'Not a web resource']))],
        );
    }

    public function testLearningOpportunitySerializesAdmissionScheduleAndStatus(): void {
        $admissionProcedure = new Note(
            'admission-procedure',
            new LocalizedString(['en' => 'Apply online before the deadline.']),
        );
        $scheduleInformation = new Note(
            'schedule-information',
            new LocalizedString(['en' => 'Mondays at 16:00.']),
        );
        $status = $this->concept(
            ElmVocabularySchemes::ACCREDITATION_STATUS,
            'https://example.test/status/active',
        );
        $opportunity = new LearningOpportunity(
            'https://example.test/opportunity/admission',
            new LocalizedString(['en' => 'Opportunity with admission details']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-admission', 'https://example.test/opportunity/admission'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/admission',
                new LocalizedString(['en' => 'Specification']),
            ),
            admissionProcedure: $admissionProcedure,
            scheduleInformation: $scheduleInformation,
            status: $status,
        );

        self::assertSame($admissionProcedure->toArray(), $opportunity->toArray()['admissionProcedure']);
        self::assertSame($scheduleInformation->toArray(), $opportunity->toArray()['scheduleInformation']);
        self::assertSame($status->toArray(), $opportunity->toArray()['status']);
    }

    public function testLearningOpportunityRejectsStatusFromWrongVocabulary(): void {
        $this->expectException(InvalidCredentialException::class);

        new LearningOpportunity(
            'https://example.test/opportunity/invalid-status',
            new LocalizedString(['en' => 'Invalid status']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-invalid-status', 'https://example.test/opportunity/invalid-status'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/invalid-status',
                new LocalizedString(['en' => 'Specification']),
            ),
            status: $this->concept(ElmVocabularySchemes::ASSESSMENT, 'https://example.test/status/wrong'),
        );
    }

    public function testLearningOpportunitySerializesGrantAndBannerImage(): void {
        $grant = new Grant(new LocalizedString(['en' => 'Study grant']));
        $bannerImage = new \IsyThl\EuropeanDigitalCredentials\MediaObject(
            'banner-image',
            'base64-image-data',
            $this->concept(
                ElmVocabularySchemes::CONTENT_ENCODING,
                'https://example.test/encoding/base64',
            ),
            $this->concept(
                ElmVocabularySchemes::CONTENT_TYPE,
                'https://example.test/file-type/png',
            ),
        );
        $opportunity = new LearningOpportunity(
            'https://example.test/opportunity/grant',
            new LocalizedString(['en' => 'Opportunity with grant']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-grant', 'https://example.test/opportunity/grant'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/grant',
                new LocalizedString(['en' => 'Specification']),
            ),
            grant: $grant,
            bannerImage: $bannerImage,
        );

        self::assertSame($grant->toArray(), $opportunity->toArray()['grant']);
        self::assertSame($bannerImage->toArray(), $opportunity->toArray()['bannerImage']);
    }

    public function testLearningOpportunitySerializesActivitySpecificationAndDeadlineInUtc(): void {
        $activitySpecification = new \IsyThl\EuropeanDigitalCredentials\LearningActivitySpecification(
            'https://example.test/activity-specification/1',
            new LocalizedString(['en' => 'Blended activity']),
        );
        $opportunity = new LearningOpportunity(
            'https://example.test/opportunity/deadline',
            new LocalizedString(['en' => 'Opportunity with deadline']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-deadline', 'https://example.test/opportunity/deadline'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/deadline',
                new LocalizedString(['en' => 'Specification']),
            ),
            learningActivitySpecification: $activitySpecification,
            applicationDeadline: new \DateTimeImmutable('2026-06-01T12:00:00+02:00'),
        );

        self::assertSame(
            $activitySpecification->toArray(),
            $opportunity->toArray()['learningActivitySpecification'],
        );
        self::assertSame('2026-06-01T10:00:00Z', $opportunity->toArray()['applicationDeadline']);
    }

    public function testLearningOpportunitySerializesPartRelations(): void {
        $part = new LearningOpportunity(
            'https://example.test/opportunity/part',
            new LocalizedString(['en' => 'Part opportunity']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-part', 'https://example.test/opportunity/part'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/part',
                new LocalizedString(['en' => 'Part specification']),
            ),
        );
        $parent = new LearningOpportunity(
            'https://example.test/opportunity/parent',
            new LocalizedString(['en' => 'Parent opportunity']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-parent', 'https://example.test/opportunity/parent'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/parent',
                new LocalizedString(['en' => 'Parent specification']),
            ),
            hasPart: [$part],
        );
        $child = new LearningOpportunity(
            'https://example.test/opportunity/child',
            new LocalizedString(['en' => 'Child opportunity']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-child', 'https://example.test/opportunity/child'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/child',
                new LocalizedString(['en' => 'Child specification']),
            ),
            isPartOf: [$parent],
        );

        self::assertSame($part->toArray(), $parent->toArray()['hasPart'][0]);
        self::assertSame($parent->toArray(), $child->toArray()['isPartOf'][0]);
    }

    public function testPriceDetailSerializesDecimalAmountAndCurrency(): void {
        $currency = new Concept(
            'http://publications.europa.eu/resource/authority/currency/EUR',
            new LocalizedString(['en' => 'Euro']),
            new ConceptScheme('http://publications.europa.eu/resource/authority/currency'),
            'EUR',
        );
        $amount = new Amount('1250.50', $currency);

        self::assertSame([
            'type' => 'Amount',
            'value' => '1250.50',
            'unit' => $currency->toArray(),
        ], $amount->toArray());
        self::assertSame($amount->toArray(), (new PriceDetail(amount: $amount))->toArray()['amount']);
    }

    public function testAmountRejectsNonDecimalValues(): void {
        $this->expectException(InvalidCredentialException::class);

        new Amount('12.5 EUR', $this->concept(
            'http://publications.europa.eu/resource/authority/currency',
            'https://example.test/currency/eur',
        ));
    }

    public function testGrantRequiresTitleAndSerializesOptionalProfileFields(): void {
        $grant = new Grant(
            new LocalizedString(['en' => 'Study grant']),
            $this->concept('https://example.test/grant-type', 'https://example.test/grant-type/allowance'),
            'https://example.test/grants/study',
            [new WebResource('grant-document', 'https://example.test/grants/study.pdf')],
        );

        self::assertSame([
            'type' => 'Grant',
            'title' => ['en' => ['Study grant']],
            'dcType' => $grant->type->toArray(),
            'contentURL' => 'https://example.test/grants/study',
            'supplementaryDocument' => [$grant->supplementaryDocuments[0]->toArray()],
        ], $grant->toArray());
    }

    public function testGrantRejectsInvalidContentUrl(): void {
        $this->expectException(InvalidCredentialException::class);

        new Grant(new LocalizedString(['en' => 'Study grant']), contentUrl: 'file:///tmp/grant');
    }

    public function testQualificationSerializesLearningOutcomeSummary(): void {
        $summary = new Note('outcome-summary', new LocalizedString([
            'en' => 'Learners can apply the acquired skills independently.',
        ]));
        $qualification = new Qualification(
            'https://example.test/qualification/summary',
            new LocalizedString(['en' => 'Qualification with summary']),
            eqfLevel: $this->concept(ElmVocabularySchemes::EQF, 'https://example.test/eqf/4'),
            nqfLevels: [$this->concept(ElmVocabularySchemes::QDR_BASE, 'https://example.test/nqf/4')],
            learningOutcomes: [new \IsyThl\EuropeanDigitalCredentials\LearningOutcome(
                'https://example.test/outcome/summary',
                new LocalizedString(['en' => 'Outcome']),
            )],
            educationSubjects: [$this->concept(ElmVocabularySchemes::ISCED_F, 'https://example.test/isced/1')],
            learningOutcomeSummary: $summary,
        );

        self::assertSame($summary->toArray(), $qualification->toArray()['learningOutcomeSummary']);
    }

    public function testQualificationSerializesEntryRequirement(): void {
        $entryRequirement = new Note(
            'entry-requirement',
            new LocalizedString(['en' => 'Prior secondary education is required.']),
        );
        $qualification = new Qualification(
            'https://example.test/qualification/entry-requirement',
            new LocalizedString(['en' => 'Qualification with entry requirement']),
            entryRequirement: $entryRequirement,
        );

        self::assertSame($entryRequirement->toArray(), $qualification->toArray()['entryRequirement']);
    }

    public function testQualificationSerializesQualificationRelations(): void {
        $baseQualification = new Qualification(
            'https://example.test/qualification/base',
            new LocalizedString(['en' => 'Base qualification']),
        );
        $qualification = new Qualification(
            'https://example.test/qualification/specialised',
            new LocalizedString(['en' => 'Specialised qualification']),
            specialisationOf: $baseQualification,
            generalisationOf: $baseQualification,
        );

        $data = $qualification->toArray();
        self::assertSame($baseQualification->toArray(), $data['specialisationOf']);
        self::assertSame($baseQualification->toArray(), $data['generalisationOf']);
    }

    public function testQualificationSerializesPartRelations(): void {
        $part = new Qualification(
            'https://example.test/qualification/part',
            new LocalizedString(['en' => 'Qualification part']),
        );
        $parent = new Qualification(
            'https://example.test/qualification/parent',
            new LocalizedString(['en' => 'Parent qualification']),
            hasPart: [$part],
            isPartOf: [$part],
        );

        $data = $parent->toArray();
        self::assertSame([$part->toArray()], $data['hasPart']);
        self::assertSame([$part->toArray()], $data['isPartOf']);
    }

    public function testQualificationRejectsInvalidPartRelations(): void {
        $this->expectException(InvalidCredentialException::class);

        new Qualification(
            'https://example.test/qualification/invalid-part',
            new LocalizedString(['en' => 'Invalid qualification']),
            hasPart: ['invalid'],
        );
    }

    public function testQualificationSerializesInfluencingActivities(): void {
        $activity = new LearningActivity(
            'https://example.test/activity/qualification',
            new LocalizedString(['en' => 'Qualification activity']),
            new AwardingProcess('https://example.test/awarding/qualification', $this->provider()),
            new LearningActivitySpecification(
                'https://example.test/activity-specification/qualification',
                new LocalizedString(['en' => 'Qualification activity']),
            ),
        );
        $qualification = new Qualification(
            'https://example.test/qualification/influenced',
            new LocalizedString(['en' => 'Influenced qualification']),
            influencedBy: [$activity],
        );

        self::assertSame([$activity->toArray()], $qualification->toArray()['influencedBy']);
    }

    public function testQualificationRejectsInvalidInfluencingActivities(): void {
        $this->expectException(InvalidCredentialException::class);

        new Qualification(
            'https://example.test/qualification/invalid-influence',
            new LocalizedString(['en' => 'Invalid qualification']),
            influencedBy: ['invalid'],
        );
    }

    public function testQualificationSerializesQualificationCodeUsingProfilePropertyName(): void {
        $qualificationCode = $this->concept(
            'https://example.test/qualification-code/1',
            'https://example.test/qualification-code/1',
        );
        $qualification = new Qualification(
            'https://example.test/qualification/code',
            new LocalizedString(['en' => 'Qualification with code']),
            qualificationCodes: [$qualificationCode],
        );

        self::assertSame(
            [$qualificationCode->toArray()],
            $qualification->toArray()['qualificationCode'],
        );
        self::assertArrayNotHasKey('qualificationCodes', $qualification->toArray());
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

    private function opportunityWithLocation(Location $location): LearningOpportunity {
        return $this->opportunityWithSpecificationTemporalScheduleAndLocation(
            new LearningAchievementSpecification(
                'https://example.test/specification/1',
                new LocalizedString(['en' => 'Specification']),
            ),
            null,
            null,
            $location,
        );
    }

    private function opportunityWithPriceDetail(PriceDetail $priceDetail): LearningOpportunity {
        return new LearningOpportunity(
            'https://example.test/opportunity/1',
            new LocalizedString(['en' => 'Opportunity']),
            $this->concept(ElmVocabularySchemes::LANGUAGE, 'https://example.test/language/en'),
            new WebResource('homepage-1', 'https://example.test/opportunity/1'),
            [$this->provider()],
            new LearningAchievementSpecification(
                'https://example.test/specification/1',
                new LocalizedString(['en' => 'Specification']),
            ),
            priceDetail: $priceDetail,
        );
    }

    private function provider(): Organisation {
        return new Organisation(
            'provider-1',
            new Location('location-1', new Address(
                'address-1',
                $this->countryConcept(),
                new Note('address-note', new LocalizedString(['en' => 'Brussels'])),
            )),
            new LocalizedString(['en' => 'Example Provider']),
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
        return $this->opportunityWithSpecificationTemporalScheduleAndLocation(
            $specification,
            $temporal,
            $schedule,
            null,
        );
    }

    private function opportunityWithSpecificationTemporalScheduleAndLocation(
        LearningAchievementSpecification|Qualification $specification,
        ?PeriodOfTime $temporal,
        ?Concept $schedule,
        ?Location $location,
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
            location: $location,
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
