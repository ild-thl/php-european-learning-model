<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningEntitlementSpecification extends Entity {

    /**
     * @param list<Note> $additionalNotes
     * @param list<WebResource> $supplementaryDocuments
     */
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly Concept $type,
        public readonly ?LocalizedString $description = null,
        public readonly array $additionalNotes = [],
        public readonly array $supplementaryDocuments = [],
        /** @var list<Concept> */
        public readonly array $limitOccupations = [],
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($type, ElmVocabularySchemes::ENTITLEMENT, 'type');
        if (array_filter($additionalNotes, static fn ($note): bool => !$note instanceof Note) !== []) {
            throw new InvalidCredentialException('Entitlement specification notes must be Note objects.');
        }
        if (
            array_filter(
                $supplementaryDocuments,
                static fn ($document): bool => !$document instanceof WebResource,
            ) !== []
        ) {
            throw new InvalidCredentialException('Entitlement specification documents must be WebResource objects.');
        }
        ConceptAssertions::assertSchemes($limitOccupations, ElmVocabularySchemes::OCCUPATIONS, 'limitOccupations');
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:learningEntitlementSpecification:' . $this->id,
            'type' => 'LearningEntitlementSpecification',
            'title' => $this->title->toArray(),
            'dcType' => $this->type->toArray(),
        ];
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->additionalNotes !== []) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNotes,
            );
        }
        if ($this->supplementaryDocuments !== []) {
            $data['supplementaryDocument'] = array_map(
                static fn (WebResource $document): array => $document->toArray(),
                $this->supplementaryDocuments,
            );
        }
        if ($this->limitOccupations !== []) {
            $data['limitOccupation'] = array_map(
                static fn (Concept $occupation): array => $occupation->toArray(),
                $this->limitOccupations,
            );
        }

        return $data;
    }
}
