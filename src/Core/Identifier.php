<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

class Identifier extends Entity {

    public function __construct(
        string $id,
        public readonly string $notation,
        public readonly ?string $schemeName = null,
        public readonly ?string $schemeAgency = null,
        public readonly ?string $creator = null,
        public readonly ?string $schemeVersion = null,
        public readonly ?string $schemeId = null,
        public readonly ?DateTimeImmutable $issued = null,
        /** @var list<Concept>|null */
        public readonly ?array $dcType = null,
        public readonly ?int $order = null,
    ) {
        parent::__construct($id);
        if ($notation === '') {
            throw new InvalidCredentialException('An identifier requires notation.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'Identifier',
            'notation' => $this->notation,
        ];
        if ($this->schemeAgency !== null) {
            $data['schemeAgency'] = $this->schemeAgency;
        }
        if ($this->schemeName !== null) {
            $data['schemeName'] = $this->schemeName;
        }
        if ($this->creator !== null) {
            $data['creator'] = $this->creator;
        }
        if ($this->schemeVersion !== null) {
            $data['schemeVersion'] = $this->schemeVersion;
        }
        if ($this->schemeId !== null) {
            $data['schemeId'] = $this->schemeId;
        }
        if ($this->issued !== null) {
            $data['issued'] = DateTimeFormatter::format($this->issued);
        }
        if ($this->dcType !== null) {
            $data['dcType'] = array_map(
                static fn (Concept $concept): array => $concept->toArray(),
                $this->dcType,
            );
        }
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'Identifier') {
            throw new InvalidCredentialException('Data is not an Identifier.');
        }
        if (!isset($data['id'], $data['notation'])) {
            throw new InvalidCredentialException('Data is missing an identifier field.');
        }

        return new self(
            $data['id'],
            $data['notation'],
            $data['schemeName'] ?? null,
            $data['schemeAgency'] ?? null,
            $data['creator'] ?? null,
            $data['schemeVersion'] ?? null,
            $data['schemeId'] ?? null,
            isset($data['issued']) ? new DateTimeImmutable($data['issued']) : null,
            isset($data['dcType'])
                ? array_map(static fn (array $concept): Concept => Concept::fromArray($concept), $data['dcType'])
                : null,
            $data['order'] ?? null,
        );
    }
}
