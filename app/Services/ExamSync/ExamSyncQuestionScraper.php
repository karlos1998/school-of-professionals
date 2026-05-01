<?php

namespace App\Services\ExamSync;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ExamSyncQuestionScraper
{
    /**
     * @return list<array{
     *   key: string,
     *   position: int,
     *   content: string,
     *   answers: list<array{content: string, is_correct: bool}>,
     *   image_url: string|null
     * }>
     */
    public function scrapeQuestions(string $basePageUrl): array
    {
        $learnUrl = rtrim($basePageUrl, '/').'/nauka';

        $response = Http::timeout(config('exam_sync.timeout_seconds'))->get($learnUrl);
        $response->throw();

        return $this->parseQuestionsFromHtml($response->body(), $learnUrl);
    }

    /**
     * @return list<array{
     *   key: string,
     *   position: int,
     *   content: string,
     *   answers: list<array{content: string, is_correct: bool}>,
     *   image_url: string|null
     * }>
     */
    public function parseQuestionsFromHtml(string $html, string $pageUrl): array
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);

        $questionNodes = $xpath->query('//section[@id="questions"]/div[@id]');

        if (! $questionNodes instanceof \DOMNodeList) {
            return [];
        }

        $questions = [];

        foreach ($questionNodes as $questionNode) {
            if (! $questionNode instanceof \DOMElement) {
                continue;
            }

            $rawId = trim((string) $questionNode->getAttribute('id'));
            $position = (int) $rawId;

            if ($position <= 0) {
                continue;
            }

            $titleNode = $xpath->query('.//h3', $questionNode)?->item(0);

            if (! $titleNode instanceof \DOMNode) {
                continue;
            }

            $content = $this->normalizeQuestionContent($titleNode->textContent);
            if ($content === '') {
                continue;
            }

            $answers = [];
            $answerNodes = $xpath->query('.//div[.//span or .//h4]/*[self::span or self::h4]', $questionNode);

            if ($answerNodes instanceof \DOMNodeList) {
                foreach ($answerNodes as $answerNode) {
                    if (! $answerNode instanceof \DOMElement) {
                        continue;
                    }

                    $rawAnswer = trim($answerNode->textContent);
                    $answerContent = $this->stripAnswerPrefix($rawAnswer);

                    if ($answerContent === '') {
                        continue;
                    }

                    $answers[] = [
                        'content' => $answerContent,
                        'is_correct' => strtolower($answerNode->tagName) === 'h4',
                    ];
                }
            }

            if ($answers === []) {
                continue;
            }

            $imageUrl = null;
            $imageNode = $xpath->query('.//img[@src]', $questionNode)?->item(0);
            if ($imageNode instanceof \DOMElement) {
                $imageSrc = trim((string) $imageNode->getAttribute('src'));
                $imageUrl = $this->resolveImageUrl($imageSrc, $pageUrl);
            }

            $questions[] = [
                'key' => (string) $position,
                'position' => $position,
                'content' => $content,
                'answers' => $answers,
                'image_url' => $imageUrl,
            ];
        }

        return $questions;
    }

    private function normalizeQuestionContent(string $raw): string
    {
        $normalized = preg_replace('/^\s*\d+\.\s*/u', '', trim($raw)) ?? '';

        return trim($normalized);
    }

    private function stripAnswerPrefix(string $raw): string
    {
        $withoutPrefix = preg_replace('/^\s*[a-zA-Z]\)\s*/u', '', trim($raw)) ?? '';

        return trim($withoutPrefix);
    }

    private function resolveImageUrl(string $imageSrc, string $pageUrl): ?string
    {
        if ($imageSrc === '') {
            return null;
        }

        if (Str::startsWith($imageSrc, ['http://', 'https://'])) {
            return $imageSrc;
        }

        $parsedPage = parse_url($pageUrl);
        $base = ($parsedPage['scheme'] ?? 'https').'://'.($parsedPage['host'] ?? '');

        if (Str::startsWith($imageSrc, '/_next/image?')) {
            parse_str((string) parse_url($imageSrc, PHP_URL_QUERY), $query);
            $rawUrl = (string) ($query['url'] ?? '');

            if ($rawUrl !== '') {
                if (Str::startsWith($rawUrl, ['http://', 'https://'])) {
                    return $rawUrl;
                }

                return $base.'/'.ltrim($rawUrl, '/');
            }
        }

        return $base.'/'.ltrim($imageSrc, '/');
    }
}
