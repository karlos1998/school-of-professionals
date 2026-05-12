<script setup lang="ts">
import AdminPageLayout from '@/layouts/AdminPageLayout.vue';
import { adminAuthoritiesService } from '@/services/admin/adminAuthoritiesService';
import type { ExamAuthorityResource, PaginationResource } from '@/types/admin/resources';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps<{ authorities: { data: ExamAuthorityResource[]; pagination: PaginationResource } }>();
const authorities = ref<ExamAuthorityResource[]>([...props.authorities.data]);

const modal = ref(false);
const editId = ref<number | null>(null);
const editSlug = ref('');
const draggedAuthorityId = ref<number | null>(null);
const isReordering = ref(false);
const form = useForm({
    name: '',
    slug: '',
});

watch(
    () => props.authorities.data,
    (items) => {
        authorities.value = [...items];
    },
);

const openCreate = (): void => {
    editId.value = null;
    editSlug.value = '';
    form.reset();
    modal.value = true;
};

const openEdit = (authority: ExamAuthorityResource): void => {
    editId.value = authority.id;
    editSlug.value = authority.slug;
    form.name = authority.name;
    form.slug = authority.slug;
    modal.value = true;
};

const save = (): void => {
    if (editId.value === null) {
        adminAuthoritiesService.create(form, () => (modal.value = false));

        return;
    }

    adminAuthoritiesService.save(form, editId.value, () => (modal.value = false));
};

const startDrag = (authorityId: number): void => {
    draggedAuthorityId.value = authorityId;
};

const dropOn = async (targetAuthorityId: number): Promise<void> => {
    const sourceAuthorityId = draggedAuthorityId.value;
    draggedAuthorityId.value = null;

    if (sourceAuthorityId === null || sourceAuthorityId === targetAuthorityId) {
        return;
    }

    const previousOrder = [...authorities.value];
    const sourceIndex = authorities.value.findIndex((authority) => authority.id === sourceAuthorityId);
    const targetIndex = authorities.value.findIndex((authority) => authority.id === targetAuthorityId);

    if (sourceIndex < 0 || targetIndex < 0) {
        return;
    }

    const nextOrder = [...authorities.value];
    const [movedAuthority] = nextOrder.splice(sourceIndex, 1);
    nextOrder.splice(targetIndex, 0, movedAuthority);
    authorities.value = nextOrder;
    isReordering.value = true;

    try {
        await adminAuthoritiesService.reorder(nextOrder.map((authority) => authority.id));
    } catch {
        authorities.value = previousOrder;
    } finally {
        isReordering.value = false;
    }
};
</script>

<template>
    <AdminPageLayout title="Organy">
        <template #header-actions>
            <v-btn color="primary" @click="openCreate">Dodaj organ</v-btn>
        </template>

        <v-progress-linear v-if="isReordering" indeterminate color="primary" class="mb-3" />

        <v-table>
            <thead>
                <tr>
                    <th />
                    <th>Nazwa widoczna</th>
                    <th>Slug w URL</th>
                    <th>Egzaminy</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="authority in authorities"
                    :key="authority.id"
                    class="authority-row"
                    draggable="true"
                    @dragstart="startDrag(authority.id)"
                    @dragover.prevent
                    @drop="dropOn(authority.id)"
                >
                    <td class="authority-row__handle">
                        <v-icon icon="mdi-drag" />
                    </td>
                    <td>{{ authority.name }}</td>
                    <td><code>{{ authority.slug }}</code></td>
                    <td>{{ authority.exams_count }}</td>
                    <td>
                        <v-btn size="small" variant="text" @click="openEdit(authority)">Edytuj</v-btn>
                    </td>
                </tr>
            </tbody>
        </v-table>

        <v-dialog v-model="modal" max-width="640">
            <v-card>
                <v-card-title>{{ editId ? 'Edytuj nazwę organu' : 'Nowy organ' }}</v-card-title>
                <v-card-text class="d-flex flex-column ga-3">
                    <v-text-field v-model="form.name" label="Nazwa widoczna" />
                    <v-text-field
                        v-if="editId === null"
                        v-model="form.slug"
                        label="Slug w URL"
                        hint="Małe litery, cyfry i myślniki"
                        persistent-hint
                    />
                    <v-text-field v-else :model-value="editSlug" label="Slug w URL" readonly />
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

<style scoped>
.authority-row {
    cursor: move;
}

.authority-row__handle {
    color: rgba(69, 71, 77, 0.48);
    width: 44px;
}
</style>
