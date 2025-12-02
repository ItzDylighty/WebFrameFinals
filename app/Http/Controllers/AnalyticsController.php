<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\ParkingArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function overview(Request $request)
    {
        $range = $request->query('range', '14d');
        $days = $this->parseDays($range);
        $end = Carbon::today();
        $start = (clone $end)->subDays($days - 1);
        $noCache = $request->boolean('noCache');
        $by = $request->query('by', 'created_at'); // created_at | reservation_date

        $lastUpdated = Reservation::max('updated_at');
        $lu = $lastUpdated ? Carbon::parse($lastUpdated)->timestamp : 0;
        $cacheKey = "analytics_overview_by_{$by}_{$start->toDateString()}_{$end->toDateString()}_lu_{$lu}";

        $compute = function () use ($start, $end, $by) {
            if ($by === 'reservation_date') {
                $base = Reservation::whereBetween('reservation_date', [$start->toDateString(), $end->toDateString()]);
            } else {
                $startDT = $start->copy()->startOfDay();
                $endDT = $end->copy()->endOfDay();
                $base = Reservation::whereBetween('created_at', [$startDT, $endDT]);
            }

            $total = (clone $base)->count();
            $pending = (clone $base)->where('status', 'pending')->count();
            $approved = (clone $base)->where('status', 'approved')->count();
            $rejected = (clone $base)->where('status', 'rejected')->count();
            $completed = (clone $base)->where('status', 'completed')->count();

            // Compute average stay in PHP for cross-DB compatibility
            $staysQuery = Reservation::query();
            if ($by === 'reservation_date') {
                $staysQuery->whereBetween('reservation_date', [$start->toDateString(), $end->toDateString()]);
            } else {
                $staysQuery->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
            }
            $stays = $staysQuery
                ->whereNotNull('checked_in_at')
                ->whereNotNull('checked_out_at')
                ->get(['checked_in_at', 'checked_out_at']);

            $totalMinutes = 0; $countStays = 0;
            foreach ($stays as $s) {
                $ci = Carbon::parse($s->checked_in_at);
                $co = Carbon::parse($s->checked_out_at);
                if ($co->gt($ci)) {
                    $totalMinutes += $co->diffInMinutes($ci);
                    $countStays++;
                }
            }
            $avgStayMinutes = $countStays > 0 ? round($totalMinutes / $countStays, 1) : 0.0;

            $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0.0;

            return [
                'range' => ['start' => $start->toDateString(), 'end' => $end->toDateString()],
                'totals' => compact('total', 'pending', 'approved', 'rejected', 'completed'),
                'approvalRate' => $approvalRate,
                'avgStayMinutes' => (float) $avgStayMinutes,
            ];
        };

        $payload = $noCache ? $compute() : Cache::remember($cacheKey, 600, $compute);

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    public function reservationsByDay(Request $request)
    {
        $days = (int) $request->query('days', 14);
        $days = max(1, min(60, $days));
        $end = Carbon::today();
        $start = (clone $end)->subDays($days - 1);
        $noCache = $request->boolean('noCache');
        $by = $request->query('by', 'created_at');

        $lastUpdated = Reservation::max('updated_at');
        $lu = $lastUpdated ? Carbon::parse($lastUpdated)->timestamp : 0;
        $cacheKey = "analytics_res_by_day_by_{$by}_{$start->toDateString()}_{$end->toDateString()}_lu_{$lu}";

        $compute = function () use ($start, $end, $by) {
            if ($by === 'reservation_date') {
                $rows = Reservation::select('reservation_date as d', 'status', DB::raw('COUNT(*) as c'))
                    ->whereBetween('reservation_date', [$start->toDateString(), $end->toDateString()])
                    ->groupBy('reservation_date', 'status')
                    ->orderBy('reservation_date')
                    ->get();
            } else {
                $startDT = $start->copy()->startOfDay();
                $endDT = $end->copy()->endOfDay();
                $rows = Reservation::select(DB::raw('DATE(created_at) as d'), 'status', DB::raw('COUNT(*) as c'))
                    ->whereBetween('created_at', [$startDT, $endDT])
                    ->groupBy(DB::raw('DATE(created_at)'), 'status')
                    ->orderBy(DB::raw('DATE(created_at)'))
                    ->get();
            }

            $map = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $map[$d->toDateString()] = ['date' => $d->toDateString(), 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'completed' => 0];
            }
            foreach ($rows as $r) {
                $date = Carbon::parse($r->d)->toDateString();
                if (!isset($map[$date])) continue;
                $status = $r->status;
                if (isset($map[$date][$status])) {
                    $map[$date][$status] = (int) $r->c;
                }
            }

            return array_values($map);
        };

        $data = $noCache ? $compute() : Cache::remember($cacheKey, 600, $compute);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function occupancyByHour(Request $request)
    {
        $dateStr = $request->query('date', Carbon::today()->toDateString());
        $date = Carbon::parse($dateStr)->startOfDay();
        $start = $date->copy();
        $end = $date->copy()->endOfDay();
        $noCache = $request->boolean('noCache');

        $lastUpdated = Reservation::max('updated_at');
        $lu = $lastUpdated ? Carbon::parse($lastUpdated)->timestamp : 0;
        $cacheKey = "analytics_occ_hour_{$start->toDateString()}_lu_{$lu}";

        $compute = function () use ($start, $end) {
            $reservations = Reservation::whereNotNull('checked_in_at')
                ->where('checked_in_at', '<', $end)
                ->where(function ($q) use ($start) {
                    $q->whereNull('checked_out_at')->orWhere('checked_out_at', '>', $start);
                })
                ->get(['checked_in_at', 'checked_out_at']);

            $hours = array_fill(0, 24, 0);

            foreach ($reservations as $r) {
                $s = Carbon::parse($r->checked_in_at);
                $e = $r->checked_out_at ? Carbon::parse($r->checked_out_at) : Carbon::now();

                if ($s->lt($start)) $s = $start->copy();
                if ($e->gt($end)) $e = $end->copy();
                if ($e->lte($s)) continue;

                $firstHour = (int)$s->format('G');
                $lastHour = (int)$e->format('G');
                for ($h = $firstHour; $h <= $lastHour; $h++) {
                    $hours[$h] += 1;
                }
            }

            return [
                'date' => $start->toDateString(),
                'hours' => $hours,
            ];
        };

        $data = $noCache ? $compute() : Cache::remember($cacheKey, 600, $compute);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function utilizationByArea(Request $request)
    {
        $dateRaw = $request->query('date');
        try {
            $dateStr = $dateRaw ? Carbon::parse($dateRaw)->toDateString() : Carbon::today()->toDateString();
        } catch (\Throwable $e) {
            $dateStr = Carbon::today()->toDateString();
        }
        $noCache = $request->boolean('noCache');

        $lastUpdated = Reservation::max('updated_at');
        $lu = $lastUpdated ? Carbon::parse($lastUpdated)->timestamp : 0;
        $cacheKey = "analytics_util_area_{$dateStr}_lu_{$lu}";

        $compute = function () use ($dateStr) {
            $areas = ParkingArea::orderBy('code')->get(['id','name','code','total_slots']);

            $counts = Reservation::select('parking_no', DB::raw('COUNT(*) as c'))
                ->whereDate('reservation_date', $dateStr)
                ->whereIn('status', ['approved', 'completed'])
                ->groupBy('parking_no')
                ->pluck('c', 'parking_no');

            $data = [];
            foreach ($areas as $a) {
                $approved = (int) ($counts[$a->code] ?? 0);
                $total = (int) $a->total_slots;
                $util = $total > 0 ? round(min(1, $approved / $total) * 100, 1) : 0.0;
                $data[] = [
                    'area_code' => $a->code,
                    'area_name' => $a->name,
                    'approved' => $approved,
                    'total_slots' => $total,
                    'utilization_pct' => $util,
                ];
            }

            return [
                'data' => $data,
                'date' => $dateStr,
            ];
        };

        $payload = $noCache ? $compute() : Cache::remember($cacheKey, 600, $compute);

        return response()->json([
            'success' => true,
            'data' => $payload['data'],
            'date' => $payload['date'],
        ]);
    }

    private function parseDays(string $range): int
    {
        if (preg_match('/^(\\d+)d$/', $range, $m)) {
            return max(1, min(60, (int)$m[1]));
        }
        return 14;
    }
}
