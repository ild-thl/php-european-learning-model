<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class ContactPoint extends Entity {

    public function __construct(
        string $id,
        /** @var list<Address>|null */
        public readonly ?array $address = null,
        /** @var list<EmailAddress>|null */
        public readonly ?array $emailAddress = null,
        // TODO: description, additionalNote, phone, contactForm, order
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = ['id' => $this->id, 'type' => 'ContactPoint'];
        if ($this->address !== null) {
            $data['address'] = array_map(static fn (Address $address): array => $address->toArray(), $this->address);
        }
        if ($this->emailAddress !== null) {
            $data['emailAddress'] = array_map(static fn (EmailAddress $emailAddress): array => $emailAddress->toArray(), $this->emailAddress);
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'ContactPoint') {
            throw new InvalidCredentialException('Data is not a ContactPoint.');
        }
        if (!isset($data['id'])) {
            throw new InvalidCredentialException('Data is missing a contact point identifier.');
        }

        return new self(
            $data['id'],
            isset($data['address']) ? array_map(static fn (array $address): Address => Address::fromArray($address), $data['address']) : null,
            isset($data['emailAddress'])
                ? array_map(static fn (array $emailAddress): EmailAddress => EmailAddress::fromArray($emailAddress), $data['emailAddress'])
                : null,
        );
    }
}
