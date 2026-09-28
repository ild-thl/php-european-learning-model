<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningOpportunity extends Entity {
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        /** @var list<Organisation> */
        public readonly array $providedBy,
        /** @var list<Identifier|LegalIdentifier>|null */
        public readonly ?array $identifier = null,
        public readonly ?Duration $duration = null,
        public readonly ?string $status = null,
        public readonly ?Concept $learningSchedule = null,
        public readonly ?PeriodOfTime $temporal = null,
        public readonly ?MediaObject $bannerImage = null,
        public readonly ?Note $scheduleInformation = null,
        public readonly ?Note $admissionProcedure = null,
        public readonly LearningAchievementSpecification|Qualification|null $learningAchievementSpecification = null,
        public readonly ?LearningActivitySpecification $learningActivitySpecification = null,
        /** @var list<Concept>|null */
        public readonly ?array $dcType = null,
        public readonly ?LocalizedString $description = null,
        /** @var list<string>|null */
        public readonly ?array $descriptionHtml = null,
        /** @var list<Note>|null */
        public readonly ?array $additionalNote = null,
        /** @var list<WebResource>|null */
        public readonly ?array $supplementaryDocument = null,
        public readonly ?Concept $defaultLanguage = null,
        /** @var list<Location>|null */
        public readonly ?array $location = null,
        /** @var list<Grant>|null */
        public readonly ?array $grant = null,
        /** @var list<WebResource>|null */
        public readonly ?array $homepage = null,
        /** @var list<PriceDetail>|null */
        public readonly ?array $priceDetail = null,
        /** @var list<LearningOpportunity>|null */
        public readonly ?array $hasPart = null,
        /** @var list<LearningOpportunity>|null */
        public readonly ?array $isPartOf = null,
        /** @var list<DateTimeImmutable>|null */
        public readonly ?array $applicationDeadline = null,
        /** @var list<Concept>|null */
        public readonly ?array $mode = null,
        public readonly ?int $order = null,
        public readonly ?DateTimeImmutable $modified = null,
    ) {
        parent::__construct($id);

        if (empty($providedBy)) {
            throw new InvalidCredentialException('A learning opportunity requires at least one provider.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningOpportunity',
            'title' => $this->title->toArray(),
            'providedBy' => array_map(
                static fn (Organisation $provider): array => $provider->toArray(),
                $this->providedBy,
            ),
        ];
        if ($this->identifier !== null) {
            $data['identifier'] = array_map(
                static fn (Identifier|LegalIdentifier $identifier): array => $identifier->toArray(),
                $this->identifier,
            );
        }
        if ($this->duration !== null) {
            $data['duration'] = (string) $this->duration;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }
        if ($this->learningSchedule !== null) {
            $data['learningSchedule'] = $this->learningSchedule->toArray();
        }
        if ($this->temporal !== null) {
            $data['temporal'] = $this->temporal->toArray();
        }
        if ($this->bannerImage !== null) {
            $data['bannerImage'] = $this->bannerImage->toArray();
        }
        if ($this->scheduleInformation !== null) {
            $data['scheduleInformation'] = $this->scheduleInformation->toArray();
        }
        if ($this->admissionProcedure !== null) {
            $data['admissionProcedure'] = $this->admissionProcedure->toArray();
        }
        if ($this->learningAchievementSpecification !== null) {
            $data['learningAchievementSpecification'] = $this->learningAchievementSpecification->toArray();
        }
        if ($this->learningActivitySpecification !== null) {
            $data['learningActivitySpecification'] = $this->learningActivitySpecification->toArray();
        }
        if ($this->dcType !== null) {
            $data['dcType'] = array_map(
                static fn (Concept $concept): array => $concept->toArray(),
                $this->dcType,
            );
        }
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->descriptionHtml !== null) {
            $data['descriptionHtml'] = $this->descriptionHtml;
        }
        if ($this->additionalNote !== null) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNote,
            );
        }
        if ($this->supplementaryDocument !== null) {
            $data['supplementaryDocument'] = array_map(
                static fn (WebResource $document): array => $document->toArray(),
                $this->supplementaryDocument,
            );
        }
        if ($this->defaultLanguage !== null) {
            $data['defaultLanguage'] = $this->defaultLanguage->toArray();
        }
        if ($this->location !== null) {
            $data['location'] = array_map(
                static fn (Location $location): array => $location->toArray(),
                $this->location,
            );
        }
        if ($this->grant !== null) {
            $data['grant'] = array_map(
                static fn (Grant $grant): array => $grant->toArray(),
                $this->grant,
            );
        }
        if ($this->homepage !== null) {
            $data['homepage'] = array_map(
                static fn (WebResource $document): array => $document->toArray(),
                $this->homepage,
            );
        }
        if ($this->priceDetail !== null) {
            $data['priceDetail'] = array_map(
                static fn (PriceDetail $priceDetail): array => $priceDetail->toArray(),
                $this->priceDetail,
            );
        }
        if ($this->hasPart !== null) {
            $data['hasPart'] = array_map(
                static fn (self $part): array => $part->toArray(),
                $this->hasPart,
            );
        }
        if ($this->isPartOf !== null) {
            $data['isPartOf'] = array_map(
                static fn (self $parent): array => $parent->toArray(),
                $this->isPartOf,
            );
        }
        if ($this->applicationDeadline !== null) {
            $data['applicationDeadline'] = array_map(
                static fn (DateTimeImmutable $deadline): string => DateTimeFormatter::format($deadline),
                $this->applicationDeadline,
            );
        }
        if ($this->mode !== null) {
            $data['mode'] = array_map(
                static fn (Concept $mode): array => $mode->toArray(),
                $this->mode,
            );
        }
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }
        if ($this->modified !== null) {
            $data['modified'] = DateTimeFormatter::format($this->modified);
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'LearningOpportunity') {
            throw new InvalidCredentialException('Data is not an LearningOpportunity.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('Data is missing title.');
        }
        if (!isset($data['providedBy']) || !is_array($data['providedBy']) || empty($data['providedBy'])) {
            throw new InvalidCredentialException('Data must have at least one providedBy.');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            array_map(
                static fn (array $providedBy): Organisation => Organisation::fromArray($providedBy),
                $data['providedBy']
            ),
            isset($data['identifier']) ? array_map(
                static fn (array $identifier): Identifier|LegalIdentifier => Identifier::fromArray($identifier),
                $data['identifier']
            ) : null,
            isset($data['duration']) ? Duration::fromString($data['duration']) : null,
            $data['status'] ?? null,
            isset($data['learningSchedule']) ? Concept::fromArray($data['learningSchedule']) : null,
            isset($data['temporal']) ? PeriodOfTime::fromArray($data['temporal']) : null,
            isset($data['bannerImage']) ? MediaObject::fromArray($data['bannerImage']) : null,
            isset($data['scheduleInformation']) ? Note::fromArray($data['scheduleInformation']) : null,
            isset($data['admissionProcedure']) ? Note::fromArray($data['admissionProcedure']) : null,
            isset($data['learningAchievementSpecification']) ? LearningAchievementSpecification::fromArray($data['learningAchievementSpecification']) : null,
            isset($data['learningActivitySpecification']) ? LearningActivitySpecification::fromArray($data['learningActivitySpecification']) : null,
            isset($data['dcType']) ? array_map(
                static fn (array $concept): Concept => Concept::fromArray($concept),
                $data['dcType']
            ) : null,
            isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            $data['descriptionHtml'] ?? null,
            isset($data['additionalNote']) ? array_map(
                static fn (array $note): Note => Note::fromArray($note),
                $data['additionalNote']
            ) : null,
            isset($data['supplementaryDocument']) ? array_map(
                static fn (array $note): WebResource => WebResource::fromArray($note),
                $data['supplementaryDocument']
            ) : null,
            isset($data['defaultLanguage']) ? Concept::fromArray($data['defaultLanguage']) : null,
            isset($data['location']) ? array_map(
                static fn (array $location): Location => Location::fromArray($location),
                $data['location']
            ) : null,
            isset($data['grant']) ? array_map(
                static fn (array $grant): Grant => Grant::fromArray($grant),
                $data['grant']
            ) : null,
            isset($data['homepage']) ? array_map(
                static fn (array $document): WebResource => WebResource::fromArray($document),
                $data['homepage']
            ) : null,
            isset($data['priceDetail']) ? array_map(
                static fn (array $priceDetail): PriceDetail => PriceDetail::fromArray($priceDetail),
                $data['priceDetail']
            ) : null,
            isset($data['hasPart']) ? array_map(
                static fn (array $part): self => self::fromArray($part),
                $data['hasPart']
            ) : null,
            isset($data['isPartOf']) ? array_map(
                static fn (array $parent): self => self::fromArray($parent),
                $data['isPartOf']
            ) : null,
            isset($data['applicationDeadline']) ? array_map(
                static fn (string $deadline): DateTimeImmutable => new DateTimeImmutable($deadline),
                $data['applicationDeadline']
            ) : null,
            isset($data['mode']) ? array_map(
                static fn (array $mode): Concept => Concept::fromArray($mode),
                $data['mode']
            ) : null,
            $data['order'] ?? null,
            isset($data['modified']) ? new DateTimeImmutable($data['modified']) : null,
        );
    }
}
