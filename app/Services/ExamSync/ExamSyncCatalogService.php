<?php

namespace App\Services\ExamSync;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ExamSyncCatalogService
{
    /**
     * @return list<array{
     *   title: string,
     *   slug: string,
     *   classes: list<array{label: string, slug: string, url: string}>
     * }>
     */
    public function getCatalog(SourceType $source): array
    {
        $response = Http::timeout(config('exam_sync.timeout_seconds'))
            ->get($source->baseUrl());

        $response->throw();

        $html = $response->body();

        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($dom);

        $sections = $xpath->query('//main//section[.//h2 and .//a[@href]]');

        if (! $sections instanceof \DOMNodeList) {
            return [];
        }

        $items = [];

        foreach ($sections as $section) {
            $titleNode = $xpath->query('.//h2', $section)?->item(0);

            if (! $titleNode instanceof \DOMNode) {
                continue;
            }

            $title = trim($titleNode->textContent);
            $links = $xpath->query('.//a[@href]', $section);

            if (! $links instanceof \DOMNodeList || $links->count() === 0) {
                continue;
            }

            $classes = [];
            foreach ($links as $link) {
                if (! $link instanceof \DOMElement) {
                    continue;
                }

                $href = trim((string) $link->getAttribute('href'));

                if ($href === '') {
                    continue;
                }

                $path = parse_url($href, PHP_URL_PATH) ?: '';
                $segments = array_values(array_filter(explode('/', $path)));
                $slug = end($segments);

                if (! is_string($slug) || $slug === '') {
                    continue;
                }

                $label = trim($link->textContent);
                $classes[] = [
                    'label' => $label,
                    'slug' => $slug,
                    'url' => rtrim($source->baseUrl(), '/').'/'.ltrim($slug, '/'),
                ];
            }

            if ($classes === []) {
                continue;
            }

            $items[] = [
                'title' => $title,
                'slug' => Str::slug($title),
                'classes' => $classes,
            ];
        }

        return $items;
    }
}
