<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

/**
 * Class accreditation
 * 
 * The quality assurance or licensing of an organisation or a qualification. An accreditation instance can be used to specify information about: (1) the quality assurance and/or licensing of an organisation, (2) the quality assurance and/or licensing of an organisation with respect to a specific qualification.
 *
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#accreditation 
 */
final class Accreditation extends Entity {

    public function __construct(
        string $id,
        /** A name given to the resource. */
        public readonly LocalizedString $title,
        /** The legal person who is issuing the accreditation decision., The Quality Assuring Authority.(i.e., assurer). */
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
        /** @var list<Note> */
        public readonly array $additionalNotes = [],
        /** @var list<WebResource> */
        public readonly array $supplementaryDocuments = [],
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
        if (
            array_filter(
                $additionalNotes,
                static fn ($note): bool => !$note instanceof Note,
            ) !== []
        ) {
            throw new InvalidCredentialException('Specification notes must be Note objects.');
        }
        if (
            array_filter(
                $supplementaryDocuments,
                static fn ($document): bool => !$document instanceof WebResource,
            ) !== []
        ) {
            throw new InvalidCredentialException('Supplementary documents must be WebResource objects.');
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

        return $data;
    }
}
