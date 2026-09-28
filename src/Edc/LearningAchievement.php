<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanLearningModel\Core\AwardingProcess;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\CreditPoint;
use IsyThl\EuropeanLearningModel\Core\Identifier;
use IsyThl\EuropeanLearningModel\Core\LearningAchievementSpecification;
use IsyThl\EuropeanLearningModel\Core\LearningActivity;
use IsyThl\EuropeanLearningModel\Core\LearningOpportunity;
use IsyThl\EuropeanLearningModel\Core\LegalIdentifier;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Core\Note;
use IsyThl\EuropeanLearningModel\Core\Qualification;
use IsyThl\EuropeanLearningModel\Core\WebResource;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningAchievement extends Claim {
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly AwardingProcess $awardedBy,
        /** @var list<Identifier|LegalIdentifier>|null */
        public readonly ?array $identifier = null,
        public readonly LearningAchievementSpecification|Qualification|null $specifiedBy = null,
        public readonly ?LearningOpportunity $learningOpportunity = null,
        /** @var list<Concept>|null */
        public readonly ?array $dcType = null,
        public readonly ?LocalizedString $description = null,
        /** @var list<Note>|null */
        public readonly ?array $additionalNote = null,
        /** @var list<WebResource>|null */
        public readonly ?array $supplementaryDocument = null,
        /** @var list<LearningActivity>|null */
        public readonly ?array $influencedBy = null,
        /** @var list<LearningAssessment>|null */
        public readonly ?array $provenBy = null,
        /** @var list<LearningEntitlement>|null */
        public readonly ?array $entitlesTo = null,
        /** @var list<LearningAchievement>|null */
        public readonly ?array $hasPart = null,
        /** @var list<LearningAchievement>|null */
        public readonly ?array $isPartOf = null,
        /** @var list<CreditPoint>|null */
        public readonly ?array $creditReceived = null,
        public readonly ?int $order = null,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningAchievement',
            'title' => $this->title->toArray(),
            'awardedBy' => $this->awardedBy->toArray(),
        ];
        if ($this->identifier !== null) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifier,
            );
        }
        if ($this->specifiedBy !== null) {
            $data['specifiedBy'] = $this->specifiedBy->toArray();
        }
        if ($this->learningOpportunity !== null) {
            $data['learningOpportunity'] = $this->learningOpportunity->toArray();
        }
        if ($this->dcType != null) {
            $data['dcType'] = array_map(
                static fn (Concept $dcType): array => $dcType->toArray(),
                $this->dcType,
            );
        }
        if ($this->description != null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->additionalNote !== null) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNote,
            );
        }
        if ($this->supplementaryDocument !== null) {
            $data['supplementaryDocument'] = array_map(
                static fn (WebResource $supplementaryDocument): array => $supplementaryDocument->toArray(),
                $this->supplementaryDocument,
            );
        }
        if ($this->influencedBy !== null) {
            $data['influencedBy'] = array_map(
                static fn (LearningActivity $activity): array => $activity->toArray(),
                $this->influencedBy,
            );
        }
        if ($this->provenBy !== null) {
            $data['provenBy'] = array_map(
                static fn (LearningAssessment $assessment): array => $assessment->toArray(),
                $this->provenBy,
            );
        }
        if ($this->entitlesTo !== null) {
            $data['entitlesTo'] = array_map(
                static fn (LearningEntitlement $entitlement): array => $entitlement->toArray(),
                $this->entitlesTo,
            );
        }
        if ($this->hasPart !== null) {
            $data['hasPart'] = array_map(
                static fn (self $achievement): array => $achievement->toArray(),
                $this->hasPart,
            );
        }
        if ($this->isPartOf !== null) {
            $data['isPartOf'] = array_map(
                static fn (self $achievement): array => $achievement->toArray(),
                $this->isPartOf,
            );
        }
        if ($this->creditReceived !== null) {
            $data['creditReceived'] = array_map(
                static fn (CreditPoint $creditPoint): array => $creditPoint->toArray(),
                $this->creditReceived,
            );
        }
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'LearningAchievement') {
            throw new InvalidCredentialException('Data is not a LearningAchievement.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('LearningAchievement requires a title.');
        }
        if (!isset($data['awardedBy'])) {
            throw new InvalidCredentialException('LearningAchievement requires an awardedBy.');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            AwardingProcess::fromArray($data['awardedBy']),
            isset($data['identifier']) ? array_map(
                static fn (array $identifier): Identifier|LegalIdentifier => Identifier::fromArray($identifier),
                $data['identifier']
            ) : null,
            isset($data['specifiedBy']) ? (
                $data['specifiedBy']['type'] === 'Qualification' ? Qualification::fromArray($data['specifiedBy'])
                    : LearningAchievementSpecification::fromArray($data['specifiedBy'])
            ) : null,
            isset($data['learningOpportunity']) ? LearningOpportunity::fromArray($data['learningOpportunity']) : null,
            isset($data['dcType']) ? array_map(
                static fn (array $dcType): Concept => Concept::fromArray($dcType),
                $data['dcType']
            ) : null,
            isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            isset($data['additionalNote']) ? array_map(
                static fn (array $additionalNote): Note => Note::fromArray($additionalNote),
                $data['additionalNote']
            ) : null,
            isset($data['supplementaryDocument']) ? array_map(
                static fn (array $supplementaryDocument): WebResource => WebResource::fromArray($supplementaryDocument),
                $data['supplementaryDocument']
            ) : null,
            isset($data['influencedBy']) ? array_map(
                static fn (array $influencedBy): LearningActivity => LearningActivity::fromArray($influencedBy),
                $data['influencedBy']
            ) : null,
            isset($data['provenBy']) ? array_map(
                static fn (array $provenBy): LearningAssessment => LearningAssessment::fromArray($provenBy),
                $data['provenBy']
            ) : null,
            isset($data['entitlesTo']) ? array_map(
                static fn (array $entitlesTo): LearningEntitlement => LearningEntitlement::fromArray($entitlesTo),
                $data['entitlesTo']
            ) : null,
            isset($data['hasPart']) ? array_map(
                static fn (array $hasPart): LearningAchievement => LearningAchievement::fromArray($hasPart),
                $data['hasPart']
            ) : null,
            isset($data['isPartOf']) ? array_map(
                static fn (array $isPartOf): LearningAchievement => LearningAchievement::fromArray($isPartOf),
                $data['isPartOf']
            ) : null,
            isset($data['creditReceived']) ? array_map(
                static fn (array $creditReceived): CreditPoint => CreditPoint::fromArray($creditReceived),
                $data['creditReceived']
            ) : null,
            isset($data['order']) ? $data['order'] : null,
        );
    }
}
