<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningEntitlementSpecification extends Entity {
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly Concept $dcType,
        public readonly Concept $entitlementStatus,
        public readonly ?LocalizedString $description = null,
        /** @var list<Note>|null */
        public readonly ?array $additionalNote = null,
        /** @var list<WebResource>|null */
        public readonly ?array $supplementaryDocument = null,
        /** @var list<Concept>|null */
        public readonly ?array $limitOccupation = null,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($entitlementStatus, ElmVocabularySchemes::ENTITLEMENT_STATUS, 'entitlementStatus');
        if ($limitOccupation != null) {
            ConceptAssertions::assertSchemes($limitOccupation, ElmVocabularySchemes::OCCUPATIONS, 'limitOccupation');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningEntitlementSpecification',
            'title' => $this->title->toArray(),
            'dcType' => $this->dcType->toArray(),
            'entitlementStatus' => $this->entitlementStatus->toArray(),
        ];
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
        if ($this->limitOccupation !== null) {
            $data['limitOccupation'] = array_map(
                static fn (Concept $occupation): array => $occupation->toArray(),
                $this->limitOccupation,
            );
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'LearningEntitlementSpecification') {
            throw new InvalidCredentialException('Data is not a LearningEntitlementSpecification.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('Data is missing title');
        }
        if (!isset($data['dcType'])) {
            throw new InvalidCredentialException('Data is missing dcType');
        }
        if (!isset($data['entitlementStatus'])) {
            throw new InvalidCredentialException('Data is missing entitlementStatus');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            Concept::fromArray($data['dcType']),
            Concept::fromArray($data['entitlementStatus']),
        );
    }
}
