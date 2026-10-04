<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guests_cannot_access_expenses_pages(): void
    {
        $response = $this->get(route('expenses.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('expenses.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_expenses_index_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Expense::create([
            'title'        => 'Tagihan Listrik PLN',
            'description'  => 'Listrik bulan September',
            'amount'       => 750000,
            'expense_date' => '2026-09-01',
            'user_id'      => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('expenses.index'));

        $response->assertOk();
        $response->assertSee('Tagihan Listrik PLN');
        $response->assertSee('750.000');
    }

    public function test_expenses_index_can_filter_by_search_query(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Expense::create([
            'title'        => 'Iuran Sampah Warga',
            'description'  => 'Iuran rutin bulanan',
            'amount'       => 50000,
            'expense_date' => '2026-09-02',
            'user_id'      => $user->id,
        ]);
        Expense::create([
            'title'        => 'Tagihan Internet IndiHome',
            'description'  => 'Paket 50 Mbps',
            'amount'       => 350000,
            'expense_date' => '2026-09-03',
            'user_id'      => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('expenses.index', ['search' => 'Sampah']));

        $response->assertOk();
        $response->assertSee('Iuran Sampah Warga');
        $response->assertDontSee('Tagihan Internet IndiHome');
    }

    public function test_expenses_index_can_filter_by_month(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Expense::create([
            'title'        => 'Pembelian Sapu dan Pel',
            'description'  => 'Peralatan kebersihan',
            'amount'       => 120000,
            'expense_date' => '2026-08-15',
            'user_id'      => $user->id,
        ]);
        Expense::create([
            'title'        => 'Isi Ulang Galon Air',
            'description'  => 'Air minum dispenser',
            'amount'       => 40000,
            'expense_date' => '2026-09-01',
            'user_id'      => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('expenses.index', ['month' => '2026-09']));

        $response->assertOk();
        $response->assertSee('Isi Ulang Galon Air');
        $response->assertDontSee('Pembelian Sapu dan Pel');
    }

    public function test_expense_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('expenses.create'));

        $response->assertOk();
        $response->assertSee('Catat Pengeluaran Operasional');
        $response->assertSee('Simpan Pengeluaran');
    }

    public function test_expense_can_be_stored_with_valid_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $payload = [
            'title'        => 'Beli Pompa Air Baru',
            'description'  => 'Penggantian pompa air lantai 2 yang rusak',
            'amount'       => 650000,
            'expense_date' => '2026-09-03',
        ];

        $response = $this->actingAs($user)->post(route('expenses.store'), $payload);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'title'   => 'Beli Pompa Air Baru',
            'amount'  => 650000,
            'user_id' => $user->id,
        ]);
    }

    public function test_expense_validation_fails_when_fields_are_missing(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'title'        => '',
            'amount'       => '',
            'expense_date' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'amount', 'expense_date']);
    }

    public function test_expense_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $expense = Expense::create([
            'title'        => 'Gaji Penjaga Kos',
            'description'  => 'Honor jaga bulan Agustus',
            'amount'       => 1500000,
            'expense_date' => '2026-08-31',
            'user_id'      => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('expenses.show', $expense));

        $response->assertOk();
        $response->assertSee('Gaji Penjaga Kos');
        $response->assertSee('1.500.000');
    }

    public function test_expense_edit_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $expense = Expense::create([
            'title'        => 'Perbaikan Pagar Kos',
            'description'  => 'Las engsel pagar depan',
            'amount'       => 200000,
            'expense_date' => '2026-09-01',
            'user_id'      => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('expenses.edit', $expense));

        $response->assertOk();
        $response->assertSee('Edit Catatan Pengeluaran');
        $response->assertSee('Perbaikan Pagar Kos');
    }

    public function test_expense_can_be_updated(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $expense = Expense::create([
            'title'        => 'Token Listrik Awal',
            'description'  => 'Nomor meter 12345678',
            'amount'       => 100000,
            'expense_date' => '2026-09-01',
            'user_id'      => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('expenses.update', $expense), [
            'title'        => 'Token Listrik Revisi',
            'description'  => 'Nomor meter 12345678 (200rb)',
            'amount'       => 200000,
            'expense_date' => '2026-09-01',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'id'     => $expense->id,
            'title'  => 'Token Listrik Revisi',
            'amount' => 200000,
        ]);
    }

    public function test_expense_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $expense = Expense::create([
            'title'        => 'Pengeluaran Salah Input',
            'description'  => 'Duplikat input',
            'amount'       => 50000,
            'expense_date' => '2026-09-01',
            'user_id'      => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('expenses.destroy', $expense));

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }
}
