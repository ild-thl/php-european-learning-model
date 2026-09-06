<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptScheme;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\LearningOutcome;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use IsyThl\EuropeanDigitalCredentials\Qualification;
use IsyThl\EuropeanLearningModel\Core\Validation\ProfileResourceRegistryInterface;
use IsyThl\EuropeanLearningModel\Core\Address as CoreAddress;
use IsyThl\EuropeanLearningModel\Core\AwardingProcess as CoreAwardingProcess;
use IsyThl\EuropeanLearningModel\Core\ContactPoint as CoreContactPoint;
use IsyThl\EuropeanLearningModel\Core\Concept as CoreConcept;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme as CoreConceptScheme;
use IsyThl\EuropeanLearningModel\Core\Identifier as CoreIdentifier;
use IsyThl\EuropeanLearningModel\Core\LegalIdentifier as CoreLegalIdentifier;
use IsyThl\EuropeanLearningModel\Core\LocalizedString as CoreLocalizedString;
use IsyThl\EuropeanLearningModel\Core\Note as CoreNote;
use IsyThl\EuropeanLearningModel\Core\Location as CoreLocation;
use IsyThl\EuropeanLearningModel\Core\Organisation as CoreOrganisation;
use IsyThl\EuropeanLearningModel\Core\EmailAddress as CoreEmailAddress;
use IsyThl\EuropeanLearningModel\Core\WebResource as CoreWebResource;
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

        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\Identifier::class, $identifier);
        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\LegalIdentifier::class, $legalIdentifier);
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

        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\Concept::class, $concept);
        self::assertSame($concept->toArray(), $roundTrip->toArray());
    }

    public function testCoreNoteAndWebResourceAliasesPreserveSerialization(): void {
        $note = new CoreNote('core-note', new CoreLocalizedString(['en' => 'Core note']));
        $resource = new CoreWebResource('core-resource', 'https://example.test/resource');

        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\Note::class, $note);
        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\WebResource::class, $resource);
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

        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\Address::class, $address);
        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\Location::class, $location);
        self::assertSame([
            'id' => 'urn:epass:location:core-location',
            'type' => 'Location',
            'address' => $address->toArray(),
        ], $location->toArray());
    }

    public function testCoreContactPointAndEmailAliasesPreserveSerialization(): void {
        $email = new CoreEmailAddress('core@example.test');
        $contactPoint = new CoreContactPoint('core-contact', emailAddress: $email);

        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\EmailAddress::class, $email);
        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\ContactPoint::class, $contactPoint);
        self::assertSame([
            'id' => 'urn:epass:contactPoint:core-contact',
            'type' => 'ContactPoint',
            'emailAddress' => [[
                'id' => 'mailto:core@example.test',
                'type' => 'Mailbox',
            ]],
        ], $contactPoint->toArray());

        $this->expectException(\IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException::class);
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

        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\Organisation::class, $organisation);
        self::assertInstanceOf(\IsyThl\EuropeanDigitalCredentials\AwardingProcess::class, $process);
        self::assertSame([
            'id' => 'urn:epass:awardingProcess:core-awarding-process',
            'type' => 'AwardingProcess',
            'awardingBody' => $organisation->toArray(),
        ], $process->toArray());
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
