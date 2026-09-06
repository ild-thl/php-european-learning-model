<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class WebResource extends \IsyThl\EuropeanLearningModel\Core\Entity {

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
