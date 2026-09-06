<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Core\PeriodOfTime;

final class AwardingOpportunity extends \IsyThl\EuropeanLearningModel\Core\Entity {

    /**
     * @param list<Identifier|LegalIdentifier> $identifiers
     * @param list<Organisation> $awardingBodies
     */
    public function __construct(
        string $id,
        public readonly LearningAchievementSpecification $learningAchievementSpecification,
        public readonly array $awardingBodies,
        public readonly array $identifiers = [],
        public readonly ?PeriodOfTime $temporal = null,
        public readonly ?Location $location = null,
    ) {
        parent::__construct($id);
        if ($awardingBodies === []) {
            throw new InvalidCredentialException('An awarding opportunity requires an awarding body.');
        }
        if (array_filter($awardingBodies, static fn ($body): bool => !$body instanceof Organisation) !== []) {
            throw new InvalidCredentialException('Awarding bodies must be Organisation objects.');
        }
        if (
            array_filter(
                $identifiers,
                static fn ($identifier): bool => (
                    !$identifier instanceof Identifier
                    && !$identifier instanceof LegalIdentifier
                ),
            ) !== []
        ) {
            throw new InvalidCredentialException('Awarding opportunity identifiers must be Identifier objects.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:awardingOpportunity:' . $this->id,
            'type' => 'AwardingOpportunity',
            'awardingBody' => array_map(
                static fn (Organisation $body): array => $body->toArray(),
                $this->awardingBodies,
            ),
            'learningAchievementSpecification' => $this->learningAchievementSpecification->toArray(),
        ];
        if ($this->identifiers !== []) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifiers,
            );
        }
        if ($this->temporal !== null) {
            $data['temporal'] = $this->temporal->toArray();
        }
        if ($this->location !== null) {
            $data['location'] = $this->location->toArray();
        }
        return $data;
    }
}
