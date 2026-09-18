<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests\Core;

use IsyThl\EuropeanLearningModel\Core\Note;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;
use IsyThl\EuropeanLearningModel\Tests\Support\SerializationAssertions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NoteTest extends TestCase {
    use SerializationAssertions;

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function validTargets(): array {
        return [
            'default' => [
                [
                    'id' => 'urn:epass:note:note-1',
                    'type' => 'Note',
                    'noteLiteral' => ['en' => ['Additional information'], 'de' => ['Zusätzliche Information']],
                ],
            ],
            'with subject' => [
                [
                    'id' => 'urn:epass:note:note-1',
                    'type' => 'Note',
                    'noteLiteral' => ['en' => ['Additional information'], 'de' => ['Zusätzliche Information']],
                    'subject' => ['en' => ['Prerequisites'], 'de' => ['Zugangsvorraussetzungen']],
                ],
            ]
        ];
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function invalidTargets(): array {
        return [
            'missing noteLiteral' => [
                [
                    'id' => 'urn:epass:note:note-1',
                    'type' => 'Note',
                ],
                \TypeError::class
            ],
        ];
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughArrayDeserialization(array $expected): void {
        $this->assertArrayRoundTrip($expected, [Note::class, 'fromArray']);
    }

    /** @param array<string, mixed> $expected */
    #[DataProvider('validTargets')]
    public function testEachValidTargetRoundTripsThroughJsonEncoding(array $expected): void {
        $this->assertJsonRoundTrip(
            $expected,
            [Note::class, 'fromArray'],
            static fn (object $object): array => $object->toArray(),
        );
    }

    /** @param array<string, mixed> $invalid */
    #[DataProvider('invalidTargets')]
    public function testInvalidTargetsAreRejected(array $invalid, string $expectedException): void {
        $this->expectException($expectedException);

        Note::fromArray($invalid);
    }
}
