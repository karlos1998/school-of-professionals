import { router, useForm } from '@inertiajs/vue3';

export const adminAuthoritiesService = {
    fetchPage(page: number, itemsPerPage: number): void {
        router.get('/admin-panel/authorities', { page, per_page: itemsPerPage }, { preserveState: true, preserveScroll: true });
    },
    create(
        form: ReturnType<typeof useForm>,
        onSuccess: () => void,
    ): void {
        form.post('/admin-panel/authorities', { onSuccess });
    },
    save(
        form: ReturnType<typeof useForm>,
        authorityId: number,
        onSuccess: () => void,
    ): void {
        form.put(`/admin-panel/authorities/${authorityId}`, { onSuccess });
    },
    async reorder(orderedIds: number[]): Promise<void> {
        const response = await fetch('/admin-panel/authorities/order', {
            method: 'PUT',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ ordered_ids: orderedIds }),
        });

        if (!response.ok) {
            throw new Error('Unable to save authority order.');
        }
    },
};
