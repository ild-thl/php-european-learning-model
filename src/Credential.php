<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel;

use IsyThl\EuropeanLearningModel\Core\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\Core\ConceptScheme;
use IsyThl\EuropeanLearningModel\Core\Concept;
use IsyThl\EuropeanLearningModel\Core\VocabularyScheme;
use IsyThl\EuropeanLearningModel\Edc\Issuer;
use IsyThl\EuropeanLearningModel\Edc\DisplayParameter;
use IsyThl\EuropeanLearningModel\Edc\CredentialSubject;
use IsyThl\EuropeanLearningModel\Core\LocalizedString;
use DateTimeImmutable;
use DateTimeZone;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

final class Credential extends \IsyThl\EuropeanLearningModel\Core\Entity {

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
        public readonly ?DateTimeImmutable $issuanceDate = null,
        public readonly ?DateTimeImmutable $issued = null,
        public readonly ?DateTimeImmutable $validUntil = null,
        public readonly ?Issuer $issuer = null,
    ) {
        parent::__construct($id);
        $this->credentialProfile = $credentialProfile ?? new Concept(
            'http://data.europa.eu/snb/credential/e34929035b',
            new LocalizedString(['en' => 'Generic']),
            new ConceptScheme(ElmVocabularySchemes::CREDENTIAL),
        );
        if ($this->credentialProfile->inScheme->id !== ElmVocabularySchemes::CREDENTIAL) {
            throw new InvalidCredentialException(
                'Credential profile must belong to the ELM credential profile scheme.',
            );
        }
        $dates = [
            'expirationDate' => $expirationDate,
            'issuanceDate' => $issuanceDate,
            'issued' => $issued,
            'validUntil' => $validUntil,
        ];
        foreach ($dates as $field => $date) {
            if ($date !== null && $date < $validFrom) {
                throw new InvalidCredentialException(sprintf('Credential %s must not precede validFrom.', $field));
            }
        }
    }

    public readonly Concept $credentialProfile;

    public function validateProfile(VocabularyScheme $scheme): void {
        $scheme->assertContains($this->credentialProfile, 'credentialProfile');
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
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
        if ($this->issuer !== null) {
            $data['issuer'] = $this->issuer->toArray();
        }
        if ($this->issuanceDate !== null) {
            $data['issuanceDate'] = $this->formatDate($this->issuanceDate);
        }
        if ($this->issued !== null) {
            $data['issued'] = $this->formatDate($this->issued);
        }
        if ($this->validUntil !== null) {
            $data['validUntil'] = $this->formatDate($this->validUntil);
        }
        return $data;
    }

    public function toJson(): string {
        $json = parent::toJson();
        (new CredentialDocumentValidator())->validateJson($json);

        return $json;
    }

    private function formatDate(DateTimeImmutable $date): string {
        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }
}
