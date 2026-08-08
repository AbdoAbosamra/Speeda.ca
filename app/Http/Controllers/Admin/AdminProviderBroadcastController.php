<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Mail\Provider\BroadcastEmail;
use App\Models\ProviderBroadcast;
use App\Models\ProviderBroadcastRecipient;
use App\Services\ProviderBroadcastService;
use App\Support\AdminHtml;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Compose-and-send screen for admin broadcast emails to service providers.
 *
 * Authoring mirrors the blog CMS (rich editor + live preview + draft/publish),
 * but the "publish" step here is irreversible — mail cannot be recalled — so
 * sending is a separate, explicit action guarded by a typed confirmation, and a
 * broadcast becomes read-only the moment it is queued.
 */
class AdminProviderBroadcastController extends Controller
{
    use LogsAdminActions;

    public function __construct(private readonly ProviderBroadcastService $broadcasts) {}

    public function index()
    {
        $broadcasts = ProviderBroadcast::query()
            ->with('author:id,name')
            ->latest()
            ->paginate(15);

        return view('admin.broadcasts.index', [
            'broadcasts' => $broadcasts,
            'audienceCount' => $this->broadcasts->audienceCount(),
            'counts' => [
                'total' => ProviderBroadcast::count(),
                'draft' => ProviderBroadcast::where('status', ProviderBroadcast::STATUS_DRAFT)->count(),
                'sent' => ProviderBroadcast::where('status', ProviderBroadcast::STATUS_SENT)->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.broadcasts.create', [
            'broadcast' => new ProviderBroadcast(),
            'audienceCount' => $this->broadcasts->audienceCount(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBroadcast($request);

        try {
            $broadcast = ProviderBroadcast::create($validated + [
                'status' => ProviderBroadcast::STATUS_DRAFT,
                'created_by' => Auth::id(),
            ]);

            $this->logCreate($broadcast, $broadcast->subject);
            ErrorHelper::flashNotification('Draft saved. Send a test to yourself before sending to providers.', 'success');

            return redirect()->route('admin.broadcasts.edit', $broadcast);
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back()->withInput();
        }
    }

    public function edit(ProviderBroadcast $broadcast)
    {
        // A queued or sent broadcast is a historical record of what recipients
        // already have in their inbox; editing it would be a lie.
        if (!$broadcast->isEditable()) {
            return redirect()->route('admin.broadcasts.show', $broadcast);
        }

        return view('admin.broadcasts.edit', [
            'broadcast' => $broadcast,
            'audienceCount' => $this->broadcasts->audienceCount(),
        ]);
    }

    public function update(Request $request, ProviderBroadcast $broadcast)
    {
        if (!$broadcast->isEditable()) {
            ErrorHelper::flashNotification('This broadcast has already been sent and can no longer be edited.', 'error');

            return redirect()->route('admin.broadcasts.show', $broadcast);
        }

        $validated = $this->validateBroadcast($request);

        try {
            $oldValues = $broadcast->getOriginal();
            $broadcast->update($validated);
            $this->logUpdate($broadcast, $oldValues, $broadcast->subject);

            ErrorHelper::flashNotification('Draft updated.', 'success');

            return redirect()->route('admin.broadcasts.edit', $broadcast);
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->back()->withInput();
        }
    }

    public function destroy(ProviderBroadcast $broadcast)
    {
        if (!$broadcast->isEditable()) {
            ErrorHelper::flashNotification('Sent broadcasts are kept as a delivery record and cannot be deleted.', 'error');

            return redirect()->route('admin.broadcasts.show', $broadcast);
        }

        $this->logAction('delete', $broadcast, ['deleted' => ['subject' => $broadcast->subject]], $broadcast->subject);
        $broadcast->delete();

        ErrorHelper::flashNotification('Draft deleted.', 'success');

        return redirect()->route('admin.broadcasts.index');
    }

    /**
     * Delivery report: who it went to, who failed, and why.
     */
    public function show(Request $request, ProviderBroadcast $broadcast)
    {
        $status = (string) $request->query('status', '');

        $recipients = $broadcast->recipients()
            ->with('serviceProvider:id,company_name')
            ->when(
                in_array($status, [
                    ProviderBroadcastRecipient::STATUS_SENT,
                    ProviderBroadcastRecipient::STATUS_FAILED,
                    ProviderBroadcastRecipient::STATUS_PENDING,
                ], true),
                fn ($query) => $query->where('status', $status)
            )
            ->orderByRaw("CASE status WHEN 'failed' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END")
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.broadcasts.show', compact('broadcast', 'recipients', 'status'));
    }

    /**
     * Render the email exactly as a provider will receive it.
     *
     * Reads from the POSTed form rather than the saved row so the preview
     * reflects unsaved edits — the same guarantee the blog preview gives, but
     * rendered server-side through the real Blade template, so what is shown
     * is genuinely what will be delivered.
     */
    public function preview(Request $request)
    {
        $validated = $this->validateBroadcast($request);

        $broadcast = new ProviderBroadcast($validated);
        $user = Auth::user();

        $html = (new BroadcastEmail(
            broadcast: $broadcast,
            recipientName: $user?->name ?: 'Your Business Name',
            dashboardUrl: $this->broadcasts->dashboardUrl(),
            unsubscribeUrl: '#',
        ))->render();

        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Send a single copy to one address, so the admin can check rendering in a
     * real inbox before committing to the whole list.
     */
    public function sendTest(Request $request, ProviderBroadcast $broadcast)
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $user = Auth::user();

        try {
            // Sent synchronously: the admin is waiting for it and needs to know
            // immediately if the mail configuration is broken.
            Mail::to($validated['test_email'])->send(new BroadcastEmail(
                broadcast: $broadcast,
                recipientName: $user?->name ?: 'Your Business Name',
                dashboardUrl: $this->broadcasts->dashboardUrl(),
                unsubscribeUrl: $user ? $this->broadcasts->unsubscribeUrl($user) : '#',
            ));

            $this->logAction('test_email', $broadcast, ['sent_to' => $validated['test_email']], $broadcast->subject);
            ErrorHelper::flashNotification('Test email sent to ' . $validated['test_email'] . '.', 'success');
        } catch (\Exception $e) {
            Log::error('[Broadcast] Test send failed', [
                'broadcast_id' => $broadcast->id,
                'error' => $e->getMessage(),
            ]);
            ErrorHelper::flashNotification('Test send failed: ' . $e->getMessage(), 'error');
        }

        return redirect()->route('admin.broadcasts.edit', $broadcast);
    }

    /**
     * The irreversible one. Requires the admin to type SEND to confirm.
     */
    public function send(Request $request, ProviderBroadcast $broadcast)
    {
        $request->validate([
            'confirm' => ['required', Rule::in(['SEND'])],
        ], [
            'confirm.in' => 'Type SEND exactly to confirm this broadcast.',
            'confirm.required' => 'Type SEND to confirm this broadcast.',
        ]);

        if (!$broadcast->isSendable()) {
            ErrorHelper::flashNotification('This broadcast has already been sent.', 'error');

            return redirect()->route('admin.broadcasts.show', $broadcast);
        }

        if (trim(AdminHtml::toText($broadcast->body)) === '') {
            ErrorHelper::flashNotification('The email body is empty. Write the message before sending.', 'error');

            return redirect()->route('admin.broadcasts.edit', $broadcast);
        }

        try {
            $queued = $this->broadcasts->queue($broadcast);

            if ($queued < 1) {
                ErrorHelper::flashNotification('No eligible providers to send to right now.', 'error');

                return redirect()->route('admin.broadcasts.edit', $broadcast);
            }

            $this->logAction('send', $broadcast, ['recipients' => $queued], $broadcast->subject);

            Log::info('[Broadcast] Queued', [
                'broadcast_id' => $broadcast->id,
                'recipients' => $queued,
                'admin_id' => Auth::id(),
            ]);

            ErrorHelper::flashNotification("Broadcast queued for {$queued} provider(s). Delivery progress is shown below.", 'success');

            return redirect()->route('admin.broadcasts.show', $broadcast);
        } catch (\Exception $e) {
            $error = ErrorHelper::handle($e);
            ErrorHelper::flashNotification($error['message'], $error['type']);

            return redirect()->route('admin.broadcasts.edit', $broadcast);
        }
    }

    /**
     * Live progress for the delivery report, polled while a send drains.
     */
    public function progress(ProviderBroadcast $broadcast)
    {
        return response()->json([
            'status' => $broadcast->status,
            'total' => $broadcast->recipients_total,
            'sent' => $broadcast->sent_count,
            'failed' => $broadcast->failed_count,
            'percent' => $broadcast->progressPercent(),
            'finished' => $broadcast->isFinished(),
        ]);
    }

    protected function validateBroadcast(Request $request): array
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:200000'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'url', 'max:2048', 'required_with:cta_label'],
        ], [
            'cta_url.required_with' => 'A button label needs a button link.',
        ]);

        // Strip active content before it is ever stored.
        $validated['body'] = AdminHtml::clean($validated['body']);

        // A body of only markup (an empty editor still posts "<p></p>") has no
        // message in it, and the "required" rule above cannot see that.
        if (trim(AdminHtml::toText($validated['body'])) === '') {
            throw ValidationException::withMessages([
                'body' => 'The email body cannot be empty.',
            ]);
        }

        return $validated;
    }
}
