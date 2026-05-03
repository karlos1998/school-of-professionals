<script setup lang="ts">
import AdminPageLayout from '@/layouts/AdminPageLayout.vue';
import { adminExamSettingsService } from '@/services/admin/adminExamSettingsService';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{ settings: { question_limit: number; passing_threshold: number } }>();

const form = useForm({
    question_limit: props.settings.question_limit,
    passing_threshold: props.settings.passing_threshold,
});

const submit = (): void => {
    adminExamSettingsService.save(form, () => undefined);
};
</script>

<template>
    <AdminPageLayout>
        <v-card max-width="760" class="pa-4">
            <v-card-title>Ustawienia egzaminu</v-card-title>
            <v-card-text class="d-flex flex-column ga-3">
                <v-text-field
                    v-model="form.question_limit"
                    type="number"
                    label="Liczba pytań w trybie Egzamin"
                    :error-messages="form.errors.question_limit"
                    min="1"
                />
                <v-text-field
                    v-model="form.passing_threshold"
                    type="number"
                    label="Próg zaliczenia"
                    :error-messages="form.errors.passing_threshold"
                    min="0"
                />
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn color="primary" :loading="form.processing" @click="submit">Zapisz ustawienia</v-btn>
            </v-card-actions>
        </v-card>
    </AdminPageLayout>
</template>
