<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ErrorHelper;
use App\Http\Controllers\Controller;
use App\Models\OfferComment;
use App\Traits\HandlesBulkActions;
use App\Traits\LogsAdminActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Moderation queue for offer comments. A comment is public only once it is
 * approved here; rejecting hides it again without deleting it.
 */
class AdminOfferCommentController extends Controller
{
    use LogsAdminActions;
    use HandlesBulkActions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), OfferComment::STATUSES, true)
            ? $request->query('status')
            : OfferComment::STATUS_PENDING;
        $search = trim((string) $request->query('q', ''));

        $comments = OfferComment::query()
            ->with(['offer:id,slug,title_en,deleted_at,service_provider_id', 'offer.serviceProvider:id,company_name', 'user:id,name,email,role', 'reviewer:id,name'])
            ->where('status', $status)
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('body', 'like', "%{$search}%")
                ->orWhere('author_name', 'like', "%{$search}%")))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $counts = OfferComment::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        return view('admin.offers.comments', compact('comments', 'counts', 'status', 'search'));
    }

    public function approve(OfferComment $comment): RedirectResponse
    {
        $this->setStatus($comment, OfferComment::STATUS_APPROVED);
        ErrorHelper::flashNotification('Comment approved and now shown on the offer.', 'success');

        return redirect()->back();
    }

    public function reject(OfferComment $comment): RedirectResponse
    {
        $this->setStatus($comment, OfferComment::STATUS_REJECTED);
        ErrorHelper::flashNotification('Comment rejected. It is hidden from the offer.', 'success');

        return redirect()->back();
    }

    public function destroy(OfferComment $comment): RedirectResponse
    {
        $offerId = $comment->offer_id;
        $this->logAction('delete', $comment, ['deleted' => $comment->toArray()], 'Offer comment #' . $comment->id);
        $comment->delete();
        OfferComment::syncCount($offerId);
        ErrorHelper::flashNotification('Comment deleted.', 'success');

        return redirect()->back();
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, 'offer_comments');
    }

    protected function bulkActions(string $resource): array
    {
        return [
            'approve' => 'approved',
            'reject' => 'rejected',
            'delete' => __('admin.bulk_verb_deleted'),
        ];
    }

    protected function bulkQuery(string $resource): Builder
    {
        return OfferComment::query();
    }

    /**
     * @return true|string
     */
    protected function applyBulkAction(string $resource, string $action, $comment)
    {
        switch ($action) {
            case 'approve':
                if ($comment->status === OfferComment::STATUS_APPROVED) {
                    return 'Already approved';
                }
                $this->setStatus($comment, OfferComment::STATUS_APPROVED);
                break;

            case 'reject':
                if ($comment->status === OfferComment::STATUS_REJECTED) {
                    return 'Already rejected';
                }
                $this->setStatus($comment, OfferComment::STATUS_REJECTED);
                break;

            case 'delete':
                $offerId = $comment->offer_id;
                $this->logAction('delete', $comment, ['deleted' => $comment->toArray()], 'Offer comment #' . $comment->id);
                $comment->delete();
                OfferComment::syncCount($offerId);
                break;

            default:
                return __('admin.bulk_reason_failed');
        }

        return true;
    }

    private function setStatus(OfferComment $comment, string $status): void
    {
        $old = $comment->getOriginal();
        $comment->forceFill([
            'status' => $status,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ])->save();

        OfferComment::syncCount($comment->offer_id);
        $this->logUpdate($comment, $old, 'Offer comment #' . $comment->id);
    }
}
