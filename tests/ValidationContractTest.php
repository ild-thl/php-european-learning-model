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
use IsyThl\EuropeanLearningModel\Core\Validation\InMemoryProfileResourceRegistry;
use IsyThl\EuropeanLearningModel\Core\Validation\StandardsValidationResult;
use IsyThl\EuropeanLearningModel\Core\Validation\StandardsValidatorInterface;
use IsyThl\EuropeanLearningModel\Loq\LoqDocumentValidator;
use IsyThl\EuropeanLearningModel\Loq\QualificationDocument;
use PHPUnit\Framework\TestCase;

final class ValidationContractTest extends TestCase {

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

    public function testProfileResourceRegistryIsAnExplicitResolutionBoundary(): void {
        $registry = new InMemoryProfileResourceRegistry([
            'LOQ-constraints.rdf' => 'local profile content',
        ]);

        self::assertSame('local profile content', $registry->get('LOQ-constraints.rdf'));
        $this->expectException(\InvalidArgumentException::class);
        $registry->get('unregistered.rdf');
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
            new InMemoryProfileResourceRegistry(['LOQ.rdf' => 'profile']),
        );

        $validator->validate(
            new QualificationDocument($this->qualification()),
            'LOQ.rdf',
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
            new InMemoryProfileResourceRegistry(['LOQ.rdf' => 'profile']),
        );

        $this->expectExceptionMessage('Missing publisher.');
        $validator->validate(new QualificationDocument($this->qualification()), 'LOQ.rdf');
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
