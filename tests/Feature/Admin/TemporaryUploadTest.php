<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    config()->set('app.admin_login', 'admin@example.com');
    config()->set('app.super_admin_login', 'kontakt@letscode.it');
    config()->set('uploads.temporary_disk', 'public');
    config()->set('uploads.temporary_directory', 'tmp/uploads');
});

it('stores temporary uploads for admins', function (): void {
    Storage::fake('public');

    $admin = User::factory()->create(['email' => 'admin@example.com']);
    $file = UploadedFile::fake()->image('question.png', 640, 360);

    $response = actingAs($admin)
        ->postJson('/admin-panel/api/uploads', ['file' => $file])
        ->assertCreated()
        ->assertJsonPath('upload.name', 'question.png');

    $path = $response->json('upload.path');

    expect($path)->toStartWith('tmp/uploads/');

    Storage::disk('public')->assertExists($path);
});

it('rejects non image temporary uploads', function (): void {
    Storage::fake('public');

    $admin = User::factory()->create(['email' => 'admin@example.com']);

    actingAs($admin)
        ->postJson('/admin-panel/api/uploads', [
            'file' => UploadedFile::fake()->create('document.pdf', 16, 'application/pdf'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});
