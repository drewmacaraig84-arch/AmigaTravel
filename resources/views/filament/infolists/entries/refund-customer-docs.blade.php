@php
    /** @var \App\Models\Booking $record */
    $record = $getRecord();
    
    // Booking-level docs
    $idUrl = $record->refund_id_image ? storage_asset_path($record->refund_id_image) : null;
    $ticketUrl = $record->refund_ticket_file ? storage_asset_path($record->refund_ticket_file) : null;

    // Check if any passenger items have their own uploaded docs
    $paxWithDocs = $record->passengers->filter(fn ($p) => filled($p->refund_id_image) || filled($p->refund_ticket_file));
@endphp

<div class="space-y-4">
    @if ($idUrl || $ticketUrl)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Valid ID --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-3.5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            Customer Valid ID
                        </span>
                    </div>

                    @if ($idUrl)
                        @php
                            $isPdf = str_ends_with(strtolower($record->refund_id_image), '.pdf');
                        @endphp
                        @if (! $isPdf)
                            <a href="{{ $idUrl }}" target="_blank" class="block group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2 text-center hover:border-primary-500 transition">
                                <img src="{{ $idUrl }}" alt="Customer Valid ID" class="max-h-48 w-auto rounded object-contain mx-auto shadow-sm group-hover:scale-[1.01] transition" />
                                <div class="mt-2 text-center text-xs font-semibold text-primary-600 dark:text-primary-400 group-hover:underline flex items-center justify-center gap-1">
                                    <span>Open Full Size Image</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </div>
                            </a>
                        @else
                            <a href="{{ $idUrl }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition group">
                                <div class="p-2.5 rounded-lg bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white block group-hover:text-primary-600">Valid ID (PDF Document)</span>
                                    <span class="text-[11px] text-gray-500">Click to view/download PDF</span>
                                </div>
                            </a>
                        @endif
                    @else
                        <p class="text-xs text-gray-400 italic py-3">No ID attached.</p>
                    @endif
                </div>
            </div>

            {{-- Original Ticket --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-3.5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                            Customer Original Ticket / Receipt
                        </span>
                    </div>

                    @if ($ticketUrl)
                        @php
                            $isTicketPdf = str_ends_with(strtolower($record->refund_ticket_file), '.pdf');
                        @endphp
                        @if (! $isTicketPdf)
                            <a href="{{ $ticketUrl }}" target="_blank" class="block group relative overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2 text-center hover:border-primary-500 transition">
                                <img src="{{ $ticketUrl }}" alt="Customer Original Ticket" class="max-h-48 w-auto rounded object-contain mx-auto shadow-sm group-hover:scale-[1.01] transition" />
                                <div class="mt-2 text-center text-xs font-semibold text-primary-600 dark:text-primary-400 group-hover:underline flex items-center justify-center gap-1">
                                    <span>Open Full Size Ticket</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </div>
                            </a>
                        @else
                            <a href="{{ $ticketUrl }}" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition group">
                                <div class="p-2.5 rounded-lg bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white block group-hover:text-primary-600">Original Ticket (PDF File)</span>
                                    <span class="text-[11px] text-gray-500">Click to view/download PDF</span>
                                </div>
                            </a>
                        @endif
                    @else
                        <p class="text-xs text-gray-400 italic py-3">No original ticket file attached.</p>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 p-4 text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 italic">No customer ID or original ticket file was uploaded with this cancellation request.</p>
        </div>
    @endif
</div>
