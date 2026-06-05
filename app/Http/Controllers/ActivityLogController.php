<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::latest()->paginate(10);

        return view('activity-log.index', [
            'logs' => $logs,
            'search' => '',
            'startDate' => '',
            'endDate' => '',
            'role' => ''
        ]);
    }
}