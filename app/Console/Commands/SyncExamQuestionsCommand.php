<?php

namespace App\Console\Commands;

use App\Jobs\SyncExamCatalogItemJob;
use App\Services\ExamSync\ExamSyncCatalogService;
use App\Services\ExamSync\ExamSyncService;
use App\Services\ExamSync\SourceType;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

use function Laravel\Prompts\multiselect;

#[Signature('exam:sync-questions {--async : Uruchamia synchronizacje asynchronicznie w kolejkach (batch)} {--source=* : Ogranicza zrodla (wit,udt)} {--no-interact : Alias dla trybu bez interakcji}')]
#[Description('Synchronizuje pytania egzaminacyjne z testy-wit.pl oraz testy-udt.pl')]
class SyncExamQuestionsCommand extends Command
{
    public function __construct(
        private readonly ExamSyncCatalogService $catalogService,
        private readonly ExamSyncService $syncService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $selectedSources = $this->resolveSources();
        $async = (bool) $this->option('async');

        foreach ($selectedSources as $sourceValue) {
            $source = SourceType::from($sourceValue);
            $catalog = $this->catalogService->getCatalog($source);

            $options = [];
            $itemMap = [];

            foreach ($catalog as $item) {
                foreach ($item['classes'] as $classData) {
                    $key = $item['slug'].'::'.$classData['slug'];
                    $options[$key] = $item['title'].' → '.$classData['label'];
                    $itemMap[$key] = [
                        'title' => $item['title'],
                        'class' => $classData,
                    ];
                }
            }

            if ($options === []) {
                $this->warn(sprintf('Brak pozycji do synchronizacji dla źródła: %s', $source->label()));

                continue;
            }

            $payload = $this->resolvePayload($source, $options, $itemMap);

            if ($payload === []) {
                $this->warn(sprintf('Nie wybrano pozycji do synchronizacji dla %s.', $source->label()));

                continue;
            }

            if ($async) {
                $this->dispatchSourceBatch($source, $payload);

                continue;
            }

            $this->info(sprintf('Synchronizacja %d pozycji z %s...', count($payload), $source->label()));
            $this->output->progressStart(count($payload));
            foreach ($payload as $entry) {
                $this->syncService->sync($source, [$entry]);
                $this->output->progressAdvance();
            }
            $this->output->progressFinish();
            $this->info(sprintf('Zakończono synchronizację źródła: %s', $source->label()));
        }

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function resolveSources(): array
    {
        /** @var list<string> $requested */
        $requested = array_values(array_filter((array) $this->option('source')));

        if ($requested !== []) {
            return array_map(
                static fn (string $source): string => SourceType::from(strtolower($source))->value,
                $requested,
            );
        }

        if ($this->isNonInteractiveMode()) {
            return [SourceType::Wit->value, SourceType::Udt->value];
        }

        $selectedSources = multiselect(
            label: 'Wybierz źródła do synchronizacji',
            options: [
                SourceType::Wit->value => SourceType::Wit->label(),
                SourceType::Udt->value => SourceType::Udt->label(),
            ],
            default: [SourceType::Wit->value, SourceType::Udt->value],
            scroll: 5,
            required: true,
        );

        return array_values(array_filter(
            $selectedSources,
            static fn (string|int $value): bool => is_string($value),
        ));
    }

    /**
     * @param  array<string, string>  $options
     * @param  array<string, array{title: string, class: array{label: string, slug: string, url: string}}>  $itemMap
     * @return list<array{title: string, class: array{label: string, slug: string, url: string}}>
     */
    private function resolvePayload(SourceType $source, array $options, array $itemMap): array
    {
        if ($this->isNonInteractiveMode()) {
            return array_values($itemMap);
        }

        $selectedItems = multiselect(
            label: sprintf('Wybierz pozycje do pobrania (%s)', $source->label()),
            options: $options,
            default: array_keys($options),
            scroll: 10,
            required: true,
        );

        $payload = [];
        foreach ($selectedItems as $itemKey) {
            if (isset($itemMap[$itemKey])) {
                $payload[] = $itemMap[$itemKey];
            }
        }

        return $payload;
    }

    /**
     * @param  list<array{title: string, class: array{label: string, slug: string, url: string}}>  $payload
     */
    private function dispatchSourceBatch(SourceType $source, array $payload): void
    {
        $jobs = array_map(
            fn (array $item): SyncExamCatalogItemJob => new SyncExamCatalogItemJob($source->value, $item),
            $payload,
        );

        $batch = Bus::batch([$jobs])
            ->name(sprintf('exam-sync:%s:%s', $source->value, now()->format('Ymd-His')))
            ->allowFailures()
            ->dispatch();

        $this->info(sprintf(
            'Wysłano batch dla %s: %d jobów, ID: %s',
            $source->label(),
            count($jobs),
            $batch->id,
        ));
    }

    private function isNonInteractiveMode(): bool
    {
        return ! $this->input->isInteractive() || (bool) $this->option('no-interact');
    }
}
