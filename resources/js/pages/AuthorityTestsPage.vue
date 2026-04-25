<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';

interface TestClass {
    name: string;
    slug: string;
    url: string;
}

interface TestItem {
    name: string;
    slug: string;
    description: string | null;
    questionCount: number;
    hasClassSelection: boolean;
    classes: TestClass[];
    url: string;
}

interface Props {
    authority: {
        name: string;
        slug: string;
    };
    tests: TestItem[];
    homeUrl: string;
}

const props = defineProps<Props>();

const classDialog = ref(false);
const selectedTestName = ref('');
const classOptions = ref<TestClass[]>([]);

const openTest = (test: TestItem): void => {
    if (props.authority.slug === 'udt' && test.hasClassSelection) {
        selectedTestName.value = test.name;
        classOptions.value = test.classes;
        classDialog.value = true;
        return;
    }

    router.visit(test.url);
};
</script>

<template>
    <MainLayout>
        <v-row justify="center">
            <v-col cols="12" lg="10">
                <v-card class="panel-card" elevation="8" rounded="xl">
                    <v-card-title class="d-flex align-center justify-space-between ga-3 flex-wrap">
                        <div>
                            <p class="text-caption text-medium-emphasis">Katalog testow</p>
                            <h1 class="text-h5 font-weight-bold">{{ authority.name }}</h1>
                        </div>
                        <v-btn :href="homeUrl" prepend-icon="mdi-arrow-left" variant="outlined">Powrot</v-btn>
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col v-for="test in tests" :key="test.slug" cols="12" md="6" lg="4">
                                <v-sheet class="test-card" border rounded="lg" @click="openTest(test)">
                                    <div class="d-flex justify-space-between align-center ga-2">
                                        <h3 class="text-subtitle-1 font-weight-bold">{{ test.name }}</h3>
                                        <v-chip size="small" color="primary" variant="tonal">{{ test.questionCount }} pyt.</v-chip>
                                    </div>
                                    <p class="text-body-2 text-medium-emphasis mt-2">{{ test.description }}</p>
                                    <v-chip
                                        v-if="test.hasClassSelection"
                                        class="mt-3"
                                        size="small"
                                        color="secondary"
                                        variant="flat"
                                        prepend-icon="mdi-shape-outline"
                                    >
                                        Wybierz klase
                                    </v-chip>
                                </v-sheet>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-dialog v-model="classDialog" max-width="460">
            <v-card rounded="lg">
                <v-card-title>Wybierz klase: {{ selectedTestName }}</v-card-title>
                <v-card-text>
                    <v-list>
                        <v-list-item v-for="classItem in classOptions" :key="classItem.slug" :href="classItem.url" rounded="lg">
                            <v-list-item-title>Klasa {{ classItem.name }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="classDialog = false">Anuluj</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </MainLayout>
</template>

<style scoped>
.panel-card {
    background: rgba(255, 255, 255, 0.94);
}

.test-card {
    padding: 16px;
    min-height: 150px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.test-card:hover {
    border-color: rgba(15, 76, 129, 0.42);
    transform: translateY(-2px);
}

@media (max-width: 700px) {
    .test-card {
        min-height: auto;
    }
}
</style>
