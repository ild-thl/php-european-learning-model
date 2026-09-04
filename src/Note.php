<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class Note extends Entity {

    public function __construct(string $id, public readonly LocalizedString $noteLiteral) {
        parent::__construct($id);
    }

    public function toArray(): array {
        return [
            'id' => 'urn:epass:note:' . $this->id,
            'type' => 'Note',
            'noteLiteral' => $this->noteLiteral->toArray(),
        ];
    }
}
