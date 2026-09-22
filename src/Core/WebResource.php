<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class WebResource extends Entity {

    public function __construct(
        string $id,
        public readonly string $contentUrl,
        public readonly ?LocalizedString $title = null,
    ) {
        parent::__construct($id);
        $scheme = parse_url($contentUrl, PHP_URL_SCHEME);
        if (filter_var($contentUrl, FILTER_VALIDATE_URL) === false || !in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidCredentialException('Web resource content URLs must be HTTP or HTTPS URLs.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'WebResource',
            'contentUrl' => $this->contentUrl,
        ];
        if ($this->title !== null) {
            $data['title'] = $this->title->toArray();
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['id'], $data['contentUrl'])) {
            throw new InvalidCredentialException('Invalid web resource data.');
        }

        return new self(
            $data['id'],
            $data['contentUrl'],
            isset($data['title']) ? LocalizedString::fromArray($data['title']) : null,
        );
    }
}
