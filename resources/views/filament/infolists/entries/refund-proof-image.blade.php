@php
    /** @var \App\Models\Booking $record */
    $record = $getRecord();
    $proofUrl = $record->refund_proof ? storage_asset_path($record->refund_proof) : null;
@endphp

@if ($proofUrl)
    <div class="space-y-2">
        <a href="{{ $proofUrl }}" target="_blank" class="block group relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-3 max-w-md hover:border-primary-500 transition">
            <img
                src="{{ $proofUrl }}"
                alt="Disbursement proof for {{ $record->transaction_number }}"
                class="max-h-80 w-auto rounded-lg object-contain mx-auto shadow-sm transition group-hover:scale-[1.01]"
            />
            <div class="mt-2.5 text-center text-xs font-semibold text-primary-600 dark:text-primary-400 group-hover:underline flex items-center justify-center gap-1">
                <span>View Full Size Proof Receipt</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </div>
        </a>
    </div>
@else
    <p class="text-xs text-gray-500 dark:text-gray-400 italic">No disbursement proof receipt uploaded.</p>
@endif
