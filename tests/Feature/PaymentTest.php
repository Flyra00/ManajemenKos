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
        Storage::fake('public');
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
        Storage::disk('public')->assertExists($payment->proof_img);
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
}

