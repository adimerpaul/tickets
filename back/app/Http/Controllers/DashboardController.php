<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'time_from' => 'nullable|date_format:H:i',
            'time_to' => 'nullable|date_format:H:i',
        ]);

        $q = Order::query()
            ->where('status', '!=', 'PENDING')
            ->whereNotNull('paid_at');

        if (!empty($data['from'])) {
            $from = Carbon::parse($data['from'])->startOfDay();
            $q->where('paid_at', '>=', $from);
        }
        if (!empty($data['to'])) {
            $to = Carbon::parse($data['to'])->endOfDay();
            $q->where('paid_at', '<=', $to);
        }

        if (!empty($data['time_from'])) {
            $q->whereRaw("TIME(paid_at) >= ?", [$data['time_from'] . ':00']);
        }
        if (!empty($data['time_to'])) {
            $q->whereRaw("TIME(paid_at) <= ?", [$data['time_to'] . ':00']);
        }

        $totalAmount = (float) $q->sum('amount_total');
        $totalCount = (int) $q->count();

        $byDay = (clone $q)
            ->selectRaw("DATE(paid_at) as d, COUNT(*) as c, SUM(amount_total) as t")
            ->groupBy(DB::raw("DATE(paid_at)"))
            ->orderBy('d')
            ->get();

        $byHour = (clone $q)
            ->selectRaw("HOUR(paid_at) as h, COUNT(*) as c, SUM(amount_total) as t")
            ->groupBy(DB::raw("HOUR(paid_at)"))
            ->orderBy('h')
            ->get();

        $byStatus = (clone $q)
            ->selectRaw("status, COUNT(*) as c, SUM(amount_total) as t")
            ->groupBy('status')
            ->get();

        return response()->json([
            'totals' => [
                'count' => $totalCount,
                'amount' => $totalAmount,
            ],
            'by_day' => $byDay,
            'by_hour' => $byHour,
            'by_status' => $byStatus,
        ]);
    }
}
