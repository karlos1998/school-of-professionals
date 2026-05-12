import { router, useForm } from '@inertiajs/vue3';

export const adminCategoriesService = {
    fetchPage(page: number, itemsPerPage: number): void {
        router.get('/admin-panel/categories', { page, per_page: itemsPerPage }, { preserveState: true, preserveScroll: true });
    },
    remove(categoryId: number): void {
        router.delete(`/admin-panel/categories/${categoryId}`);
    },
    save(
        form: ReturnType<typeof useForm>,
        editId: number | null,
        onSuccess: () => void,
    ): void {
        if (editId) {
            form.put(`/admin-panel/categories/${editId}`, { onSuccess });

            return;
        }

        form.post('/admin-panel/categories', { onSuccess });
    },
};
