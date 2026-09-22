<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class MediaObject extends Entity {

    public function __construct(
        string $id,
        public readonly string $content,
        public readonly Concept $contentEncoding,
        public readonly Concept $contentType,
    ) {
        parent::__construct($id);
        ConceptAssertions::assertScheme($contentEncoding, ElmVocabularySchemes::CONTENT_ENCODING, 'contentEncoding');
        ConceptAssertions::assertScheme($contentType, ElmVocabularySchemes::CONTENT_TYPE, 'contentType');
        if ($content === '') {
            throw new InvalidCredentialException('A media object requires content.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'type' => 'MediaObject',
            'content' => $this->content,
            'contentEncoding' => $this->contentEncoding->toArray(),
            'contentType' => $this->contentType->toArray(),
        ];
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'MediaObject') {
            throw new InvalidCredentialException('Data is not a MediaObject.');
        }
        if (!isset($data['id'], $data['content'], $data['contentEncoding'], $data['contentType'])) {
            throw new InvalidCredentialException('Data is missing a media object field.');
        }

        return new self(
            $data['id'],
            $data['content'],
            Concept::fromArray($data['contentEncoding']),
            Concept::fromArray($data['contentType']),
        );
    }
}
