<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class IndividualDisplay extends \IsyThl\EuropeanLearningModel\Core\Entity {

    /** @param list<DisplayDetail> $displayDetails */
    public function __construct(
        string $id,
        public readonly Concept $language,
        public readonly array $displayDetails,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($language, ElmVocabularySchemes::LANGUAGE, 'language');
        if (
            $displayDetails === [] || array_filter(
                $displayDetails,
                static fn ($displayDetail): bool => !$displayDetail instanceof DisplayDetail,
            ) !== []
        ) {
            throw new InvalidCredentialException('An individual display requires display details.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => 'urn:epass:individualDisplay:' . $this->id,
            'type' => 'IndividualDisplay',
            'displayDetail' => array_map(
                static fn (DisplayDetail $displayDetail): array => $displayDetail->toArray(),
                $this->displayDetails,
            ),
            'language' => $this->language->toArray(),
        ];
    }
}
