<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import { router } from '@inertiajs/vue3';

const props = withDefaults(defineProps<{
    backTo?: string | null;
    backLabel?: string;
    title?: string | null;
}>(), {
    backTo: '/admin-panel',
    backLabel: 'Wróć do dashboardu',
    title: null,
});
</script>

<template>
    <MainLayout>
        <div class="d-flex justify-space-between align-center mb-4">
            <v-btn
                v-if="props.backTo"
                variant="text"
                prepend-icon="mdi-arrow-left"
                @click="router.visit(props.backTo)"
            >
                {{ props.backLabel }}
            </v-btn>
            <div v-else />
            <slot name="header-actions" />
        </div>

        <h2 v-if="props.title" class="mb-4">{{ props.title }}</h2>

        <slot />
    </MainLayout>
</template>
