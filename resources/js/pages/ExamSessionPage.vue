<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useExamRunnerStore } from '@/stores/examRunnerStore';
import type { ExamMode, ExamQuestion, ExamSessionPayload } from '@/types/exam-flow';

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
    backUrl: string;
    exam: ExamSessionPayload;
}

const props = defineProps<Props>();
const store = useExamRunnerStore();

const mode = ref<ExamMode>('sequential');

const modeOptions = [
    { title: 'Po kolei + feedback', value: 'sequential', icon: 'mdi-order-numeric-ascending' },
    { title: 'Losowo + feedback', value: 'random', icon: 'mdi-shuffle-variant' },
    { title: 'Tryb nauki', value: 'study', icon: 'mdi-book-open-variant' },
    { title: 'Losowe 20 pytań', value: 'exam20', icon: 'mdi-clipboard-check-outline' },
] as const;

onMounted(() => {
    const key = [props.authority.slug, props.test.slug, props.selectedClass?.slug ?? 'no-class'].join(':');
    store.loadExam(props.exam, key);
});

const selectedModeLabel = computed(() => {
    return modeOptions.find((option) => option.value === store.session.mode)?.title ?? 'Brak';
});

const currentQuestionNumber = computed(() => store.session.questionPointer + 1);
const hasStarted = computed(() => store.totalQuestions > 0);
const isStudyMode = computed(() => store.session.mode === 'study' && hasStarted.value);
const isExam20Mode = computed(() => store.session.mode === 'exam20' && hasStarted.value);
const isInstantMode = computed(() => ['sequential', 'random'].includes(store.session.mode) && hasStarted.value);

const chooseAnswer = (question: ExamQuestion, answerId: number | null): void => {
    if (answerId === null) {
        return;
    }

    store.answerQuestion(question.id, answerId);
};

const answerColor = (question: ExamQuestion, answerId: number): 'default' | 'success' | 'error' | 'primary' => {
    const selectedAnswerId = store.selectedAnswerId(question.id);
    const correctAnswerId = store.correctAnswerId(question.id);

    if (store.session.mode === 'study') {
        return answerId === correctAnswerId ? 'success' : 'default';
    }

    if (store.session.mode === 'exam20' && !store.isSessionFinished) {
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
                        <div class="d-flex ga-2">
                            <v-btn :href="backUrl" prepend-icon="mdi-arrow-left" variant="outlined">Lista testow</v-btn>
                            <v-chip color="primary" prepend-icon="mdi-lightning-bolt" variant="flat">{{ selectedModeLabel }}</v-chip>
                        </div>
                    </v-card-title>

                    <v-card-text>
                        <v-row class="mb-4">
                            <v-col v-for="option in modeOptions" :key="option.value" cols="12" md="6">
                                <v-sheet :class="['mode-item', { active: mode === option.value }]" border rounded="lg" @click="mode = option.value">
                                    <div class="d-flex align-center ga-3">
                                        <v-avatar color="primary" size="34" variant="tonal"><v-icon :icon="option.icon" /></v-avatar>
                                        <strong>{{ option.title }}</strong>
                                    </div>
                                </v-sheet>
                            </v-col>
                        </v-row>

                        <div class="d-flex flex-wrap ga-3 mb-6">
                            <v-btn color="primary" prepend-icon="mdi-play-circle" size="large" @click="store.startSession(mode)">Start</v-btn>
                            <v-btn color="secondary" prepend-icon="mdi-restore" size="large" @click="store.resetSession()">Reset</v-btn>
                            <v-chip v-if="hasStarted" color="info" variant="tonal">Pytania: {{ store.totalQuestions }}</v-chip>
                            <v-chip v-if="hasStarted" color="success" variant="tonal">Poprawne: {{ store.correctAnswersCount }}</v-chip>
                        </div>

                        <v-alert v-if="!hasStarted" border="start" color="info" variant="tonal">
                            Wybierz tryb i kliknij Start, aby rozpoczac rozwiazywanie testu.
                        </v-alert>

                        <template v-else>
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

                            <template v-else-if="store.currentQuestion">
                                <div class="question-shell">
                                    <div class="d-flex justify-space-between align-center mb-3">
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

                                    <v-alert v-if="isExam20Mode && store.isSessionFinished" color="success" variant="tonal" class="mt-4">
                                        Wynik koncowy: {{ store.correctAnswersCount }} / {{ store.totalQuestions }}
                                    </v-alert>

                                    <div class="d-flex flex-wrap ga-3 mt-6">
                                        <v-btn prepend-icon="mdi-chevron-left" variant="outlined" @click="store.goToPreviousQuestion()">Poprzednie</v-btn>
                                        <v-btn color="primary" append-icon="mdi-chevron-right" @click="store.goToNextQuestion()">Nastepne</v-btn>
                                        <v-btn
                                            v-if="isExam20Mode && !store.isSessionFinished"
                                            color="secondary"
                                            :disabled="!store.canFinalizeExam20"
                                            prepend-icon="mdi-flag-checkered"
                                            @click="store.finalizeExam20()"
                                        >
                                            Zakoncz test i pokaz wynik
                                        </v-btn>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </MainLayout>
</template>

<style scoped>
.panel-card {
    background: rgba(255, 255, 255, 0.94);
}

.mode-item,
.answer-item {
    padding: 14px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid rgba(16, 71, 112, 0.12);
}

.mode-item.active {
    border-color: #0f4c81;
    box-shadow: 0 6px 16px rgba(15, 76, 129, 0.14);
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
    border: 1px solid rgba(15, 76, 129, 0.15);
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 251, 255, 0.9));
}
</style>
