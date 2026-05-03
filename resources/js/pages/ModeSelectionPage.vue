<script setup lang="ts">
import { computed } from 'vue';
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

const props = defineProps<Props>();

const learningModes = computed<ModeRoute[]>(() => {
    return props.modeRoutes.filter((mode) => ['sequential', 'random', 'study'].includes(mode.value));
});

const examModes = computed<ModeRoute[]>(() => {
    return props.modeRoutes.filter((mode) => mode.value === 'exam');
});
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
                        <v-card class="mode-section mb-5" variant="outlined" rounded="lg">
                            <v-card-title class="section-title">
                                <v-icon icon="mdi-school-outline" class="mr-2" />
                                Nauka
                            </v-card-title>
                            <v-card-text>
                                <v-row>
                                    <v-col v-for="mode in learningModes" :key="mode.value" cols="12" sm="6">
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

                        <v-card class="mode-section mode-section--exam" variant="outlined" rounded="lg">
                            <v-card-title class="section-title">
                                <v-icon icon="mdi-clipboard-check-outline" class="mr-2" />
                                Egzamin
                            </v-card-title>
                            <v-card-text>
                                <v-row>
                                    <v-col v-for="mode in examModes" :key="mode.value" cols="12" sm="6">
                                        <v-card class="mode-item mode-item--exam h-100" border rounded="lg" :href="mode.url">
                                            <div class="d-flex align-center justify-space-between ga-2">
                                                <strong>{{ mode.label }}</strong>
                                                <v-icon icon="mdi-chevron-right" />
                                            </div>
                                        </v-card>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </MainLayout>
</template>

<style scoped>
.panel-card {
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.98), rgba(250, 246, 235, 0.9));
    border: 1px solid rgba(220, 176, 69, 0.5);
    box-shadow:
        0 14px 36px rgba(16, 28, 44, 0.14),
        inset 0 0 0 1px rgba(255, 255, 255, 0.82);
}

.mode-item {
    padding: 14px;
    transition: all 0.2s ease;
    cursor: pointer;
    border: 1px solid rgba(216, 168, 57, 0.45);
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.95), rgba(249, 245, 234, 0.78));
}

.mode-item:hover {
    border-color: rgba(190, 146, 44, 0.9);
    box-shadow: 0 10px 22px rgba(20, 30, 44, 0.12);
    transform: translateY(-2px) scale(1.01);
}

.mode-section {
    border: 1px solid rgba(216, 168, 57, 0.38);
    background: linear-gradient(155deg, rgba(255, 255, 255, 0.88), rgba(251, 248, 238, 0.74));
}

.mode-section--exam {
    border-color: rgba(88, 90, 96, 0.3);
    background: linear-gradient(155deg, rgba(247, 247, 248, 0.9), rgba(237, 237, 239, 0.76));
}

.section-title {
    font-size: 1rem;
    font-weight: 700;
    color: #45474d;
}

.mode-item--exam {
    border-color: rgba(96, 99, 108, 0.42);
    background: linear-gradient(150deg, rgba(253, 253, 253, 0.95), rgba(240, 240, 242, 0.84));
}
</style>
