<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

class Stats
{
    public function __invoke(): array
    {
        return [
            '30d' => $this->period(Carbon::parse('30 days ago')),
            'lifetime' => $this->period(Carbon::parse('2018-03-01')),
            'time' => Carbon::now()->toW3cString(),
        ];
    }

    protected function period(Carbon $from): array
    {
        $query = Scan::query()
            ->where('created_at', '>', $from);

        return [
            'scans' => $this->scans($query->clone()),
            'websites' => $this->scans($query->clone()->joinSub(function (\Illuminate\Database\Query\Builder $sub) {
                $sub->from('scans')
                    ->selectRaw('max(created_at) as max_created_at, website_id as max_website_id')
                    ->whereNotNull('scanned_at')
                    ->groupBy('website_id');
            }, 'last_scans', function (JoinClause $join) {
                $join->on('max_created_at', 'created_at');
                $join->on('max_website_id', 'website_id');
            })),
        ];
    }

    protected function scans(Builder $scanQuery): array
    {
        $ratings = ['A+', 'A', 'A-', 'B', 'B-', 'C', 'C-', 'D'];

        $extensionCount = $scanQuery->clone()
            ->joinSub(function (\Illuminate\Database\Query\Builder $sub) {
                $sub->from('extension_scan')
                    ->selectRaw('count(1) as count_extensions, scan_id as count_scan_id')
                    ->groupBy('scan_id');
            }, 'extension_scan', 'count_scan_id', 'id');

        return [
            'total' => $scanQuery->count(),
            'ratings' => array_combine($ratings, array_map(function (string $rating) use ($scanQuery) {
                return $scanQuery->clone()->where('rating', $rating)->count();
            }, $ratings)),
            'extensionCount' => [
                'avg' => round($extensionCount->avg('count_extensions'), 1),
                'max' => $extensionCount->max('count_extensions'),
                'min' => $extensionCount->min('count_extensions'),
            ],
        ];
    }
}
