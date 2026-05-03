<script setup lang="ts">
import { computed } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useExamRunnerStore } from '@/stores/examRunnerStore';

interface Props {
    authorities: Array<{
        name: string;
        slug: string;
        url: string;
    }>;
}

defineProps<Props>();

const examRunnerStore = useExamRunnerStore();
const recentTestRoutes = computed(() => examRunnerStore.recentTestRoutes);
</script>

<template>
    <MainLayout>
        <v-row justify="center" align="center" class="welcome-row">
            <v-col cols="12" md="10" lg="8">
                <v-card class="panel-card" elevation="8" rounded="xl">
                    <v-card-title class="text-h5 font-weight-bold">Witaj w platformie testowej</v-card-title>
                    <v-card-subtitle>Wybierz obszar egzaminow, aby przejsc do listy testow.</v-card-subtitle>
                    <v-card-text>
                        <v-row>
                            <v-col v-for="authority in authorities" :key="authority.slug" cols="12" md="6">
                                <v-card :href="authority.url" class="authority-card" color="surface" variant="flat" rounded="lg">
                                    <v-card-text>
                                        <p class="text-overline text-medium-emphasis">Egzaminy</p>
                                        <h2 class="text-h5 font-weight-bold">{{ authority.name }}</h2>
                                        <p class="text-body-2 text-medium-emphasis mt-2">
                                            Przejdz do katalogu testow i rozpocznij rozwiazywanie.
                                        </p>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>

                        <v-card
                            v-if="recentTestRoutes.length > 0"
                            class="recent-tests-card mt-6"
                            variant="flat"
                            rounded="lg"
                        >
                            <v-card-text>
                                <p class="text-overline text-medium-emphasis mb-1">Szybki powrot</p>
                                <h2 class="text-h6 font-weight-bold mb-3">Ostatnio odwiedzane testy</h2>

                                <v-row>
                                    <v-col
                                        v-for="recentRoute in recentTestRoutes"
                                        :key="recentRoute.url"
                                        cols="12"
                                        md="6"
                                    >
                                        <v-sheet class="recent-route-item" border rounded="lg">
                                            <div class="d-flex justify-space-between align-center ga-3">
                                                <div>
                                                    <p class="text-body-2 font-weight-medium mb-0">{{ recentRoute.label }}</p>
                                                    <p class="text-caption text-medium-emphasis mb-0">{{ recentRoute.url }}</p>
                                                </div>
                                                <v-btn
                                                    :href="recentRoute.url"
                                                    color="primary"
                                                    variant="outlined"
                                                    size="small"
                                                >
                                                    Przejdz
                                                </v-btn>
                                            </div>
                                        </v-sheet>
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
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.98), rgba(250, 245, 233, 0.92));
    border: 1px solid rgba(221, 176, 68, 0.5);
    box-shadow:
        0 16px 42px rgba(15, 28, 46, 0.14),
        inset 0 0 0 1px rgba(255, 255, 255, 0.8);
}

.welcome-row {
    min-height: calc(100vh - 180px);
}

.authority-card {
    border: 1px solid rgba(216, 168, 57, 0.45);
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.96), rgba(251, 247, 236, 0.8));
    transition: all 0.22s ease;
}

.authority-card:hover {
    transform: translateY(-2px) scale(1.01);
    border-color: rgba(186, 142, 41, 0.85);
    box-shadow: 0 10px 24px rgba(22, 31, 45, 0.12);
}

.recent-tests-card {
    border: 1px solid rgba(214, 169, 62, 0.42);
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.95), rgba(248, 244, 234, 0.8));
}

.recent-route-item {
    padding: 12px;
    border: 1px solid rgba(214, 169, 62, 0.35);
    background: rgba(255, 255, 255, 0.82);
}

@media (max-width: 700px) {
    .welcome-row {
        min-height: calc(100vh - 150px);
    }
}
</style>
