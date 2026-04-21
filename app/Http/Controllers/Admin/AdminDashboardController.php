<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProviderProfile;
use App\Models\Purchase;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\WedRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = Cache::remember('admin:dashboard:stats', now()->addMinutes(5), function () {
            return [
                'wed_requests_total'    => WedRequest::withTrashed()->count(),
                'wed_requests_7d'       => WedRequest::withTrashed()->where('created_at', '>=', now()->subDays(7))->count(),
                'wed_requests_30d'      => WedRequest::withTrashed()->where('created_at', '>=', now()->subDays(30))->count(),

                'service_requests_by_status' => ServiceRequest::withTrashed()
                    ->select('status', DB::raw('COUNT(*) as c'))
                    ->groupBy('status')->pluck('c', 'status')->toArray(),

                'providers_total'       => User::role('provider')->count(),
                'providers_verified'    => ProviderProfile::where('verified', true)->count(),
                'providers_pending'     => ProviderProfile::where('verified', false)->count(),
                'providers_suspended'   => ProviderProfile::whereNotNull('suspended_at')->count(),

                'unique_couples'        => WedRequest::withTrashed()->distinct('couple_whatsapp')->count('couple_whatsapp'),

                'revenue_dzd'           => (int) Purchase::where('status', 'paid')->sum('amount_dzd'),
                'purchases_pending_old' => Purchase::where('status', 'pending')->where('created_at', '<', now()->subHour())->count(),
                'credits_outstanding'   => (int) ProviderProfile::sum('credit_balance'),

                'match_rate'            => self::computeMatchRate(),
                'avg_time_to_accept'    => self::computeAvgAcceptTime(),

                'failed_purchases_24h'  => Purchase::whereIn('status', ['failed', 'cancelled'])
                    ->where('created_at', '>=', now()->subDay())->count(),

                'stuck_matching'        => ServiceRequest::where('status', 'matching')
                    ->where('updated_at', '<', now()->subMinutes(30))->count(),
            ];
        });

        $recentActivity = AuditLog::latest('created_at')->limit(20)->with('user')->get();

        return view('admin.dashboard', [
            'stats'           => $stats,
            'recentActivity'  => $recentActivity,
        ]);
    }

    private static function computeMatchRate(): float
    {
        $total = ServiceRequest::withTrashed()->count();
        if ($total === 0) {
            return 0.0;
        }
        $accepted = ServiceRequest::withTrashed()->where('status', 'accepted')->count();

        return round($accepted / $total * 100, 1);
    }

    private static function computeAvgAcceptTime(): ?int
    {
        $rows = ServiceRequest::whereNotNull('unlocked_at')->select('created_at', 'unlocked_at')->get();
        if ($rows->isEmpty()) {
            return null;
        }

        $total = 0;
        foreach ($rows as $r) {
            $total += $r->unlocked_at->diffInMinutes($r->created_at);
        }

        return (int) round($total / $rows->count());
    }
}
