<?php

namespace App\Http\Controllers;

use App\Models\KosSetting;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Jika user adalah Penyewa (Tenant), tampilkan Portal Khusus Penghuni
        if ($user && ($user->hasRole('tenant') || ($user->tenant()->exists() && !$user->hasRole('admin') && !$user->hasRole('owner') && !$user->hasRole('staff')))) {
            return $this->tenantDashboard($user);
        }

        $currentMonth = now()->month;
        $currentYear  = now()->year;


        // 1. Statistik Kartu Atas
        $totalRooms     = Room::where('is_active', true)->count();
        $availableRooms = Room::where('is_active', true)->where('status', 'available')->count();
        $occupiedRooms  = Room::where('is_active', true)->where('status', 'occupied')->count();
        $maintRooms     = Room::where('is_active', true)->where('status', 'maintenance')->count();

        $activeLeases   = Lease::where('status', 'active')->count();
        $totalTenants   = Tenant::count();

        $monthlyIncome = Payment::where('status', 'paid')
            ->where(function ($q) use ($currentYear, $currentMonth) {
                $q->whereYear('payment_date', $currentYear)->whereMonth('payment_date', $currentMonth)
                  ->orWhere(function ($sub) use ($currentYear, $currentMonth) {
                      $sub->whereNull('payment_date')
                          ->whereYear('billing_period', $currentYear)
                          ->whereMonth('billing_period', $currentMonth);
                  });
            })->sum('amount');

        $unpaidPayments = Payment::whereIn('status', ['unpaid', 'pending', 'overdue']);
        $unpaidSum      = (clone $unpaidPayments)->sum('amount');
        $unpaidCount    = (clone $unpaidPayments)->count();

        // 2. Chart Pendapatan 6 Bulan Terakhir
        $chartMonths = [];
        $maxIncomeVal = 1;

        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $y = $dt->year;
            $m = $dt->month;

            $inc = Payment::where('status', 'paid')
                ->where(function ($q) use ($y, $m) {
                    $q->whereYear('payment_date', $y)->whereMonth('payment_date', $m)
                      ->orWhere(function ($sub) use ($y, $m) {
                          $sub->whereNull('payment_date')
                              ->whereYear('billing_period', $y)
                              ->whereMonth('billing_period', $m);
                      });
                })->sum('amount');

            $maxIncomeVal = max($maxIncomeVal, $inc);

            $chartMonths[] = [
                'label'  => $dt->translatedFormat('M'),
                'full'   => $dt->translatedFormat('F Y'),
                'amount' => $inc,
            ];
        }

        foreach ($chartMonths as &$cm) {
            $cm['pct'] = $maxIncomeVal > 0 ? max(6, round(($cm['amount'] / $maxIncomeVal) * 100)) : 6;
        }
        unset($cm);

        // 3. Okupansi Donut Chart (keliling lingkaran radius 56 = 351.9)
        $circumference = 351.86;
        $occupancyPct = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
        $dashTerisi = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * $circumference, 1) : 0;
        $dashKosong = $totalRooms > 0 ? round(($availableRooms / $totalRooms) * $circumference, 1) : 0;
        $dashPerbaikan = $totalRooms > 0 ? round(($maintRooms / $totalRooms) * $circumference, 1) : 0;

        // 4. Pembayaran Terbaru
        $recentPayments = Payment::with(['lease.tenant.user', 'lease.room'])
            ->latest('created_at')
            ->take(5)
            ->get();

        // 5. Maintenance / Keluhan Aktif
        $activeComplaints = MaintenanceRequest::with(['room', 'tenant.user'])
            ->whereIn('status', ['reported', 'in_progress'])
            ->latest('reported_at')
            ->take(4)
            ->get();

        // 6. Ringkasan Kamar
        $rooms = Room::where('is_active', true)
            ->orderBy('room_number')
            ->take(8)
            ->get();

        return view('dashboard', compact(
            'totalRooms',
            'availableRooms',
            'occupiedRooms',
            'maintRooms',
            'activeLeases',
            'totalTenants',
            'monthlyIncome',
            'unpaidSum',
            'unpaidCount',
            'chartMonths',
            'occupancyPct',
            'dashTerisi',
            'dashKosong',
            'dashPerbaikan',
            'circumference',
            'recentPayments',
            'activeComplaints',
            'rooms'
        ));
    }

    /**
     * Tampilkan Portal Pribadi Khusus Penyewa (Tenant Dashboard)
     */
    protected function tenantDashboard($user)
    {
        $tenant = $user->tenant;

        // Kontrak sewa aktif / terkini
        $activeLease = null;
        $remainingDays = 0;
        $totalDays = 0;
        $leaseProgressPct = 0;

        if ($tenant) {
            $activeLease = $tenant->leases()
                ->with(['room.facilities'])
                ->whereIn('status', ['active', 'pending'])
                ->latest('start_date')
                ->first();

            if ($activeLease) {
                $start = Carbon::parse($activeLease->start_date);
                $end = Carbon::parse($activeLease->end_date);
                $today = Carbon::today();

                $totalDays = max(1, $start->diffInDays($end));
                $remainingDays = max(0, $today->diffInDays($end, false));

                $daysElapsed = max(0, $start->diffInDays($today));
                $leaseProgressPct = min(100, round(($daysElapsed / $totalDays) * 100));
            }
        }

        // Tagihan & Status Pembayaran Penyewa
        $unpaidPayments = collect();
        $recentPayments = collect();

        if ($tenant) {
            $leaseIds = $tenant->leases()->pluck('id');

            $unpaidPayments = Payment::whereIn('lease_id', $leaseIds)
                ->whereIn('status', ['unpaid', 'pending', 'overdue'])
                ->orderBy('due_date', 'asc')
                ->get();

            $recentPayments = Payment::whereIn('lease_id', $leaseIds)
                ->with('lease.room')
                ->latest('created_at')
                ->take(5)
                ->get();
        }

        // Riwayat Keluhan Perbaikan (Maintenance)
        $maintenanceRequests = collect();
        if ($tenant) {
            $maintenanceRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
                ->with('room')
                ->latest('reported_at')
                ->take(5)
                ->get();
        }

        // Pengaturan Kos (Rekening Bank & WhatsApp Pengelola)
        $kosSettings = KosSetting::settings();

        return view('dashboard-tenant', compact(
            'user',
            'tenant',
            'activeLease',
            'remainingDays',
            'totalDays',
            'leaseProgressPct',
            'unpaidPayments',
            'recentPayments',
            'maintenanceRequests',
            'kosSettings'
        ));
    }
}

