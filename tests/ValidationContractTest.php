<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Qualification;
use IsyThl\EuropeanLearningModel\Core\Validation\ProfileResourceRegistryInterface;
use IsyThl\EuropeanLearningModel\Address as CoreAddress;
use IsyThl\EuropeanLearningModel\AwardingProcess as CoreAwardingProcess;
use IsyThl\EuropeanLearningModel\ContactPoint as CoreContactPoint;
use IsyThl\EuropeanLearningModel\Core\Concept as CoreConcept;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme as CoreConceptScheme;
use IsyThl\EuropeanLearningModel\Core\CreditPoint as CoreCreditPoint;
use IsyThl\EuropeanLearningModel\DisplayDetail as CoreDisplayDetail;
use IsyThl\EuropeanLearningModel\DisplayParameter as CoreDisplayParameter;
use IsyThl\EuropeanLearningModel\GradingScheme as CoreGradingScheme;
use IsyThl\EuropeanLearningModel\Core\Identifier as CoreIdentifier;
use IsyThl\EuropeanLearningModel\LegalIdentifier as CoreLegalIdentifier;
use IsyThl\EuropeanLearningModel\Core\LocalizedString as CoreLocalizedString;
use IsyThl\EuropeanLearningModel\Note as CoreNote;
use IsyThl\EuropeanLearningModel\Location as CoreLocation;
use IsyThl\EuropeanLearningModel\Core\LearningOutcome as CoreLearningOutcome;
use IsyThl\EuropeanLearningModel\LearningActivity as CoreLearningActivity;
use IsyThl\EuropeanLearningModel\LearningActivitySpecification as CoreLearningActivitySpecification;
use IsyThl\EuropeanLearningModel\Core\LearningOutcome;
use IsyThl\EuropeanLearningModel\MediaObject as CoreMediaObject;
use IsyThl\EuropeanLearningModel\Organisation as CoreOrganisation;
use IsyThl\EuropeanLearningModel\EmailAddress as CoreEmailAddress;
use IsyThl\EuropeanLearningModel\WebResource as CoreWebResource;
use IsyThl\EuropeanLearningModel\Core\Validation\InMemoryProfileResourceRegistry;
use IsyThl\EuropeanLearningModel\Core\Validation\FilesystemProfileResourceRegistry;
use IsyThl\EuropeanLearningModel\Core\Validation\StandardsValidationResult;
use IsyThl\EuropeanLearningModel\Core\Validation\StandardsValidatorInterface;
use IsyThl\EuropeanLearningModel\Loq\LoqDocumentValidator;
use IsyThl\EuropeanLearningModel\Loq\LoqDatasetDocument;
use IsyThl\EuropeanLearningModel\Loq\LoqProfile;
use IsyThl\EuropeanLearningModel\Loq\QualificationDocument;
use PHPUnit\Framework\TestCase;

final class ValidationContractTest extends TestCase {

    public function testCoreIdentifierAliasesPreserveLegacyIdentityAndSerialization(): void {
        $identifier = new CoreIdentifier('core-id', '123', 'example-scheme');
        $legalIdentifier = new CoreLegalIdentifier(
            'core-legal-id',
            '456',
            new Concept(
                'http://publications.europa.eu/resource/authority/country/BEL',
                new LocalizedString(['en' => 'Belgium']),
                new ConceptScheme(ElmVocabularySchemes::COUNTRY),
            ),
        );

        self::assertInstanceOf(CoreIdentifier::class, $identifier);
        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\LegalIdentifier::class, $legalIdentifier);
        self::assertSame([
            'id' => 'urn:epass:identifier:core-id',
            'type' => 'Identifier',
            'notation' => '123',
            'schemeName' => 'example-scheme',
        ], $identifier->toArray());
    }

    public function testStandardsValidatorContractCarriesResultWithoutChoosingImplementation(): void {
        $validator = new class implements StandardsValidatorInterface {
            public function validate(string $document, string $profileResource): StandardsValidationResult {
                return $document === '' || $profileResource === ''
                    ? StandardsValidationResult::invalid(['Document and profile are required.'])
                    : StandardsValidationResult::valid();
            }
        };

        self::assertTrue($validator->validate('{"@id":"urn:test:1"}', 'loq')->valid);
        self::assertSame(
            ['Document and profile are required.'],
            $validator->validate('', 'loq')->errors,
        );
    }

    public function testCoreConceptAliasesPreserveLegacyIdentityAndRoundTrip(): void {
        $concept = new CoreConcept(
            'https://example.test/concept/core',
            new CoreLocalizedString(['en' => 'Core concept']),
            new CoreConceptScheme('https://example.test/scheme/core'),
            'CORE-1',
        );
        $roundTrip = CoreConcept::fromArray($concept->toArray());

        self::assertInstanceOf(CoreConcept::class, $concept);
        self::assertSame($concept->toArray(), $roundTrip->toArray());
    }

    public function testCoreNoteAndWebResourceAliasesPreserveSerialization(): void {
        $note = new CoreNote('core-note', new CoreLocalizedString(['en' => 'Core note']));
        $resource = new CoreWebResource('core-resource', 'https://example.test/resource');

        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\Note::class, $note);
        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\WebResource::class, $resource);
        self::assertSame([
            'id' => 'urn:epass:note:core-note',
            'type' => 'Note',
            'noteLiteral' => ['en' => ['Core note']],
        ], $note->toArray());
        self::assertSame([
            'id' => 'urn:epass:webResource:core-resource',
            'type' => 'WebResource',
            'contentURL' => 'https://example.test/resource',
        ], $resource->toArray());
    }

    public function testCoreAddressAndLocationAliasesPreserveNestedSerialization(): void {
        $address = new CoreAddress(
            'core-address',
            new CoreConcept(
                'http://publications.europa.eu/resource/authority/country/BEL',
                new CoreLocalizedString(['en' => 'Belgium']),
                new CoreConceptScheme(ElmVocabularySchemes::COUNTRY),
            ),
            new CoreNote('core-address-note', new CoreLocalizedString(['en' => 'Brussels'])),
        );
        $location = new CoreLocation('core-location', $address);

        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\Address::class, $address);
        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\Location::class, $location);
        self::assertSame([
            'id' => 'urn:epass:location:core-location',
            'type' => 'Location',
            'address' => $address->toArray(),
        ], $location->toArray());
    }

    public function testCoreContactPointAndEmailAliasesPreserveSerialization(): void {
        $email = new CoreEmailAddress('core@example.test');
        $contactPoint = new CoreContactPoint('core-contact', emailAddress: $email);

        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\EmailAddress::class, $email);
        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\ContactPoint::class, $contactPoint);
        self::assertSame([
            'id' => 'urn:epass:contactPoint:core-contact',
            'type' => 'ContactPoint',
            'emailAddress' => [[
                'id' => 'mailto:core@example.test',
                'type' => 'Mailbox',
            ]],
        ], $contactPoint->toArray());

        $this->expectException(\IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException::class);
        new CoreEmailAddress('invalid email');
    }

    public function testCoreOrganisationAndAwardingProcessAliasesPreserveNestedSerialization(): void {
        $organisation = new CoreOrganisation(
            'core-organisation',
            new CoreLocation(
                'core-organisation-location',
                new CoreAddress(
                    'core-organisation-address',
                    new CoreConcept(
                        'http://publications.europa.eu/resource/authority/country/BEL',
                        new CoreLocalizedString(['en' => 'Belgium']),
                        new CoreConceptScheme(ElmVocabularySchemes::COUNTRY),
                    ),
                    new CoreNote('core-organisation-note', new CoreLocalizedString(['en' => 'Brussels'])),
                ),
            ),
            new CoreLocalizedString(['en' => 'Core organisation']),
        );
        $process = new CoreAwardingProcess('core-awarding-process', $organisation);

        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\Organisation::class, $organisation);
        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\AwardingProcess::class, $process);
        self::assertSame([
            'id' => 'urn:epass:awardingProcess:core-awarding-process',
            'type' => 'AwardingProcess',
            'awardingBody' => $organisation->toArray(),
        ], $process->toArray());
    }

    public function testCoreMediaObjectAliasPreservesControlledConceptSerialization(): void {
        $media = new CoreMediaObject(
            'core-media',
            'aGVsbG8=',
            new CoreConcept(
                'https://example.test/encoding/base64',
                new CoreLocalizedString(['en' => 'Base64']),
                new CoreConceptScheme(ElmVocabularySchemes::CONTENT_ENCODING),
            ),
            new CoreConcept(
                'https://example.test/content-type/image',
                new CoreLocalizedString(['en' => 'Image']),
                new CoreConceptScheme(ElmVocabularySchemes::CONTENT_TYPE),
            ),
        );

        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\MediaObject::class, $media);
        self::assertSame('MediaObject', $media->toArray()['type']);
        self::assertSame('aGVsbG8=', $media->toArray()['content']);
    }

    public function testCoreCreditPointAndLearningOutcomeAliasesPreserveProfileValues(): void {
        $creditPoint = new CoreCreditPoint(
            'core-credit-point',
            new CoreConcept(
                'https://example.test/credit-framework/ects',
                new CoreLocalizedString(['en' => 'ECTS']),
                new CoreConceptScheme(ElmVocabularySchemes::EDUCATION_CREDIT),
            ),
            '6',
        );
        $outcome = new CoreLearningOutcome(
            'core-outcome',
            new CoreLocalizedString(['en' => 'Core learning outcome']),
            relatedSkills: [new CoreConcept(
                'https://example.test/skill/1',
                new CoreLocalizedString(['en' => 'Skill']),
                new CoreConceptScheme(ElmVocabularySchemes::DCF_SKILLS),
            )],
        );

        self::assertInstanceOf(CoreCreditPoint::class, $creditPoint);
        self::assertInstanceOf(CoreLearningOutcome::class, $outcome);
        self::assertSame('6', $creditPoint->toArray()['point']);
        self::assertSame('LearningOutcome', $outcome->toArray()['type']);
        self::assertCount(1, $outcome->toArray()['relatedSkills']);
    }

    public function testCoreLearningActivityAliasesPreserveNestedGraphValues(): void {
        $organisation = new CoreOrganisation(
            'core-organisation',
            new CoreLocation(
                'core-location',
                new CoreAddress(
                    'core-address',
                    new CoreConcept(
                        'https://example.test/country/be',
                        new CoreLocalizedString(['en' => 'Belgium']),
                        new CoreConceptScheme(ElmVocabularySchemes::COUNTRY),
                    ),
                    new CoreNote('core-note', new CoreLocalizedString(['en' => 'Core address'])),
                ),
            ),
            new CoreLocalizedString(['en' => 'Core organisation']),
        );
        $specification = new CoreLearningActivitySpecification(
            'core-activity-spec',
            new CoreLocalizedString(['en' => 'Core activity']),
        );
        $activity = new CoreLearningActivity(
            'core-activity',
            new CoreLocalizedString(['en' => 'Core activity']),
            new CoreAwardingProcess('core-awarding-process', $organisation),
            $specification,
            hasPart: [new CoreLearningActivity(
                'core-sub-activity',
                new CoreLocalizedString(['en' => 'Core sub-activity']),
                new CoreAwardingProcess('core-sub-awarding-process', $organisation),
                $specification,
            )],
        );

        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\LearningActivity::class, $activity);
        self::assertSame('LearningActivity', $activity->toArray()['type']);
        self::assertCount(1, $activity->toArray()['hasPart']);
    }

    public function testCoreDisplayAndGradingAliasesPreserveProfileValues(): void {
        $gradingScheme = new CoreGradingScheme(
            'core-grading-scheme',
            new CoreLocalizedString(['en' => 'How grades are described']),
            new CoreLocalizedString(['en' => 'Core grading']),
        );
        $language = new CoreConcept(
            'https://publications.europa.eu/resource/authority/language/ENG',
            new CoreLocalizedString(['en' => 'English']),
            new CoreConceptScheme(ElmVocabularySchemes::LANGUAGE),
        );
        $displayParameter = new CoreDisplayParameter(
            'core-display-parameter',
            $language,
            $language,
            new CoreLocalizedString(['en' => 'Core credential']),
        );
        $displayDetail = new CoreDisplayDetail(
            'core-display-detail',
            1,
            new CoreMediaObject(
                'core-display-image',
                'aW1hZ2U=',
                new CoreConcept(
                    'https://example.test/encoding/base64',
                    new CoreLocalizedString(['en' => 'Base64']),
                    new CoreConceptScheme(ElmVocabularySchemes::CONTENT_ENCODING),
                ),
                new CoreConcept(
                    'https://example.test/content-type/image',
                    new CoreLocalizedString(['en' => 'Image']),
                    new CoreConceptScheme(ElmVocabularySchemes::CONTENT_TYPE),
                ),
            ),
        );

        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\GradingScheme::class, $gradingScheme);
        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\DisplayParameter::class, $displayParameter);
        self::assertInstanceOf(\IsyThl\EuropeanLearningModel\DisplayDetail::class, $displayDetail);
        self::assertSame('GradingScheme', $gradingScheme->toArray()['type']);
        self::assertSame(1, $displayDetail->toArray()['page']);
        self::assertArrayNotHasKey('description', $displayParameter->toArray());
    }

    public function testProfileResourceRegistryIsAnExplicitResolutionBoundary(): void {
        $registry = new InMemoryProfileResourceRegistry([
            'LOQ-constraints.rdf' => 'local profile content',
        ]);

        self::assertSame('local profile content', $registry->get('LOQ-constraints.rdf'));
        $this->expectException(\InvalidArgumentException::class);
        $registry->get('unregistered.rdf');
    }

    public function testLoqProfileExposesPinnedResourceIdentity(): void {
        self::assertSame(
            'http://data.europa.eu/snb/model/application-profile/loq-constraints',
            LoqProfile::LOQ_CONSTRAINTS->value,
        );
        self::assertSame('LOQ-constraints.rdf', LoqProfile::LOQ_CONSTRAINTS->resourceName());
    }

    public function testFilesystemRegistryReadsOnlyCallerSelectedLocalFiles(): void {
        $registry = new FilesystemProfileResourceRegistry(__DIR__ . '/../resources/profile');

        self::assertStringContainsString(
            'loq-constraints',
            $registry->get(LoqProfile::LOQ_CONSTRAINTS->resourceName()),
        );
        $this->expectException(\InvalidArgumentException::class);
        $registry->get('../composer.json');
    }

    public function testLoqDocumentValidatorDelegatesToInjectedStandardsValidator(): void {
        $standardsValidator = new class implements StandardsValidatorInterface {
            public bool $called = false;

            public function validate(string $document, string $profileResource): StandardsValidationResult {
                $this->called = true;
                return str_contains($document, 'Qualification') && $profileResource === 'profile'
                    ? StandardsValidationResult::valid()
                    : StandardsValidationResult::invalid(['The LOQ graph is invalid.']);
            }
        };
        $validator = new LoqDocumentValidator(
            $standardsValidator,
            new InMemoryProfileResourceRegistry(['LOQ-constraints.rdf' => 'profile']),
        );

        $validator->validate(
            new QualificationDocument($this->qualification()),
            LoqProfile::LOQ_CONSTRAINTS,
        );
        self::assertTrue($standardsValidator->called);
    }

    public function testLoqDocumentValidatorExposesStandardsDiagnostics(): void {
        $validator = new LoqDocumentValidator(
            new class implements StandardsValidatorInterface {
                public function validate(string $document, string $profileResource): StandardsValidationResult {
                    return StandardsValidationResult::invalid(['Missing publisher.']);
                }
            },
            new InMemoryProfileResourceRegistry(['LOQ-constraints.rdf' => 'profile']),
        );

        $this->expectExceptionMessage('Missing publisher.');
        $validator->validate(new QualificationDocument($this->qualification()), LoqProfile::LOQ_CONSTRAINTS);
    }

    public function testLoqDocumentValidatorAcceptsDatasetDocuments(): void {
        $standardsValidator = new class implements StandardsValidatorInterface {
            public function validate(string $document, string $profileResource): StandardsValidationResult {
                return str_contains($document, 'Qualification') && $profileResource === 'profile'
                    ? StandardsValidationResult::valid()
                    : StandardsValidationResult::invalid(['The LOQ dataset is invalid.']);
            }
        };
        $validator = new LoqDocumentValidator(
            $standardsValidator,
            new InMemoryProfileResourceRegistry(['LOQ-constraints.rdf' => 'profile']),
        );

        $validator->validate(new LoqDatasetDocument($this->qualification()), LoqProfile::LOQ_CONSTRAINTS);
        self::assertTrue(true);
    }

    /** @return list<Qualification> */
    private function qualification(): array {
        $concept = static function (string $scheme, string $id): Concept {
            return new Concept(
                $id,
                new LocalizedString(['en' => $id]),
                new ConceptScheme($scheme),
            );
        };

        return [new Qualification(
            'https://example.test/qualifications/1',
            new LocalizedString(['en' => 'Example qualification']),
            eqfLevel: $concept(ElmVocabularySchemes::EQF, 'https://example.test/eqf/4'),
            nqfLevels: [$concept(ElmVocabularySchemes::QDR_BASE, 'https://example.test/nqf/4')],
            learningOutcomes: [new LearningOutcome(
                'https://example.test/outcomes/1',
                new LocalizedString(['en' => 'Example outcome']),
            )],
            educationSubjects: [$concept(ElmVocabularySchemes::ISCED_F, 'https://example.test/isced/1')],
        )];
    }
}
