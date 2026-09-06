<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\LocalizedString;

final class Note extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(string $id, public readonly LocalizedString $noteLiteral) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => 'urn:epass:note:' . $this->id,
            'type' => 'Note',
            'noteLiteral' => $this->noteLiteral->toArray(),
        ];
    }
}
