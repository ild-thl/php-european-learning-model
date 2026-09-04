<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class AwardingProcess extends Entity {

    public function __construct(string $id, public readonly Organisation $awardingBody) {
        parent::__construct($id);
        if ($id === '') {
            throw new InvalidCredentialException('An awarding process requires an identifier.');
        }
    }

    public function toArray(): array {
        return [
            'id' => 'urn:epass:awardingProcess:' . $this->id,
            'type' => 'AwardingProcess',
            'awardingBody' => $this->awardingBody->toArray(),
        ];
    }
}
