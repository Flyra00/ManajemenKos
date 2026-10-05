<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TenantBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        Storage::fake('public');
    }

    public function test_public_user_can_view_available_rooms_catalog(): void
    {
        $availableRoom = Room::create([
            'room_number' => 'A-01',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $occupiedRoom = Room::create([
            'room_number' => 'B-02',
            'floor'       => 2,
            'price'       => 1800000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $response = $this->get(route('public.rooms.index'));

        $response->assertOk();
        $response->assertSee('A-01');
        $response->assertSee('1.500.000');
        // Kamar yang terisi tidak boleh muncul di katalog sewa publik
        $response->assertDontSee('B-02');
    }

    public function test_public_user_can_view_room_detail(): void
    {
        $facility = Facility::create(['name' => 'Kamar Mandi Dalam']);

        $room = Room::create([
            'room_number' => 'C-03',
            'floor'       => 1,
            'price'       => 1600000,
            'status'      => 'available',
            'is_active'   => true,
            'description' => 'Kamar luas dan bersih',
        ]);
        $room->facilities()->attach($facility);

        $response = $this->get(route('public.rooms.show', $room));

        $response->assertOk();
        $response->assertSee('Kamar C-03');
        $response->assertSee('Kamar Mandi Dalam');
        $response->assertSee('Formulir Sewa Kamar');
    }

    public function test_room_cannot_be_booked_twice_while_booking_awaits_payment(): void
    {
        $room = Room::create([
            'room_number' => 'DBL-01',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        // Booking pertama berhasil
        $this->post(route('public.rooms.book', $room), [
            'name'            => 'Penyewa Pertama',
            'email'           => 'pertama@example.com',
            'phone'           => '081200000001',
            'ktp_number'      => '3201012345671001',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ])->assertSessionHas('success');

        // Booking kedua untuk kamar yang sama harus ditolak (kamar masih dipegang booking pertama)
        $response = $this->post(route('public.rooms.book', $room), [
            'name'            => 'Penyewa Kedua',
            'email'           => 'kedua@example.com',
            'phone'           => '081200000002',
            'ktp_number'      => '3201012345671002',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        $response->assertSessionHas('error');

        // Hanya boleh ada satu kontrak untuk kamar ini, dan akun penyewa kedua tidak dibuat.
        $this->assertSame(1, Lease::where('room_id', $room->id)->count());
        $this->assertDatabaseMissing('users', ['email' => 'kedua@example.com']);
    }

    public function test_expired_pending_booking_is_released_and_room_can_be_rebooked(): void
    {
        $room = Room::create([
            'room_number' => 'EXP-01',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        // Booking lama yang ditinggalkan: pending, invoicenya sudah lewat jatuh tempo.
        $user = User::factory()->create();
        $user->assignRole('tenant');
        $tenant = Tenant::create(['user_id' => $user->id, 'ktp_number' => '3201012345671099']);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->subDays(3)->toDateString(),
            'end_date'       => now()->addDays(57)->toDateString(),
            'monthly_price'  => 1500000,
            'deposit_amount' => 0,
            'status'         => 'pending',
        ]);

        Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-EXP-001',
            'amount'         => 1500000,
            'billing_period' => now()->subDays(3)->startOfMonth()->toDateString(),
            'due_date'       => now()->subDays(2)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'unpaid',
        ]);

        // Booking baru untuk kamar yang sama harus berhasil
        $response = $this->post(route('public.rooms.book', $room), [
            'name'            => 'Penyewa Pengganti',
            'email'           => 'pengganti@example.com',
            'phone'           => '081200000012',
            'ktp_number'      => '3201012345671012',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        $response->assertSessionHas('success');

        // Booking lama dibebaskan (dibatalkan), booking baru tercatat.
        $this->assertSame('cancelled', $lease->fresh()->status);
        $this->assertSame(2, Lease::where('room_id', $room->id)->count());
    }

    public function test_room_with_pending_booking_is_hidden_from_public_catalog(): void
    {
        $room = Room::create([
            'room_number' => 'HID-01',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $this->post(route('public.rooms.book', $room), [
            'name'            => 'Penyewa Holding',
            'email'           => 'holding@example.com',
            'phone'           => '081200000021',
            'ktp_number'      => '3201012345671021',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ])->assertSessionHas('success');

        // Status kamar masih 'available' (belum dibayar), tapi tidak boleh tampil di katalog publik.
        $this->assertSame('available', $room->fresh()->status);

        $response = $this->get(route('public.rooms.index'));
        $response->assertOk();
        $response->assertDontSee('HID-01');
    }

    public function test_guest_can_book_room_and_receive_invoice_with_24h_due_date(): void
    {
        $room = Room::create([
            'room_number' => 'D-04',
            'floor'       => 2,
            'price'       => 1700000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $response = $this->post(route('public.rooms.book', $room), [
            'name'            => 'Ahmad Dani',
            'email'           => 'dani@example.com',
            'phone'           => '081234567899',
            'ktp_number'      => '3201012345670001',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        // Harus dibuat user tenant
        $this->assertDatabaseHas('users', [
            'email' => 'dani@example.com',
            'name'  => 'Ahmad Dani',
        ]);

        $user = User::where('email', 'dani@example.com')->first();
        $this->assertTrue($user->hasRole('tenant'));

        // Harus ada lease pending
        $this->assertDatabaseHas('leases', [
            'room_id' => $room->id,
            'status'  => 'pending',
        ]);

        // Harus terbit invoice dengan tenggat 1x24 jam
        $payment = Payment::latest()->first();
        $this->assertNotNull($payment);
        $this->assertEquals(1700000, $payment->amount);
        $this->assertEquals('unpaid', $payment->status);
        $this->assertEquals(now()->addDay()->toDateString(), $payment->due_date->toDateString());

        $response->assertRedirect($payment->public_url);
    }

    public function test_tenant_can_upload_payment_proof_to_invoice(): void
    {
        $room = Room::create([
            'room_number' => 'E-05',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $this->post(route('public.rooms.book', $room), [
            'name'            => 'Bambang',
            'email'           => 'bambang@example.com',
            'phone'           => '081233344455',
            'ktp_number'      => '3201012345670002',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        $payment = Payment::latest()->first();

        $file = UploadedFile::fake()->image('bukti_transfer.jpg');

        $response = $this->post(URL::signedRoute('invoices.pay', ['invoice_number' => $payment->invoice_number]), [
            'payment_method' => 'bank_tf',
            'proof_image'    => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('pending', $payment->status);
        $this->assertNotNull($payment->proof_img);
        Storage::disk('public')->assertExists($payment->proof_img);
    }

    public function test_tenant_can_pay_using_qris(): void
    {
        $room = Room::create([
            'room_number' => 'QRIS-01',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $this->post(route('public.rooms.book', $room), [
            'name'            => 'Sari QRIS',
            'email'           => 'sari@example.com',
            'phone'           => '081233344499',
            'ktp_number'      => '3201012345670003',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        $payment = Payment::latest()->first();
        $this->assertEquals('pending', $payment->lease->status);

        $file = UploadedFile::fake()->image('bukti_qris.jpg');

        $response = $this->post(URL::signedRoute('invoices.pay', ['invoice_number' => $payment->invoice_number]), [
            'payment_method' => 'qris',
            'proof_image'    => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals('qris', $payment->payment_method);
        $this->assertEquals('pending', $payment->status);
    }

    public function test_admin_can_verify_payment_and_room_becomes_occupied(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = Room::create([
            'room_number' => 'F-06',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $this->post(route('public.rooms.book', $room), [
            'name'            => 'Citra Kirana',
            'email'           => 'citra@example.com',
            'phone'           => '081299887766',
            'ktp_number'      => '3201012345670004',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        $payment = Payment::latest()->first();
        $this->assertEquals('available', $room->fresh()->status);
        $this->assertEquals('pending', $payment->lease->fresh()->status);

        // Admin memverifikasi pembayaran lunas
        $response = $this->actingAs($admin)->put(route('payments.verify', $payment));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status kamar harus resmi menjadi occupied
        $this->assertEquals('occupied', $room->fresh()->status);
        // Kontrak sewa aktif
        $this->assertEquals('active', $payment->lease->fresh()->status);
        // Pembayaran berstatus paid
        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertEquals($admin->id, $payment->fresh()->verified_by);
    }

    public function test_booking_fails_gracefully_when_phone_is_already_registered_to_another_user(): void
    {
        // Pengguna lama yang sudah punya nomor HP ini
        User::factory()->create([
            'email' => 'dafa@gmail.com',
            'phone' => '081317465707',
        ]);

        $room = Room::create([
            'room_number' => 'G-07',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        // Calon penyewa baru dengan email berbeda mencoba menggunakan nomor HP yang sama
        $response = $this->post(route('public.rooms.book', $room), [
            'name'            => 'DIMASSALIM',
            'email'           => 'dimas@gmail.com',
            'phone'           => '081317465707',
            'ktp_number'      => '3201012345670005',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertDatabaseMissing('users', ['email' => 'dimas@gmail.com']);
    }

    public function test_invoice_status_endpoint_returns_realtime_verification_status(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $room = Room::create([
            'room_number' => 'H-08',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $this->post(route('public.rooms.book', $room), [
            'name'            => 'Eko Santoso',
            'email'           => 'eko@example.com',
            'phone'           => '081234112233',
            'ktp_number'      => '3201012345670006',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        $payment = Payment::latest()->first();

        // 1. Cek sebelum verifikasi (status: pending/unpaid)
        $statusUrl = URL::signedRoute('invoices.status', ['invoice_number' => $payment->invoice_number]);
        $response = $this->getJson($statusUrl);
        $response->assertOk()
            ->assertJson([
                'is_paid' => false,
            ]);

        // 2. Admin memverifikasi pembayaran
        $this->actingAs($admin)->put(route('payments.verify', $payment));

        // 3. Cek kembali secara real-time via endpoint status
        $responseAfter = $this->getJson($statusUrl);
        $responseAfter->assertOk()
            ->assertJson([
                'status'  => 'paid',
                'is_paid' => true,
            ]);
    }

    public function test_booking_requires_valid_16_digit_ktp_number(): void
    {
        $room = Room::create([
            'room_number' => 'VAL-01',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        // 1. Kosong (tanpa KTP)
        $responseEmpty = $this->post(route('public.rooms.book', $room), [
            'name'            => 'Budi KTP',
            'email'           => 'budiktp@example.com',
            'phone'           => '081234567800',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);
        $responseEmpty->assertSessionHasErrors(['ktp_number']);

        // 2. Format salah (kurang dari 16 digit)
        $responseInvalid = $this->post(route('public.rooms.book', $room), [
            'name'            => 'Budi KTP',
            'email'           => 'budiktp@example.com',
            'phone'           => '081234567800',
            'ktp_number'      => '12345',
            'password'        => 'password123',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);
        $responseInvalid->assertSessionHasErrors(['ktp_number']);
    }

    public function test_authenticated_tenant_can_book_room_and_ktp_is_saved(): void
    {
        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        $room = Room::create([
            'room_number' => 'AUTH-01',
            'floor'       => 1,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $response = $this->actingAs($tenantUser)->post(route('public.rooms.book', $room), [
            'ktp_number'      => '3201012345678999',
            'start_date'      => now()->toDateString(),
            'duration_months' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201012345678999',
        ]);
    }

    public function test_tenant_cannot_create_maintenance_ticket_without_active_room(): void
    {
        $tenantUser = User::factory()->create();
        $tenantUser->assignRole('tenant');

        // Mengakses create maintenance langsung tanpa kamar sewa aktif
        $response = $this->actingAs($tenantUser)->get(route('maintenance.create'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');

        // Mengirim store maintenance
        $room = Room::create([
            'room_number' => 'MNT-01',
            'floor'       => 1,
            'price'       => 1000000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $postResponse = $this->actingAs($tenantUser)->post(route('maintenance.store'), [
            'room_id'     => $room->id,
            'title'       => 'AC Rusak',
            'priority'    => 'medium',
            'description' => 'AC tidak dingin',
        ]);
        $postResponse->assertRedirect(route('dashboard'));
        $postResponse->assertSessionHas('error');
    }
}
