<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials\Tests;

use IsyThl\EuropeanLearningModel\Core\Validation\ProfileResourceRegistryInterface;
use IsyThl\EuropeanLearningModel\Core\Validation\InMemoryProfileResourceRegistry;
use IsyThl\EuropeanLearningModel\Core\Validation\StandardsValidationResult;
use IsyThl\EuropeanLearningModel\Core\Validation\StandardsValidatorInterface;
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
}
