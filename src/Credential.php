<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use DateTimeImmutable;
use DateTimeZone;

final class Credential extends Entity
{
    private const CONTEXT = [
        'https://www.w3.org/2018/credentials/v1',
        'http://data.europa.eu/snb/model/context/edc-ap',
    ];

    public function __construct(
        string $id,
        public readonly CredentialSubject $credentialSubject,
        public readonly DisplayParameter $displayParameter,
        public readonly DateTimeImmutable $validFrom,
        public readonly ?DateTimeImmutable $expirationDate = null,
        ?Concept $credentialProfile = null,
    ) {
        parent::__construct($id);
        $this->credentialProfile = $credentialProfile ?? new Concept(
            'http://data.europa.eu/snb/credential/e34929035b',
            new LocalizedString(['en' => 'Generic']),
            new ConceptScheme('http://data.europa.eu/snb/credential/25831c2'),
        );
    }

    public readonly Concept $credentialProfile;

    public function toArray(): array
    {
        $data = [
            'id' => 'urn:credential:' . $this->id,
            'type' => ['VerifiableCredential', 'EuropeanDigitalCredential'],
            'credentialProfiles' => [$this->credentialProfile->toArray()],
            'displayParameter' => $this->displayParameter->toArray(),
            'credentialSchema' => [
                'id' => 'http://data.europa.eu/snb/model/ap/edc-generic-full',
                'type' => 'ShaclValidator2017',
            ],
            'credentialSubject' => $this->credentialSubject->toArray(),
            'validFrom' => $this->formatDate($this->validFrom),
            '@context' => self::CONTEXT,
        ];
        if ($this->expirationDate !== null) {
            $data['expirationDate'] = $this->formatDate($this->expirationDate);
        }
        return $data;
    }

    private function formatDate(DateTimeImmutable $date): string
    {
        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }
}