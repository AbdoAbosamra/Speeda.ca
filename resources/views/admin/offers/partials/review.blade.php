{{-- Admin decision on a provider submission (pending, or rejected and reconsidered). --}}
@php
    $isPending = $offer->review_status === \App\Models\Offer::STATUS_PENDING;
    $deadline = $offer->submitted_at?->copy()->addHours(24);
@endphp
<section class="ao-card ao-sec ao-review mb-4" aria-label="Review provider submission">
    <div class="ao-review-head">
        @if($isPending)
            <h3><i class="fas fa-clock text-warning"></i> Provider submission waiting for your review</h3>
            <p class="mb-0">
                Sent by <strong>{{ $offer->providerName() }}</strong>
                @if($offer->submitted_at) on {{ $offer->submitted_at->format('M j, Y H:i') }} @endif
                @if($deadline)
                    · <strong class="{{ $deadline->isPast() ? 'text-danger' : '' }}">{{ $deadline->isPast() ? 'Overdue since' : 'Review by' }} {{ $deadline->format('M j, H:i') }}</strong>
                @endif
            </p>
        @else
            <h3><i class="fas fa-ban text-danger"></i> You rejected this submission</h3>
            <p class="mb-0">
                @if($offer->reviewed_at) {{ $offer->reviewed_at->format('M j, Y H:i') }}@if($offer->reviewer) by {{ $offer->reviewer->name }}@endif. @endif
                Reason sent to the provider: <em>{{ $offer->rejection_reason ?: '—' }}</em>
            </p>
        @endif
    </div>

    <ol class="ao-review-steps">
        <li>Check the provider, text, image and dates below. Fix anything small yourself.</li>
        <li>Choose <strong>Show on the home page</strong> in section 7 if it should be featured.</li>
        <li><strong>Save and approve</strong> publishes it with your edits. The provider gets a notification.</li>
    </ol>

    <div class="ao-review-actions">
        {{-- Submits the editor form below (form="offer-editor"), so edits are saved with the approval. --}}
        <button class="admin-btn admin-btn-primary text-white" type="submit" form="offer-editor" name="review_decision" value="approved">
            <i class="fas fa-check"></i> Save and approve
        </button>

        @if($isPending)
            <details class="ao-reject" @if($errors->has('rejection_reason')) open @endif>
                <summary class="admin-btn admin-btn-secondary"><i class="fas fa-xmark"></i> Reject…</summary>
                <form method="POST" action="{{ route('admin.offers.review', $offer) }}" class="d-grid gap-2 mt-3">
                    @csrf @method('PATCH')
                    <input type="hidden" name="decision" value="rejected">
                    <label for="rejection_reason" class="fw-semibold">Reason (the provider sees this and can fix and resubmit)</label>
                    <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="3" maxlength="2000" required
                              placeholder="e.g. The discount is unclear. Please say exactly what is included.">{{ old('rejection_reason') }}</textarea>
                    @error('rejection_reason')<span class="ao-error">{{ $message }}</span>@enderror
                    <button class="admin-btn admin-btn-danger" type="submit"><i class="fas fa-xmark"></i> Reject and notify the provider</button>
                </form>
            </details>
        @endif
    </div>
</section>

@once
@push('styles')
<style>
    .ao-review { border-inline-start: 4px solid #f59e0b; }
    .ao-review h3 { font-size: 1.05rem; font-weight: 700; display: flex; gap: .5rem; align-items: center; }
    .ao-review-steps { margin: .9rem 0; padding-inline-start: 1.2rem; color: #475569; font-size: .88rem; display: grid; gap: .25rem; }
    .ao-review-actions { display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-start; }
    .ao-reject { flex: 1 1 320px; }
    .ao-reject > summary { list-style: none; cursor: pointer; display: inline-flex; }
    .ao-reject > summary::-webkit-details-marker { display: none; }
    .admin-btn-danger { background: #dc2626; color: #fff; border: 0; }
    .admin-btn-danger:hover { background: #b91c1c; color: #fff; }
</style>
@endpush
@endonce
