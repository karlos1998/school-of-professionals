import { router, useForm } from '@inertiajs/vue3';

export const adminAuthoritiesService = {
    fetchPage(page: number, itemsPerPage: number): void {
        router.get('/admin-panel/authorities', { page, per_page: itemsPerPage }, { preserveState: true, preserveScroll: true });
    },
    save(
        form: ReturnType<typeof useForm>,
        authorityId: number,
        onSuccess: () => void,
    ): void {
        form.put(`/admin-panel/authorities/${authorityId}`, { onSuccess });
    },
};
