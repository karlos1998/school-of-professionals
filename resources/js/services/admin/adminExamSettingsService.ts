import { router, useForm } from '@inertiajs/vue3';

export const adminExamSettingsService = {
    save(form: ReturnType<typeof useForm>, onSuccess: () => void): void {
        form.put('/admin-panel/exam-settings', { onSuccess });
    },
    backToDashboard(): void {
        router.visit('/admin-panel');
    },
};
