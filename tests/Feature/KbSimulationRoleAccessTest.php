<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KbSimulationRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $roleSlug, array $attributes = []): User
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => str($roleSlug)->replace('_', ' ')->title()->toString()],
        );

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
        ], $attributes));
    }

    public function test_marketing_user_cannot_send_pricing_override_to_calculate_endpoint(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_MARKETING);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'rate_percent_override' => 1.25,
        ]);

        $response->assertForbidden();
    }

    public function test_marketing_user_cannot_send_tata_laksana_plus_override_to_store_endpoint(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_MARKETING);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.store'), [
            'tata_laksana_plus_percent_override' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_test_user_cannot_edit_kb_pricing_overrides(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_MARKETING, ['email' => 'test@example.com']);

        $this->assertFalse($user->canEditKbPricing());

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'rate_percent_override' => 1.25,
        ]);

        $response->assertForbidden();
    }

    public function test_marketing_user_can_calculate_with_default_pricing_values_when_no_override_is_edited(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_MARKETING);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'tanggal_simulasi' => '2026-06-01',
            'tanggal_lahir' => '1985-06-01',
            'gaji_pensiun' => 5000000,
            'tenor' => 10,
            'plafond' => 100000000,
            'rate_percent_override' => 16,
            'admin_angsuran_percent_override' => 10,
        ]);

        $this->assertNotSame(403, $response->getStatusCode());
    }

    public function test_legacy_marketing_routes_keep_the_same_pricing_override_guard(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_MARKETING, ['email' => 'test@example.com']);

        $calculateResponse = $this->actingAs($user)->postJson(route('calculatesimulasi'), [
            'rate_percent_override' => 25,
            'jenis_pensiun' => 'Sendiri',
            'tanggal_simulasi' => '2026-06-01',
            'tanggal_lahir' => '1985-06-01',
            'gaji_pensiun' => 5000000,
            'tenor' => 10,
            'plafond' => 100000000,
        ]);

        $calculateResponse->assertForbidden();
    }

    public function test_marketing_user_cannot_send_tata_laksana_plus_override(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_MARKETING);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'tata_laksana_plus_percent_override' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_supervisor_user_cannot_send_tata_laksana_plus_override(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_SUPERVISOR);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'tata_laksana_plus_percent_override' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_admin_user_can_send_tata_laksana_plus_override(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_ADMIN);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'produk' => 'Platinum',
            'jenis_pensiun' => 'Sendiri',
            'bank_tujuan' => 'KB',
            'tanggal_simulasi' => '2026-08-26',
            'tanggal_lahir' => '1956-06-02',
            'instansi' => 'TASPEN',
            'gaji_pensiun' => 5000000,
            'angsuran_lainnya' => 1500000,
            'tenor' => 60,
            'plafond' => 100000000,
            'tata_laksana_plus_percent_override' => 5,
        ]);

        $this->assertNotSame(403, $response->getStatusCode());
    }

    public function test_supervisor_user_can_access_override_fields_and_reaches_validation_layer(): void
    {
        /** @var User $user */
        $user = $this->makeUser(User::ROLE_SUPERVISOR);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'rate_percent_override' => 1.25,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal_lahir']);
    }
}