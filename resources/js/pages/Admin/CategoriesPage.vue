<script setup lang="ts">
import AdminPageLayout from '@/layouts/AdminPageLayout.vue';
import { adminCategoriesService } from '@/services/admin/adminCategoriesService';
import type { ExamCategoryResource, PaginationResource } from '@/types/admin/resources';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ categories: { data: ExamCategoryResource[]; pagination: PaginationResource } }>();
const pagination = props.categories.pagination ?? { current_page: 1, last_page: 1, per_page: 50, total: 0 };

const modal = ref(false);
const editId = ref<number | null>(null);
const form = useForm({
    name: '',
    is_favorite: false,
});

const openCreate = (): void => {
    editId.value = null;
    form.reset();
    form.is_favorite = false;
    modal.value = true;
};

const openEdit = (category: ExamCategoryResource): void => {
    editId.value = category.id;
    form.name = category.name;
    form.is_favorite = category.is_favorite;
    modal.value = true;
};

const save = (): void => adminCategoriesService.save(form, editId.value, () => (modal.value = false));

const handleTableOptions = (options: { page: number; itemsPerPage: number }): void => {
    adminCategoriesService.fetchPage(options.page, options.itemsPerPage);
};
</script>

<template>
    <AdminPageLayout>
        <template #header-actions>
            <v-btn color="primary" dusk="admin-categories-add-button" @click="openCreate">Dodaj kategorię</v-btn>
        </template>

        <v-data-table-server
            :items="props.categories.data"
            :headers="[
                { title: '', key: 'is_favorite', sortable: false },
                { title: 'Nazwa', key: 'name' },
                { title: 'Egzaminy', key: 'exams_count' },
                { title: 'Akcje', key: 'actions', sortable: false },
            ]"
            :items-length="pagination.total"
            :page="pagination.current_page"
            :items-per-page="pagination.per_page"
            @update:options="handleTableOptions"
        >
            <template #item.is_favorite="{ item }">
                <v-icon :color="item.is_favorite ? 'secondary' : 'rgba(69, 71, 77, 0.22)'" icon="mdi-star" />
            </template>
            <template #item.actions="{ item }">
                <v-btn size="small" variant="text" @click="openEdit(item)">Edytuj</v-btn>
                <v-btn size="small" variant="text" color="error" @click="adminCategoriesService.remove(item.id)">Usuń</v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="modal" max-width="640">
            <v-card>
                <v-card-title>{{ editId ? 'Edytuj kategorię' : 'Nowa kategoria' }}</v-card-title>
                <v-card-text class="d-flex flex-column ga-3">
                    <v-text-field v-model="form.name" label="Nazwa" />
                    <v-checkbox v-model="form.is_favorite" label="Ulubiona" color="secondary" />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn @click="modal = false">Anuluj</v-btn>
                    <v-btn color="primary" @click="save">Zapisz</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </AdminPageLayout>
</template>
