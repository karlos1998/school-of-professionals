<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';

interface Props {
    authorities: Array<{
        name: string;
        slug: string;
        url: string;
    }>;
}

defineProps<Props>();
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
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </MainLayout>
</template>

<style scoped>
.panel-card {
    background: rgba(255, 255, 255, 0.94);
}

.welcome-row {
    min-height: calc(100vh - 180px);
}

.authority-card {
    border: 1px solid rgba(15, 76, 129, 0.2);
    transition: all 0.2s ease;
}

.authority-card:hover {
    transform: translateY(-2px);
    border-color: rgba(15, 76, 129, 0.45);
}

@media (max-width: 700px) {
    .welcome-row {
        min-height: calc(100vh - 150px);
    }
}
</style>
