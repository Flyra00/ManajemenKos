<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DocumentExportTest extends TestCase
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
            'name'  => 'Budi Dharmawan',
            'phone' => '081234567890',
        ]);

        $tenant = Tenant::create([
            'user_id'                 => $tenantUser->id,
            'ktp_number'              => '3201998877665544',
            'job'                     => 'Programmer',
            'emergency_name'    => 'Ibu Siti',
            'emergency_contact' => '081987654321',
        ]);

        $room = Room::create([
            'room_number' => 'C-302',
            'price'       => 1600000,
            'floor'       => 3,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->startOfMonth()->toDateString(),
            'end_date'       => now()->addMonths(12)->toDateString(),
            'm_price'        => 1600000,
            'deposit_amount' => 500000,
            'status'         => 'active',
        ]);

        $payment = Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-202609-001',
            'amount'         => 1600000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDays(7)->toDateString(),
            'payment_date'   => now(),
            'payment_method' => 'bank_tf',
            'status'         => 'paid',
            'notes'          => 'Pembayaran Sewa Bulan September',
        ]);

        return [$tenantUser, $room, $lease, $payment];
    }

    public function test_payment_receipt_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [$tenantUser, $room, $lease, $payment] = $this->createFixture();

        $response = $this->actingAs($admin)->get(route('payments.receipt', $payment));

        $response->assertOk();
        $response->assertSee('INV-202609-001');
        $response->assertSee('Budi Dharmawan');
        $response->assertSee('Kamar C-302');
        $response->assertSee('L U N A S');
        $response->assertSee('Satu Juta Enam Ratus Ribu Rupiah');
    }

    public function test_public_invoice_receipt_can_be_accessed_by_guests_via_signed_url(): void
    {
        [$tenantUser, $room, $lease, $payment] = $this->createFixture();

        $response = $this->get($payment->public_receipt_url);

        $response->assertOk();
        $response->assertSee('INV-202609-001');
        $response->assertSee('Budi Dharmawan');
        $response->assertSee('L U N A S');
    }

    public function test_lease_contract_spk_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [$tenantUser, $room, $lease, $payment] = $this->createFixture();

        $response = $this->actingAs($admin)->get(route('leases.contract', $lease));

        $response->assertOk();
        $response->assertSee('SURAT PERJANJIAN SEWA MENYEWA KAMAR KOS');
        $response->assertSee('Budi Dharmawan');
        $response->assertSee('3201998877665544');
        $response->assertSee('Kamar C-302');
        $response->assertSee('Pasal 1 — Objek Sewa dan Fasilitas');
        $response->assertSee('Pasal 2 — Jangka Waktu Sewa');
        $response->assertSee('Pasal 3 — Tarif Sewa dan Uang Jaminan (Deposit)');
    }

    public function test_financial_report_can_be_exported_to_csv(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [$tenantUser, $room, $lease, $payment] = $this->createFixture();

        $response = $this->actingAs($admin)->get(route('reports.export', [
            'tab'   => 'income',
            'year'  => now()->year,
            'month' => 'all',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="laporan-kosfly-income-', $response->headers->get('Content-Disposition'));

        // Capture streamed content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        $this->assertStringContainsString('Invoice', $content);
        $this->assertStringContainsString('INV-202609-001', $content);
        $this->assertStringContainsString('Budi Dharmawan', $content);
    }

    public function test_unsigned_invoice_url_is_rejected(): void
    {
        [$tenantUser, $room, $lease, $payment] = $this->createFixture();

        // Tanpa tanda tangan, nomor invoice tidak boleh membuka apa pun.
        $this->get(route('invoices.show', $payment->invoice_number))->assertForbidden();
        $this->get(route('invoices.receipt', $payment->invoice_number))->assertForbidden();
    }

    public function test_public_invoice_page_can_be_accessed_by_guests_via_signed_url(): void
    {
        [$tenantUser, $room, $lease, $payment] = $this->createFixture();

        $response = $this->get($payment->public_url);

        $response->assertOk();
        $response->assertSee('INV-202609-001');
        $response->assertSee('Budi Dharmawan');
    }

    public function test_unsigned_payment_upload_is_rejected(): void
    {
        [$tenantUser, $room, $lease, $payment] = $this->createFixture();

        $response = $this->post(route('invoices.pay', $payment->invoice_number), [
            'payment_method' => 'bank_tf',
            'proof_image'    => \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg'),
        ]);

        $response->assertForbidden();
    }

    public function test_terbilang_helper_converts_numbers_accurately(): void
    {
        $service = app(BillingService::class);

        $this->assertEquals('Satu Juta Lima Ratus Ribu Rupiah', $service->terbilang(1500000));
        $this->assertEquals('Dua Ratus Lima Puluh Ribu Rupiah', $service->terbilang(250000));
        $this->assertEquals('Tujuh Puluh Lima Ribu Rupiah', $service->terbilang(75000));
        $this->assertEquals('Nol Rupiah', $service->terbilang(0));
    }
}
