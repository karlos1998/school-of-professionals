<script setup lang="ts">
import AdminPageLayout from '@/layouts/AdminPageLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { adminQuestionsService } from '@/services/admin/adminQuestionsService';
import { temporaryUploadsService } from '@/services/api/temporaryUploadsService';
import type { AnswerResource, PaginationResource, QuestionResource } from '@/types/admin/resources';
import { ref } from 'vue';

const props = defineProps<{ exam: { id: number; name: string }; questions: { data: QuestionResource[]; pagination: PaginationResource } }>();
const pagination = props.questions.pagination ?? { current_page: 1, last_page: 1, per_page: 50, total: 0 };

const modal = ref(false);
const editId = ref<number | null>(null);
const imageInput = ref<HTMLInputElement | null>(null);
const imagePreviewUrl = ref<string | null>(null);
const imageName = ref<string | null>(null);
const imageUploadProgress = ref(0);
const isUploadingImage = ref(false);
const imageUploadError = ref<string | null>(null);
const form = useForm({
    position: 1,
    content: '',
    image_path: null as string | null,
    explanation: '',
    answers: [{ content: '', is_correct: true }, { content: '', is_correct: false }] as AnswerResource[],
});

const setCorrect = (idx: number): void => { form.answers = form.answers.map((a, i) => ({ ...a, is_correct: i === idx })); };
const addAnswer = (): void => { form.answers.push({ content: '', is_correct: false }); };
const isTemporaryUpload = (path: string | null): path is string => path?.startsWith('tmp/uploads/') ?? false;

const resetForm = (): void => {
    editId.value = null;
    form.position = 1;
    form.content = '';
    form.image_path = null;
    form.explanation = '';
    form.answers = [{ content: '', is_correct: true }, { content: '', is_correct: false }];
    form.clearErrors();
    imagePreviewUrl.value = null;
    imageName.value = null;
    imageUploadProgress.value = 0;
    imageUploadError.value = null;
};

const openCreate = (): void => {
    resetForm();
    modal.value = true;
};

const save = (): void => {
    if (isUploadingImage.value) {
        return;
    }

    adminQuestionsService.save(form, props.exam.id, editId.value, () => {
        modal.value = false;
        resetForm();
    });
};

const openEdit = (question: QuestionResource): void => {
    resetForm();
    editId.value = question.id;
    form.position = question.position;
    form.content = question.content;
    form.image_path = question.image_path;
    form.explanation = question.explanation ?? '';
    form.answers = question.answers.map((answer) => ({ content: answer.content, is_correct: answer.is_correct }));
    imagePreviewUrl.value = question.image_url;
    imageName.value = question.image_path?.split('/').pop() ?? null;
    modal.value = true;
};

const handleTableOptions = (options: { page: number; itemsPerPage: number }): void => {
    adminQuestionsService.fetchPage(props.exam.id, options.page, options.itemsPerPage);
};

const selectImage = (): void => {
    imageInput.value?.click();
};

const removeImage = async (): Promise<void> => {
    const currentPath = form.image_path;

    form.image_path = null;
    imagePreviewUrl.value = null;
    imageName.value = null;
    imageUploadError.value = null;

    if (isTemporaryUpload(currentPath)) {
        await temporaryUploadsService.remove(currentPath);
    }
};

const uploadErrorMessage = (error: unknown): string => {
    if (typeof error === 'object' && error !== null && 'message' in error && typeof error.message === 'string') {
        return error.message;
    }

    return 'Nie udało się wgrać obrazu.';
};

const handleImageSelected = async (event: Event): Promise<void> => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (!file) {
        return;
    }

    if (isTemporaryUpload(form.image_path)) {
        await temporaryUploadsService.remove(form.image_path);
    }

    isUploadingImage.value = true;
    imageUploadProgress.value = 0;
    imageUploadError.value = null;

    try {
        const upload = await temporaryUploadsService.store(file, (percentage) => {
            imageUploadProgress.value = percentage;
        });

        form.image_path = upload.path;
        imagePreviewUrl.value = upload.url;
        imageName.value = upload.name;
    } catch (error) {
        imageUploadError.value = uploadErrorMessage(error);
    } finally {
        isUploadingImage.value = false;
        input.value = '';
    }
};
</script>
<template>
    <AdminPageLayout :back-to="'/admin-panel/tests'" back-label="Wróć do testów" :title="props.exam.name">
        <template #header-actions>
            <v-btn color="primary" @click="openCreate">Dodaj pytanie</v-btn>
        </template>
        <v-data-table-server :items="props.questions.data" :headers="[{ title: '#', key: 'position' }, { title: 'Pytanie', key: 'content' }, { title: 'Akcje', key: 'actions', sortable: false }]" :items-length="pagination.total" :page="pagination.current_page" :items-per-page="pagination.per_page" @update:options="handleTableOptions">
            <template #item.actions="{ item }">
                <v-btn size="small" variant="text" @click="openEdit(item)">Edytuj</v-btn>
                <v-btn size="small" variant="text" color="error" @click="adminQuestionsService.remove(props.exam.id, item.id)">Usuń</v-btn>
            </template>
        </v-data-table-server>

        <v-dialog v-model="modal" max-width="800">
            <v-card>
                <v-card-title>{{ editId ? 'Edytuj pytanie' : 'Nowe pytanie' }}</v-card-title>
                <v-card-text class="d-flex flex-column ga-3">
                    <v-text-field v-model="form.position" type="number" label="Pozycja" />
                    <v-textarea v-model="form.content" label="Treść pytania" />
                    <div class="question-image-uploader">
                        <div class="d-flex align-center justify-space-between ga-3 flex-wrap">
                            <div>
                                <p class="text-subtitle-2 font-weight-bold mb-1">Grafika pytania</p>
                                <p class="text-caption text-medium-emphasis mb-0">{{ imageName ?? 'Brak wybranego pliku' }}</p>
                            </div>
                            <div class="d-flex ga-2">
                                <v-btn variant="outlined" prepend-icon="mdi-image-plus" :disabled="isUploadingImage" @click="selectImage">
                                    {{ imagePreviewUrl ? 'Zmień' : 'Dodaj' }}
                                </v-btn>
                                <v-btn v-if="imagePreviewUrl" variant="text" color="error" prepend-icon="mdi-delete" :disabled="isUploadingImage" @click="removeImage">
                                    Usuń
                                </v-btn>
                            </div>
                        </div>
                        <input ref="imageInput" class="d-none" type="file" accept="image/png,image/jpeg,image/webp,image/gif" @change="handleImageSelected" />
                        <v-progress-linear
                            v-if="isUploadingImage"
                            :model-value="imageUploadProgress"
                            color="primary"
                            height="8"
                            rounded
                            class="mt-4"
                        />
                        <v-alert v-if="imageUploadError" type="error" variant="tonal" density="compact" class="mt-4">
                            {{ imageUploadError }}
                        </v-alert>
                        <v-img
                            v-if="imagePreviewUrl"
                            :src="imagePreviewUrl"
                            max-height="260"
                            class="question-image-preview mt-4"
                            cover
                        />
                    </div>
                    <v-textarea v-model="form.explanation" label="Wyjaśnienie" />
                    <div v-for="(answer, idx) in form.answers" :key="idx" class="d-flex ga-2">
                        <v-text-field v-model="answer.content" :label="`Odpowiedź ${idx + 1}`" />
                        <v-checkbox :model-value="answer.is_correct" @update:model-value="setCorrect(idx)" label="Poprawna" />
                    </div>
                    <v-btn variant="text" @click="addAnswer">Dodaj odpowiedź</v-btn>
                </v-card-text>
                <v-card-actions><v-spacer /><v-btn @click="modal = false">Anuluj</v-btn><v-btn color="primary" :loading="form.processing" :disabled="isUploadingImage" @click="save">Zapisz</v-btn></v-card-actions>
            </v-card>
        </v-dialog>
    </AdminPageLayout>
</template>

<style scoped>
.question-image-uploader {
    border: 1px solid rgba(69, 71, 77, 0.18);
    border-radius: 8px;
    padding: 16px;
}

.question-image-preview {
    border: 1px solid rgba(69, 71, 77, 0.16);
    border-radius: 8px;
}
</style>
