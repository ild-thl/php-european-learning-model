<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Core;

use DateTimeImmutable;
use IsyThl\EuropeanLearningModel\Core\Person;
use IsyThl\EuropeanLearningModel\Core\Organisation;
use IsyThl\EuropeanLearningModel\Edc\LearningAssessment;
use IsyThl\EuropeanLearningModel\Core\DateTimeFormatter;
use IsyThl\EuropeanLearningModel\Exception\InvalidCredentialException;

/**
 * Class AwardingProcess
 *
 * @see https://europa.eu/europass/elm-browser/documentation/rdf/ap/edc/documentation/edc-generic-no-cv_en.html#awarding-process
 */
final class AwardingProcess extends Entity {

    public function __construct(
        string $id,
        public readonly ?Concept $educationalSystemNote = null,
        public readonly ?DateTimeImmutable $awardingDate = null,
        public readonly ?LocalizedString $description = null,
        public readonly ?Location $location = null,
        /** @var list<Note>|null */
        public readonly ?array $additionalNote = null,
        // TODO: awards: Claim Node
        /** @var list<LearningAssessment>|null */
        public readonly ?array $used = null,
        // TODO: hasAgreement: RecognitionAgreement
        // TODO: recogises: Awarding Process
        /** @var list<AwardingProcess>|null */
        public readonly ?array $recognises = null,
        /** @var list<Organisation|Person>|null */
        public readonly ?array $awardingBody = null,
        public readonly ?int $order = null,
    ) {
        parent::__construct($id);
        if ($id === '') {
            throw new InvalidCredentialException('An awarding process requires an identifier.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'AwardingProcess',
        ];
        if ($this->educationalSystemNote !== null) {
            $data['educationalSystemNote'] = $this->educationalSystemNote->toArray();
        }
        if ($this->awardingDate !== null) {
            $data['awardingDate'] = DateTimeFormatter::format($this->awardingDate);
        }
        if ($this->description !== null) {
            $data['description'] = $this->description->toArray();
        }
        if ($this->location !== null) {
            $data['location'] = $this->location->toArray();
        }
        if ($this->additionalNote !== null) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNote,
            );
        }
        if ($this->used !== null) {
            $data['used'] = array_map(
                static fn (LearningAssessment $assessment): array => $assessment->toArray(),
                $this->used,
            );
        }
        if ($this->recognises !== null) {
            $data['recognises'] = array_map(
                static fn (AwardingProcess $process): array => $process->toArray(),
                $this->recognises,
            );
        }
        if ($this->awardingBody !== null) {
            $data['awardingBody'] = array_map(
                static fn (Organisation|Person $body): array => $body->toArray(),
                $this->awardingBody,
            );
        }
        if ($this->order !== null) {
            $data['order'] = $this->order;
        }
        return $data;
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self {
        if (!isset($data['type']) || $data['type'] !== 'AwardingProcess') {
            throw new InvalidCredentialException('Data is not an AwardingProcess.');
        }

        return new self(
            $data['id'],
            isset($data['educationalSystemNote']) ? Concept::fromArray($data['educationalSystemNote']) : null,
            isset($data['awardingDate']) ? new DateTimeImmutable($data['awardingDate']) : null,
            isset($data['description']) ? LocalizedString::fromArray($data['description']) : null,
            isset($data['location']) ? Location::fromArray($data['location']) : null,
            isset($data['additionalNote']) ? array_map(
                static fn (array $note): Note => Note::fromArray($note),
                $data['additionalNote'],
            ) : null,
            isset($data['used']) ? array_map(
                static fn (array $assessment): LearningAssessment => LearningAssessment::fromArray($assessment),
                $data['used'],
            ) : null,
            isset($data['recognises']) ? array_map(
                static fn (array $process): AwardingProcess => AwardingProcess::fromArray($process),
                $data['recognises'],
            ) : null,
            isset($data['awardingBody']) ? array_map(
                static fn (array $body): Organisation|Person =>
                    $body['type'] === 'Organisation' ? Organisation::fromArray($body)
                    : Person::fromArray($body),
                $data['awardingBody'],
            ) : null,
            isset($data['order']) ? $data['order'] : null,
        );
    }
}
