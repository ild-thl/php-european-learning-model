<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

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
            'id' => 'urn:epass:mediaObject:' . $this->id,
            'type' => 'MediaObject',
            'content' => $this->content,
            'contentEncoding' => $this->contentEncoding->toArray(),
            'contentType' => $this->contentType->toArray(),
        ];
    }
}
