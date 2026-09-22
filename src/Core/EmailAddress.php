<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

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

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type'], $data['id']) || $data['type'] !== 'Mailbox') {
            throw new InvalidCredentialException('Data is not a Mailbox.');
        }

        return new self(
            is_string($data['id']) && str_starts_with($data['id'], 'mailto:')
                ? substr($data['id'], strlen('mailto:'))
                : $data['id'],
        );
    }
}
