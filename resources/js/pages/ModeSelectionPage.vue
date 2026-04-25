<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import type { ExamMode } from '@/types/exam-flow';

interface ModeRoute {
    value: ExamMode;
    label: string;
    url: string;
}

interface Props {
    authority: {
        name: string;
        slug: string;
    };
    test: {
        name: string;
        slug: string;
    };
    selectedClass: {
        name: string;
        slug: string;
    } | null;
    backUrl: string;
    modeRoutes: ModeRoute[];
}

defineProps<Props>();
</script>

<template>
    <MainLayout>
        <v-row justify="center">
            <v-col cols="12" lg="10" xl="8">
                <v-card class="panel-card" elevation="8" rounded="xl">
                    <v-card-title class="d-flex justify-space-between align-start ga-3 flex-wrap">
                        <div>
                            <p class="text-caption text-medium-emphasis">{{ authority.name }} / {{ test.name }}</p>
                            <h1 class="text-h5 font-weight-bold">
                                Wybierz tryb testu
                                <span v-if="selectedClass">(Klasa {{ selectedClass.name }})</span>
                            </h1>
                        </div>
                        <v-btn :href="backUrl" prepend-icon="mdi-arrow-left" variant="outlined">Lista testow</v-btn>
                    </v-card-title>

                    <v-card-text>
                        <v-row>
                            <v-col v-for="mode in modeRoutes" :key="mode.value" cols="12" sm="6">
                                <v-card class="mode-item h-100" border rounded="lg" :href="mode.url">
                                    <div class="d-flex align-center justify-space-between ga-2">
                                        <strong>{{ mode.label }}</strong>
                                        <v-icon icon="mdi-chevron-right" />
                                    </div>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </MainLayout>
</template>

<style scoped>
.panel-card {
    background: rgba(255, 255, 255, 0.96);
}

.mode-item {
    padding: 14px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid rgba(16, 71, 112, 0.12);
}

.mode-item:hover {
    border-color: rgba(15, 76, 129, 0.42);
    transform: translateY(-2px);
}
</style>
