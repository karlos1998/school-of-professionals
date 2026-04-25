export type ExamMode = 'sequential' | 'random' | 'study' | 'exam';

export interface ExamAnswer {
    id: number;
    content: string;
    isCorrect: boolean;
}

export interface ExamQuestion {
    id: number;
    position: number;
    content: string;
    explanation: string | null;
    answers: ExamAnswer[];
}

export interface ExamSessionPayload {
    id: number;
    authoritySlug: string;
    testSlug: string;
    name: string;
    description: string | null;
    class: {
        name: string;
        slug: string;
    } | null;
    questions: ExamQuestion[];
}

export interface ExamConfig {
    questionLimit: number;
    passingThreshold: number;
}
