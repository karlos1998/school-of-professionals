<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Exam;
use App\Models\ExamAuthority;
use App\Models\ExamCategory;
use App\Models\ExamClass;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        Exam::query()->delete();

        /** @var array<string, ExamAuthority> $authorities */
        $authorities = [];
        foreach (['UDT', 'WIT'] as $name) {
            $authorities[$name] = ExamAuthority::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }

        /** @var array<string, ExamCategory> $categories */
        $categories = [];
        foreach ([
            'Dzwigi budowlane',
            'Podesty ruchome',
            'Wozki jezdniowe',
            'Hakowy i sygnalista',
            'Maszyny drogowe',
            'Transport bliski',
        ] as $name) {
            $categories[$name] = ExamCategory::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }

        /** @var array<string, ExamClass> $classes */
        $classes = [];
        foreach (['I', 'II', 'III'] as $name) {
            $classes[$name] = ExamClass::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }

        $examDefinitions = [
            // UDT: test z klasami I + II
            [
                'authority' => 'UDT',
                'category' => 'Dzwigi budowlane',
                'class' => 'I',
                'name' => 'Dzwigi budowlane',
                'description' => 'Pytania przygotowujace do egzaminu UDT z dzwigow budowlanych.',
            ],
            [
                'authority' => 'UDT',
                'category' => 'Dzwigi budowlane',
                'class' => 'II',
                'name' => 'Dzwigi budowlane',
                'description' => 'Pytania przygotowujace do egzaminu UDT z dzwigow budowlanych.',
            ],

            // UDT: test tylko z klasa I
            [
                'authority' => 'UDT',
                'category' => 'Podesty ruchome',
                'class' => 'I',
                'name' => 'Podesty ruchome',
                'description' => 'Pytania przygotowujace do egzaminu UDT z podestow ruchomych.',
            ],

            // UDT: test bez klas
            [
                'authority' => 'UDT',
                'category' => 'Wozki jezdniowe',
                'class' => null,
                'name' => 'Wozki jezdniowe',
                'description' => 'Pytania przygotowujace do egzaminu UDT z wozkow jezdniowych.',
            ],

            // WIT: testy bez klas
            [
                'authority' => 'WIT',
                'category' => 'Maszyny drogowe',
                'class' => null,
                'name' => 'Maszyny drogowe',
                'description' => 'Pytania techniczne do egzaminu WIT z obslugi maszyn drogowych.',
            ],
            [
                'authority' => 'WIT',
                'category' => 'Hakowy i sygnalista',
                'class' => null,
                'name' => 'Hakowy i sygnalista',
                'description' => 'Pytania praktyczne do egzaminu WIT dla hakowych i sygnalistow.',
            ],
            [
                'authority' => 'WIT',
                'category' => 'Transport bliski',
                'class' => null,
                'name' => 'Transport bliski',
                'description' => 'Pytania proceduralne do egzaminu WIT z zakresu transportu bliskiego.',
            ],
        ];

        foreach ($examDefinitions as $definition) {
            $slugParts = [
                $definition['authority'],
                $definition['category'],
                $definition['class'] ? "klasa-{$definition['class']}" : 'bez-klasy',
            ];

            $exam = Exam::query()->create([
                'exam_authority_id' => $authorities[$definition['authority']]->id,
                'exam_category_id' => $categories[$definition['category']]->id,
                'exam_class_id' => $definition['class'] ? $classes[$definition['class']]->id : null,
                'name' => $definition['name'],
                'slug' => Str::slug(implode('-', $slugParts)),
                'description' => $definition['description'],
            ]);

            $this->seedQuestionsForExam($exam);
        }
    }

    private function seedQuestionsForExam(Exam $exam): void
    {
        $topics = [
            'BHP i przygotowanie stanowiska',
            'Warunki bezpiecznej eksploatacji',
            'Dokumentacja i uprawnienia',
            'Czynnosci przed rozpoczeciem pracy',
            'Sytuacje awaryjne',
            'Komunikacja i sygnalizacja',
            'Warunki atmosferyczne',
            'Kontrola techniczna',
            'Wylaczenie urzadzenia z ruchu',
            'Obowiazki operatora',
        ];

        for ($index = 1; $index <= 30; $index++) {
            $topic = $topics[($index - 1) % count($topics)];

            $question = Question::query()->create([
                'exam_id' => $exam->id,
                'position' => $index,
                'content' => sprintf(
                    '(%s) Pytanie %d. Jak postapic w obszarze: %s?',
                    $exam->name,
                    $index,
                    $topic,
                ),
                'explanation' => 'Poprawna odpowiedz wynika z aktualnych przepisow i instrukcji producenta urzadzenia.',
            ]);

            $correctOption = ($index % 4) + 1;

            for ($answerIndex = 1; $answerIndex <= 4; $answerIndex++) {
                Answer::query()->create([
                    'question_id' => $question->id,
                    'content' => $this->answerContentFor($topic, $answerIndex),
                    'is_correct' => $answerIndex === $correctOption,
                ]);
            }
        }
    }

    private function answerContentFor(string $topic, int $variant): string
    {
        return match ($variant) {
            1 => "Wykonac pelna kontrole zgodnie z procedura dla: {$topic}.",
            2 => "Kontynuowac prace bez dodatkowej kontroli w obszarze: {$topic}.",
            3 => "Zglosic temat {$topic} po zakonczeniu zmiany, bez zatrzymania pracy.",
            default => "Skonsultowac {$topic} z przelozonym i zastosowac instrukcje stanowiskowa.",
        };
    }
}
