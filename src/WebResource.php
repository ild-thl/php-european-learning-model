<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class WebResource extends Entity
{
    public function __construct(
        string $id,
        public readonly string $contentUrl,
        public readonly ?LocalizedString $title = null,
    ) {
        parent::__construct($id);
        if (filter_var($contentUrl, FILTER_VALIDATE_URL) === false || !in_array(parse_url($contentUrl, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new \InvalidArgumentException('Web resource content URLs must be HTTP or HTTPS URLs.');
        }
    }

    public function toArray(): array
    {
        $data = [
            'id' => 'urn:epass:webResource:' . $this->id,
            'type' => 'WebResource',
            'contentURL' => $this->contentUrl,
        ];
        if ($this->title !== null) {
            $data['title'] = $this->title->toArray();
        }
        return $data;
    }
}