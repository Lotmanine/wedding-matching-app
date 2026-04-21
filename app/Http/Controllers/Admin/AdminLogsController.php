<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLogsController extends Controller
{
    public function audit(Request $request): View
    {
        $action = (string) $request->query('action', '');
        $userId = (string) $request->query('user_id', '');

        $query = AuditLog::with('user')->latest('created_at');
        if ($action !== '') {
            $query->where('action', 'like', $action . '%');
        }
        if ($userId !== '') {
            $query->where('user_id', (int) $userId);
        }

        $logs = $query->paginate(100)->withQueryString();

        return view('admin.logs.audit', compact('logs', 'action', 'userId'));
    }
}
