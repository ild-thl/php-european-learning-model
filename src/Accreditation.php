<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;

final class Accreditation extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly Organisation $accreditingAgent,
        /** @var list<Concept> */
        public readonly array $accreditedForEqfLevels = [],
        /** @var list<Concept> */
        public readonly array $accreditedForThematicAreas = [],
        /** @var list<Concept> */
        public readonly array $accreditedInJurisdictions = [],
        public readonly ?Concept $decision = null,
        /** @var list<Concept> */
        public readonly array $limitCredentialTypes = [],
        public readonly ?Concept $status = null,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertSchemes(
            $accreditedForEqfLevels,
            ElmVocabularySchemes::EQF,
            'accreditedForEqfLevels',
        );
        ConceptAssertions::assertSchemes(
            $accreditedForThematicAreas,
            ElmVocabularySchemes::ISCED_F,
            'accreditedForThematicAreas',
        );
        ConceptAssertions::assertSchemes(
            $accreditedInJurisdictions,
            ElmVocabularySchemes::ATU,
            'accreditedInJurisdictions',
        );
        if ($decision !== null) {
            ConceptAssertions::assertScheme($decision, ElmVocabularySchemes::ACCREDITATION_DECISION, 'decision');
        }
        ConceptAssertions::assertSchemes(
            $limitCredentialTypes,
            ElmVocabularySchemes::CREDENTIAL,
            'limitCredentialTypes',
        );
        if ($status !== null) {
            ConceptAssertions::assertScheme($status, ElmVocabularySchemes::ACCREDITATION_STATUS, 'status');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:accreditation:' . $this->id,
            'type' => 'Accreditation',
            'title' => $this->title->toArray(),
            'accreditingAgent' => $this->accreditingAgent->toArray(),
        ];
        if ($this->accreditedForEqfLevels !== []) {
            $data['accreditedForEQFLevel'] = array_map(
                static fn (Concept $concept): array => $concept->toArray(),
                $this->accreditedForEqfLevels,
            );
        }
        if ($this->accreditedForThematicAreas !== []) {
            $data['accreditedForThematicArea'] = array_map(
                static fn (Concept $concept): array => $concept->toArray(),
                $this->accreditedForThematicAreas,
            );
        }
        if ($this->accreditedInJurisdictions !== []) {
            $data['accreditedInJurisdiction'] = array_map(
                static fn (Concept $concept): array => $concept->toArray(),
                $this->accreditedInJurisdictions,
            );
        }
        if ($this->decision !== null) {
            $data['decision'] = $this->decision->toArray();
        }
        if ($this->limitCredentialTypes !== []) {
            $data['limitCredentialType'] = array_map(
                static fn (Concept $concept): array => $concept->toArray(),
                $this->limitCredentialTypes,
            );
        }
        if ($this->status !== null) {
            $data['status'] = $this->status->toArray();
        }

        return $data;
    }
}
