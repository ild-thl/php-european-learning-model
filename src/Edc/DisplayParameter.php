<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Edc;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\ConceptAssertions;
use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\Entity;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;

final class DisplayParameter extends Entity {
    public function __construct(
        string $id,
        public readonly Concept $language,
        public readonly Concept $primaryLanguage,
        public readonly LocalizedString $title,
        public readonly ?LocalizedString $description = null,
        /** @var list<IndividualDisplay>|null */
        public readonly ?array $individualDisplay = null,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($language, ElmVocabularySchemes::LANGUAGE, 'language');
        ConceptAssertions::assertScheme($primaryLanguage, ElmVocabularySchemes::LANGUAGE, 'primaryLanguage');
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'DisplayParameter',
            'primaryLanguage' => $this->primaryLanguage->toArray(),
            'language' => $this->language->toArray(),
            'title' => $this->title->toArray(),
        ];

        if ($this->individualDisplay !== null) {
            $data['individualDisplay'] = array_map(
                static fn (IndividualDisplay $display): array => $display->toArray(),
                $this->individualDisplay,
            );
        }

        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        return new self(
            $data['id'],
            Concept::fromArray($data['language']),
            Concept::fromArray($data['primaryLanguage']),
            new LocalizedString($data['title']),
            isset($data['description']) ? new LocalizedString($data['description']) : null,
            isset($data['individualDisplay']) ? array_map(
                static fn (array $individualDisplay): IndividualDisplay => IndividualDisplay::fromArray($individualDisplay),
                $data['individualDisplay'],
            ) : null,
        );
    }
}
