import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { ExamMode, ExamQuestion, ExamSessionPayload } from '@/types/exam-flow';
import { EXAM_20_SIZE, shuffle } from '@/utils/exam';

interface SessionState {
    mode: ExamMode;
    questionOrder: number[];
    questionPointer: number;
    answersByQuestion: Record<number, number>;
    checkedQuestionIds: number[];
    completed: boolean;
}

const defaultSessionState = (): SessionState => ({
    mode: 'sequential',
    questionOrder: [],
    questionPointer: 0,
    answersByQuestion: {},
    checkedQuestionIds: [],
    completed: false,
});

export const useExamRunnerStore = defineStore(
    'exam-runner',
    () => {
        const exam = ref<ExamSessionPayload | null>(null);
        const examKey = ref<string | null>(null);
        const session = ref<SessionState>(defaultSessionState());

        const orderedQuestions = computed<ExamQuestion[]>(() => {
            if (!exam.value || session.value.questionOrder.length === 0) {
                return [];
            }

            return session.value.questionOrder
                .map((questionId) => exam.value?.questions.find((question) => question.id === questionId))
                .filter((question): question is ExamQuestion => Boolean(question));
        });

        const currentQuestion = computed<ExamQuestion | null>(() => {
            if (session.value.mode === 'study') {
                return null;
            }

            return orderedQuestions.value[session.value.questionPointer] ?? null;
        });

        const answeredCount = computed<number>(() => Object.keys(session.value.answersByQuestion).length);
        const totalQuestions = computed<number>(() => orderedQuestions.value.length);

        const correctAnswersCount = computed<number>(() => {
            if (!exam.value) {
                return 0;
            }

            return Object.entries(session.value.answersByQuestion).reduce((accumulator, [questionIdRaw, answerId]) => {
                const questionId = Number(questionIdRaw);
                const question = exam.value?.questions.find((item) => item.id === questionId);

                if (!question) {
                    return accumulator;
                }

                const selectedAnswer = question.answers.find((answer) => answer.id === answerId);
                return selectedAnswer?.isCorrect ? accumulator + 1 : accumulator;
            }, 0);
        });

        const isSessionFinished = computed<boolean>(() => {
            return session.value.mode === 'exam20' ? session.value.completed : false;
        });

        const canFinalizeExam20 = computed<boolean>(() => {
            return session.value.mode === 'exam20' && answeredCount.value === totalQuestions.value;
        });

        const loadExam = (payload: ExamSessionPayload, key: string): void => {
            if (examKey.value === key && exam.value?.id === payload.id) {
                return;
            }

            exam.value = payload;
            examKey.value = key;
            resetSession();
        };

        const buildQuestionOrder = (mode: ExamMode): number[] => {
            if (!exam.value) {
                return [];
            }

            const sortedIds = exam.value.questions
                .slice()
                .sort((first, second) => first.position - second.position)
                .map((question) => question.id);

            if (mode === 'random') {
                return shuffle(sortedIds);
            }

            if (mode === 'exam20') {
                return shuffle(sortedIds).slice(0, Math.min(EXAM_20_SIZE, sortedIds.length));
            }

            return sortedIds;
        };

        const startSession = (mode: ExamMode): void => {
            if (!exam.value) {
                return;
            }

            session.value = {
                mode,
                questionOrder: buildQuestionOrder(mode),
                questionPointer: 0,
                answersByQuestion: {},
                checkedQuestionIds: [],
                completed: false,
            };
        };

        const resetSession = (): void => {
            session.value = defaultSessionState();
        };

        const answerQuestion = (questionId: number, answerId: number): void => {
            if (session.value.completed) {
                return;
            }

            const alreadyAnswered = session.value.answersByQuestion[questionId] !== undefined;

            if (alreadyAnswered && session.value.mode !== 'exam20') {
                return;
            }

            session.value.answersByQuestion[questionId] = answerId;

            if (!session.value.checkedQuestionIds.includes(questionId)) {
                session.value.checkedQuestionIds.push(questionId);
            }
        };

        const finalizeExam20 = (): void => {
            if (!canFinalizeExam20.value) {
                return;
            }

            session.value.completed = true;
        };

        const goToNextQuestion = (): void => {
            if (session.value.questionPointer >= totalQuestions.value - 1) {
                return;
            }

            session.value.questionPointer += 1;
        };

        const goToPreviousQuestion = (): void => {
            if (session.value.questionPointer <= 0) {
                return;
            }

            session.value.questionPointer -= 1;
        };

        const isQuestionChecked = (questionId: number): boolean => {
            return session.value.checkedQuestionIds.includes(questionId);
        };

        const selectedAnswerId = (questionId: number): number | null => {
            return session.value.answersByQuestion[questionId] ?? null;
        };

        const correctAnswerId = (questionId: number): number | null => {
            const question = exam.value?.questions.find((item) => item.id === questionId);
            return question?.answers.find((answer) => answer.isCorrect)?.id ?? null;
        };

        return {
            exam,
            session,
            orderedQuestions,
            currentQuestion,
            answeredCount,
            totalQuestions,
            correctAnswersCount,
            isSessionFinished,
            canFinalizeExam20,
            loadExam,
            startSession,
            resetSession,
            answerQuestion,
            finalizeExam20,
            goToNextQuestion,
            goToPreviousQuestion,
            isQuestionChecked,
            selectedAnswerId,
            correctAnswerId,
        };
    },
    {
        persist: true,
    },
);
