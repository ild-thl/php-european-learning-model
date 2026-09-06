<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core\Validation;

interface StandardsValidatorInterface {

    public function validate(string $document, string $profileResource): StandardsValidationResult;
}
