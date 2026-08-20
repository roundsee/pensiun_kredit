<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KbSimulationRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketing_user_cannot_send_pricing_override_to_calculate_endpoint(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => User::ROLE_MARKETING,
        ]);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'rate_percent_override' => 1.25,
        ]);

        $response->assertForbidden();
    }

    public function test_marketing_user_cannot_send_pricing_override_to_store_endpoint(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => User::ROLE_MARKETING,
        ]);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.store'), [
            'admin_angsuran_percent_override' => 0.75,
        ]);

        $response->assertForbidden();
    }

    public function test_test_user_cannot_edit_kb_pricing_overrides(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'role' => User::ROLE_MARKETING,
        ]);

        $this->assertFalse($user->canEditKbPricing());

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'rate_percent_override' => 1.25,
        ]);

        $response->assertForbidden();
    }

    public function test_marketing_user_can_calculate_with_default_pricing_values_when_no_override_is_edited(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => User::ROLE_MARKETING,
        ]);

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

    public function test_supervisor_user_can_access_override_fields_and_reaches_validation_layer(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => User::ROLE_SUPERVISOR,
        ]);

        $response = $this->actingAs($user)->postJson(route('kb_simulasi.calculate'), [
            'rate_percent_override' => 1.25,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal_lahir']);
    }
}