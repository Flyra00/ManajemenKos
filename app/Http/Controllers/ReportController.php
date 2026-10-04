<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->input('year', date('Y'));
        $month = $request->input('month', 'all');
        $tab   = $request->input('tab', 'all');

        // Query dasar Pendapatan (Status Lunas)
        $incomeQuery = Payment::with(['lease.tenant.user', 'lease.room'])
            ->where('status', 'paid');

        // Query dasar Pengeluaran
        $expenseQuery = Expense::with('user');

        // Filter Tahun
        $incomeQuery->where(function ($q) use ($year) {
            $q->whereYear('payment_date', $year)
              ->orWhere(function ($sub) use ($year) {
                  $sub->whereNull('payment_date')
                      ->whereYear('billing_period', $year);
              });
        });

        $expenseQuery->whereYear('expense_date', $year);

        // Filter Bulan (jika bukan 'all')
        if ($month !== 'all' && is_numeric($month)) {
            $m = (int) $month;
            $incomeQuery->where(function ($q) use ($m) {
                $q->whereMonth('payment_date', $m)
                  ->orWhere(function ($sub) use ($m) {
                      $sub->whereNull('payment_date')
                          ->whereMonth('billing_period', $m);
                  });
            });

            $expenseQuery->whereMonth('expense_date', $m);
        }

        $totalIncome  = (clone $incomeQuery)->sum('amount');
        $totalExpense = (clone $expenseQuery)->sum('amount');
        $netProfit    = $totalIncome - $totalExpense;

        // Statistik Okupansi Kamar
        $totalRooms     = Room::where('is_active', true)->count();
        $occupiedRooms  = Room::where('is_active', true)->where('status', 'occupied')->count();
        $availableRooms = Room::where('is_active', true)->where('status', 'available')->count();
        $occupancyRate  = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // Data Grafik 6 Bulan Terakhir (Cashflow Trend)
        $chartMonths = [];
        $maxChartVal = 1;

        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $cYear  = $dt->year;
            $cMonth = $dt->month;

            $inc = Payment::where('status', 'paid')
                ->where(function ($q) use ($cYear, $cMonth) {
                    $q->whereYear('payment_date', $cYear)->whereMonth('payment_date', $cMonth)
                      ->orWhere(function ($sub) use ($cYear, $cMonth) {
                          $sub->whereNull('payment_date')
                              ->whereYear('billing_period', $cYear)
                              ->whereMonth('billing_period', $cMonth);
                      });
                })->sum('amount');

            $exp = Expense::whereYear('expense_date', $cYear)
                ->whereMonth('expense_date', $cMonth)
                ->sum('amount');

            $maxChartVal = max($maxChartVal, $inc, $exp);

            $chartMonths[] = [
                'label'   => $dt->translatedFormat('M Y'),
                'income'  => $inc,
                'expense' => $exp,
            ];
        }

        // Hitung persentase tinggi bar untuk chart CSS KosFly
        foreach ($chartMonths as &$item) {
            $item['income_pct']  = $maxChartVal > 0 ? max(4, round(($item['income'] / $maxChartVal) * 100)) : 4;
            $item['expense_pct'] = $maxChartVal > 0 ? max(4, round(($item['expense'] / $maxChartVal) * 100)) : 4;
        }
        unset($item);

        // Data tabel sesuai tab aktif
        $tableData = null;
        if ($tab === 'all' || $tab === 'income') {
            $tableData = $incomeQuery->latest('payment_date')->paginate(10)->withQueryString();
        } elseif ($tab === 'expenses') {
            $tableData = $expenseQuery->latest('expense_date')->paginate(10)->withQueryString();
        } elseif ($tab === 'occupancy') {
            $tableData = Room::with(['leases' => function ($q) {
                $q->where('status', 'active')->with('tenant.user');
            }])->where('is_active', true)->orderBy('room_number')->paginate(10)->withQueryString();
        } elseif ($tab === 'maintenance') {
            $tableData = MaintenanceRequest::with(['room', 'tenant.user'])
                ->latest('reported_at')
                ->paginate(10)
                ->withQueryString();
        }

        $stats = [
            'total_income'   => $totalIncome,
            'total_expense'  => $totalExpense,
            'net_profit'     => $netProfit,
            'occupancy_rate' => $occupancyRate,
            'total_rooms'    => $totalRooms,
            'occupied_rooms' => $occupiedRooms,
            'available_rooms'=> $availableRooms,
        ];

        return view('reports.index', compact(
            'stats',
            'chartMonths',
            'tableData',
            'year',
            'month',
            'tab'
        ));
    }

    /**
     * Ekspor data laporan ke format CSV / Spreadsheet.
     */
    public function export(Request $request)
    {
        $year  = (int) $request->input('year', date('Y'));
        $month = $request->input('month', 'all');
        $tab   = $request->input('tab', 'all');

        $monthStr = $month !== 'all' ? str_pad($month, 2, '0', STR_PAD_LEFT) : 'semua-bulan';
        $filename = "laporan-kosfly-{$tab}-{$year}-{$monthStr}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $incomeQuery = Payment::with(['lease.tenant.user', 'lease.room'])->where('status', 'paid');
        $expenseQuery = Expense::with('user');

        $incomeQuery->where(function ($q) use ($year) {
            $q->whereYear('payment_date', $year)
              ->orWhere(function ($sub) use ($year) {
                  $sub->whereNull('payment_date')->whereYear('billing_period', $year);
              });
        });
        $expenseQuery->whereYear('expense_date', $year);

        if ($month !== 'all' && is_numeric($month)) {
            $m = (int) $month;
            $incomeQuery->where(function ($q) use ($m) {
                $q->whereMonth('payment_date', $m)
                  ->orWhere(function ($sub) use ($m) {
                      $sub->whereNull('payment_date')->whereMonth('billing_period', $m);
                  });
            });
            $expenseQuery->whereMonth('expense_date', $m);
        }

        $callback = function () use ($tab, $incomeQuery, $expenseQuery) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            if ($tab === 'expenses') {
                fputcsv($handle, ['ID', 'Judul Pengeluaran', 'Keterangan', 'Tanggal', 'Nominal (Rp)', 'Dicatat Oleh']);
                foreach ($expenseQuery->latest('expense_date')->cursor() as $exp) {
                    fputcsv($handle, [
                        $exp->id,
                        $exp->title,
                        $exp->description ?? '-',
                        $exp->expense_date ? $exp->expense_date->format('d/m/Y') : '-',
                        $exp->amount,
                        $exp->user->name ?? 'Admin',
                    ]);
                }
            } elseif ($tab === 'occupancy') {
                fputcsv($handle, ['Nomor Kamar', 'Lantai', 'Harga Sewa / Bulan (Rp)', 'Status Kamar', 'Penghuni Saat Ini', 'No. HP']);
                $rooms = Room::with(['leases' => function ($q) {
                    $q->where('status', 'active')->with('tenant.user');
                }])->where('is_active', true)->orderBy('room_number')->get();
                foreach ($rooms as $r) {
                    $activeLease = $r->leases->first();
                    fputcsv($handle, [
                        $r->room_number,
                        'Lantai ' . $r->floor,
                        $r->price,
                        $r->status === 'occupied' ? 'Terisi' : ($r->status === 'maintenance' ? 'Perbaikan' : 'Tersedia'),
                        $activeLease?->tenant?->user?->name ?? '-',
                        $activeLease?->tenant?->user?->phone ?? '-',
                    ]);
                }
            } elseif ($tab === 'maintenance') {
                fputcsv($handle, ['ID', 'Judul Keluhan', 'Kamar', 'Pelapor', 'Prioritas', 'Status', 'Biaya (Rp)', 'Tgl Lapor', 'Tgl Selesai']);
                $maintQuery = MaintenanceRequest::with(['room', 'tenant.user'])->latest('reported_at');
                foreach ($maintQuery->cursor() as $m) {
                    fputcsv($handle, [
                        $m->id,
                        $m->title,
                        $m->room ? 'Kamar ' . $m->room->room_number : '-',
                        $m->tenant?->user?->name ?? '-',
                        ucfirst($m->priority),
                        ucfirst($m->status),
                        $m->cost,
                        $m->reported_at ? $m->reported_at->format('d/m/Y') : '-',
                        $m->resolved_at ? $m->resolved_at->format('d/m/Y') : '-',
                    ]);
                }
            } else {
                fputcsv($handle, ['Invoice', 'Kamar', 'Penghuni', 'Periode Sewa', 'Tgl Bayar', 'Metode', 'Status', 'Pemasukan (Rp)']);
                foreach ($incomeQuery->latest('payment_date')->cursor() as $pay) {
                    fputcsv($handle, [
                        $pay->invoice_number,
                        $pay->lease?->room ? 'Kamar ' . $pay->lease->room->room_number : '-',
                        $pay->lease?->tenant?->user?->name ?? '-',
                        $pay->billing_period ? Carbon::parse($pay->billing_period)->translatedFormat('F Y') : '-',
                        $pay->payment_date ? Carbon::parse($pay->payment_date)->format('d/m/Y') : '-',
                        ucfirst($pay->payment_method),
                        ucfirst($pay->status),
                        $pay->amount,
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
