<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class VocabularyScheme extends Entity {

    /**
     * @param list<Concept> $concepts
     */
    public function __construct(
        string $id,
        public readonly ConceptScheme $scheme,
        public readonly array $concepts = [],
        public readonly ?LocalizedString $title = null,
        public readonly ?string $source = null,
    ) {
        parent::__construct($id);
        if ($scheme->id !== $id) {
            throw new InvalidCredentialException('A vocabulary snapshot id must match its concept scheme.');
        }
        if (array_filter($concepts, static fn ($concept): bool => !$concept instanceof Concept) !== []) {
            throw new InvalidCredentialException('Vocabulary entries must be Concept objects.');
        }
        $conceptIds = array_map(static fn (Concept $concept): string => $concept->id, $concepts);
        if (count($conceptIds) !== count(array_unique($conceptIds))) {
            throw new InvalidCredentialException('A vocabulary snapshot cannot contain duplicate concepts.');
        }
        if (array_filter($concepts, fn (Concept $concept): bool => $concept->inScheme->id !== $id) !== []) {
            throw new InvalidCredentialException('Vocabulary concepts must belong to the snapshot scheme.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'VocabularyScheme',
            'scheme' => $this->scheme->toArray(),
            'concept' => array_map(
                static fn (Concept $concept): array => $concept->toArray(),
                $this->concepts,
            ),
        ];
        if ($this->title !== null) {
            $data['title'] = $this->title->toArray();
        }
        if ($this->source !== null) {
            $data['source'] = $this->source;
        }

        return $data;
    }

    public function contains(Concept $concept): bool {
        return $concept->inScheme->id === $this->id
            && in_array($concept->id, $this->conceptIds(), true);
    }

    public function find(string $conceptId): ?Concept {
        foreach ($this->concepts as $concept) {
            if ($concept->id === $conceptId) {
                return $concept;
            }
        }

        return null;
    }

    public function findByNotation(string $notation): ?Concept {
        foreach ($this->concepts as $concept) {
            if ($concept->notation === $notation) {
                return $concept;
            }
        }

        return null;
    }

    /** @return list<string> */
    private function conceptIds(): array {
        return array_map(static fn (Concept $concept): string => $concept->id, $this->concepts);
    }
}
