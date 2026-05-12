<script setup lang="ts">
import AdminPageLayout from '@/layouts/AdminPageLayout.vue';
import { adminAuthoritiesService } from '@/services/admin/adminAuthoritiesService';
import type { ExamAuthorityResource, PaginationResource } from '@/types/admin/resources';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{ authorities: { data: ExamAuthorityResource[]; pagination: PaginationResource } }>();
const pagination = props.authorities.pagination ?? { current_page: 1, last_page: 1, per_page: 50, total: 0 };

const modal = ref(false);
const editId = ref<number | null>(null);
const editSlug = ref('');
const form = useForm({
    name: '',
});

const openEdit = (authority: ExamAuthorityResource): void => {
    editId.value = authority.id;
    editSlug.value = authority.slug;
    form.name = authority.name;
    modal.value = true;
};

const save = (): void => {
    if (editId.value === null) {
        return;
    }

    adminAuthoritiesService.save(form, editId.value, () => (modal.value = false));
};

const handleTableOptions = (options: { page: number; itemsPerPage: number }): void => {
    adminAuthoritiesService.fetchPage(options.page, options.itemsPerPage);
};
</script>

<template>
    <AdminPageLayout title="Organy">
        <v-data-table-server
            :items="props.authorities.data"
            :headers="[
                { title: 'Nazwa widoczna', key: 'name' },
                { title: 'Slug w URL', key: 'slug' },
                { title: 'Egzaminy', key: 'exams_count' },
                { title: 'Akcje', key: 'actions', sortable: false },
            ]"
            :items-length="pagination.total"
            :page="pagination.current_page"
            :items-per-page="pagination.per_page"
            @update:options="handleTableOptions"
        >
            <template #item.slug="{ item }">
                <code>{{ item.slug }}</code>
            </template>
            <template #item.actions="{ item }">
                <v-btn size="small" variant="text" @click="openEdit(item)">Edytuj</v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="modal" max-width="640">
            <v-card>
                <v-card-title>Edytuj nazwę organu</v-card-title>
                <v-card-text class="d-flex flex-column ga-3">
                    <v-text-field :model-value="editSlug" label="Slug w URL" readonly />
                    <v-text-field v-model="form.name" label="Nazwa widoczna" />
                </v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn @click="modal = false">Anuluj</v-btn>
                    <v-btn color="primary" :loading="form.processing" @click="save">Zapisz</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </AdminPageLayout>
</template>
