<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/DashboardPage', [
            'modules' => [
                [
                    'title' => 'Testy',
                    'description' => 'Zarządzanie testami i pytaniami',
                    'url' => '/admin-panel/tests',
                ],
                [
                    'title' => 'Klasy',
                    'description' => 'Zarządzanie klasami egzaminów',
                    'url' => '/admin-panel/classes',
                ],
            ],
        ]);
    }
}
