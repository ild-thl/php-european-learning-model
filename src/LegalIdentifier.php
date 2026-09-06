<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LegalIdentifier extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(
        string $id,
        public readonly string $notation,
        public readonly Concept $spatial,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($spatial, ElmVocabularySchemes::COUNTRY, 'spatial');
        if ($notation === '') {
            throw new InvalidCredentialException('A legal identifier requires notation.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => 'urn:epass:legalIdentifier:' . $this->id,
            'type' => 'LegalIdentifier',
            'notation' => $this->notation,
            'spatial' => $this->spatial->toArray(),
        ];
    }
}
