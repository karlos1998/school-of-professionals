<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Services\Admin\AdminCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCategoryController extends Controller
{
    public function __construct(public AdminCategoryService $adminCategoryService) {}

    public function index(Request $request): Response
    {
        $perPage = $request->integer('per_page', 50);

        return Inertia::render('Admin/CategoriesPage', $this->adminCategoryService->indexPayload($perPage));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        /** @var array{name:string} $data */
        $data = $request->validated();
        $this->adminCategoryService->create($data);

        return back()->with('success', 'Kategoria została dodana.');
    }

    public function update(UpdateCategoryRequest $request, string $categoryId): RedirectResponse
    {
        /** @var array{name:string} $data */
        $data = $request->validated();
        $this->adminCategoryService->update((int) $categoryId, $data);

        return back()->with('success', 'Kategoria została zaktualizowana.');
    }

    public function destroy(string $categoryId): RedirectResponse
    {
        $this->adminCategoryService->delete((int) $categoryId);

        return back()->with('success', 'Kategoria została usunięta.');
    }
}
