<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAuthorityRequest;
use App\Services\Admin\AdminAuthorityService;
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

    public function update(UpdateAuthorityRequest $request, string $authorityId): RedirectResponse
    {
        /** @var array{name:string} $data */
        $data = $request->validated();
        $this->adminAuthorityService->update((int) $authorityId, $data);

        return back()->with('success', 'Nazwa organu została zaktualizowana.');
    }
}
