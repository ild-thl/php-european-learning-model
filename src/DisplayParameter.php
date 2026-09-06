<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\ConceptAssertions;
use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class DisplayParameter extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(
        string $id,
        public readonly Concept $language,
        public readonly Concept $primaryLanguage,
        public readonly LocalizedString $title,
        public readonly ?LocalizedString $description = null,
        /** @var list<IndividualDisplay> */
        public readonly array $individualDisplays = [],
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($language, ElmVocabularySchemes::LANGUAGE, 'language');
        ConceptAssertions::assertScheme($primaryLanguage, ElmVocabularySchemes::LANGUAGE, 'primaryLanguage');
        if (
            array_filter(
                $individualDisplays,
                static fn ($individualDisplay): bool => !$individualDisplay instanceof IndividualDisplay,
            ) !== []
        ) {
            throw new InvalidCredentialException('Display parameters accept only individual displays.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:displayParameter:' . $this->id,
            'type' => 'DisplayParameter',
            'primaryLanguage' => $this->primaryLanguage->toArray(),
            'language' => $this->language->toArray(),
            'title' => $this->title->toArray(),
        ];

        if ($this->individualDisplays !== []) {
            $data['individualDisplay'] = array_map(
                static fn (IndividualDisplay $individualDisplay): array => $individualDisplay->toArray(),
                $this->individualDisplays,
            );
        }

        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }

        return $data;
    }
}
