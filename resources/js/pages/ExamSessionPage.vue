<script setup lang="ts">
import { computed, onMounted } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useExamRunnerStore } from '@/stores/examRunnerStore';
import type { ExamConfig, ExamMode, ExamQuestion, ExamSessionPayload } from '@/types/exam-flow';

interface Props {
    authority: {
        name: string;
        slug: string;
    };
    test: {
        name: string;
        slug: string;
    };
    selectedClass: {
        name: string;
        slug: string;
    } | null;
    selectedMode: ExamMode;
    selectedModeLabel: string;
    modeSelectionUrl: string;
    backUrl: string;
    examConfig: ExamConfig;
    exam: ExamSessionPayload;
}

const props = defineProps<Props>();
const store = useExamRunnerStore();

onMounted(() => {
    const key = [
        props.authority.slug,
        props.test.slug,
        props.selectedClass?.slug ?? 'no-class',
        props.selectedMode,
    ].join(':');

    store.loadExam(props.exam, key, props.examConfig);
    store.activateMode(props.selectedMode);
});

const currentQuestionNumber = computed(() => store.session.questionPointer + 1);
const isStudyMode = computed(() => store.session.mode === 'study');
const isExamMode = computed(() => store.session.mode === 'exam');
const isInstantMode = computed(() => ['sequential', 'random'].includes(store.session.mode));
const shouldShowExamSummary = computed(() => isExamMode.value && store.isSessionFinished);
const incorrectAnswersCount = computed(() => Math.max(store.totalQuestions - store.correctAnswersCount, 0));
const isExamPassed = computed(() => store.correctAnswersCount >= props.examConfig.passingThreshold);
const canGoNext = computed(() => store.canGoToNextQuestion());

const chooseAnswer = (question: ExamQuestion, answerId: number | null): void => {
    if (answerId === null) {
        return;
    }

    store.answerQuestion(question.id, answerId);
};

const restartSession = (): void => {
    store.startSession(props.selectedMode);
};

const answerColor = (question: ExamQuestion, answerId: number): 'default' | 'success' | 'error' | 'primary' => {
    const selectedAnswerId = store.selectedAnswerId(question.id);
    const correctAnswerId = store.correctAnswerId(question.id);

    if (store.session.mode === 'study') {
        return answerId === correctAnswerId ? 'success' : 'default';
    }

    if (store.session.mode === 'exam' && !store.isSessionFinished) {
        return answerId === selectedAnswerId ? 'primary' : 'default';
    }

    if (!store.isQuestionChecked(question.id)) {
        return answerId === selectedAnswerId ? 'primary' : 'default';
    }

    if (answerId === correctAnswerId) {
        return 'success';
    }

    if (answerId === selectedAnswerId && selectedAnswerId !== correctAnswerId) {
        return 'error';
    }

    return 'default';
};

const questionStatusText = (question: ExamQuestion): string => {
    const selectedAnswerId = store.selectedAnswerId(question.id);
    const correctAnswerId = store.correctAnswerId(question.id);

    if (!selectedAnswerId || !store.isQuestionChecked(question.id)) {
        return 'Wybierz odpowiedz';
    }

    return selectedAnswerId === correctAnswerId ? 'Poprawna odpowiedz' : 'Niepoprawna odpowiedz';
};

const questionResult = (question: ExamQuestion): 'success' | 'error' => {
    const selectedAnswerId = store.selectedAnswerId(question.id);
    const correctAnswerId = store.correctAnswerId(question.id);

    return selectedAnswerId === correctAnswerId ? 'success' : 'error';
};
</script>

<template>
    <MainLayout>
        <v-row justify="center">
            <v-col cols="12" xl="10">
                <v-card class="panel-card" elevation="8" rounded="xl">
                    <v-card-title class="d-flex justify-space-between align-start ga-3 flex-wrap">
                        <div>
                            <p class="text-caption text-medium-emphasis">{{ authority.name }} / {{ test.name }}</p>
                            <h1 class="text-h5 font-weight-bold">
                                {{ exam.name }}
                                <span v-if="selectedClass">(Klasa {{ selectedClass.name }})</span>
                            </h1>
                        </div>
                        <div class="d-flex ga-2 flex-wrap justify-end">
                            <v-chip color="primary" variant="tonal">{{ selectedModeLabel }}</v-chip>
                            <v-btn :href="modeSelectionUrl" prepend-icon="mdi-arrow-left" variant="outlined">Zmien tryb</v-btn>
                        </div>
                    </v-card-title>

                    <v-card-text>
                        <div class="d-flex flex-wrap ga-3 mb-6">
                            <v-chip color="info" variant="tonal">Pytania: {{ store.totalQuestions }}</v-chip>
                            <v-chip color="success" variant="tonal">Poprawne: {{ store.correctAnswersCount }}</v-chip>
                            <v-btn color="warning" variant="tonal" prepend-icon="mdi-refresh" @click="restartSession">
                                Zacznij od poczatku
                            </v-btn>
                            <v-btn :href="backUrl" variant="text" prepend-icon="mdi-format-list-bulleted">Lista testow</v-btn>
                        </div>

                        <template v-if="isStudyMode">
                            <v-expansion-panels variant="accordion">
                                <v-expansion-panel v-for="question in store.orderedQuestions" :key="question.id" elevation="0">
                                    <v-expansion-panel-title>Pytanie {{ question.position }}</v-expansion-panel-title>
                                    <v-expansion-panel-text>
                                        <p class="font-weight-medium mb-4">{{ question.content }}</p>
                                        <v-list density="compact">
                                            <v-list-item v-for="answer in question.answers" :key="answer.id" :base-color="answerColor(question, answer.id)">
                                                <template #prepend>
                                                    <v-icon
                                                        :color="answer.isCorrect ? 'success' : 'default'"
                                                        :icon="answer.isCorrect ? 'mdi-check-circle' : 'mdi-circle-outline'"
                                                    />
                                                </template>
                                                <v-list-item-title>{{ answer.content }}</v-list-item-title>
                                            </v-list-item>
                                        </v-list>
                                    </v-expansion-panel-text>
                                </v-expansion-panel>
                            </v-expansion-panels>
                        </template>

                        <template v-else-if="shouldShowExamSummary">
                            <v-alert :color="isExamPassed ? 'success' : 'error'" variant="tonal" class="mb-4">
                                Wynik koncowy: {{ store.correctAnswersCount }} / {{ store.totalQuestions }}
                                <br>
                                Poprawne: {{ store.correctAnswersCount }}, bledne: {{ incorrectAnswersCount }}
                                <br>
                                Status: {{ isExamPassed ? 'ZDANE' : 'NIEZDANE' }} (prog: {{ props.examConfig.passingThreshold }})
                            </v-alert>

                            <v-card
                                v-for="question in store.orderedQuestions"
                                :key="question.id"
                                class="summary-card mb-4"
                                :color="questionResult(question)"
                                variant="tonal"
                            >
                                <v-card-title class="d-flex justify-space-between align-center ga-2">
                                    <span>Pytanie {{ question.position }}</span>
                                    <v-chip :color="questionResult(question)" size="small" variant="flat">
                                        {{ questionResult(question) === 'success' ? 'Poprawnie' : 'Blednie' }}
                                    </v-chip>
                                </v-card-title>
                                <v-card-text>
                                    <p class="font-weight-medium mb-3">{{ question.content }}</p>

                                    <v-list density="compact" class="bg-transparent">
                                        <v-list-item
                                            v-for="answer in question.answers"
                                            :key="answer.id"
                                            :base-color="answerColor(question, answer.id)"
                                        >
                                            <template #prepend>
                                                <v-icon
                                                    :icon="answer.id === store.correctAnswerId(question.id) ? 'mdi-check-circle' : 'mdi-circle-outline'"
                                                    :color="answer.id === store.correctAnswerId(question.id) ? 'success' : 'default'"
                                                />
                                            </template>
                                            <v-list-item-title>{{ answer.content }}</v-list-item-title>
                                        </v-list-item>
                                    </v-list>
                                </v-card-text>
                            </v-card>
                        </template>

                        <template v-else-if="store.currentQuestion">
                            <div class="question-shell">
                                <div class="d-flex justify-space-between align-center mb-3 ga-2 flex-wrap">
                                    <strong>Pytanie {{ currentQuestionNumber }} / {{ store.totalQuestions }}</strong>
                                    <v-chip size="small" variant="outlined">
                                        Odpowiedziano: {{ store.answeredCount }}/{{ store.totalQuestions }}
                                    </v-chip>
                                </div>

                                <v-progress-linear :model-value="(store.answeredCount / store.totalQuestions) * 100" color="primary" height="10" rounded class="mb-5" />
                                <p class="text-h6 mb-5">{{ store.currentQuestion.content }}</p>

                                <v-radio-group :model-value="store.selectedAnswerId(store.currentQuestion.id)" @update:model-value="(value) => chooseAnswer(store.currentQuestion!, value)">
                                    <v-sheet
                                        v-for="answer in store.currentQuestion.answers"
                                        :key="answer.id"
                                        :class="['answer-item', answerColor(store.currentQuestion, answer.id)]"
                                        border
                                        rounded="lg"
                                        @click="chooseAnswer(store.currentQuestion!, answer.id)"
                                    >
                                        <v-radio :label="answer.content" :value="answer.id" color="primary" />
                                    </v-sheet>
                                </v-radio-group>

                                <v-alert
                                    v-if="isInstantMode"
                                    :color="store.selectedAnswerId(store.currentQuestion.id) === store.correctAnswerId(store.currentQuestion.id) ? 'success' : 'error'"
                                    variant="tonal"
                                    class="mt-4"
                                >
                                    {{ questionStatusText(store.currentQuestion) }}
                                </v-alert>

                                <div class="d-flex flex-wrap ga-3 mt-6 nav-actions">
                                    <v-btn prepend-icon="mdi-chevron-left" variant="outlined" @click="store.goToPreviousQuestion()">Poprzednie</v-btn>
                                    <v-btn color="primary" append-icon="mdi-chevron-right" :disabled="!canGoNext" @click="store.goToNextQuestion()">
                                        Nastepne
                                    </v-btn>
                                </div>
                            </div>
                        </template>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </MainLayout>
</template>

<style scoped>
.panel-card {
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.98), rgba(250, 246, 235, 0.9));
    border: 1px solid rgba(220, 176, 69, 0.5);
    box-shadow:
        0 14px 36px rgba(16, 28, 44, 0.14),
        inset 0 0 0 1px rgba(255, 255, 255, 0.84);
}

.answer-item {
    padding: 14px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid rgba(216, 168, 57, 0.38);
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.94), rgba(249, 245, 235, 0.78));
}

.answer-item.success {
    border-color: rgba(30, 130, 76, 0.7);
    background: rgba(30, 130, 76, 0.08);
}

.answer-item.error {
    border-color: rgba(180, 35, 24, 0.7);
    background: rgba(180, 35, 24, 0.08);
}

.question-shell {
    padding: 18px;
    border: 1px solid rgba(215, 168, 58, 0.42);
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(249, 245, 236, 0.86));
}

.summary-card {
    border: 1px solid rgba(214, 166, 56, 0.36);
}

@media (max-width: 700px) {
    .nav-actions :deep(.v-btn) {
        flex: 1 1 100%;
    }
}
</style>
