<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Feedback inbox: messages (subject + body) sent by providers from their
 * portal. Accessible to admins and managers (day-to-day ops), who mark each
 * message as handled once dealt with.
 */
class AdminFeedbackController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'new');

        $query = Feedback::with(['user.providerProfile', 'handledBy'])->latest('created_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.feedback.index', [
            'feedback' => $query->paginate(25)->withQueryString(),
            'status'   => $status,
            'counts'   => [
                'new'     => Feedback::where('status', 'new')->count(),
                'handled' => Feedback::where('status', 'handled')->count(),
            ],
        ]);
    }

    public function handle(Request $request, int $id): RedirectResponse
    {
        $feedback = Feedback::findOrFail($id);

        if (! $feedback->isNew()) {
            return back()->with('error', __('This message has already been handled.'));
        }

        $feedback->forceFill([
            'status'             => 'handled',
            'handled_by_user_id' => $request->user()->id,
            'handled_at'         => now(),
        ])->save();

        $this->audit->log('feedback.handle', $feedback, ['author_id' => $feedback->user_id]);

        return back()->with('status', __('Message marked as handled.'));
    }
}
