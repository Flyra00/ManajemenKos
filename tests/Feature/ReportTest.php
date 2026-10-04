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

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guests_cannot_access_reports(): void
    {
        $response = $this->get(route('reports.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_reports_dashboard(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Analisis Operasional');
        $response->assertSee('Tren Arus Kas');
    }


    public function test_reports_calculates_income_expense_and_occupancy_accurately(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // 1. Setup Kamar & Kontrak
        $room1 = Room::create([
            'room_number' => '101',
            'floor'       => 1,
            'price'       => 1000000,
            'status'      => 'occupied',
            'is_active'   => true,
        ]);
        $room2 = Room::create([
            'room_number' => '102',
            'floor'       => 1,
            'price'       => 1000000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201123456780001',
        ]);

        $lease = Lease::create([
            'tenant_id'      => $tenant->id,
            'room_id'        => $room1->id,
            'start_date'     => '2026-09-01',
            'monthly_price'  => 1000000,
            'deposit_amount' => 500000,
            'status'         => 'active',
        ]);

        // 2. Pendapatan (Paid Payment)
        Payment::create([
            'lease_id'        => $lease->id,
            'invoice_number'  => 'INV-202609-001',
            'amount'          => 1000000,
            'billing_period'  => '2026-09-01',
            'due_date'        => '2026-09-10',
            'payment_date'    => '2026-09-02',
            'payment_method'  => 'bank_tf',
            'status'          => 'paid',
        ]);

        // 3. Pengeluaran
        Expense::create([
            'title'        => 'Listrik PLN',
            'description'  => 'Token listrik 200rb',
            'amount'       => 200000,
            'expense_date' => '2026-09-02',
            'user_id'      => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index', [
            'year'  => '2026',
            'month' => '9',
        ]));

        $response->assertOk();
        // Pendapatan 1.000.000
        $response->assertSee('1.000.000');
        // Pengeluaran 200.000
        $response->assertSee('200.000');
        // Keuntungan bersih 800.000 (1.000.000 - 200.000)
        $response->assertSee('800.000');
        // Okupansi 50% (1 dari 2 kamar)
        $response->assertSee('50%');
    }

    public function test_reports_can_switch_tabs_for_different_categories(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // Test tab expenses
        $expense = Expense::create([
            'title'        => 'Beli Alat Pel',
            'amount'       => 75000,
            'expense_date' => '2026-09-01',
            'user_id'      => $user->id,
        ]);

        $responseExp = $this->actingAs($user)->get(route('reports.index', ['tab' => 'expenses']));
        $responseExp->assertOk();
        $responseExp->assertSee('Beli Alat Pel');

        // Test tab occupancy
        $room = Room::create([
            'room_number' => 'B-05',
            'floor'       => 2,
            'price'       => 1500000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $responseOcc = $this->actingAs($user)->get(route('reports.index', ['tab' => 'occupancy']));
        $responseOcc->assertOk();
        $responseOcc->assertSee('Kamar B-05');

        // Test tab maintenance
        $tenantUser = User::factory()->create();
        $tenant = Tenant::create([
            'user_id'    => $tenantUser->id,
            'ktp_number' => '3201123456789999',
        ]);

        $maint = MaintenanceRequest::create([
            'room_id'     => $room->id,
            'tenant_id'   => $tenant->id,
            'title'       => 'Pintu Kamar Macet',
            'description' => 'Engsel seret',
            'priority'    => 'medium',
            'status'      => 'reported',
            'cost'        => 50000,
            'reported_at' => now()->toDateString(),
        ]);


        $responseMaint = $this->actingAs($user)->get(route('reports.index', ['tab' => 'maintenance']));
        $responseMaint->assertOk();
        $responseMaint->assertSee('Pintu Kamar Macet');
    }
}

