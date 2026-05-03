<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRequest;
use App\Http\Requests\Admin\UpdateClassRequest;
use App\Services\Admin\AdminClassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminClassController extends Controller
{
    public function __construct(public AdminClassService $adminClassService) {}

    public function index(Request $request): Response
    {
        $perPage = $request->integer('per_page', 50);

        return Inertia::render('Admin/ClassesPage', $this->adminClassService->indexPayload($perPage));
    }

    public function store(StoreClassRequest $request): RedirectResponse
    {
        /** @var array{name:string} $data */
        $data = $request->validated();
        $this->adminClassService->create($data);

        return back()->with('success', 'Klasa została dodana.');
    }

    public function update(UpdateClassRequest $request, string $classId): RedirectResponse
    {
        /** @var array{name:string} $data */
        $data = $request->validated();
        $this->adminClassService->update((int) $classId, $data);

        return back()->with('success', 'Klasa została zaktualizowana.');
    }

    public function destroy(string $classId): RedirectResponse
    {
        $this->adminClassService->delete((int) $classId);

        return back()->with('success', 'Klasa została usunięta.');
    }
}
