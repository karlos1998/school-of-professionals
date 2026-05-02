<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import { adminClassesService } from '@/services/admin/adminClassesService';
import type { ExamClassResource, PaginationResource } from '@/types/admin/resources';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ classes: { data: ExamClassResource[]; pagination: PaginationResource } }>();
const pagination = props.classes.pagination ?? { current_page: 1, last_page: 1, per_page: 50, total: 0 };

const modal = ref(false);
const editId = ref<number | null>(null);
const form = useForm({
    name: '',
    slug: '',
});

const openCreate = (): void => {
    editId.value = null;
    form.reset();
    modal.value = true;
};

const openEdit = (examClass: ExamClassResource): void => {
    editId.value = examClass.id;
    form.name = examClass.name;
    form.slug = examClass.slug;
    modal.value = true;
};

const save = (): void => adminClassesService.save(form, editId.value, () => (modal.value = false));

const handleTableOptions = (options: { page: number; itemsPerPage: number }): void => {
    adminClassesService.fetchPage(options.page, options.itemsPerPage);
};
</script>

<template>
    <MainLayout>
        <div class="d-flex justify-space-between mb-4">
            <v-btn variant="text" prepend-icon="mdi-arrow-left" @click="$inertia.visit('/admin-panel')">Wróć do dashboardu</v-btn>
            <v-btn color="primary" @click="openCreate">Dodaj klasę</v-btn>
        </div>

        <v-data-table-server
            :items="props.classes.data"
            :headers="[
                { title: 'Nazwa', key: 'name' },
                { title: 'Slug', key: 'slug' },
                { title: 'Egzaminy', key: 'exams_count' },
                { title: 'Akcje', key: 'actions', sortable: false },
            ]"
            :items-length="pagination.total"
            :page="pagination.current_page"
            :items-per-page="pagination.per_page"
            @update:options="handleTableOptions"
        >
            <template #item.actions="{ item }">
                <v-btn size="small" variant="text" @click="openEdit(item)">Edytuj</v-btn>
                <v-btn size="small" variant="text" color="error" @click="adminClassesService.remove(item.id)">Usuń</v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="modal" max-width="640">
            <v-card>
                <v-card-title>{{ editId ? 'Edytuj klasę' : 'Nowa klasa' }}</v-card-title>
                <v-card-text class="d-flex flex-column ga-3">
                    <v-text-field v-model="form.name" label="Nazwa" />
                    <v-text-field v-model="form.slug" label="Slug" />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn @click="modal = false">Anuluj</v-btn>
                    <v-btn color="primary" @click="save">Zapisz</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </MainLayout>
</template>
