<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StaffPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_archive_renders_with_counts(): void
    {
        Staff::create(['name' => 'Budi Santoso', 'role' => 'Guru Matematika', 'type' => 'guru']);
        Staff::create(['name' => 'Siti Aminah', 'role' => 'Staf TU', 'type' => 'tendik']);

        $this->get('/guru-tendik')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/StaffArchive')
                ->where('type', null)
                ->where('counts.all', 2)
                ->where('counts.guru', 1)
                ->where('counts.tendik', 1)
                ->has('items.data', 2)
                ->where('items.per_page', 8)
            );
    }

    public function test_staff_archive_filters_by_type(): void
    {
        Staff::create(['name' => 'Budi', 'role' => 'Guru Matematika', 'type' => 'guru']);
        Staff::create(['name' => 'Siti', 'role' => 'Staf TU', 'type' => 'tendik']);

        $this->get('/guru-tendik?type=guru')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('type', 'guru')
                ->where('items.total', 1)
                ->where('items.data.0.name', 'Budi')
            );
    }

    public function test_staff_archive_ignores_unknown_type(): void
    {
        Staff::create(['name' => 'Budi', 'role' => 'Guru', 'type' => 'guru']);

        $this->get('/guru-tendik?type=alien')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('type', null)
                ->where('items.total', 1)
            );
    }

    public function test_staff_detail_renders(): void
    {
        $staff = Staff::create(['name' => 'Budi Santoso', 'role' => 'Guru Matematika', 'type' => 'guru']);

        $this->get("/guru-tendik/{$staff->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/StaffDetail')
                ->where('staffMember.id', $staff->id)
                ->where('staffMember.name', 'Budi Santoso')
            );
    }

    public function test_staff_detail_404_for_invalid_id(): void
    {
        $this->get('/guru-tendik/999')
            ->assertNotFound();
    }

    public function test_staff_photo_upload(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post('/admin/staff', [
            'name' => 'Budi',
            'role' => 'Guru',
            'type' => 'guru',
            'photo' => UploadedFile::fake()->image('foto.jpg', 200, 200),
        ])->assertRedirect(route('admin.staff.index'));

        $staff = Staff::firstWhere('name', 'Budi');
        $this->assertNotNull($staff->photo);
        $this->assertStringContainsString('/storage/staff/', $staff->photo);
    }

    public function test_staff_photo_replaced_on_update(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'admin']);
        $staff = Staff::create(['name' => 'Budi', 'role' => 'Guru', 'type' => 'guru', 'photo' => '/storage/staff/old.webp']);

        Storage::disk('public')->put('staff/old.webp', 'old-image');

        $this->actingAs($user)->put("/admin/staff/{$staff->id}", [
            'name' => 'Budi',
            'role' => 'Guru',
            'type' => 'guru',
            'photo' => UploadedFile::fake()->image('baru.jpg', 200, 200),
        ])->assertRedirect(route('admin.staff.index'));

        $staff->refresh();
        $this->assertStringContainsString('/storage/staff/', $staff->photo);
        $this->assertNotEquals('/storage/staff/old.webp', $staff->photo);
        Storage::disk('public')->assertMissing('staff/old.webp');
    }
}
