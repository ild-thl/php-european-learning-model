<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\LearningAchievementSpecification;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use IsyThl\EuropeanDigitalCredentials\Organisation;
use IsyThl\EuropeanDigitalCredentials\WebResource;
use IsyThl\EuropeanLearningModel\Core\PeriodOfTime;

final class LearningOpportunity {

    /** @param list<Organisation> $providedBy */
    public function __construct(
        public readonly string $id,
        public readonly LocalizedString $title,
        public readonly Concept $defaultLanguage,
        public readonly WebResource $homepage,
        public readonly array $providedBy,
        public readonly LearningAchievementSpecification|QualificationReference $learningAchievementSpecification,
        public readonly ?Organisation $publisher = null,
        public readonly ?PeriodOfTime $temporal = null,
        public readonly ?Concept $learningSchedule = null,
    ) {
        self::assertUri($id);
        if ($providedBy === []) {
            throw new InvalidCredentialException('A learning opportunity requires at least one provider.');
        }
        if (array_filter($providedBy, static fn ($provider): bool => !$provider instanceof Organisation) !== []) {
            throw new InvalidCredentialException('Learning opportunity providers must be organisations.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningOpportunity',
            'title' => $this->title->toArray(),
            'defaultLanguage' => $this->defaultLanguage->toArray(),
            'homepage' => $this->homepage->toArray(),
            'providedBy' => array_map(
                static fn (Organisation $provider): array => $provider->toArray(),
                $this->providedBy,
            ),
            'learningAchievementSpecification' => $this->learningAchievementSpecification->toArray(),
        ];
        if ($this->publisher !== null) {
            $data['publisher'] = $this->publisher->toArray();
        }
        if ($this->temporal !== null) {
            $data['temporal'] = $this->temporal->toArray();
        }
        if ($this->learningSchedule !== null) {
            $data['learningSchedule'] = $this->learningSchedule->toArray();
        }
        return $data;
    }

    private static function assertUri(string $id): void {
        if ($id === '' || preg_match('/^[a-z][a-z0-9+.-]*:\S+$/i', $id) !== 1) {
            throw new InvalidCredentialException('Learning opportunity identifiers must be persistent URIs.');
        }
    }
}
