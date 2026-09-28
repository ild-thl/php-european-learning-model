<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class AwardingOpportunity extends Entity {
    /**
     * @param  list<Identifier>  $identifiers
     * @param  list<Organisation>  $awardingBody
     */
    public function __construct(
        string $id,
        /** @var list<Identifier|LegalIdentifier>|null */
        public readonly ?array $identifiers = null,
        public readonly ?LearningAchievementSpecification $learningAchievementSpecification = null,
        /** @var list<Organisation>|null */
        public readonly ?array $awardingBody = null,
        public readonly ?PeriodOfTime $temporal = null,
        public readonly ?Location $location = null,
        public readonly ?int $order = null,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'AwardingOpportunity',
        ];
        if ($this->identifiers !== null) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifiers,
            );
        }
        if ($this->awardingBody !== null) {
            $data['awardingBody'] = array_map(
                static fn (Organisation $body): array => $body->toArray(),
                $this->awardingBody,
            );
        }
        if (isset($this->learningAchievementSpecification)) {
            $data['learningAchievementSpecification'] = $this->learningAchievementSpecification->toArray();
        }
        if ($this->temporal !== null) {
            $data['temporal'] = $this->temporal->toArray();
        }
        if ($this->location !== null) {
            $data['location'] = $this->location->toArray();
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'AwardingOpportunity') {
            throw new InvalidCredentialException('Data is not a AwardingOpportunity.');
        }

        return new self(
            $data['id'] ?? '',
            isset($data['identifier']) ? array_map(
                static fn (array $identifier): Identifier|LegalIdentifier => Identifier::fromArray($identifier),
                $data['identifier'],
            ) : null,
            isset($data['learningAchievementSpecification']) ? LearningAchievementSpecification::fromArray($data['learningAchievementSpecification']) : null,
            isset($data['awardingBody']) ? array_map(
                static fn (array $body): Organisation => Organisation::fromArray($body),
                $data['awardingBody'],
            ) : null,
            isset($data['temporal']) ? PeriodOfTime::fromArray($data['temporal']) : null,
            isset($data['location']) ? Location::fromArray($data['location']) : null,
            $data['order'] ?? null,
        );
    }
}
