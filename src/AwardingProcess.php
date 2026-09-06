<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\Organisation;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class AwardingProcess extends \IsyThl\EuropeanLearningModel\Core\Entity {

    public function __construct(string $id, public readonly Organisation $awardingBody) {
        parent::__construct($id);
        if ($id === '') {
            throw new InvalidCredentialException('An awarding process requires an identifier.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return [
            'id' => 'urn:epass:awardingProcess:' . $this->id,
            'type' => 'AwardingProcess',
            'awardingBody' => $this->awardingBody->toArray(),
        ];
    }
}
