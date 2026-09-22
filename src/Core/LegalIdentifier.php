<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class LegalIdentifier extends Identifier {

    /** @param list<Concept>|null $dcType */
    public function __construct(
        string $id,
        string $notation,
        public readonly Concept $spatial,
        ?string $schemeName = null,
        ?string $schemeAgency = null,
        ?string $creator = null,
        ?string $schemeVersion = null,
        ?string $schemeId = null,
        ?DateTimeImmutable $issued = null,
        ?array $dcType = null,
        ?int $order = null,
    ) {
        parent::__construct(
            $id,
            $notation,
            $schemeName,
            $schemeAgency,
            $creator,
            $schemeVersion,
            $schemeId,
            $issued,
            $dcType,
            $order,
        );
        ConceptAssertions::assertScheme($spatial, ElmVocabularySchemes::COUNTRY, 'spatial');
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = parent::toArray();
        $data['type'] = 'LegalIdentifier';
        $data['spatial'] = $this->spatial->toArray();

        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'LegalIdentifier') {
            throw new InvalidCredentialException('Data is not a LegalIdentifier.');
        }
        if (!isset($data['id'], $data['notation'], $data['spatial'])) {
            throw new InvalidCredentialException('Data is missing a legal identifier field.');
        }

        return new self(
            $data['id'],
            $data['notation'],
            Concept::fromArray($data['spatial']),
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
