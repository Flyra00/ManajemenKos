<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaseLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function createFixture(): array
    {
        $tenantUser = User::factory()->create([
            'name'  => 'Ahmad Fauzi',
            'phone' => '081234567890',
        ]);
        $tenantUser->assignRole('tenant');

        $tenant = Tenant::create([
            'user_id'                 => $tenantUser->id,
            'ktp_number'              => '3201123456789001',
            'job'                     => 'Karyawan Swasta',
            'emergency_name'    => 'Bapak Hasan',
            'emergency_contact' => '081987654321',
        ]);

        $room = Room::create([
            'room_number' => 'B-105',
            'price'       => 1500000,
            'floor'       => 1,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->subMonths(3)->startOfMonth()->toDateString(),
            'end_date'       => now()->addMonths(1)->endOfMonth()->toDateString(),
            'm_price'        => 1500000,
            'deposit_amount' => 500000,
            'status'         => 'active',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $owner = User::factory()->create();
        $owner->assignRole('owner');

        return compact('tenantUser', 'tenant', 'room', 'lease', 'admin', 'owner');
    }

    public function test_admin_can_renew_active_lease_with_predefined_duration(): void
    {
        $fixture = $this->createFixture();
        $lease = $fixture['lease'];
        $oldEndDate = $lease->end_date->copy();

        $response = $this->actingAs($fixture['admin'])
            ->post(route('leases.renew', $lease), [
                'duration_type'    => '3_months',
                'monthly_price'    => 1550000,
                'generate_invoice' => 0,
                'renewal_note'     => 'Perpanjangan sewa kuartal',
            ]);

        $response->assertRedirect(route('leases.show', $lease));
        $response->assertSessionHas('success');

        $lease->refresh();
        $this->assertEquals(1, $lease->renewal_count);
        $this->assertNotNull($lease->last_renewed_at);
        $this->assertEquals(1550000, (int) $lease->monthly_price);
        $this->assertEquals($oldEndDate->addMonths(3)->toDateString(), $lease->end_date->toDateString());
        $this->assertEquals('active', $lease->status);
        $this->assertEquals('occupied', $lease->room->fresh()->status);
        $this->assertStringContainsString('Perpanjangan sewa kuartal', $lease->note);
    }

    public function test_admin_can_renew_lease_with_automatic_invoice_generation(): void
    {
        $fixture = $this->createFixture();
        $lease = $fixture['lease'];

        $initialPaymentCount = Payment::where('lease_id', $lease->id)->count();

        $response = $this->actingAs($fixture['admin'])
            ->post(route('leases.renew', $lease), [
                'duration_type'    => '1_month',
                'monthly_price'    => 1500000,
                'generate_invoice' => 1,
            ]);

        $response->assertRedirect(route('leases.show', $lease));

        $this->assertGreaterThan($initialPaymentCount, Payment::where('lease_id', $lease->id)->count());
        $newPayment = Payment::where('lease_id', $lease->id)->latest()->first();
        $this->assertEquals(1500000, (int) $newPayment->amount);
    }

    public function test_admin_can_renew_lease_with_custom_date(): void
    {
        $fixture = $this->createFixture();
        $lease = $fixture['lease'];
        $customDate = now()->addMonths(8)->endOfMonth()->toDateString();

        $response = $this->actingAs($fixture['admin'])
            ->post(route('leases.renew', $lease), [
                'duration_type' => 'custom',
                'new_end_date'  => $customDate,
                'monthly_price' => 1500000,
            ]);

        $response->assertRedirect(route('leases.show', $lease));

        $lease->refresh();
        $this->assertEquals($customDate, $lease->end_date->toDateString());
    }

    public function test_admin_can_checkout_lease_and_release_room_to_available(): void
    {
        $fixture = $this->createFixture();
        $lease = $fixture['lease'];
        $room = $fixture['room'];

        $checkoutDate = now()->toDateString();

        $response = $this->actingAs($fixture['admin'])
            ->post(route('leases.checkout', $lease), [
                'checkout_date'     => $checkoutDate,
                'room_condition'    => 'good',
                'deposit_deduction' => 0,
                'deposit_refunded'  => 500000,
                'room_status'       => 'available',
                'record_expense'    => 1,
                'checkout_notes'    => 'Kamar bersih dan kunci diserahkan lengkap.',
            ]);

        $response->assertRedirect(route('leases.show', $lease));
        $response->assertSessionHas('success');

        $lease->refresh();
        $this->assertEquals('completed', $lease->status);
        $this->assertEquals($checkoutDate, $lease->checkout_date->toDateString());
        $this->assertEquals('good', $lease->room_condition);
        $this->assertEquals(0, (float) $lease->deposit_deduction);
        $this->assertEquals(500000, (float) $lease->deposit_refunded);

        // Kamar harus kembali 'available'
        $this->assertEquals('available', $room->fresh()->status);

        // Pembukuan pengeluaran (expense) harus otomatis tercatat
        $expense = Expense::where('amount', 500000)->first();
        $this->assertNotNull($expense);
        $this->assertEquals($checkoutDate, $expense->expense_date->toDateString());
    }

    public function test_checkout_with_damage_deduction_and_maintenance_room_status(): void
    {
        $fixture = $this->createFixture();
        $lease = $fixture['lease'];
        $room = $fixture['room'];

        $checkoutDate = now()->toDateString();

        $response = $this->actingAs($fixture['admin'])
            ->post(route('leases.checkout', $lease), [
                'checkout_date'     => $checkoutDate,
                'room_condition'    => 'damaged',
                'deposit_deduction' => 150000,
                'deposit_refunded'  => 350000,
                'room_status'       => 'maintenance',
                'record_expense'    => 1,
                'checkout_notes'    => 'Pintu kamar mandi rusak perlu diperbaiki.',
            ]);

        $response->assertRedirect(route('leases.show', $lease));

        $lease->refresh();
        $this->assertEquals('completed', $lease->status);
        $this->assertEquals(150000, (float) $lease->deposit_deduction);
        $this->assertEquals(350000, (float) $lease->deposit_refunded);

        // Kamar harus berstatus 'maintenance'
        $this->assertEquals('maintenance', $room->fresh()->status);

        // Expense tercatat sebesar sisa refund deposit 350000
        $this->assertDatabaseHas('expenses', [
            'amount' => 350000,
        ]);
    }

    public function test_checkout_receipt_can_be_viewed(): void
    {
        $fixture = $this->createFixture();
        $lease = $fixture['lease'];

        // Lakukan checkout terlebih dahulu
        $lease->update([
            'status'            => 'completed',
            'checkout_date'     => now()->toDateString(),
            'room_condition'    => 'good',
            'deposit_deduction' => 50000,
            'deposit_refunded'  => 450000,
            'checkout_notes'    => 'Pemeriksaan selesai.',
        ]);

        $response = $this->actingAs($fixture['admin'])
            ->get(route('leases.checkout-receipt', $lease));

        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA SERAH TERIMA KAMAR');
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('450.000');
        $response->assertSee('Empat Ratus Lima Puluh Ribu Rupiah');
    }

    public function test_owner_cannot_renew_or_checkout_leases(): void
    {
        $fixture = $this->createFixture();
        $lease = $fixture['lease'];

        // Owner coba renew
        $renewResponse = $this->actingAs($fixture['owner'])
            ->post(route('leases.renew', $lease), [
                'duration_type' => '3_months',
                'monthly_price' => 1500000,
            ]);
        $renewResponse->assertStatus(403);

        // Owner coba checkout
        $checkoutResponse = $this->actingAs($fixture['owner'])
            ->post(route('leases.checkout', $lease), [
                'checkout_date'  => now()->toDateString(),
                'room_condition' => 'good',
                'room_status'    => 'available',
            ]);
        $checkoutResponse->assertStatus(403);
    }
}
