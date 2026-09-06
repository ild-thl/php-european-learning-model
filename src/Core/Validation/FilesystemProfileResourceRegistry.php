<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core\Validation;

use InvalidArgumentException;
use RuntimeException;

final class FilesystemProfileResourceRegistry implements ProfileResourceRegistryInterface {

    public function __construct(private readonly string $directory) {
        if ($directory === '') {
            throw new InvalidArgumentException('A profile resource directory is required.');
        }
    }

    public function get(string $profileResource): string {
        if ($profileResource === '' || basename($profileResource) !== $profileResource) {
            throw new InvalidArgumentException('Profile resources must be local file names.');
        }

        $path = rtrim($this->directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $profileResource;
        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException(sprintf('Profile resource "%s" is not available.', $profileResource));
        }
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Profile resource "%s" could not be read.', $profileResource));
        }

        return $contents;
    }
}
