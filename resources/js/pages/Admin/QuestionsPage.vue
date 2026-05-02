<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

type Answer = { content: string; is_correct: boolean };
type Question = { id: number; position: number; content: string; explanation: string | null; answers: Answer[] };

const props = defineProps<{ exam: { id: number; name: string }; questions: { data: Question[] }; pagination: { current_page: number; last_page: number; per_page: number; total: number } }>();

const modal = ref(false);
const editId = ref<number | null>(null);
const form = useForm({ position: 1, content: '', explanation: '', answers: [{ content: '', is_correct: true }, { content: '', is_correct: false }] as Answer[] });

const setCorrect = (idx: number): void => { form.answers = form.answers.map((a, i) => ({ ...a, is_correct: i === idx })); };
const addAnswer = (): void => { form.answers.push({ content: '', is_correct: false }); };
const save = (): void => {
    const base = `/admin-panel/tests/${props.exam.id}/questions`;
    if (editId.value) { form.put(`${base}/${editId.value}`, { onSuccess: () => (modal.value = false) }); return; }
    form.post(base, { onSuccess: () => (modal.value = false) });
};

const openEdit = (question: Question): void => {
    editId.value = question.id;
    form.position = question.position;
    form.content = question.content;
    form.explanation = question.explanation ?? '';
    form.answers = question.answers.map((answer) => ({ content: answer.content, is_correct: answer.is_correct }));
    modal.value = true;
};

const handleTableOptions = (options: { page: number; itemsPerPage: number }): void => {
    router.get(`/admin-panel/tests/${props.exam.id}/questions`, { page: options.page, per_page: options.itemsPerPage }, { preserveState: true, preserveScroll: true });
};
</script>
<template>
    <MainLayout>
        <div class="d-flex justify-space-between mb-4">
            <h2>{{ props.exam.name }}</h2>
            <v-btn color="primary" @click="modal = true">Dodaj pytanie</v-btn>
        </div>
        <v-data-table-server :items="props.questions.data" :headers="[{ title: '#', key: 'position' }, { title: 'Pytanie', key: 'content' }, { title: 'Akcje', key: 'actions', sortable: false }]" :items-length="props.pagination.total" :page="props.pagination.current_page" :items-per-page="props.pagination.per_page" @update:options="handleTableOptions">
            <template #item.actions="{ item }">
                <v-btn size="small" variant="text" @click="openEdit(item)">Edytuj</v-btn>
                <v-btn size="small" variant="text" color="error" @click="router.delete(`/admin-panel/tests/${props.exam.id}/questions/${item.id}`)">Usuń</v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="modal" max-width="800">
            <v-card>
                <v-card-title>{{ editId ? 'Edytuj pytanie' : 'Nowe pytanie' }}</v-card-title>
                <v-card-text class="d-flex flex-column ga-3">
                    <v-text-field v-model="form.position" type="number" label="Pozycja" />
                    <v-textarea v-model="form.content" label="Treść pytania" />
                    <v-textarea v-model="form.explanation" label="Wyjaśnienie" />
                    <div v-for="(answer, idx) in form.answers" :key="idx" class="d-flex ga-2">
                        <v-text-field v-model="answer.content" :label="`Odpowiedź ${idx + 1}`" />
                        <v-checkbox :model-value="answer.is_correct" @update:model-value="setCorrect(idx)" label="Poprawna" />
                    </div>
                    <v-btn variant="text" @click="addAnswer">Dodaj odpowiedź</v-btn>
                </v-card-text>
                <v-card-actions><v-spacer /><v-btn @click="modal = false">Anuluj</v-btn><v-btn color="primary" @click="save">Zapisz</v-btn></v-card-actions>
            </v-card>
        </v-dialog>
    </MainLayout>
</template>
