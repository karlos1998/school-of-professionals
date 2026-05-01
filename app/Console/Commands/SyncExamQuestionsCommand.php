<?php

namespace App\Console\Commands;

use App\Services\ExamSync\ExamSyncCatalogService;
use App\Services\ExamSync\ExamSyncService;
use App\Services\ExamSync\SourceType;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use function Laravel\Prompts\multiselect;

#[Signature('exam:sync-questions')]
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
}
