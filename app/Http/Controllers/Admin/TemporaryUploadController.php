<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteTemporaryUploadRequest;
use App\Http\Requests\Admin\StoreTemporaryUploadRequest;
use App\Services\TemporaryUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class TemporaryUploadController extends Controller
{
    public function __construct(
        private readonly TemporaryUploadService $temporaryUploadService,
    ) {}

    public function store(StoreTemporaryUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');

        abort_unless($file instanceof UploadedFile, 422);

        return response()->json([
            'upload' => $this->temporaryUploadService->store($file),
        ], 201);
    }

    public function destroy(DeleteTemporaryUploadRequest $request): JsonResponse
    {
        $this->temporaryUploadService->delete((string) $request->validated('path'));

        return response()->json(status: 204);
    }
}
