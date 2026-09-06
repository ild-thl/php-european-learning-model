<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

final class LearningActivitySpecification extends LearningAchievementSpecification {

    protected static function typeScheme(): string {
        return ElmVocabularySchemes::LEARNING_ACTIVITY;
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = parent::toArray();
        $data['id'] = 'urn:epass:learningActivitySpec:' . $this->id;
        $data['type'] = 'LearningActivitySpecification';

        return $data;
    }
}
