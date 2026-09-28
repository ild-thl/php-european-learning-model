<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Grant extends Entity {
    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly ?string $contentUrl = null,
        public readonly ?Concept $dcType = null,
        public readonly ?LocalizedString $description = null,
        /** @var list<WebResource>|null */
        public readonly ?array $supplementaryDocuments = null,
        public readonly ?int $order = null,
    ) {
        parent::__construct($id);

        if ($contentUrl !== null) {
            $scheme = parse_url($contentUrl, PHP_URL_SCHEME);
            if (filter_var($contentUrl, FILTER_VALIDATE_URL) === false || !in_array($scheme, ['http', 'https'], true)) {
                throw new InvalidCredentialException('Grant content URLs must be HTTP or HTTPS URLs.');
            }
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'Grant',
            'title' => $this->title->toArray(),
        ];
        if ($this->dcType !== null) {
            $data['dcType'] = $this->dcType->toArray();
        }
        if ($this->contentUrl !== null) {
            $data['contentURL'] = $this->contentUrl;
        }
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->supplementaryDocuments !== null) {
            $data['supplementaryDocument'] = array_map(
                static fn (WebResource $document): array => $document->toArray(),
                $this->supplementaryDocuments,
            );
        }
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Grant') {
            throw new InvalidCredentialException('Data is not an Grant.');
        }
        if (!isset($data['title'])) {
            throw new InvalidCredentialException('Data is missing title.');
        }

        return new self(
            $data['id'],
            LocalizedString::fromArray($data['title']),
            $data['contentUrl'] ?? null,
            isset($data['dcType']) ? Concept::fromArray($data['dcType']) : null,
            isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            isset($data['supplementaryDocument']) ? array_map(
                static fn (array $document): WebResource => WebResource::fromArray($document),
                $data['supplementaryDocument'],
            ) : null,
            $data['order'] ?? null,
        );
    }
}
