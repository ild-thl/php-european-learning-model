<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class MediaObject extends Entity
{
    public function __construct(
        string $id,
        public readonly string $content,
        public readonly Concept $contentEncoding,
        public readonly Concept $contentType,
    ) {
        parent::__construct($id);
        if ($content === '') {
            throw new InvalidCredentialException('A media object requires content.');
        }
    }

    public function toArray(): array
    {
        return [
            'id' => 'urn:epass:mediaObject:' . $this->id,
            'type' => 'MediaObject',
            'content' => $this->content,
            'contentEncoding' => $this->contentEncoding->toArray(),
            'contentType' => $this->contentType->toArray(),
        ];
    }
}