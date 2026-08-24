<div class="max-w-4xl mx-auto px-4 py-8">
    @if(!$booking)
        <div class="rounded-2xl border border-red-200 bg-red-50 p-8 text-center">
            <h2 class="text-xl font-bold text-red-900">Booking Not Found</h2>
            <p class="mt-2 text-sm text-red-700">No booking matches reference "#{{ $transaction_number }}".</p>
            <a href="{{ route('book.status') }}" class="mt-6 inline-block rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
                Return to Booking Status
            </a>
        </div>
    @else
        @php
            $cancellation = $booking->serviceCancellation;
            $status = $booking->disruption_status;
        @endphp

        {{-- Disruption Banner --}}
        <div class="overflow-hidden rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-500/10 via-amber-50 to-orange-50 p-6 sm:p-8 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-500/20 px-3.5 py-1 text-xs font-extrabold uppercase tracking-wider text-amber-900 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-700" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Unavoidable Schedule Disruption Notice
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Select Replacement Travel Date</h1>
                    <p class="mt-2 text-sm text-slate-700 font-medium max-w-2xl">
                        Your original {{ $booking->schedule_service ?? 'travel' }} voyage on <strong>{{ $booking->departure_date->format('M d, Y') }}</strong> was cancelled by <strong>{{ $cancellation->carrier ?? 'the operator' }}</strong> due to {{ $cancellation ? strtolower(str_replace('_', ' ', $cancellation->reason_category)) : 'unavoidable advisories' }}.
                    </p>
                </div>
            </div>

            {{-- Carrier message box --}}
            @if($cancellation && $cancellation->customer_message)
                <div class="mt-6 rounded-2xl border border-amber-200 bg-white/80 backdrop-blur-sm p-4 text-sm text-amber-950 font-medium">
                    <span class="font-bold text-amber-900">Operator Statement:</span> {{ $cancellation->customer_message }}
                </div>
            @endif

            {{-- Highlights grid --}}
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Booking Reference</p>
                    <p class="mt-1 text-base font-extrabold text-slate-900">#{{ $booking->transaction_number }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Service Resume Date</p>
                    <p class="mt-1 text-base font-extrabold {{ $cancellation && $cancellation->resume_date ? 'text-emerald-700' : 'text-amber-600' }}">
                        {{ $cancellation && $cancellation->resume_date ? $cancellation->resume_date->format('M d, Y') : 'To Be Announced' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Feedback Alert --}}
        @if($feedback)
            <div class="mb-6 rounded-2xl border p-4 text-sm font-semibold {{ $submitted ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-rose-200 bg-rose-50 text-rose-900' }}">
                {{ $feedback }}
            </div>
        @endif

        @if($submitted)
            <div class="text-center py-12">
                <a href="{{ route('book.status', ['transaction_number' => $booking->transaction_number]) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-slate-800 transition">
                    View Booking Status
                </a>
            </div>
        @else

            {{-- Passenger Items Selection Card --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Select Passenger(s) for this Action</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Choose which passenger items you wish to reschedule or refund.</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" wire:click="selectAllPassengers" class="text-xs font-semibold text-pink-600 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-3 py-1.5 rounded-lg border border-pink-200 transition">
                            Select All
                        </button>
                        <button type="button" wire:click="deselectAllPassengers" class="text-xs font-semibold text-slate-600 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg border border-slate-200 transition">
                            Deselect All
                        </button>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($booking->passengers->sortBy('item_number') as $passenger)
                        @php
                            $pItemNum = (int) ($passenger->item_number ?? 1);
                            $isLocked = ! $passenger->isActiveBookingItem();
                            $isSelected = in_array($pItemNum, array_map('intval', $selectedPassengerItems), true) && ! $isLocked;
                            $pStatus = $passenger->status ?? 'pending';
                            $pStatusLabel = $passenger->getStatusLabel();
                        @endphp
                        <label for="{{ $isLocked ? '' : 'reschedule_pax_' . $pItemNum }}" class="flex items-center justify-between p-3.5 rounded-2xl border transition {{ $isLocked ? 'opacity-50 bg-slate-100 border-slate-200 cursor-not-allowed' : ($isSelected ? 'border-pink-500 bg-pink-50/40 ring-1 ring-pink-500 cursor-pointer' : 'border-slate-200 bg-slate-50/50 hover:border-slate-300 cursor-pointer') }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <input type="checkbox"
                                    wire:model.live="selectedPassengerItems"
                                    value="{{ $pItemNum }}"
                                    id="reschedule_pax_{{ $pItemNum }}"
                                    @if($isLocked) disabled @endif
                                    class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500 border-slate-300 {{ $isLocked ? 'cursor-not-allowed opacity-40' : 'cursor-pointer' }}">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-900 text-white text-xs font-bold shrink-0">
                                    {{ $pItemNum }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs truncate">{{ $passenger->name ?? 'Passenger' }}</p>
                                    <p class="text-[11px] text-slate-500 capitalize">{{ $passenger->type ?? 'adult' }} &bull; ₱{{ number_format($passenger->getEffectiveItemTotal(), 2) }}</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 shrink-0">
                                {{ $pStatusLabel }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Cancel & Refund Form --}}
            @if($showRefundForm)
                <div class="rounded-3xl border border-rose-200 bg-white p-6 shadow-xl mb-8 relative">
                    <button wire:click="closeRefundForm" type="button" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @php
                        $netRefund = $booking->getTicketBase();
                        $nonRefundable = $booking->getNonRefundableFees();
                    @endphp
                    <div class="mb-6">
                        <h2 class="text-xl font-black text-rose-700">Cancel &amp; Request Refund</h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Since this voyage was cancelled by the operator, your ticket fares and accommodation charges are <strong>100% refundable</strong> (<strong>₱{{ number_format($netRefund, 2) }}</strong>).
                        </p>
                        @if($nonRefundable > 0)
                            <div class="mt-3 rounded-2xl bg-amber-50/90 border border-amber-200 p-3.5 text-xs text-amber-900 leading-relaxed flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <span class="font-bold">Fee Policy Notice:</span> In accordance with booking terms, third-party payment gateway processing fees and web administrative fees (₱{{ number_format($nonRefundable, 2) }}) are non-refundable as they cover direct automated payment processing costs.
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Refund Method</label>
                            <select wire:model.live="refund_method" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:ring-rose-500">
                                <option value="GCash">GCash</option>
                                <option value="Online Wallet">Other Online Wallet</option>
                                <option value="Bank Account">Bank Account</option>
                            </select>
                        </div>

                        @if(in_array($refund_method, ['Bank Account', 'Online Wallet']))
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Institution Name</label>
                                <input type="text" wire:model="refund_bank_name" placeholder="e.g. BDO, Maya" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:ring-rose-500">
                                @error('refund_bank_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Account Number / Mobile</label>
                            <input type="text" wire:model="refund_account_number" placeholder="e.g. 09123456789" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:ring-rose-500">
                            @error('refund_account_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Account Name</label>
                            <input type="text" wire:model="refund_account_name" placeholder="e.g. Juan Dela Cruz" class="w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium focus:border-rose-500 focus:ring-rose-500">
                            @error('refund_account_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Verification & Authorization Documents Upload --}}
                    <div class="mt-6 border-t border-slate-100 pt-6">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                            Verification &amp; Authorization Documents
                        </label>
                        <p class="text-xs text-slate-500 mb-4">Please upload your valid ID, original ticket, and an Authorization Letter (if requesting on behalf of the passenger or for partial passenger refund).</p>

                        <div class="grid gap-4 sm:grid-cols-3">
                            {{-- Valid ID Upload --}}
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 shadow-sm hover:border-rose-300 transition">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">1. Valid ID</span>
                                    @if($refund_id_image)
                                        <button type="button" wire:click="$set('refund_id_image', null)" class="text-xs font-semibold text-rose-600 hover:underline">Remove</button>
                                    @endif
                                </div>

                                @if($refund_id_image)
                                    @php
                                        $isIdPdf = in_array(strtolower(pathinfo($refund_id_image->getClientOriginalName(), PATHINFO_EXTENSION)), ['pdf']);
                                    @endphp
                                    <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-2 text-center">
                                        @if(!$isIdPdf && method_exists($refund_id_image, 'temporaryUrl'))
                                            <img src="{{ $refund_id_image->temporaryUrl() }}" class="mx-auto max-h-24 rounded-lg object-contain shadow-sm" alt="Valid ID Preview" />
                                        @else
                                            <div class="py-2 flex flex-col items-center justify-center text-slate-600">
                                                <svg class="w-8 h-8 text-rose-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                <span class="text-[11px] font-semibold truncate max-w-full px-1">{{ $refund_id_image->getClientOriginalName() }}</span>
                                            </div>
                                        @endif
                                        <p class="mt-1 text-[11px] text-emerald-600 font-medium flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Attached
                                        </p>
                                    </div>
                                @else
                                    <label class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-white p-3 text-center cursor-pointer hover:bg-rose-50/30 hover:border-rose-300 transition">
                                        <svg class="w-7 h-7 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-xs font-semibold text-slate-600">Upload Valid ID</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">JPG, PNG, PDF (Max 10MB)</span>
                                        <input type="file" wire:model="refund_id_image" accept="image/*,application/pdf" class="hidden" />
                                    </label>
                                @endif
                                <div wire:loading wire:target="refund_id_image" class="text-xs text-rose-600 mt-1 font-medium">Uploading ID...</div>
                                @error('refund_id_image') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Original Ticket Upload --}}
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 shadow-sm hover:border-rose-300 transition">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">2. Original Ticket</span>
                                    @if($refund_ticket_file)
                                        <button type="button" wire:click="$set('refund_ticket_file', null)" class="text-xs font-semibold text-rose-600 hover:underline">Remove</button>
                                    @endif
                                </div>

                                @if($refund_ticket_file)
                                    @php
                                        $isTicketPdf = in_array(strtolower(pathinfo($refund_ticket_file->getClientOriginalName(), PATHINFO_EXTENSION)), ['pdf']);
                                    @endphp
                                    <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-2 text-center">
                                        @if(!$isTicketPdf && method_exists($refund_ticket_file, 'temporaryUrl'))
                                            <img src="{{ $refund_ticket_file->temporaryUrl() }}" class="mx-auto max-h-24 rounded-lg object-contain shadow-sm" alt="Original Ticket Preview" />
                                        @else
                                            <div class="py-2 flex flex-col items-center justify-center text-slate-600">
                                                <svg class="w-8 h-8 text-rose-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                                <span class="text-[11px] font-semibold truncate max-w-full px-1">{{ $refund_ticket_file->getClientOriginalName() }}</span>
                                            </div>
                                        @endif
                                        <p class="mt-1 text-[11px] text-emerald-600 font-medium flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Attached
                                        </p>
                                    </div>
                                @else
                                    <label class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-white p-3 text-center cursor-pointer hover:bg-rose-50/30 hover:border-rose-300 transition">
                                        <svg class="w-7 h-7 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                        <span class="text-xs font-semibold text-slate-600">Upload Ticket</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">PDF or Image (Max 10MB)</span>
                                        <input type="file" wire:model="refund_ticket_file" accept="image/*,application/pdf" class="hidden" />
                                    </label>
                                @endif
                                <div wire:loading wire:target="refund_ticket_file" class="text-xs text-rose-600 mt-1 font-medium">Uploading ticket...</div>
                                @error('refund_ticket_file') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Authorization Letter Upload --}}
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 shadow-sm hover:border-rose-300 transition">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">3. Auth Letter</span>
                                    @if($refund_auth_letter)
                                        <button type="button" wire:click="$set('refund_auth_letter', null)" class="text-xs font-semibold text-rose-600 hover:underline">Remove</button>
                                    @endif
                                </div>

                                @if($refund_auth_letter)
                                    @php
                                        $isAuthPdf = in_array(strtolower(pathinfo($refund_auth_letter->getClientOriginalName(), PATHINFO_EXTENSION)), ['pdf']);
                                    @endphp
                                    <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-white p-2 text-center">
                                        @if(!$isAuthPdf && method_exists($refund_auth_letter, 'temporaryUrl'))
                                            <img src="{{ $refund_auth_letter->temporaryUrl() }}" class="mx-auto max-h-24 rounded-lg object-contain shadow-sm" alt="Authorization Letter Preview" />
                                        @else
                                            <div class="py-2 flex flex-col items-center justify-center text-slate-600">
                                                <svg class="w-8 h-8 text-rose-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span class="text-[11px] font-semibold truncate max-w-full px-1">{{ $refund_auth_letter->getClientOriginalName() }}</span>
                                            </div>
                                        @endif
                                        <p class="mt-1 text-[11px] text-emerald-600 font-medium flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Attached
                                        </p>
                                    </div>
                                @else
                                    <label class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-white p-3 text-center cursor-pointer hover:bg-rose-50/30 hover:border-rose-300 transition">
                                        <svg class="w-7 h-7 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span class="text-xs font-semibold text-slate-600">Upload Auth Letter</span>
                                        <span class="text-[10px] text-slate-400 mt-0.5">Representative permission</span>
                                        <input type="file" wire:model="refund_auth_letter" accept="image/*,application/pdf" class="hidden" />
                                    </label>
                                @endif
                                <div wire:loading wire:target="refund_auth_letter" class="text-xs text-rose-600 mt-1 font-medium">Uploading auth letter...</div>
                                @error('refund_auth_letter') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-6">
                        <button wire:click="closeRefundForm" type="button" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
                        <button wire:click="submitCancelAndRefund" type="button" class="rounded-xl bg-rose-600 px-8 py-3 text-sm font-bold text-white hover:bg-rose-700 shadow-sm transition">Submit Refund Request</button>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900">Replacement Booking</h2>
                @if(!$showRefundForm)
                    <button wire:click="openRefundForm" type="button" class="text-sm font-bold text-rose-600 hover:text-rose-700 hover:underline">
                        Or Cancel &amp; Refund instead
                    </button>
                @endif
            </div>

            @php
                $resumeDate = $booking->serviceCancellation?->resume_date;
                $resumeDateString = $resumeDate ? $resumeDate->format('Y-m-d') : null;
                $isResumeBlocked = ! $resumeDate;
                $departureDateMin = $resumeDateString ?: today()->format('Y-m-d');
                $returnDateMin = $dep_date
                    ? max($dep_date, $resumeDateString ?: $dep_date)
                    : ($resumeDateString ?: today()->format('Y-m-d'));
            @endphp

            @if($isResumeBlocked)
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-8 text-center shadow-sm">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-amber-100 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-amber-900 mb-2">Rescheduling Not Yet Available</h3>
                    <p class="text-sm text-amber-800 max-w-md mx-auto">
                        The operator has not yet announced a service resume date for this disruption.
                        Please wait until the service resume date is published before selecting replacement travel dates.
                    </p>
                </div>
            @else
            <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">


                {{-- WIZARD STEP 1: DEPARTURE SCHEDULE --}}
                @if($step === 'departure_date')
                    <div class="mb-6 pb-6 border-b border-slate-100">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Step 1: Pick a Departure Date</h3>
                        <input type="date" wire:model.live="dep_date" min="{{ $departureDateMin }}" class="w-full max-w-xs rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    
                    <div wire:poll.60s>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Available Schedules for {{ \Carbon\Carbon::parse($dep_date)->format('M d, Y') }}</h4>
                        @if($this->availableDepartureSchedules->isEmpty())
                            <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">No schedules available on this date for this route.</div>
                        @else
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach($this->availableDepartureSchedules as $sch)
                                    @php $schIsPast = $sch->departure_time->isPast(); @endphp
                                    <div
                                        @if(!$schIsPast) wire:click="selectDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === 'airline' ? 0 : $sch->price }})" @endif
                                        class="relative group rounded-2xl border p-5 transition
                                            {{ $schIsPast
                                                ? 'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed pointer-events-none'
                                                : 'border-slate-200 cursor-pointer hover:border-emerald-500 hover:shadow-md' }}"
                                    >
                                        @if($schIsPast)
                                            <div class="absolute top-0 right-0">
                                                <div class="bg-slate-500 text-white text-[9px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-bl-xl rounded-tr-2xl shadow-sm">
                                                    Departed
                                                </div>
                                            </div>
                                        @endif
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="font-black {{ $schIsPast ? 'text-slate-400 line-through' : 'text-slate-900' }}">{{ $sch->formatted_departure }} &mdash; {{ $sch->formatted_arrival }}</h5>
                                                <p class="text-xs font-bold {{ $schIsPast ? 'text-slate-400' : 'text-emerald-700' }}">{{ $sch->ferryRoute->operator }}</p>
                                                <p class="text-xs text-slate-500 mt-1">{{ $sch->ferryRoute->origin }} &rarr; {{ $sch->ferryRoute->destination }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- WIZARD STEP 2: DEPARTURE ACCOMMODATION --}}
                @if($step === 'departure_accommodation')
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Step 2: Departure Accommodation</h3>
                        <button wire:click="setStep('departure_date')" class="text-sm font-semibold text-emerald-600 hover:underline">Change Schedule</button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($this->departureAccommodations as $acc)
                            @php
                                $priceDiffBadge = $acc->price - ($originalDepAccPrice ?? 0);
                                $isDisabled = $priceDiffBadge < 0;
                            @endphp
                            <div @if(!$isDisabled) wire:click="selectDepartureAccommodation('{{ $acc->id }}', {{ $acc->price }})" @endif class="group rounded-2xl border p-5 text-center transition {{ $isDisabled ? 'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed pointer-events-none' : 'border-slate-200 cursor-pointer hover:border-emerald-500 hover:shadow-md' }}">
                                <h4 class="font-bold {{ $isDisabled ? 'text-slate-400' : 'text-slate-900' }}">{{ $acc->name }}</h4>
                                <p class="text-xs text-slate-500 mt-1">{{ $acc->description }}</p>
                                <p class="mt-3 font-black {{ $isDisabled ? 'text-slate-400' : 'text-emerald-700' }}">₱{{ number_format($acc->price, 2) }}</p>
                                @if($isDisabled)
                                    <p class="mt-1 text-[10px] font-bold text-red-500 uppercase tracking-wide">Not eligible for free replacement</p>
                                @elseif($priceDiffBadge > 0)
                                    <p class="mt-1 text-xs font-bold text-amber-600">+₱{{ number_format($priceDiffBadge, 2) }} extra vs original</p>
                                @elseif($priceDiffBadge < 0)
                                    <p class="mt-1 text-xs font-bold text-emerald-600">₱{{ number_format(abs($priceDiffBadge), 2) }} cheaper than original</p>
                                @else
                                    <p class="mt-1 text-xs text-slate-400">Same price as original</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($this->departureAccommodations->isEmpty())
                        <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">No accommodations listed for this schedule. Please go back and choose another.</div>
                    @endif
                @endif

                {{-- WIZARD STEP 3: RETURN SCHEDULE (Round Trip Only) --}}
                @if($step === 'return_date')
                    <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-6">
                        <h3 class="text-lg font-bold text-slate-900">Step 3: Pick a Return Date</h3>
                        <button wire:click="setStep('departure_accommodation')" class="text-sm font-semibold text-emerald-600 hover:underline">&larr; Back</button>
                    </div>
                    <div class="mb-6">
                        <input type="date" wire:model.live="ret_date" min="{{ $returnDateMin }}" class="w-full max-w-xs rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    
                    <div wire:poll.60s>
                        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Available Return Schedules for {{ \Carbon\Carbon::parse($ret_date)->format('M d, Y') }}</h4>
                        @if($this->availableReturnSchedules->isEmpty())
                            <div class="rounded-xl bg-slate-50 p-6 text-center text-sm text-slate-500">No schedules available on this date for this route.</div>
                        @else
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach($this->availableReturnSchedules as $sch)
                                    @php $schIsPast = $sch->departure_time->isPast(); @endphp
                                    <div
                                        @if(!$schIsPast) wire:click="selectReturnSchedule({{ $sch->id }}, {{ $booking->getMode() === 'airline' ? 0 : $sch->price }})" @endif
                                        class="relative group rounded-2xl border p-5 transition
                                            {{ $schIsPast
                                                ? 'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed pointer-events-none'
                                                : 'border-slate-200 cursor-pointer hover:border-emerald-500 hover:shadow-md' }}"
                                    >
                                        @if($schIsPast)
                                            <div class="absolute top-0 right-0">
                                                <div class="bg-slate-500 text-white text-[9px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-bl-xl rounded-tr-2xl shadow-sm">
                                                    Departed
                                                </div>
                                            </div>
                                        @endif
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h5 class="font-black {{ $schIsPast ? 'text-slate-400 line-through' : 'text-slate-900' }}">{{ $sch->formatted_departure }} &mdash; {{ $sch->formatted_arrival }}</h5>
                                                <p class="text-xs font-bold {{ $schIsPast ? 'text-slate-400' : 'text-emerald-700' }}">{{ $sch->ferryRoute->operator }}</p>
                                                <p class="text-xs text-slate-500 mt-1">{{ $sch->ferryRoute->origin }} &rarr; {{ $sch->ferryRoute->destination }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- WIZARD STEP 4: RETURN ACCOMMODATION --}}
                @if($step === 'return_accommodation')
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900">Step 4: Return Accommodation</h3>
                        <button wire:click="setStep('return_date')" class="text-sm font-semibold text-emerald-600 hover:underline">Change Schedule</button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($this->returnAccommodations as $acc)
                            @php
                                $retDiffBadge = $acc->price - ($originalRetAccPrice ?? 0);
                                $isDisabled = $retDiffBadge != 0;
                            @endphp
                            <div @if(!$isDisabled) wire:click="selectReturnAccommodation('{{ $acc->id }}', {{ $acc->price }})" @endif class="group rounded-2xl border p-5 text-center transition {{ $isDisabled ? 'border-slate-200 bg-slate-50 opacity-60 cursor-not-allowed pointer-events-none' : 'border-slate-200 cursor-pointer hover:border-emerald-500 hover:shadow-md' }}">
                                <h4 class="font-bold {{ $isDisabled ? 'text-slate-400' : 'text-slate-900' }}">{{ $acc->name }}</h4>
                                <p class="text-xs text-slate-500 mt-1">{{ $acc->description }}</p>
                                <p class="mt-3 font-black {{ $isDisabled ? 'text-slate-400' : 'text-emerald-700' }}">₱{{ number_format($acc->price, 2) }}</p>
                                @if($isDisabled)
                                    <p class="mt-1 text-[10px] font-bold text-red-500 uppercase tracking-wide">Not eligible for free replacement</p>
                                @elseif($retDiffBadge > 0)
                                    <p class="mt-1 text-xs font-bold text-amber-600">+₱{{ number_format($retDiffBadge, 2) }} extra vs original</p>
                                @elseif($retDiffBadge < 0)
                                    <p class="mt-1 text-xs font-bold text-emerald-600">₱{{ number_format(abs($retDiffBadge), 2) }} cheaper than original</p>
                                @else
                                    <p class="mt-1 text-xs text-slate-400">Same price as original</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- WIZARD STEP 5: CONFIRM --}}
                @if($step === 'confirm')
                    <div class="mb-6 flex items-center justify-between border-b border-slate-100 pb-4">
                        <h3 class="text-lg font-bold text-slate-900">Review &amp; Confirm</h3>
                        <button wire:click="setStep('{{ $this->isRoundTrip() ? 'return_accommodation' : 'departure_accommodation' }}')" class="text-sm font-semibold text-emerald-600 hover:underline">&larr; Back to edits</button>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-2xl bg-slate-50 p-6">
                            <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Departure Details</h4>
                            <p class="font-medium text-slate-900">Date: {{ \Carbon\Carbon::parse($dep_date)->format('M d, Y') }}</p>
                            <p class="text-sm text-slate-600">Passengers: {{ $booking->passengers()->count() ?: 1 }}</p>
                        </div>
                        
                        @if($this->isRoundTrip())
                            <div class="rounded-2xl bg-slate-50 p-6">
                                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-4">Return Details</h4>
                                <p class="font-medium text-slate-900">Date: {{ \Carbon\Carbon::parse($ret_date)->format('M d, Y') }}</p>
                            </div>
                        @endif

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-6">
                            <h4 class="text-sm font-bold uppercase tracking-wider text-emerald-800 mb-4">Rebooking Fee Computation</h4>
                            <div class="space-y-3 text-sm text-emerald-900">
                                <div class="flex justify-between">
                                    <span>Original Booking Total</span>
                                    <span>₱{{ number_format($originalFare, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>New Booking Total</span>
                                    <span>₱{{ number_format($newFare, 2) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-emerald-200 mt-2">
                                    <span>Rate Difference</span>
                                    <span>₱{{ number_format($rebookRateDiff, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Refund Surcharge</span>
                                    <span>₱{{ number_format($rebookSurcharge, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Revalidation Fee</span>
                                    <span>₱{{ number_format($rebookRevalidationFee, 2) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-emerald-200 font-bold text-base">
                                    <span>Total Rebooking Fee</span>
                                    <span>₱{{ number_format($totalRebookFee, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        @if($priceDiff > 0)
                            @php
                                $qrCodePath = \App\Models\PaymentSetting::current()->qr_code_path;
                            @endphp
                            <div class="rounded-2xl border border-amber-200 bg-white p-6 shadow-sm">
                                <h4 class="text-base font-bold text-slate-900 mb-2">Additional Payment Required</h4>
                                <p class="text-sm text-slate-600 mb-6">Since your new selections cost more than your original booking, please pay the difference of <strong>₱{{ number_format($priceDiff, 2) }}</strong> and upload the receipt below.</p>

                                <div class="grid gap-6 lg:grid-cols-[1fr_auto] items-start">
                                    <div class="space-y-4">
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <div class="flex items-center gap-3 mb-3">
                                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4a1 1 0 010 2H6v12h8V9a1 1 0 112 0v7a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                        <path d="M9 7a1 1 0 012 0v4a1 1 0 11-2 0V7z"/>
                                                    </svg>
                                                </span>
                                                <div>
                                                    <p class="font-bold text-slate-900">Upload Payment Receipt</p>
                                                    <p class="text-sm text-slate-600">Attach your proof of payment for the rebooking difference.</p>
                                                </div>
                                            </div>
                                            <label class="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 cursor-pointer hover:border-emerald-400 transition">
                                                <span class="flex items-center gap-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 11-2 0V4H5v12h5a1 1 0 110 2H4a1 1 0 01-1-1V3zm9.293 4.293a1 1 0 011.414 0L15 9.586V7a1 1 0 112 0v5a1 1 0 01-1 1h-5a1 1 0 110-2h2.586l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Choose file
                                                </span>
                                                <input type="file" wire:model="paymentProof" class="sr-only">
                                            </label>
                                            @error('paymentProof') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
                                        </div>

                                        <p class="text-sm text-slate-600">Your receipt will be attached to the booking and transaction record so our team can verify your payment.</p>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 w-full max-w-[220px]">
                                        <h5 class="text-sm font-bold text-slate-900 mb-3">Payment QR Code</h5>
                                        @if($qrCodePath)
                                            <img src="{{ storage_asset_path($qrCodePath) }}" alt="Payment QR Code" class="h-44 w-full object-contain rounded-2xl border border-slate-200 bg-white" />
                                        @else
                                            <div class="flex h-44 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-4 text-center text-sm text-slate-500">
                                                QR code not uploaded yet. Please wait for the admin to upload the payment QR code.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex justify-end pt-4">
                            <button wire:click="submitReschedule" wire:loading.attr="disabled" class="rounded-xl bg-[#216417] px-8 py-3.5 text-sm font-extrabold text-white shadow-sm hover:bg-[#1a5012] disabled:opacity-50 transition">
                                Submit Reschedule Request
                            </button>
                        </div>
                    </div>
                @endif
            </div> {{-- end wizard card --}}
            @endif {{-- end resume blocked check --}}

        @endif
    @endif
</div>
