<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

class LearningActivitySpecification extends Entity {
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        /** @var list<Identifier|LegalIdentifier>|null */
        public readonly ?array $identifier = null,
        /** @var list<LocalizedString>|null */
        public readonly ?array $altLabel = null,
        public readonly ?string $status = null,
        public readonly ?Duration $volumeOfLearning = null,
        /** @var list<Concept>|null */
        public readonly ?array $dcType = null,
        /** @var list<Concept>|null */
        public readonly ?array $language = null,
        public readonly ?LocalizedString $description = null,
        /** @var list<Note>|null */
        public readonly ?array $additionalNote = null,
        /** @var list<WebResource>|null */
        public readonly ?array $supplementaryDocument = null,
        /** @var list<LearningActivitySpecification>|null */
        public readonly ?array $generalisationOf = null,
        /** @var list<LearningActivitySpecification>|null */
        public readonly ?array $specialisationOf = null,
        /** @var list<CreditPoint>|null */
        public readonly ?array $creditPoint = null,
        /** @var list<LearningActivitySpecification>|null */
        public readonly ?array $hasPart = null,
        /** @var list<LearningActivitySpecification>|null */
        public readonly ?array $isPartOf = null,
        /** @var list<Concept>|null */
        public readonly ?array $mode = null,
        /** @var list<WebResource>|null */
        public readonly ?array $homepage = null,
        /** @var list<string>|null */
        public readonly ?array $category = null,
        /** @var list<Concept>|null */
        public readonly ?array $targetGroup = null,
        /** @var list<AwardingOpportunity>|null */
        public readonly ?array $awardingOpportunity = null,
        /** @var list<Concept>|null */
        public readonly ?array $educationSubject = null,
        /** @var list<LearningOutcome>|null */
        public readonly ?array $learningOutcome = null,
        /** @var list<LearningActivitySpecification>|null */
        public readonly ?array $influencedBy = null,
        /** @var list<LearningAssessmentSpecification>|null */
        public readonly ?array $provenBy = null,
        /** @var list<Concept>|null */
        public readonly ?array $iscedfCode = null,
        /** @var list<LearningEntitlementSpecification>|null */
        public readonly ?array $entitlesTo = null,
        /** @var list<Concept>|null */
        public readonly ?array $educationLevel = null,
        public readonly ?int $order = null,
        public readonly ?DateTimeImmutable $modified = null,
    ) {
        parent::__construct($id);
        if ($language !== null) {
            ConceptAssertions::assertSchemes($language, ElmVocabularySchemes::LANGUAGE, 'language');
        }
        if ($targetGroup !== null) {
            ConceptAssertions::assertSchemes($targetGroup, ElmVocabularySchemes::TARGET_GROUP, 'targetGroup');
        }
        if ($mode !== null) {
            ConceptAssertions::assertSchemes($mode, ElmVocabularySchemes::ASSESSMENT, 'mode');
        }
        if ($iscedfCode !== null) {
            ConceptAssertions::assertSchemes($iscedfCode, ElmVocabularySchemes::ISCED_F, 'ISCEDFCode');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningActivitySpecification',
            'title' => $this->title->toArray(),
        ];
        if ($this->identifier !== null) {
            $data['identifier'] = array_map(
                static fn (Identifier $identifier): array => $identifier->toArray(),
                $this->identifier,
            );
        }
        if ($this->altLabel !== null) {
            $data['altLabel'] = array_map(
                static fn (LocalizedString $altLabel): array => $altLabel->toArray(),
                $this->altLabel,
            );
        }
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }
        if ($this->volumeOfLearning !== null) {
            $data['volumeOfLearning'] = (string) $this->volumeOfLearning;
        }
        if ($this->dcType !== null) {
            $data['dcType'] = array_map(
                static fn (Concept $type): array => $type->toArray(),
                $this->dcType,
            );
        }
        if ($this->language !== null) {
            $data['language'] = array_map(
                static fn (Concept $language): array => $language->toArray(),
                $this->language,
            );
        }
        if ($this->description !== null) {
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
                static fn (WebResource $document): array => $document->toArray(),
                $this->supplementaryDocument,
            );
        }
        if ($this->generalisationOf !== null) {
            $data['generalisationOf'] = array_map(
                static fn (LearningActivitySpecification $generalisationOf): array => $generalisationOf->toArray(),
                $this->generalisationOf,
            );
        }
        if ($this->specialisationOf !== null) {
            $data['specialisationOf'] = array_map(
                static fn (LearningActivitySpecification $specialisationOf): array => $specialisationOf->toArray(),
                $this->specialisationOf,
            );
        }
        if ($this->creditPoint !== null) {
            $data['creditPoint'] = array_map(
                static fn (CreditPoint $creditPoint): array => $creditPoint->toArray(),
                $this->creditPoint,
            );
        }
        if ($this->hasPart !== null) {
            $data['hasPart'] = array_map(
                static fn (LearningActivitySpecification $hasPart): array => $hasPart->toArray(),
                $this->hasPart,
            );
        }
        if ($this->isPartOf !== null) {
            $data['isPartOf'] = array_map(
                static fn (LearningActivitySpecification $isPartOf): array => $isPartOf->toArray(),
                $this->isPartOf,
            );
        }
        if ($this->mode !== null) {
            $data['mode'] = array_map(
                static fn (Concept $mode): array => $mode->toArray(),
                $this->mode,
            );
        }
        if ($this->homepage !== null) {
            $data['homepage'] = array_map(
                static fn (WebResource $homepage): array => $homepage->toArray(),
                $this->homepage,
            );
        }
        if ($this->category !== null) {
            $data['category'] = $this->category;
        }
        if ($this->targetGroup !== null) {
            $data['targetGroup'] = array_map(
                static fn (Concept $targetGroup): array => $targetGroup->toArray(),
                $this->targetGroup,
            );
        }
        if ($this->awardingOpportunity !== null) {
            $data['awardingOpportunity'] = array_map(
                static fn (AwardingOpportunity $awardingOpportunity): array => $awardingOpportunity->toArray(),
                $this->awardingOpportunity,
            );
        }
        if ($this->educationSubject !== null) {
            $data['educationSubject'] = array_map(
                static fn (Concept $subject): array => $subject->toArray(),
                $this->educationSubject,
            );
        }
        if ($this->learningOutcome !== null) {
            $data['learningOutcome'] = array_map(
                static fn (LearningOutcome $learningOutcome): array => $learningOutcome->toArray(),
                $this->learningOutcome,
            );
        }
        if ($this->influencedBy !== null) {
            $data['influencedBy'] = array_map(
                static fn (LearningActivitySpecification $influencedBy): array => $influencedBy->toArray(),
                $this->influencedBy,
            );
        }
        if ($this->provenBy !== null) {
            $data['provenBy'] = array_map(
                static fn (LearningAssessmentSpecification $provenBy): array => $provenBy->toArray(),
                $this->provenBy,
            );
        }
        if ($this->iscedfCode !== null) {
            $data['ISCEDFCode'] = array_map(
                static fn (Concept $iscedfCode): array => $iscedfCode->toArray(),
                $this->iscedfCode,
            );
        }
        if ($this->entitlesTo !== null) {
            $data['entitlesTo'] = array_map(
                static fn (LearningEntitlementSpecification $entitlesTo): array => $entitlesTo->toArray(),
                $this->entitlesTo,
            );
        }
        if ($this->educationLevel !== null) {
            $data['educationLevel'] = array_map(
                static fn (Concept $level): array => $level->toArray(),
                $this->educationLevel,
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
        if (!isset($data['type']) || $data['type'] !== 'LearningActivitySpecification') {
            throw new \InvalidArgumentException('Data is not a LearningActivitySpecification.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('Data is missing title');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            isset($data['identifier']) ? array_map(
                static fn (array $identifier): Identifier => Identifier::fromArray($identifier),
                $data['identifier']
            ) : null,
            isset($data['altLabel']) ? array_map(
                static fn (array $altLabel): LocalizedString => LocalizedString::fromArray($altLabel),
                $data['altLabel']
            ) : null,
            $data['status'] ?? null,
            isset($data['volumeOfLearning']) ? Duration::fromString($data['volumeOfLearning']) : null,
            isset($data['dcType']) ? array_map(
                static fn (array $type): Concept => Concept::fromArray($type),
                $data['dcType'],
            ) : null,
            isset($data['language']) ? array_map(
                static fn (array $language): Concept => Concept::fromArray($language),
                $data['language'],
            ) : null,
            isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            isset($data['additionalNote']) ? array_map(
                static fn (array $additionalNote): Note => Note::fromArray($additionalNote),
                $data['additionalNote'],
            ) : null,
            isset($data['supplementaryDocument']) ? array_map(
                static fn (array $supplementaryDocument): WebResource => WebResource::fromArray($supplementaryDocument),
                $data['supplementaryDocument'],
            ) : null,
            isset($data['generalisationOf']) ? array_map(
                static fn (array $generalisationOf): LearningActivitySpecification => LearningActivitySpecification::fromArray($generalisationOf),
                $data['generalisationOf'],
            ) : null,
            isset($data['specialisationOf']) ? array_map(
                static fn (array $specialisationOf): LearningActivitySpecification => LearningActivitySpecification::fromArray($specialisationOf),
                $data['specialisationOf'],
            ) : null,
            isset($data['creditPoint']) ? array_map(
                static fn (array $creditPoint): CreditPoint => CreditPoint::fromArray($creditPoint),
                $data['creditPoint'],
            ) : null,
            isset($data['hasPart']) ? array_map(
                static fn (array $hasPart): LearningActivitySpecification => LearningActivitySpecification::fromArray($hasPart),
                $data['hasPart'],
            ) : null,
            isset($data['isPartOf']) ? array_map(
                static fn (array $isPartOf): LearningActivitySpecification => LearningActivitySpecification::fromArray($isPartOf),
                $data['isPartOf'],
            ) : null,
            isset($data['mode']) ? array_map(
                static fn (array $mode): Concept => Concept::fromArray($mode),
                $data['mode'],
            ) : null,
            isset($data['homepage']) ? array_map(
                static fn (array $homepage): WebResource => WebResource::fromArray($homepage),
                $data['homepage'],
            ) : null,
            isset($data['category']) ? $data['category'] : null,
            isset($data['targetGroup']) ? array_map(
                static fn (array $targetGroup): Concept => Concept::fromArray($targetGroup),
                $data['targetGroup'],
            ) : null,
            isset($data['awardingOpportunity']) ? array_map(
                static fn (array $awardingOpportunity): AwardingOpportunity => AwardingOpportunity::fromArray($awardingOpportunity),
                $data['awardingOpportunity'],
            ) : null,
            isset($data['educationSubject']) ? array_map(
                static fn (array $educationSubject): Concept => Concept::fromArray($educationSubject),
                $data['educationSubject'],
            ) : null,
            isset($data['learningOutcome']) ? array_map(
                static fn (array $learningOutcome): LearningOutcome => LearningOutcome::fromArray($learningOutcome),
                $data['learningOutcome'],
            ) : null,
            isset($data['influencedBy']) ? array_map(
                static fn (array $influencedBy): LearningActivitySpecification => LearningActivitySpecification::fromArray($influencedBy),
                $data['influencedBy'],
            ) : null,
            isset($data['provenBy']) ? array_map(
                static fn (array $provenBy): LearningAssessmentSpecification => LearningAssessmentSpecification::fromArray($provenBy),
                $data['provenBy'],
            ) : null,
            isset($data['ISCEDFCode']) ? array_map(
                static fn (array $iscedfCode): Concept => Concept::fromArray($iscedfCode),
                $data['ISCEDFCode'],
            ) : null,
            isset($data['entitlesTo']) ? array_map(
                static fn (array $entitlesTo): LearningEntitlementSpecification => LearningEntitlementSpecification::fromArray($entitlesTo),
                $data['entitlesTo'],
            ) : null,
            isset($data['educationLevel']) ? array_map(
                static fn (array $educationLevel): Concept => Concept::fromArray($educationLevel),
                $data['educationLevel'],
            ) : null,
            isset($data['order']) ? $data['order'] : null,
            isset($data['modified']) ? new DateTimeImmutable($data['modified']) : null,
        );
    }
}
