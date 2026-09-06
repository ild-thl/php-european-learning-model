<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanLearningModel\Core\ConceptAssertions;
use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\LearningAchievementSpecification;
use IsyThl\EuropeanLearningModel\Qualification;

final class LoqProfileValidator {

    public function validateQualification(Qualification $qualification): void {
        self::assertPersistentIdentifier($qualification->id, 'qualification');
        if ($qualification->eqfLevel === null) {
            throw new InvalidCredentialException('A LOQ qualification requires one EQF level.');
        }
        if ($qualification->nqfLevels === []) {
            throw new InvalidCredentialException('A LOQ qualification requires at least one NQF level.');
        }
        if ($qualification->educationSubjects === []) {
            throw new InvalidCredentialException('A LOQ qualification requires at least one ISCED-F subject.');
        }
        if ($qualification->learningOutcomes === []) {
            throw new InvalidCredentialException('A LOQ qualification requires at least one learning outcome.');
        }
    }

    public function validateLearningOpportunity(LearningOpportunity $opportunity): void {
        self::assertPersistentIdentifier($opportunity->id, 'learning opportunity');
        ConceptAssertions::assertScheme(
            $opportunity->defaultLanguage,
            ElmVocabularySchemes::LANGUAGE,
            'defaultLanguage',
        );
        if ($opportunity->learningAchievementSpecification instanceof LearningAchievementSpecification) {
            self::assertPersistentIdentifier(
                $opportunity->learningAchievementSpecification->id,
                'learning achievement specification',
            );
        }
    }

    private static function assertPersistentIdentifier(string $identifier, string $subject): void {
        if ($identifier === '' || preg_match('/^[a-z][a-z0-9+.-]*:\S+$/i', $identifier) !== 1) {
            throw new InvalidCredentialException(sprintf(
                'A LOQ %s requires a persistent URI identifier.',
                $subject,
            ));
        }
    }
}
