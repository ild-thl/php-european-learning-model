<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class EmailAddress extends Entity {

    public function __construct(string $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidCredentialException('Invalid email address.');
        }
        parent::__construct($email);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        return ['id' => 'mailto:' . $this->id, 'type' => 'Mailbox'];
    }
}
