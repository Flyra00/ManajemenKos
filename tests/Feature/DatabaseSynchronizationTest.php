<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSynchronizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_booking_with_deposit_50_percent_bills_half_and_generates_remaining_invoice_on_verification(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = Room::create([
            'room_number' => 'SYNC-01',
            'floor'       => '1',
            'price'       => 1000000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        // Calon tenant memesan kamar 1 bulan dengan opsi deposit_50
        $response = $this->post(route('public.rooms.book', $room), [
            'name'            => 'Penyewa Deposit',
            'email'           => 'deposit@test.com',
            'phone'           => '081234567899',
            'password'        => 'password123',
            'start_date'      => now()->addDays(2)->toDateString(),
            'duration_months' => 1,
            'payment_type'    => 'deposit_50',
        ]);

        $response->assertSessionHasNoErrors();

        // 1. Cek Kontrak: deposit_amount tercatat 50% (500.000)
        $lease = Lease::where('room_id', $room->id)->first();
        $this->assertNotNull($lease);
        $this->assertEquals(500000, (float) $lease->deposit_amount);

        // 2. Cek Invoice Pertama: Jumlahnya hanya Rp 500.000 (bukan Rp 1.000.000!)
        $depositInvoice = Payment::where('lease_id', $lease->id)->first();
        $this->assertNotNull($depositInvoice);
        $this->assertEquals(500000, (float) $depositInvoice->amount);
        $this->assertEquals('unpaid', $depositInvoice->status);

        // 3. Admin memverifikasi pembayaran deposit
        $verifyRes = $this->actingAs($admin)->put(route('payments.verify', $depositInvoice));
        $verifyRes->assertSessionHas('success');


        // Deposit invoice status jadi paid
        $depositInvoice->refresh();
        $this->assertEquals('paid', $depositInvoice->status);

        // 4. SINKRONISASI: Sistem otomatis menerbitkan invoice pelunasan untuk sisa 50% (Rp 500.000)
        $remainingInvoice = Payment::where('lease_id', $lease->id)
            ->where('id', '!=', $depositInvoice->id)
            ->first();

        $this->assertNotNull($remainingInvoice, 'Invoice pelunasan sisa harus otomatis terbit');
        $this->assertEquals(500000, (float) $remainingInvoice->amount, 'Nomor invoice sisa harus berkurang 50% sesuai deposit!');
        $this->assertEquals('unpaid', $remainingInvoice->status);
    }

    public function test_completed_maintenance_with_cost_synchronizes_to_expenses(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('tenant');
        $tenant = Tenant::create(['user_id' => $user->id, 'ktp_number' => '1234567890123456']);

        $room = Room::create([
            'room_number' => 'MNT-01',
            'floor'       => '1',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        // Buat maintenance dengan status completed dan cost 250.000
        $response = $this->actingAs($admin)->post(route('maintenance.store'), [
            'title'       => 'Perbaikan Pipa Bocor',
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'priority'    => 'high',
            'status'      => 'completed',
            'cost'        => 250000,
            'description' => 'Pipa kamar mandi diganti baru',
        ]);

        $response->assertSessionHas('success');

        // Verifikasi otomatis tercatat di tabel expenses
        $expense = Expense::where('amount', 250000)->first();
        $this->assertNotNull($expense, 'Expense harus otomatis tercatat saat maintenance selesai dengan biaya');
        $this->assertStringContainsString('Perbaikan Pipa Bocor', $expense->title);
    }

    public function test_overdue_payments_are_automatically_synchronized(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('tenant');
        $tenant = Tenant::create(['user_id' => $user->id, 'ktp_number' => '1234567890123457']);

        $room = Room::create([
            'room_number' => 'OVD-01',
            'floor'       => '1',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->subMonth()->toDateString(),
            'end_date'       => now()->addMonths(5)->toDateString(),
            'monthly_price'  => 1000000,
            'deposit_amount' => 0,
            'status'         => 'active',
        ]);

        $pastPayment = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-OVD-TEST-01',
            'amount'         => 1000000,
            'billing_period' => now()->subMonth()->startOfMonth()->toDateString(),
            'due_date'       => now()->subDays(5)->toDateString(), // Sudah lewat 5 hari lalu
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);

        // Saat mengakses index pembayaran, sistem menyinkronkan status overdue secara otomatis
        $this->actingAs($admin)->get(route('payments.index'));

        $pastPayment->refresh();
        $this->assertEquals('overdue', $pastPayment->status, 'Status tagihan lewat jatuh tempo harus otomatis overdue');
    }

    public function test_cannot_delete_tenant_with_unpaid_payments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('tenant');
        $tenant = Tenant::create(['user_id' => $user->id, 'ktp_number' => '1234567890123458']);

        $room = Room::create([
            'room_number' => 'DEL-01',
            'floor'       => '1',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->subMonth()->toDateString(),
            'end_date'       => now()->toDateString(),
            'monthly_price'  => 1000000,
            'deposit_amount' => 0,
            'status'         => 'completed', // Kontrak sudah selesai
        ]);

        Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-UNPAID-DEL',
            'amount'         => 1000000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDay()->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid', // Tapi masih ada tagihan belum dibayar!
        ]);

        // Coba hapus penghuni
        $res = $this->actingAs($admin)->delete(route('tenants.destroy', $tenant));
        $res->assertSessionHas('error');

        // Pastikan tenant tidak terhapus
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id]);
    }

    public function test_room_recovers_from_maintenance_status_when_repairs_are_completed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $user->assignRole('tenant');
        $tenant = Tenant::create(['user_id' => $user->id, 'ktp_number' => '1234567890123459']);

        $room = Room::create([
            'room_number' => 'REC-01',
            'floor'       => '1',
            'price'       => 1000000,
            'status'      => 'maintenance', // Kamar sedang perbaikan
            'is_active'   => true,
        ]);

        $maintenance = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Ganti Saklar Listrik',
            'description' => 'Saklar konslet',
            'priority'    => 'medium',
            'status'      => 'in_progress',
            'reported_at' => now(),
        ]);

        // Teknisi/admin menyelesaikan perbaikan
        $res = $this->actingAs($admin)->put(route('maintenance.update', $maintenance), [
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Ganti Saklar Listrik',
            'description' => 'Saklar konslet sudah diganti baru',
            'priority'    => 'medium',
            'status'      => 'completed',
            'cost'        => 50000,
        ]);

        $res->assertSessionHas('success');

        // Status kamar otomatis pulih kembali ke available!
        $room->refresh();
        $this->assertEquals('available', $room->status, 'Kamar harus otomatis kembali available setelah perbaikan selesai');
    }
}

