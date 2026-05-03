<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useExamRunnerStore } from '@/stores/examRunnerStore';

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
const examRunnerStore = useExamRunnerStore();

const classDialog = ref(false);
const selectedTestName = ref('');
const classOptions = ref<TestClass[]>([]);

const openTest = (test: TestItem): void => {
    examRunnerStore.rememberRecentTestRoute(`${props.authority.name} - ${test.name}`, test.url);

    if (test.hasClassSelection) {
        selectedTestName.value = test.name;
        classOptions.value = test.classes;
        classDialog.value = true;
        return;
    }

    router.visit(test.url);
};

const openClassTest = (classItem: TestClass): void => {
    examRunnerStore.rememberRecentTestRoute(
        `${props.authority.name} - ${selectedTestName.value} (Klasa ${classItem.name})`,
        classItem.url,
    );
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
            <v-card class="class-dialog" rounded="xl">
                <v-card-title class="class-dialog__title">
                    <span>Wybierz klase: {{ selectedTestName }}</span>
                </v-card-title>
                <v-card-text>
                    <v-list class="class-list bg-transparent">
                        <v-list-item
                            v-for="classItem in classOptions"
                            :key="classItem.slug"
                            :href="classItem.url"
                            rounded="lg"
                            class="class-item"
                            @click="openClassTest(classItem)"
                        >
                            <template #prepend>
                                <v-icon icon="mdi-medal-outline" color="secondary" />
                            </template>
                            <v-list-item-title>Klasa {{ classItem.name }}</v-list-item-title>
                            <template #append>
                                <v-icon icon="mdi-chevron-right" />
                            </template>
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
    background: linear-gradient(155deg, rgba(255, 255, 255, 0.98), rgba(250, 246, 235, 0.9));
    border: 1px solid rgba(221, 176, 68, 0.52);
    box-shadow:
        0 14px 38px rgba(14, 29, 48, 0.14),
        inset 0 0 0 1px rgba(255, 255, 255, 0.84);
}

.test-card {
    padding: 16px;
    min-height: 150px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid rgba(214, 169, 62, 0.45);
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.97), rgba(250, 246, 236, 0.82));
}

.test-card:hover {
    border-color: rgba(188, 145, 44, 0.88);
    box-shadow: 0 12px 26px rgba(19, 31, 45, 0.12);
    transform: translateY(-2px) scale(1.01);
}

.class-dialog {
    border: 1px solid rgba(220, 176, 69, 0.58);
    background: linear-gradient(150deg, rgba(255, 255, 255, 0.98), rgba(249, 245, 234, 0.96));
}

.class-dialog__title {
    background: linear-gradient(145deg, #3b3d42, #4d5057);
    color: #f3d68b;
    border-bottom: 1px solid rgba(218, 174, 67, 0.42);
}

.class-list {
    gap: 10px;
    display: grid;
}

.class-item {
    border: 1px solid rgba(217, 171, 62, 0.42);
    transition: all 0.2s ease;
}

.class-item:hover {
    transform: translateY(-1px);
    border-color: rgba(190, 146, 44, 0.8);
    background: rgba(255, 255, 255, 0.75);
}

@media (max-width: 700px) {
    .test-card {
        min-height: auto;
    }
}
</style>
