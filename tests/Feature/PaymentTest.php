<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function createLeaseFixture(): Lease
    {
        $tenantUser = User::factory()->create(['name' => 'Fajar Pratama']);
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201556677889900',
        ]);
        $room = Room::create([
            'room_number' => 'P-101',
            'price' => 1500000,
            'status' => 'occupied',
        ]);

        return Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 1500000,
            'status' => 'active',
        ]);
    }

    public function test_payments_index_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-2026-0001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'bank_tf',
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('payments.index'));

        $response->assertOk();
        $response->assertSee('INV-2026-0001');
        $response->assertSee('Fajar Pratama');
    }

    public function test_payments_can_be_filtered_by_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-PAID-001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'bank_tf',
            'status' => 'paid',
        ]);

        Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-UNPAID-002',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'cash',
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->get(route('payments.index', ['status' => 'paid']));

        $response->assertOk();
        $response->assertSee('INV-PAID-001');
        $response->assertDontSee('INV-UNPAID-002');
    }

    public function test_payments_create_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->createLeaseFixture();

        $response = $this->actingAs($admin)->get(route('payments.create'));

        $response->assertOk();
        $response->assertSee('Buat Tagihan / Pembayaran');
    }

    public function test_payment_can_be_stored_with_proof_image(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();
        $proof = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->actingAs($admin)->post(route('payments.store'), [
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-2026-0003',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_date' => '2026-09-05',
            'payment_method' => 'bank_tf',
            'status' => 'paid',
            'proof_img' => $proof,
            'notes' => 'Transfer via BCA',
        ]);

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'invoice_number' => 'INV-2026-0003',
            'amount' => 1500000,
            'status' => 'paid',
            'verified_by' => $admin->id,
        ]);

        $payment = Payment::where('invoice_number', 'INV-2026-0003')->first();
        $this->assertNotNull($payment->proof_img);
        Storage::disk('local')->assertExists($payment->proof_img);
    }

    public function test_payment_invoice_number_must_be_unique(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-DUP-0001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'bank_tf',
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->post(route('payments.store'), [
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-DUP-0001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'cash',
            'status' => 'unpaid',
        ]);

        $response->assertSessionHasErrors('invoice_number');
    }

    public function test_payment_show_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        $payment = Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-SHOW-0001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('payments.show', $payment));

        $response->assertOk();
        $response->assertSee('INV-SHOW-0001');
    }

    public function test_payment_edit_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        $payment = Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-EDIT-0001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'cash',
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->get(route('payments.edit', $payment));

        $response->assertOk();
        $response->assertSee('Edit Invoice INV-EDIT-0001');
    }

    public function test_payment_can_be_updated_and_verified(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        $payment = Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-UPD-0001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'bank_tf',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('payments.update', $payment), [
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-UPD-0001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'bank_tf',
            'status' => 'paid',
            'notes' => 'Telah diverifikasi lunas',
        ]);

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals($admin->id, $payment->verified_by);
        $this->assertNotNull($payment->payment_date);
    }

    public function test_admin_verification_extends_active_lease_and_counts_renewal(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        $payment = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-VERIFY-EXT',
            'amount'         => 1500000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDays(5)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);

        $this->actingAs($admin)->put(route('payments.verify', $payment))->assertSessionHas('success');

        // Verifikasi admin harus menghasilkan sinkronisasi yang sama dengan Midtrans:
        // kontrak aktif, masa aktif +30 hari, dan pencatatan perpanjangan.
        $lease->refresh();
        $this->assertSame('active', $lease->status);
        $this->assertEquals(1, (int) $lease->renewal_count);
        $this->assertNotNull($lease->last_renewed_at);
        $this->assertEquals(now()->addDays(30)->toDateString(), $lease->end_date->toDateString());
    }

    public function test_verifying_an_already_paid_invoice_does_not_extend_lease_again(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201776655443322',
        ]);
        $room = Room::create([
            'room_number' => 'IDEMP-01',
            'price'       => 1500000,
            'status'      => 'occupied',
        ]);

        $initialEnd = now()->addDays(10)->startOfDay();
        $lease = Lease::create([
            'tenant_id'  => $tenant->id,
            'room_id'    => $room->id,
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date'   => $initialEnd->toDateString(),
            'm_price'    => 1500000,
            'status'     => 'active',
        ]);

        $payment = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-IDEMP-001',
            'amount'         => 1500000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDays(5)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);

        // Verifikasi pertama: masa aktif +30 hari, renewal_count = 1
        $this->actingAs($admin)->put(route('payments.verify', $payment))->assertSessionHas('success');

        $firstEnd = $initialEnd->copy()->addDays(30)->toDateString();
        $this->assertSame($firstEnd, $lease->fresh()->end_date->toDateString());
        $this->assertEquals(1, (int) $lease->fresh()->renewal_count);

        // Verifikasi kedua (klik dobel / submit ulang) tidak boleh mengubah apa pun
        $this->actingAs($admin)->put(route('payments.verify', $payment))->assertSessionHas('info');

        $this->assertSame($firstEnd, $lease->fresh()->end_date->toDateString());
        $this->assertEquals(1, (int) $lease->fresh()->renewal_count);
    }

    public function test_verifying_deposit_invoice_twice_does_not_create_second_remaining_invoice(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201887766554433',
        ]);
        $room = Room::create([
            'room_number' => 'IDEMP-02',
            'price'       => 1000000,
            'status'      => 'available',
        ]);

        // Kontrak booking dengan uang muka 50% (Rp 500.000 dari total Rp 1.000.000)
        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->toDateString(),
            'end_date'       => now()->addMonth()->toDateString(),
            'm_price'        => 1000000,
            'deposit_amount' => 500000,
            'status'         => 'pending',
        ]);

        $depositInvoice = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-IDEMP-DEP',
            'amount'         => 500000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDay()->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);

        $this->actingAs($admin)->put(route('payments.verify', $depositInvoice))->assertSessionHas('success');

        // Invoice sisa (Rp 500.000) terbit tepat sekali
        $this->assertSame(2, $lease->payments()->count());

        // Verifikasi ulang tidak boleh menerbitkan invoice sisa kedua
        $this->actingAs($admin)->put(route('payments.verify', $depositInvoice))->assertSessionHas('info');

        $this->assertSame(2, $lease->payments()->count());
        $this->assertSame('active', $lease->fresh()->status);
    }

    public function test_info_flash_is_rendered_as_toast(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        $payment = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-INFO-0001',
            'amount'         => 1500000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDays(5)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'paid',
        ]);

        // Halaman detail pembayaran memakai layout utama, yang kini merender
        // flash 'info' sebagai toast (dipakai oleh guard verifikasi ganda).
        $this->actingAs($admin)
            ->withSession(['info' => 'Pesan uji info tampil'])
            ->get(route('payments.show', $payment))
            ->assertOk()
            ->assertSee('Pesan uji info tampil');
    }

    public function test_payment_can_be_deleted_safely(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $lease = $this->createLeaseFixture();

        $payment = Payment::create([
            'lease_id' => $lease->id,
            'invoice_number' => 'INV-DEL-0001',
            'amount' => 1500000,
            'billing_period' => '2026-09-01',
            'due_date' => '2026-09-10',
            'payment_method' => 'cash',
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->delete(route('payments.destroy', $payment));

        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_artisan_command_syncs_overdue_payments(): void
    {
        $lease = $this->createLeaseFixture();
        $pastPayment = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-CMD-OVD-01',
            'amount'         => 1000000,
            'billing_period' => now()->subMonth()->startOfMonth()->toDateString(),
            'due_date'       => now()->subDays(5)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);

        $this->artisan('kos:sync-overdue-payments')->assertExitCode(0);

        $this->assertEquals('overdue', $pastPayment->fresh()->status);
    }
}

