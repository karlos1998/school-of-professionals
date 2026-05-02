import { router, useForm } from '@inertiajs/vue3';

export const adminExamsService = {
    visitQuestions(examId: number): void {
        router.visit(`/admin-panel/tests/${examId}/questions`);
    },
    remove(examId: number): void {
        router.delete(`/admin-panel/tests/${examId}`);
    },
    fetchPage(page: number, itemsPerPage: number): void {
        router.get('/admin-panel', { page, per_page: itemsPerPage }, { preserveState: true, preserveScroll: true });
    },
    save(
        form: ReturnType<typeof useForm>,
        editId: number | null,
        onSuccess: () => void,
    ): void {
        if (editId) {
            form.put(`/admin-panel/tests/${editId}`, { onSuccess });

            return;
        }

        form.post('/admin-panel/tests', { onSuccess });
    },
};
