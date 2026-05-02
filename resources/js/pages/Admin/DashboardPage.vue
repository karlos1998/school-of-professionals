<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import { router } from '@inertiajs/vue3';

type ModuleCard = {
    title: string;
    description: string;
    url: string;
};

const props = defineProps<{ modules: ModuleCard[] }>();
</script>

<template>
    <MainLayout>
        <h2 class="mb-4">Panel administratora</h2>
        <v-row>
            <v-col v-for="moduleItem in props.modules" :key="moduleItem.url" cols="12" md="6">
                <v-card class="h-100">
                    <v-card-title>{{ moduleItem.title }}</v-card-title>
                    <v-card-text>{{ moduleItem.description }}</v-card-text>
                    <v-card-actions>
                        <v-btn color="primary" @click="router.visit(moduleItem.url)">Przejdź</v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </MainLayout>
</template>
