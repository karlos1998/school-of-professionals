import { router, useForm } from '@inertiajs/vue3';

export const adminQuestionsService = {
    remove(examId: number, questionId: number): void {
        router.delete(`/admin-panel/tests/${examId}/questions/${questionId}`);
    },
    fetchPage(examId: number, page: number, itemsPerPage: number): void {
        router.get(`/admin-panel/tests/${examId}/questions`, { page, per_page: itemsPerPage }, { preserveState: true, preserveScroll: true });
    },
    save(
        form: ReturnType<typeof useForm>,
        examId: number,
        editId: number | null,
        onSuccess: () => void,
    ): void {
        const base = `/admin-panel/tests/${examId}/questions`;
        if (editId) {
            form.put(`${base}/${editId}`, { onSuccess });

            return;
        }

        form.post(base, { onSuccess });
    },
};
