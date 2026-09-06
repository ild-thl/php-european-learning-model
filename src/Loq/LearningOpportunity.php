<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Loq;

use IsyThl\EuropeanDigitalCredentials\Concept;
use IsyThl\EuropeanDigitalCredentials\ConceptAssertions;
use IsyThl\EuropeanDigitalCredentials\Exception\InvalidCredentialException;
use IsyThl\EuropeanDigitalCredentials\ElmVocabularySchemes;
use IsyThl\EuropeanDigitalCredentials\LearningAchievementSpecification;
use IsyThl\EuropeanDigitalCredentials\LocalizedString;
use IsyThl\EuropeanDigitalCredentials\Location;
use IsyThl\EuropeanDigitalCredentials\Note;
use IsyThl\EuropeanDigitalCredentials\Organisation;
use IsyThl\EuropeanDigitalCredentials\WebResource;
use IsyThl\EuropeanLearningModel\Core\PeriodOfTime;
use IsyThl\EuropeanLearningModel\Core\PriceDetail;

final class LearningOpportunity {

    /** @param list<Organisation> $providedBy */
    public function __construct(
        public readonly string $id,
        public readonly LocalizedString $title,
        public readonly Concept $defaultLanguage,
        public readonly WebResource $homepage,
        public readonly array $providedBy,
        public readonly LearningAchievementSpecification|QualificationReference $learningAchievementSpecification,
        public readonly ?Organisation $publisher = null,
        public readonly ?PeriodOfTime $temporal = null,
        public readonly ?Concept $learningSchedule = null,
        public readonly ?Location $location = null,
        public readonly ?PriceDetail $priceDetail = null,
        public readonly ?string $duration = null,
        public readonly ?Concept $mode = null,
        /** @var list<WebResource> */
        public readonly array $supplementaryDocuments = [],
        /** @var list<Note> */
        public readonly array $additionalNotes = [],
        public readonly ?Note $admissionProcedure = null,
        public readonly ?Note $scheduleInformation = null,
        public readonly ?Concept $status = null,
    ) {
        self::assertUri($id);
        if ($providedBy === []) {
            throw new InvalidCredentialException('A learning opportunity requires at least one provider.');
        }
        if (array_filter($providedBy, static fn ($provider): bool => !$provider instanceof Organisation) !== []) {
            throw new InvalidCredentialException('Learning opportunity providers must be organisations.');
        }
        if (
            array_filter(
                $supplementaryDocuments,
                static fn ($document): bool => !$document instanceof WebResource,
            ) !== []
        ) {
            throw new InvalidCredentialException('Supplementary documents must be WebResource objects.');
        }
        if (array_filter($additionalNotes, static fn ($note): bool => !$note instanceof Note) !== []) {
            throw new InvalidCredentialException('Additional notes must be Note objects.');
        }
        if ($status !== null) {
            ConceptAssertions::assertScheme($status, ElmVocabularySchemes::ACCREDITATION_STATUS, 'status');
        }
        if (
            $duration !== null
            && preg_match(
                '/^P(?:\d+Y)?(?:\d+M)?(?:\d+D)?(?:T(?=\d)(?:\d+H)?(?:\d+M)?(?:\d+(?:\.\d+)?S)?)?$/',
                $duration,
            ) !== 1
        ) {
            throw new InvalidCredentialException('Learning opportunity duration must use ISO 8601 format.');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array {
        $data = [
            'id' => $this->id,
            'type' => 'LearningOpportunity',
            'title' => $this->title->toArray(),
            'defaultLanguage' => $this->defaultLanguage->toArray(),
            'homepage' => $this->homepage->toArray(),
            'providedBy' => array_map(
                static fn (Organisation $provider): array => $provider->toArray(),
                $this->providedBy,
            ),
            'learningAchievementSpecification' => $this->learningAchievementSpecification->toArray(),
        ];
        if ($this->publisher !== null) {
            $data['publisher'] = $this->publisher->toArray();
        }
        if ($this->temporal !== null) {
            $data['temporal'] = $this->temporal->toArray();
        }
        if ($this->learningSchedule !== null) {
            $data['learningSchedule'] = $this->learningSchedule->toArray();
        }
        if ($this->location !== null) {
            $data['location'] = $this->location->toArray();
        }
        if ($this->priceDetail !== null) {
            $data['priceDetail'] = $this->priceDetail->toArray();
        }
        if ($this->duration !== null) {
            $data['duration'] = $this->duration;
        }
        if ($this->mode !== null) {
            $data['mode'] = $this->mode->toArray();
        }
        if ($this->supplementaryDocuments !== []) {
            $data['supplementaryDocument'] = array_map(
                static fn (WebResource $document): array => $document->toArray(),
                $this->supplementaryDocuments,
            );
        }
        if ($this->additionalNotes !== []) {
            $data['additionalNote'] = array_map(
                static fn (Note $note): array => $note->toArray(),
                $this->additionalNotes,
            );
        }
        if ($this->admissionProcedure !== null) {
            $data['admissionProcedure'] = $this->admissionProcedure->toArray();
        }
        if ($this->scheduleInformation !== null) {
            $data['scheduleInformation'] = $this->scheduleInformation->toArray();
        }
        if ($this->status !== null) {
            $data['status'] = $this->status->toArray();
        }
        return $data;
    }

    private static function assertUri(string $id): void {
        if ($id === '' || preg_match('/^[a-z][a-z0-9+.-]*:\S+$/i', $id) !== 1) {
            throw new InvalidCredentialException('Learning opportunity identifiers must be persistent URIs.');
        }
    }
}
