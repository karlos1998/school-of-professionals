import { httpClient } from '@/services/api/httpClient';

export type TemporaryUpload = {
    path: string;
    url: string;
    name: string;
    size: number;
    mime_type: string | null;
};

type UploadResponse = {
    upload: TemporaryUpload;
};

export const temporaryUploadsService = {
    async store(file: File, onProgress: (percentage: number) => void): Promise<TemporaryUpload> {
        const response = await httpClient.upload<UploadResponse>('/admin-panel/api/uploads', file, { onProgress });

        return response.upload;
    },

    async remove(path: string): Promise<void> {
        await httpClient.delete('/admin-panel/api/uploads', { path });
    },
};
