<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_tenants_index_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create(['name' => 'Budi Santoso']);
        Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201123456780001',
            'job' => 'Mahasiswa',
        ]);

        $response = $this->actingAs($admin)->get(route('tenants.index'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        // NIK disembunyikan dari admin demi hak privasi
        $response->assertSee('••••••••••••••••');
        $response->assertDontSee('3201123456780001');
    }

    public function test_tenants_create_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('tenants.create'));

        $response->assertOk();
        $response->assertSee('Simpan Penghuni');
    }

    public function test_tenant_can_be_stored_along_with_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('tenants.store'), [
            'name' => 'Ahmad Dani',
            'email' => 'ahmad@example.com',
            'phone' => '081299998888',
            'ktp_number' => '3201999988880002',
            'job' => 'Software Engineer',
            'emergency_name' => 'Pak Joko',
            'emergency_contact' => '081277776666',
            'password' => 'Rahasia#2026',
            'password_confirmation' => 'Rahasia#2026',
        ]);

        $response->assertRedirect(route('tenants.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad@example.com',
            'name' => 'Ahmad Dani',
        ]);

        $this->assertDatabaseHas('tenants', [
            'ktp_number' => '3201999988880002',
            'job' => 'Software Engineer',
            'emergency_name' => 'Pak Joko',
        ]);

        $tenantUser = User::where('email', 'ahmad@example.com')->firstOrFail();
        $this->assertTrue($tenantUser->hasRole('tenant'));

        // Password yang diketik admin harus benar-benar dipakai...
        $this->assertTrue(Hash::check('Rahasia#2026', $tenantUser->password));
        // ...dan password bawaan yang lama tidak boleh berlaku lagi.
        $this->assertFalse(Hash::check('password123', $tenantUser->password));
    }

    public function test_tenant_password_is_required_and_must_be_confirmed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Password tidak diisi
        $response = $this->actingAs($admin)->post(route('tenants.store'), [
            'name' => 'Tanpa Password',
            'email' => 'tanpa@example.com',
            'ktp_number' => '3201111100009999',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'tanpa@example.com']);
        $this->assertDatabaseMissing('tenants', ['ktp_number' => '3201111100009999']);

        // Konfirmasi password tidak cocok
        $response = $this->actingAs($admin)->post(route('tenants.store'), [
            'name' => 'Beda Konfirmasi',
            'email' => 'beda@example.com',
            'ktp_number' => '3201111100008888',
            'password' => 'Rahasia#2026',
            'password_confirmation' => 'Rahasia#2027',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'beda@example.com']);
    }

    public function test_tenant_password_must_meet_minimum_requirements(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('tenants.store'), [
            'name' => 'Password Pendek',
            'email' => 'pendek@example.com',
            'ktp_number' => '3201111100007777',
            'password' => 'abc',
            'password_confirmation' => 'abc',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'pendek@example.com']);
    }

    public function test_tenant_can_login_with_password_set_by_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->post(route('tenants.store'), [
            'name' => 'Login Berhasil',
            'email' => 'login@example.com',
            'ktp_number' => '3201111100006666',
            'password' => 'Rahasia#2026',
            'password_confirmation' => 'Rahasia#2026',
        ]);

        // Password bawaan yang lama sudah tidak berlaku
        $this->assertFalse(Auth::attempt([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]));

        // Penghuni bisa login dengan password yang ditentukan admin
        $this->assertTrue(Auth::attempt([
            'email' => 'login@example.com',
            'password' => 'Rahasia#2026',
        ]));
    }

    public function test_tenant_ktp_must_be_unique(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $user1 = User::factory()->create();
        Tenant::create([
            'user_id' => $user1->id,
            'ktp_number' => '3201999988880002',
        ]);

        $response = $this->actingAs($admin)->post(route('tenants.store'), [
            'name' => 'Orang Lain',
            'email' => 'oranglain@example.com',
            'ktp_number' => '3201999988880002',
            'password' => 'Rahasia#2026',
            'password_confirmation' => 'Rahasia#2026',
        ]);

        $response->assertSessionHasErrors('ktp_number');
    }

    public function test_tenant_show_page_can_be_rendered(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create(['name' => 'Siti Aminah']);
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201555544440003',
        ]);

        $response = $this->actingAs($admin)->get(route('tenants.show', $tenant));

        $response->assertOk();
        $response->assertSee('Siti Aminah');
        // NIK disembunyikan dari admin
        $response->assertSee('••••••••••••••••');
        $response->assertDontSee('3201555544440003');
    }

    public function test_tenant_edit_page_redirects_with_info(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create(['name' => 'Rian Hidayat']);
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201777788880004',
        ]);

        $response = $this->actingAs($admin)->get(route('tenants.edit', $tenant));

        $response->assertRedirect(route('tenants.show', $tenant));
        $response->assertSessionHas('info');
    }

    public function test_tenant_cannot_be_updated_by_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create([
            'name' => 'Rian Lama',
            'email' => 'rianlama@example.com',
        ]);
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201777788880004',
            'job' => 'PNS',
        ]);

        $response = $this->actingAs($admin)->put(route('tenants.update', $tenant), [
            'name' => 'Rian Baru',
            'email' => 'rianbaru@example.com',
            'phone' => '081233334444',
            'ktp_number' => '3201777788880004',
            'job' => 'Wiraswasta',
        ]);

        $response->assertRedirect(route('tenants.show', $tenant));
        $response->assertSessionHas('error');

        // Data tidak boleh berubah
        $this->assertDatabaseHas('users', [
            'id' => $tenantUser->id,
            'name' => 'Rian Lama',
        ]);
    }

    public function test_tenant_cannot_be_deleted_by_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201888899990005',
        ]);

        $response = $this->actingAs($admin)->delete(route('tenants.destroy', $tenant));

        $response->assertRedirect(route('tenants.index'));
        $response->assertSessionHas('error');

        // Akun tidak boleh terhapus
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
        ]);
    }

    public function test_tenant_cannot_be_deleted_when_has_active_lease(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id' => $tenantUser->id,
            'ktp_number' => '3201888899990006',
        ]);

        $room = Room::create([
            'room_number' => 'T-101',
            'price' => 1000000,
            'status' => 'occupied',
        ]);

        Lease::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'start_date' => now()->toDateString(),
            'm_price' => 1000000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('tenants.destroy', $tenant));

        $response->assertRedirect(route('tenants.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
        ]);
    }

    public function test_tenant_can_be_stored_with_nullable_fields_empty(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('tenants.store'), [
            'name' => 'Fajar Santoso',
            'email' => 'fajar@example.com',
            'ktp_number' => '3201555566667777',
            'password' => 'Rahasia#2026',
            'password_confirmation' => 'Rahasia#2026',
            // emergency_name, emergency_contact, job dikosongkan
        ]);

        $response->assertRedirect(route('tenants.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenants', [
            'ktp_number' => '3201555566667777',
            'emergency_name' => null,
            'emergency_contact' => null,
            'job' => null,
        ]);
    }

    public function test_multiple_guest_bookings_do_not_conflict_on_default_ktp_number(): void
    {
        $room1 = Room::create([
            'room_number' => 'G-01',
            'price' => 1200000,
            'status' => 'available',
            'is_active' => true,
        ]);
        $room2 = Room::create([
            'room_number' => 'G-02',
            'price' => 1300000,
            'status' => 'available',
            'is_active' => true,
        ]);

        // Booking 1
        $res1 = $this->post(route('public.rooms.book', $room1), [
            'name' => 'User Pertama',
            'email' => 'user1@example.com',
            'phone' => '081111111111',
            'ktp_number' => '3201012345670011',
            'password' => 'password123',
            'start_date' => now()->toDateString(),
            'duration_months' => 1,
        ]);
        $res1->assertRedirect();

        auth()->logout();

        // Booking 2 (berbeda user dengan KTP masing-masing)
        $res2 = $this->post(route('public.rooms.book', $room2), [
            'name' => 'User Kedua',
            'email' => 'user2@example.com',
            'phone' => '082222222222',
            'ktp_number' => '3201012345670012',
            'password' => 'password123',
            'start_date' => now()->toDateString(),
            'duration_months' => 1,
        ]);
        $res2->assertRedirect();

        $this->assertDatabaseCount('tenants', 2);
    }

    public function test_tenant_can_view_own_ktp_in_profile(): void
    {
        $tenantUser = User::factory()->create(['name' => 'Budi Mandiri']);
        $tenantUser->assignRole('tenant');

        Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201998877660001',
        ]);

        $response = $this->actingAs($tenantUser)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertSee('3201998877660001');
        $response->assertSee('Nomor KTP (NIK Pribadi)');
    }

    public function test_booking_defaults_to_1_month_lease_duration(): void
    {
        $room = Room::create([
            'room_number' => 'X-99',
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $today = now()->startOfDay();

        $response = $this->post(route('public.rooms.book', $room), [
            'name'            => 'Penyewa Baru',
            'email'           => 'baru@example.com',
            'phone'           => '081234567800',
            'ktp_number'      => '3201012345670013',
            'password'        => 'password123',
            'start_date'      => $today->toDateString(),
        ]);

        $response->assertRedirect();

        $lease = Lease::where('room_id', $room->id)->first();
        $this->assertNotNull($lease);

        // Masa aktif default 1 bulan
        $this->assertEquals($today->copy()->addMonths(1)->toDateString(), $lease->end_date->toDateString());
    }

    public function test_booking_calculates_end_date_and_invoice_for_selected_duration_months(): void
    {
        $room = Room::create([
            'room_number' => 'X-100',
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $today = now()->startOfDay();

        $response = $this->post(route('public.rooms.book', $room), [
            'name'            => 'Penyewa 3 Bulan',
            'email'           => 'tiga@example.com',
            'phone'           => '081234567801',
            'ktp_number'      => '3201012345670014',
            'password'        => 'password123',
            'start_date'      => $today->toDateString(),
            'duration_months' => 3,
        ]);

        $response->assertRedirect();

        $lease = Lease::where('room_id', $room->id)->first();
        $this->assertNotNull($lease);
        $this->assertEquals($today->copy()->addMonths(3)->toDateString(), $lease->end_date->toDateString());

        // Invoice harus bernilai 3 x 1.500.000 = 4.500.000
        $payment = $lease->payments()->first();
        $this->assertNotNull($payment);
        $this->assertEquals(4500000, (float) $payment->amount);
    }

    public function test_verifying_subsequent_monthly_payment_extends_lease_by_30_days(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201112233445566',
        ]);

        $room = Room::create([
            'room_number' => 'X-88',
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);

        $initialEnd = now()->addDays(20)->startOfDay();

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room->id,
            'start_date'     => now()->subDays(40)->toDateString(),
            'end_date'       => $initialEnd->toDateString(),
            'm_price'        => 1000000,
            'deposit_amount' => 0,
            'status'         => 'active',
        ]);

        $payment = \App\Models\Payment::create([
            'lease_id'       => $lease->id,
            'invoice_number' => 'INV-TEST-0001',
            'amount'         => 1000000,
            'billing_period' => now()->startOfMonth()->toDateString(),
            'due_date'       => now()->addDays(5)->toDateString(),
            'payment_method' => 'bank_tf',
            'status'         => 'pending',
        ]);

        // Verifikasi pembayaran oleh admin
        $response = $this->actingAs($admin)->put(route('payments.verify', $payment));

        $response->assertRedirect();
        $lease->refresh();

        // Masa aktif harus bertambah +30 hari dari tanggal berakhir sebelumnya
        $expectedEnd = $initialEnd->copy()->addDays(30)->toDateString();
        $this->assertEquals($expectedEnd, $lease->end_date->toDateString());
    }
}


