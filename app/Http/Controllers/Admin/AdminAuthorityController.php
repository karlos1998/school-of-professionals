<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderAuthoritiesRequest;
use App\Http\Requests\Admin\StoreAuthorityRequest;
use App\Http\Requests\Admin\UpdateAuthorityRequest;
use App\Services\Admin\AdminAuthorityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthorityController extends Controller
{
    public function __construct(public AdminAuthorityService $adminAuthorityService) {}

    public function index(Request $request): Response
    {
        $perPage = $request->integer('per_page', 50);

        return Inertia::render('Admin/AuthoritiesPage', $this->adminAuthorityService->indexPayload($perPage));
    }

    public function store(StoreAuthorityRequest $request): RedirectResponse
    {
        /** @var array{name:string,slug:string} $data */
        $data = $request->validated();
        $this->adminAuthorityService->create($data);

        return back()->with('success', 'Organ został dodany.');
    }

    public function update(UpdateAuthorityRequest $request, string $authorityId): RedirectResponse
    {
        /** @var array{name:string} $data */
        $data = $request->validated();
        $this->adminAuthorityService->update((int) $authorityId, $data);

        return back()->with('success', 'Nazwa organu została zaktualizowana.');
    }

    public function reorder(ReorderAuthoritiesRequest $request): JsonResponse
    {
        /** @var list<int> $orderedIds */
        $orderedIds = array_values($request->validated('ordered_ids'));

        $this->adminAuthorityService->reorder($orderedIds);

        return response()->json(status: 204);
    }
}
