export type OptionResource = {
    id: number;
    name: string;
};

export type ExamResource = {
    id: number;
    name: string;
    description: string | null;
    exam_authority_id: number;
    exam_category_id: number;
    exam_class_id: number | null;
    authority: string | null;
    category: string | null;
    exam_class: string | null;
    questions_count: number;
};

export type AnswerResource = {
    content: string;
    is_correct: boolean;
};

export type QuestionResource = {
    id: number;
    position: number;
    content: string;
    explanation: string | null;
    answers: AnswerResource[];
};

export type PaginationResource = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

export type ExamClassResource = {
    id: number;
    name: string;
    exams_count: number;
};
