<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Edc\Claim;
use IsyThl\EuropeanLearningModel\Edc\LearningAchievement;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningActivity extends Claim {
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly AwardingProcess $awardedBy,
        /** @var list<Identifier|LegalIdentifier>|null */
        public readonly ?array $identifier = null,
        public readonly ?LearningOpportunity $learningOpportunity = null,
        public readonly ?Duration $workload = null,
        public readonly ?LearningActivitySpecification $specifiedBy = null,
        public readonly ?int $levelOfCompletion = null,
        /** @var list<Concept>|null */
        public readonly ?array $dcType = null,
        public readonly ?LocalizedString $description = null,
        /** @var list<Note>|null */
        public readonly ?array $additionalNote = null,
        /** @var list<WebResource>|null */
        public readonly ?array $supplementaryDocument = null,
        /** @var list<Location>|null */
        public readonly ?array $location = null,
        /** @var list<LearningActivity>|null */
        public readonly ?array $hasPart = null,
        /** @var list<LearningActivity>|null */
        public readonly ?array $isPartOf = null,
        /** @var list<PeriodOfTime>|null */
        public readonly ?array $temporal = null,
        /** @var list<Person|Organisation>|null */
        public readonly ?array $directedBy = null,
        /** @var list<LearningAchievement>|null */
        public readonly ?array $influences = null,
        public readonly ?int $order = null,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningActivity',
            'title' => $this->title->toArray(),
            'awardedBy' => $this->awardedBy->toArray(),
        ];
        if ($this->identifier !== null) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifier,
            );
        }
        if ($this->learningOpportunity !== null) {
            $data['learningOpportunity'] = $this->learningOpportunity->toArray();
        }
        if ($this->workload !== null) {
            $data['workload'] = (string) $this->workload;
        }
        if ($this->specifiedBy !== null) {
            $data['specifiedBy'] = $this->specifiedBy->toArray();
        }
        if ($this->levelOfCompletion !== null) {
            $data['levelOfCompletion'] = $this->levelOfCompletion;
        }
        if ($this->dcType !== null) {
            $data['dcType'] = array_map(
                static fn (Concept $dcType): array => $dcType->toArray(),
                $this->dcType,
            );
        }
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->additionalNote !== null) {
            $data['additionalNote'] = array_map(
                static fn (Note $additionalNote): array => $additionalNote->toArray(),
                $this->additionalNote,
            );
        }
        if ($this->supplementaryDocument !== null) {
            $data['supplementaryDocument'] = array_map(
                static fn (WebResource $supplementaryDocument): array => $supplementaryDocument->toArray(),
                $this->supplementaryDocument,
            );
        }
        if ($this->location !== null) {
            $data['location'] = array_map(
                static fn (Location $location): array => $location->toArray(),
                $this->location,
            );
        }
        if ($this->hasPart !== null) {
            $data['hasPart'] = array_map(
                static fn (self $activity): array => $activity->toArray(),
                $this->hasPart,
            );
        }
        if ($this->isPartOf !== null) {
            $data['isPartOf'] = array_map(
                static fn (self $activity): array => $activity->toArray(),
                $this->isPartOf,
            );
        }
        if ($this->temporal !== null) {
            $data['temporal'] = array_map(
                static fn (PeriodOfTime $temporal): array => $temporal->toArray(),
                $this->temporal,
            );
        }
        if ($this->directedBy !== null) {
            $data['directedBy'] = array_map(
                static fn (Person|Organisation $directedBy): array => $directedBy->toArray(),
                $this->directedBy,
            );
        }
        if ($this->influences !== null) {
            $data['influences'] = array_map(
                static fn (LearningAchievement $influences): array => $influences->toArray(),
                $this->influences,
            );
        }
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'LearningActivity') {
            throw new InvalidCredentialException('Data is not a LearningActivity.');
        }
        if (!isset($data['id'])) {
            throw new InvalidCredentialException('LearningActivity is missing id.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('LearningActivity is missing title.');
        }
        if (!isset($data['awardedBy'])) {
            throw new InvalidCredentialException('LearningActivity is missing awardedBy.');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            AwardingProcess::fromArray($data['awardedBy']),
            isset($data['identifier']) ? array_map(
                static fn (array $identifier): Identifier => Identifier::fromArray($identifier),
                $data['identifier']
            ) : null,
            isset($data['learningOpportunity']) ? LearningOpportunity::fromArray($data['learningOpportunity']) : null,
            isset($data['workload']) ? Duration::fromString($data['workload']) : null,
            isset($data['specifiedBy']) ? LearningActivitySpecification::fromArray($data['specifiedBy']) : null,
            isset($data['levelOfCompletion']) ? $data['levelOfCompletion'] : null,
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
            isset($data['location']) ? array_map(
                static fn (array $location): Location => Location::fromArray($location),
                $data['location']
            ) : null,
            isset($data['hasPart']) ? array_map(
                static fn (array $hasPart): self => self::fromArray($hasPart),
                $data['hasPart']
            ) : null,
            isset($data['isPartOf']) ? array_map(
                static fn (array $isPartOf): self => self::fromArray($isPartOf),
                $data['isPartOf']
            ) : null,
            isset($data['temporal']) ? array_map(
                static fn (array $temporal): PeriodOfTime => PeriodOfTime::fromArray($temporal),
                $data['temporal']
            ) : null,
            isset($data['directedBy']) ? array_map(
                static fn (array $directedBy): Person|Organisation => (
                    $directedBy['type'] === 'Person' ? Person::fromArray($directedBy) :
                        Organisation::fromArray($directedBy)
                ),
                $data['directedBy']
            ) : null,
            isset($data['influences']) ? array_map(
                static fn (array $influences): LearningAchievement => LearningAchievement::fromArray($influences),
                $data['influences']
            ) : null,
            $data['order'] ?? null,
        );
    }
}
