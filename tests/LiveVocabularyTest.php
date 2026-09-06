<?php

declare(strict_types=1);

namespace IsyThl\EuropeanLearningModel\Tests;

use IsyThl\EuropeanLearningModel\ElmVocabularySchemes;
use IsyThl\EuropeanLearningModel\RdfVocabularyProvider;
use IsyThl\EuropeanLearningModel\Vocabulary\EscoVocabularySearchProvider;
use IsyThl\EuropeanLearningModel\VocabularyResourceFetcher;
use IsyThl\EuropeanLearningModel\Vocabulary\VocabularySearchResourceFetcher;
use PHPUnit\Framework\TestCase;

final class LiveVocabularyTest extends TestCase {

    public function testEveryRegisteredSchemeCanBeRetrievedFromItsAuthoritativeEndpoint(): void {
        if (getenv('ELM_LIVE_VOCABULARY_TESTS') !== '1') {
            self::markTestSkipped('Set ELM_LIVE_VOCABULARY_TESTS=1 to run live vocabulary qualification.');
        }
        if (!function_exists('curl_init')) {
            self::markTestSkipped('The live vocabulary test requires ext-curl.');
        }

        $fetcher = new class implements VocabularyResourceFetcher {
            public function fetch(string $resource): string {
                $handle = curl_init($resource);
                if ($handle === false) {
                    throw new \RuntimeException('Unable to initialize the live vocabulary request.');
                }
                curl_setopt_array($handle, [
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTPHEADER => ['Accept: application/rdf+xml, application/xml;q=0.9'],
                    CURLOPT_USERAGENT => 'isy-thl/european-digital-credentials live qualification',
                ]);
                $body = curl_exec($handle);
                $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
                curl_close($handle);
                if (!is_string($body) || $status >= 400) {
                    throw new \RuntimeException(sprintf('Vocabulary endpoint returned HTTP %d.', $status));
                }

                return $body;
            }
        };
        $escoFetcher = new class implements VocabularySearchResourceFetcher {
            public function search(
                string $schemeId,
                string $query,
                string $language,
                int $limit,
                ?string $cursor,
                array $fallbackLanguages = [],
            ): string {
                $url = $cursor ?? 'https://ec.europa.eu/esco/api/search';
                if ($cursor === null) {
                    $type = $schemeId === ElmVocabularySchemes::OCCUPATIONS ? 'occupation' : 'skill';
                    $url .= '?' . http_build_query([
                        'text' => $query,
                        'language' => $language,
                        'type' => $type,
                        'isInScheme' => $schemeId,
                        'offset' => 0,
                        'limit' => $limit,
                        'full' => 'false',
                    ]);
                }
                $handle = curl_init($url);
                if ($handle === false) {
                    throw new \RuntimeException('Unable to initialize the live ESCO request.');
                }
                curl_setopt_array($handle, [
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTPHEADER => ['Accept: application/json'],
                    CURLOPT_USERAGENT => 'isy-thl/european-digital-credentials live qualification',
                ]);
                $body = curl_exec($handle);
                $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
                curl_close($handle);
                if (!is_string($body) || $status >= 400) {
                    throw new \RuntimeException(sprintf('ESCO endpoint returned HTTP %d.', $status));
                }

                return $body;
            }
        };
        $escoProvider = new EscoVocabularySearchProvider($escoFetcher);
        $provider = new RdfVocabularyProvider($fetcher, 15_000_000, 100);
        $failures = [];

        foreach (ElmVocabularySchemes::all() as $name => $schemeId) {
            try {
                if (
                    $schemeId === ElmVocabularySchemes::ESCO_SKILLS
                    || $schemeId === ElmVocabularySchemes::OCCUPATIONS
                ) {
                    $query = $schemeId === ElmVocabularySchemes::OCCUPATIONS ? 'manager' : 'engineer';
                    $page = $escoProvider->searchConcepts($schemeId, $query, 'en', 5);
                    if ($page->concepts === []) {
                        $failures[$name] = 'ESCO search returned no concepts';
                    }
                    continue;
                }
                if (ElmVocabularySchemes::requiresSearch($schemeId)) {
                    $document = $fetcher->fetch($schemeId);
                    $xml = simplexml_load_string($document, \SimpleXMLElement::class, LIBXML_NONET);
                    if ($xml === false) {
                        $failures[$name] = 'search endpoint did not return parseable RDF/XML';
                    }
                    continue;
                }
                $scheme = $provider->getScheme($schemeId);
                if ($scheme === null || $scheme->getConcepts() === []) {
                    $failures[$name] = 'empty parsed scheme';
                }
            } catch (\Throwable $exception) {
                $failures[$name] = $exception->getMessage();
            }
        }

        self::assertSame([], $failures, json_encode($failures, JSON_THROW_ON_ERROR));
    }
}
