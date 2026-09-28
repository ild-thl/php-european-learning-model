<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LearningAssessmentSpecification extends Entity {
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly ?Concept $dcType = null,
        public readonly ?GradingScheme $gradingScheme = null,
        /** @var list<Concept>|null */
        public readonly ?array $language = null,
        /** @var list<Concept>|null */
        public readonly ?array $mode = null,
    ) {
        parent::__construct($id);
        if ($language !== null) {
            ConceptAssertions::assertSchemes($language, ElmVocabularySchemes::LANGUAGE, 'language');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningAssessmentSpecification',
            'title' => $this->title->toArray(),
        ];

        if ($this->dcType !== null) {
            $data['dcType'] = [$this->dcType->toArray()];
        }
        if ($this->gradingScheme !== null) {
            $data['gradingScheme'] = $this->gradingScheme->toArray();
        }
        if ($this->language !== null) {
            $data['language'] = array_map(fn (Concept $concept) => $concept->toArray(), $this->language);
        }
        if ($this->mode !== null) {
            $data['mode'] = array_map(fn (Concept $concept) => $concept->toArray(), $this->mode);
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'LearningAssessmentSpecification') {
            throw new InvalidCredentialException('Data is not a LearningAssessmentSpecification.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('Data is missing title');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            isset($data['dcType'][0]) ? Concept::fromArray($data['dcType'][0]) : null,
            isset($data['gradingScheme']) ? GradingScheme::fromArray($data['gradingScheme']) : null,
            isset($data['language']) ? array_map(fn (array $concept) => Concept::fromArray($concept), $data['language']) : null,
            isset($data['mode']) ? array_map(fn (array $concept) => Concept::fromArray($concept), $data['mode']) : null,
        );
    }
}
