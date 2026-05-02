import { router, useForm } from '@inertiajs/vue3';

export const adminClassesService = {
    fetchPage(page: number, itemsPerPage: number): void {
        router.get('/admin-panel/classes', { page, per_page: itemsPerPage }, { preserveState: true, preserveScroll: true });
    },
    remove(classId: number): void {
        router.delete(`/admin-panel/classes/${classId}`);
    },
    save(
        form: ReturnType<typeof useForm>,
        editId: number | null,
        onSuccess: () => void,
    ): void {
        if (editId) {
            form.put(`/admin-panel/classes/${editId}`, { onSuccess });

            return;
        }

        form.post('/admin-panel/classes', { onSuccess });
    },
};
