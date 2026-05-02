<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { adminExamsService } from '@/services/admin/adminExamsService';
import type { ExamResource, OptionResource, PaginationResource } from '@/types/admin/resources';
import { ref } from 'vue';

const props = defineProps<{ exams: { data: ExamResource[]; pagination: PaginationResource }; authorities: OptionResource[]; categories: OptionResource[]; classes: OptionResource[] }>();
const pagination = props.exams.pagination ?? { current_page: 1, last_page: 1, per_page: 50, total: 0 };

const modal = ref(false);
const editId = ref<number | null>(null);
const form = useForm({ exam_authority_id: null as number | null, exam_category_id: null as number | null, exam_class_id: null as number | null, name: '', description: '' });

const openCreate = (): void => { editId.value = null; form.reset(); modal.value = true; };
const openEdit = (exam: ExamResource): void => {
    editId.value = exam.id;
    form.exam_authority_id = exam.exam_authority_id;
    form.exam_category_id = exam.exam_category_id;
    form.exam_class_id = exam.exam_class_id;
    form.name = exam.name;
    form.description = exam.description ?? '';
    modal.value = true;
};
const save = (): void => adminExamsService.save(form, editId.value, () => (modal.value = false));

const handleTableOptions = (options: { page: number; itemsPerPage: number }): void => {
    adminExamsService.fetchPage(options.page, options.itemsPerPage);
};
</script>
<template>
    <MainLayout>
        <div class="d-flex justify-space-between mb-4">
            <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="$inertia.visit('/admin-panel')">Wróć do dashboardu</v-btn>
            <v-btn color="primary" @click="openCreate">Dodaj test</v-btn>
        </div>
        <v-data-table-server :items="props.exams.data" :headers="[
            { title: 'Nazwa', key: 'name' },
            { title: 'Organ', key: 'authority' },
            { title: 'Kategoria', key: 'category' },
            { title: 'Klasa', key: 'exam_class' },
            { title: 'Pytania', key: 'questions_count' },
            { title: 'Akcje', key: 'actions', sortable: false },
        ]" :items-length="pagination.total" :page="pagination.current_page" :items-per-page="pagination.per_page" item-value="id" @update:options="handleTableOptions">
            <template #item.actions="{ item }">
                <v-btn size="small" variant="text" @click="adminExamsService.visitQuestions(item.id)">Pytania</v-btn>
                <v-btn size="small" variant="text" @click="openEdit(item)">Edytuj</v-btn>
                <v-btn size="small" variant="text" color="error" @click="adminExamsService.remove(item.id)">Usuń</v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="modal" max-width="720">
            <v-card>
                <v-card-title>{{ editId ? 'Edytuj test' : 'Nowy test' }}</v-card-title>
                <v-card-text class="d-flex flex-column ga-3">
                    <v-select v-model="form.exam_authority_id" :items="props.authorities" item-title="name" item-value="id" label="Organ" />
                    <v-select v-model="form.exam_category_id" :items="props.categories" item-title="name" item-value="id" label="Kategoria" />
                    <v-select v-model="form.exam_class_id" :items="props.classes" item-title="name" item-value="id" label="Klasa" clearable />
                    <v-text-field v-model="form.name" label="Nazwa" />
                    <v-textarea v-model="form.description" label="Opis" />
                </v-card-text>
                <v-card-actions><v-spacer /><v-btn @click="modal = false">Anuluj</v-btn><v-btn color="primary" @click="save">Zapisz</v-btn></v-card-actions>
            </v-card>
        </v-dialog>
    </MainLayout>
</template>
