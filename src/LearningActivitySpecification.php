<?php

declare(strict_types=1);

namespace IsyThl\EuropeanDigitalCredentials;

final class LearningActivitySpecification extends LearningAchievementSpecification {

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = parent::toArray();
        $data['id'] = 'urn:epass:learningActivitySpec:' . $this->id;
        $data['type'] = 'LearningActivitySpecification';

        return $data;
    }
}
