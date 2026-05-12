type UploadProgressHandler = (percentage: number) => void;

export type UploadRequestOptions = {
    onProgress?: UploadProgressHandler;
};

const csrfToken = (): string => {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
};

const parseJson = <T>(raw: string): T => {
    return raw ? JSON.parse(raw) as T : ({} as T);
};

export const httpClient = {
    upload<TResponse>(url: string, file: File, options: UploadRequestOptions = {}): Promise<TResponse> {
        return new Promise((resolve, reject) => {
            const request = new XMLHttpRequest();
            const body = new FormData();

            body.append('file', file);

            request.open('POST', url);
            request.setRequestHeader('Accept', 'application/json');
            request.setRequestHeader('X-CSRF-TOKEN', csrfToken());
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            request.upload.onprogress = (event): void => {
                if (!event.lengthComputable) {
                    return;
                }

                options.onProgress?.(Math.round((event.loaded / event.total) * 100));
            };

            request.onload = (): void => {
                const response = parseJson<unknown>(request.responseText);

                if (request.status >= 200 && request.status < 300) {
                    resolve(response as TResponse);

                    return;
                }

                reject(response);
            };

            request.onerror = (): void => {
                reject(new Error('Upload failed.'));
            };

            request.send(body);
        });
    },

    async delete(url: string, payload: Record<string, unknown>): Promise<void> {
        await fetch(url, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });
    },
};
