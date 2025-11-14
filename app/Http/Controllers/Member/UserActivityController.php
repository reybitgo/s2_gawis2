<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivityController extends Controller
{
    use \App\Http\Traits\HasPaginationLimit;

    /**
     * Display the user's activity logs
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get log type from request, default to 'all'
        $logType = $request->get('type', 'all');
        $search = $request->get('search', '');
        $level = $request->get('level', 'all');

        $query = ActivityLog::with(['user', 'transaction', 'order', 'relatedUser'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($logType !== 'all') {
            $query->where('type', $logType);
        }

        if ($level !== 'all') {
            $query->where('level', $level);
        }

        if (!empty($search)) {
            $query->search($search);
        }

        // Get paginated logs
        $perPage = $this->getPerPage($request, 15);
        $activityLogs = $query->paginate($perPage)->appends($request->query());

        // Transform database records to match the view format
        $logs = $activityLogs->through(function($log) {
            return [
                'id' => $log->id,
                'timestamp' => $log->created_at,
                'level' => $log->level,
                'type' => $log->type,
                'event' => $log->event,
                'message' => $log->message,
                'user_id' => $log->user_id,
                'ip_address' => $log->ip_address ?? 'N/A',
                'user_agent' => $log->user_agent ?? 'N/A',
                'metadata' => $log->metadata,
                'transaction_id' => $log->transaction_id,
                'order_id' => $log->order_id,
                'related_user_id' => $log->related_user_id,
            ];
        });

        return view('member.activity-logs', compact('activityLogs', 'logType', 'search', 'level', 'perPage'));
    }

    /**
     * Export user activity logs
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,json',
            'type' => 'nullable|string',
            'level' => 'nullable|string',
            'search' => 'nullable|string'
        ]);

        $user = Auth::user();

        // Query activity logs for current user with same filters
        $query = ActivityLog::with(['user', 'transaction', 'order', 'relatedUser'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Apply filters
        $logType = $request->get('type', 'all');
        $level = $request->get('level', 'all');
        $search = $request->get('search', '');

        if ($logType !== 'all') {
            $query->where('type', $logType);
        }

        if ($level !== 'all') {
            $query->where('level', $level);
        }

        if (!empty($search)) {
            $query->search($search);
        }

        // Get logs (up to 10000 for export)
        $activityLogs = $query->limit(10000)->get();

        // Transform for export
        $logs = $activityLogs->map(function($log) {
            return [
                'id' => $log->id,
                'timestamp' => $log->created_at,
                'level' => $log->level,
                'type' => $log->type,
                'event' => $log->event,
                'message' => $log->message,
                'user_id' => $log->user_id,
                'ip_address' => $log->ip_address ?? 'N/A',
                'user_agent' => $log->user_agent ?? 'N/A',
                'metadata' => $log->metadata,
                'transaction_id' => $log->transaction_id,
                'order_id' => $log->order_id,
                'related_user_id' => $log->related_user_id,
            ];
        });

        $logs = $logs->values();
        $format = $request->get('format');
        $timestamp = now()->format('Y-m-d_H-i-s');

        if ($format === 'csv') {
            return $this->exportLogsAsCSV($logs, $timestamp);
        } else {
            return $this->exportLogsAsJSON($logs, $timestamp);
        }
    }

    /**
     * Export logs as CSV
     */
    private function exportLogsAsCSV($logs, $timestamp)
    {
        $filename = "my_activity_logs_{$timestamp}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ];

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'ID',
                'Timestamp',
                'Level',
                'Type',
                'Message',
                'IP Address',
                'User Agent'
            ]);

            // Add data rows
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['id'],
                    $log['timestamp']->format('Y-m-d H:i:s'),
                    $log['level'],
                    $log['type'],
                    $log['message'],
                    $log['ip_address'],
                    $log['user_agent']
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export logs as JSON
     */
    private function exportLogsAsJSON($logs, $timestamp)
    {
        $filename = "my_activity_logs_{$timestamp}.json";

        // Format logs for JSON export
        $exportData = [
            'export_info' => [
                'exported_at' => now()->toISOString(),
                'total_records' => $logs->count(),
                'export_format' => 'json',
                'user_id' => Auth::id()
            ],
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log['id'],
                    'timestamp' => $log['timestamp']->toISOString(),
                    'level' => $log['level'],
                    'type' => $log['type'],
                    'message' => $log['message'],
                    'ip_address' => $log['ip_address'],
                    'user_agent' => $log['user_agent']
                ];
            })->values()
        ];

        return response()->json($exportData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
