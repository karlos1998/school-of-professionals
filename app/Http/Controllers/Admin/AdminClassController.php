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
        $this->adminClassService->create($request->validated());

        return back()->with('success', 'Klasa została dodana.');
    }

    public function update(UpdateClassRequest $request, string $classId): RedirectResponse
    {
        $this->adminClassService->update((int) $classId, $request->validated());

        return back()->with('success', 'Klasa została zaktualizowana.');
    }

    public function destroy(string $classId): RedirectResponse
    {
        $this->adminClassService->delete((int) $classId);

        return back()->with('success', 'Klasa została usunięta.');
    }
}
