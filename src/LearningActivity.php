<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;

final class LearningActivity extends Claim {

    public function __construct(
        string $id,
        public readonly LocalizedString $title,
        public readonly AwardingProcess $awardedBy,
        public readonly LearningActivitySpecification $specifiedBy,
        public readonly ?LocalizedString $description = null,
    ) {
        parent::__construct($id);
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => 'urn:epass:activity:' . $this->id,
            'type' => 'LearningActivity',
            'awardedBy' => $this->awardedBy->toArray(),
            'title' => $this->title->toArray(),
            'specifiedBy' => $this->specifiedBy->toArray(),
        ];
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }

        return $data;
    }
}
