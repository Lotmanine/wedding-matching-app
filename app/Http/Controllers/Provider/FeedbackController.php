<?php

declare(strict_types=1);

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\User;
use App\Support\Logger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Lets a logged-in provider send the team a subject + message (bug report,
 * remark, suggestion). Stored for the /admin/feedback inbox, then every
 * admin is alerted by email.
 */
class FeedbackController extends Controller
{
    public function create(): View
    {
        return view('provider.feedback.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();

        $feedback = Feedback::create([
            'user_id' => $user->id,
            'subject' => (string) Str::of($validated['subject'])->squish(),
            'message' => trim($validated['message']),
        ]);

        $this->alertAdmins($feedback, $user);

        return redirect()->route('provider.feedback.create')
            ->with('status', 'Merci, votre message a bien été envoyé. Notre équipe le lira rapidement.');
    }

    /**
     * The message is already saved: a mail failure must never surface to the
     * provider nor lose the feedback, so it is only logged.
     */
    private function alertAdmins(Feedback $feedback, User $author): void
    {
        $recipients = User::role('admin')->pluck('email')->filter()->unique()->values()->all();
        if ($recipients === []) {
            return;
        }

        $sender = $author->providerProfile?->business_name ?? $author->name;

        $body = "Nouveau feedback prestataire sur Zawaj Pro\n\n"
            . "De : {$sender} ({$author->email})\n"
            . "Sujet : {$feedback->subject}\n\n"
            . "{$feedback->message}\n\n"
            . 'Boîte de réception : ' . route('admin.feedback.index');

        try {
            Mail::raw($body, fn ($m) => $m->to($recipients)->subject('[Zawaj Pro] Feedback : ' . $feedback->subject));
        } catch (\Throwable $e) {
            Logger::warning('feedback.alert_email_failed', [
                'feedback_id' => $feedback->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
