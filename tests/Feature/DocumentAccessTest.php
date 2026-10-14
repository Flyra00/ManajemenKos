<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresi IDOR: kuitansi, kontrak sewa, dan berita acara check-out hanya boleh
 * diakses oleh penyewa pemilik dokumen tersebut atau pengelola (admin/owner/staff).
 */
class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private static int $seq = 0;

    /**
     * Bangun 1 penyewa + kamar + kontrak + invoice. Mengembalikan [lease, payment, user].
     */
    private function createTenantWithLease(string $roomNumber, string $invoiceNumber): array
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');

        self::$seq++;

        $tenant = Tenant::create([
            'user_id'    => $user->id,
            'ktp_number' => '3201' . str_pad((string) self::$seq, 12, '0', STR_PAD_LEFT),
        ]);

        $room = Room::create([
            'room_number' => $roomNumber,
            'price'       => 1500000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id'  => $tenant->id,
            'room_id'    => $room->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date'   => now()->addDays(20)->toDateString(),
            'm_price'    => 1500000,
            'status'     => 'active',
        ]);

        $payment = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => $invoiceNumber,
            'amount'         => 1500000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDays(5)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'paid',
        ]);

        return [$lease, $payment, $user];
    }

    public function test_guests_are_redirected_to_login(): void
    {
        [$lease, $payment] = $this->createTenantWithLease('SEC-00', 'INV-SEC-0000');

        $this->get(route('leases.contract', $lease))->assertRedirect(route('login'));
        $this->get(route('leases.checkout-receipt', $lease))->assertRedirect(route('login'));
        $this->get(route('payments.receipt', $payment))->assertRedirect(route('login'));
    }

    public function test_tenant_cannot_view_another_tenants_lease_contract(): void
    {
        [$lease, , ] = $this->createTenantWithLease('SEC-01', 'INV-SEC-0001');
        [, , $otherUser] = $this->createTenantWithLease('SEC-02', 'INV-SEC-0002');

        $this->actingAs($otherUser)
            ->get(route('leases.contract', $lease))
            ->assertForbidden();
    }

    public function test_tenant_cannot_view_another_tenants_checkout_receipt(): void
    {
        [$lease, , ] = $this->createTenantWithLease('SEC-03', 'INV-SEC-0003');
        [, , $otherUser] = $this->createTenantWithLease('SEC-04', 'INV-SEC-0004');

        $this->actingAs($otherUser)
            ->get(route('leases.checkout-receipt', $lease))
            ->assertForbidden();
    }

    public function test_tenant_cannot_view_another_tenants_payment_receipt(): void
    {
        [, $payment, ] = $this->createTenantWithLease('SEC-05', 'INV-SEC-0005');
        [, , $otherUser] = $this->createTenantWithLease('SEC-06', 'INV-SEC-0006');

        $this->actingAs($otherUser)
            ->get(route('payments.receipt', $payment))
            ->assertForbidden();
    }

    public function test_tenant_can_view_own_documents(): void
    {
        [$lease, $payment, $user] = $this->createTenantWithLease('SEC-07', 'INV-SEC-0007');

        $this->actingAs($user)->get(route('leases.contract', $lease))->assertOk();
        $this->actingAs($user)->get(route('leases.checkout-receipt', $lease))->assertOk();
        $this->actingAs($user)->get(route('payments.receipt', $payment))->assertOk();
    }

    public function test_management_can_view_documents(): void
    {
        [$lease, $payment] = $this->createTenantWithLease('SEC-08', 'INV-SEC-0008');

        foreach (['admin', 'owner', 'staff'] as $role) {
            $manager = User::factory()->create();
            $manager->assignRole($role);

            $this->actingAs($manager)->get(route('leases.contract', $lease))->assertOk();
            $this->actingAs($manager)->get(route('leases.checkout-receipt', $lease))->assertOk();
            $this->actingAs($manager)->get(route('payments.receipt', $payment))->assertOk();
        }
    }

    public function test_tenant_cannot_view_another_tenants_payment_proof(): void
    {
        Storage::fake('local');
        $stored = UploadedFile::fake()->image('bukti1.jpg')->store('payments/proofs', 'local');

        [, $payment, ] = $this->createTenantWithLease('SEC-09', 'INV-SEC-0009');
        $payment->update(['proof_img' => $stored]);

        [, , $otherUser] = $this->createTenantWithLease('SEC-10', 'INV-SEC-0010');

        $this->actingAs($otherUser)
            ->get(route('payments.proof', $payment))
            ->assertForbidden();
    }

    public function test_tenant_can_view_own_payment_proof(): void
    {
        Storage::fake('local');
        $stored = UploadedFile::fake()->image('bukti2.jpg')->store('payments/proofs', 'local');

        [, $payment, $user] = $this->createTenantWithLease('SEC-11', 'INV-SEC-0011');
        $payment->update(['proof_img' => $stored]);

        $this->actingAs($user)
            ->get(route('payments.proof', $payment))
            ->assertOk();
    }

    public function test_tenant_cannot_view_another_tenants_maintenance_image(): void
    {
        Storage::fake('local');
        $stored = UploadedFile::fake()->image('kerusakan1.jpg')->store('maintenance', 'local');

        [$lease, , ] = $this->createTenantWithLease('SEC-12', 'INV-SEC-0012');
        $maint = MaintenanceRequest::create([
            'room_id'     => $lease->room_id,
            'tenant_id'   => $lease->tenant_id,
            'title'       => 'Pintu Rusak',
            'description' => 'Engsel lepas',
            'priority'    => 'medium',
            'status'      => 'reported',
            'image_path'  => $stored,
            'reported_at' => now(),
        ]);

        [, , $otherUser] = $this->createTenantWithLease('SEC-13', 'INV-SEC-0013');

        $this->actingAs($otherUser)
            ->get(route('maintenance.image', $maint))
            ->assertForbidden();
    }

    public function test_tenant_can_view_own_maintenance_image(): void
    {
        Storage::fake('local');
        $stored = UploadedFile::fake()->image('kerusakan2.jpg')->store('maintenance', 'local');

        [$lease, , $user] = $this->createTenantWithLease('SEC-14', 'INV-SEC-0014');
        $maint = MaintenanceRequest::create([
            'room_id'     => $lease->room_id,
            'tenant_id'   => $lease->tenant_id,
            'title'       => 'Kran Bocor',
            'description' => 'Menetes terus',
            'priority'    => 'low',
            'status'      => 'reported',
            'image_path'  => $stored,
            'reported_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('maintenance.image', $maint))
            ->assertOk();
    }
}
