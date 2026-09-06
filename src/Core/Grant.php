<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Concept;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\LocalizedString;
use IsyThl\EuropeanLearningModel\WebResource;

final class Grant {

    /** @param list<WebResource> $supplementaryDocuments */
    public function __construct(
        public readonly LocalizedString $title,
        public readonly ?Concept $type = null,
        public readonly ?string $contentUrl = null,
        public readonly array $supplementaryDocuments = [],
    ) {
        if ($contentUrl !== null) {
            $scheme = parse_url($contentUrl, PHP_URL_SCHEME);
            if (filter_var($contentUrl, FILTER_VALIDATE_URL) === false || !in_array($scheme, ['http', 'https'], true)) {
                throw new InvalidCredentialException('Grant content URLs must be HTTP or HTTPS URLs.');
            }
        }
        if (
            array_filter(
                $supplementaryDocuments,
                static fn ($document): bool => !$document instanceof WebResource,
            ) !== []
        ) {
            throw new InvalidCredentialException('Grant supplementary documents must be WebResource objects.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'type' => 'Grant',
            'title' => $this->title->toArray(),
        ];
        if ($this->type !== null) {
            $data['dcType'] = $this->type->toArray();
        }
        if ($this->contentUrl !== null) {
            $data['contentURL'] = $this->contentUrl;
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
