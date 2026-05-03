import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { ExamConfig, ExamMode, ExamQuestion, ExamSessionPayload } from '@/types/exam-flow';
import { shuffle } from '@/utils/exam';

interface SessionState {
    mode: ExamMode;
    questionOrder: number[];
    questionPointer: number;
    answersByQuestion: Record<number, number>;
    checkedQuestionIds: number[];
}

interface RecentTestRoute {
    label: string;
    url: string;
    visitedAt: string;
}

const DEFAULT_RECENT_TEST_ROUTES_LIMIT = 2;

const defaultSessionState = (): SessionState => ({
    mode: 'sequential',
    questionOrder: [],
    questionPointer: 0,
    answersByQuestion: {},
    checkedQuestionIds: [],
});

export const useExamRunnerStore = defineStore(
    'exam-runner',
    () => {
        const exam = ref<ExamSessionPayload | null>(null);
        const examKey = ref<string | null>(null);
        const examConfig = ref<ExamConfig>({
            questionLimit: 20,
            passingThreshold: 16,
        });
        const session = ref<SessionState>(defaultSessionState());
        const recentTestRoutesLimit = ref<number>(DEFAULT_RECENT_TEST_ROUTES_LIMIT);
        const recentTestRoutes = ref<RecentTestRoute[]>([]);

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
            return session.value.mode === 'exam' && answeredCount.value === totalQuestions.value;
        });

        const normalizeLegacyMode = (): void => {
            if ((session.value.mode as unknown) === 'exam20') {
                session.value.mode = 'exam';
            }
        };

        const loadExam = (payload: ExamSessionPayload, key: string, config: ExamConfig): void => {
            normalizeLegacyMode();
            examConfig.value = config;

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

            if (mode === 'exam') {
                return shuffle(sortedIds).slice(0, Math.min(examConfig.value.questionLimit, sortedIds.length));
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
            };
        };

        const activateMode = (mode: ExamMode): void => {
            if (!exam.value) {
                return;
            }

            normalizeLegacyMode();

            const hasQuestionOrder = session.value.questionOrder.length > 0;
            const isSameMode = session.value.mode === mode;

            if (hasQuestionOrder && isSameMode) {
                if (mode === 'exam') {
                    const expectedCount = Math.min(examConfig.value.questionLimit, exam.value.questions.length);

                    if (session.value.questionOrder.length !== expectedCount) {
                        startSession(mode);
                    }
                }

                return;
            }

            startSession(mode);
        };

        const resetSession = (): void => {
            session.value = defaultSessionState();
        };

        const answerQuestion = (questionId: number, answerId: number): void => {
            const alreadyAnswered = session.value.answersByQuestion[questionId] !== undefined;

            if (alreadyAnswered && session.value.mode !== 'exam') {
                return;
            }

            session.value.answersByQuestion[questionId] = answerId;

            if (!session.value.checkedQuestionIds.includes(questionId)) {
                session.value.checkedQuestionIds.push(questionId);
            }
        };

        const goToNextQuestion = (): void => {
            if (session.value.questionPointer >= totalQuestions.value - 1) {
                return;
            }

            if (session.value.mode === 'exam') {
                const currentQuestionId = session.value.questionOrder[session.value.questionPointer];
                const currentAnswered = session.value.answersByQuestion[currentQuestionId] !== undefined;

                if (!currentAnswered) {
                    return;
                }
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

        const canGoToNextQuestion = (): boolean => {
            if (session.value.questionPointer >= totalQuestions.value - 1) {
                return false;
            }

            if (session.value.mode !== 'exam') {
                return true;
            }

            const currentQuestionId = session.value.questionOrder[session.value.questionPointer];

            return session.value.answersByQuestion[currentQuestionId] !== undefined;
        };

        const normalizeRecentRouteUrl = (url: string): string | null => {
            if (!url) {
                return null;
            }

            if (url.startsWith('/')) {
                return url;
            }

            try {
                const parsedUrl = new URL(url);
                const currentOrigin = typeof window !== 'undefined' ? window.location.origin : null;

                if (currentOrigin && parsedUrl.origin === currentOrigin) {
                    return `${parsedUrl.pathname}${parsedUrl.search}${parsedUrl.hash}`;
                }
            } catch {
                return null;
            }

            return null;
        };

        const rememberRecentTestRoute = (label: string, url: string): void => {
            const normalizedUrl = normalizeRecentRouteUrl(url);

            if (!normalizedUrl) {
                return;
            }

            const now = new Date().toISOString();
            const deduplicated = recentTestRoutes.value.filter((route) => route.url !== normalizedUrl);

            recentTestRoutes.value = [
                {
                    label,
                    url: normalizedUrl,
                    visitedAt: now,
                },
                ...deduplicated,
            ].slice(0, recentTestRoutesLimit.value);
        };

        const setRecentTestRoutesLimit = (limit: number): void => {
            if (!Number.isFinite(limit) || limit < 1) {
                return;
            }

            recentTestRoutesLimit.value = Math.floor(limit);
            recentTestRoutes.value = recentTestRoutes.value.slice(0, recentTestRoutesLimit.value);
        };

        return {
            exam,
            examKey,
            examConfig,
            session,
            orderedQuestions,
            currentQuestion,
            answeredCount,
            totalQuestions,
            correctAnswersCount,
            isSessionFinished,
            recentTestRoutes,
            recentTestRoutesLimit,
            loadExam,
            activateMode,
            startSession,
            resetSession,
            answerQuestion,
            goToNextQuestion,
            goToPreviousQuestion,
            isQuestionChecked,
            selectedAnswerId,
            correctAnswerId,
            canGoToNextQuestion,
            rememberRecentTestRoute,
            setRecentTestRoutesLimit,
        };
    },
    {
        persist: {
            pick: ['exam', 'examKey', 'examConfig', 'session', 'recentTestRoutes', 'recentTestRoutesLimit'],
        },
    },
);
