<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class Issuer extends Organisation
{
    public function __construct(
        string $id,
        Location $location,
        LocalizedString $legalName,
        LegalIdentifier $registration,
        ?ContactPoint $contactPoint = null,
        ?MediaObject $logo = null,
    ) {
        parent::__construct($id, $location, $legalName, $registration, $contactPoint, $logo);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['id'] = $this->id;
        return $data;
    }
}