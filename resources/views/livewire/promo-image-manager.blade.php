<div class="space-y-6">

    {{-- Notification listener --}}
    <div x-data x-on:notify.window="
        let n = $event.detail;
        let color = n.type === 'success' ? 'bg-green-600' : 'bg-red-600';
        let el = document.createElement('div');
        el.className = 'fixed top-5 right-5 z-[9999] px-5 py-3 rounded-xl text-white text-sm font-semibold shadow-lg transition ' + color;
        el.innerText = n.message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3000);
    "></div>

    {{-- Image Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">

        @foreach($displaySlots as $slot)
            @php
                $hasImage = isset($images[$slot]);
                $isRequired = in_array($slot, ['1', '5']);
                $imgUrl = $hasImage ? asset($promoDir . '/' . $images[$slot]) . '?v=' . time() : null;
            @endphp

            <div class="relative group rounded-xl overflow-hidden border-2 {{ $hasImage ? 'border-slate-600' : 'border-dashed border-slate-500' }} bg-slate-800 aspect-[4/3] flex items-center justify-center">

                @if($hasImage)
                    {{-- Actual image --}}
                    <img src="{{ $imgUrl }}" alt="Promo {{ $slot }}" class="w-full h-full object-cover">

                    {{-- Overlay on hover --}}
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                        <span class="text-white text-xs font-bold tracking-widest uppercase">Slot {{ $slot }}</span>
                        {{-- Replace button --}}
                        <button wire:click="startReplace('{{ $slot }}')"
                                class="flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-lg transition">
                            ✏️ Replace
                        </button>
                        {{-- Delete button --}}
                        <button wire:click="deleteImage('{{ $slot }}')"
                                wire:confirm="Delete slot {{ $slot }} image? This cannot be undone."
                                class="flex items-center gap-1 px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-xs font-semibold rounded-lg transition">
                            🗑 Delete
                        </button>
                    </div>

                @else
                    {{-- Placeholder --}}
                    <div class="flex flex-col items-center justify-center text-slate-500 gap-2 px-2 text-center">
                        <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-xs font-semibold {{ $isRequired ? 'text-amber-400' : 'text-slate-400' }}">
                            Slot {{ $slot }}{{ $isRequired ? ' (Required)' : '' }}
                        </span>
                        <button wire:click="startReplace('{{ $slot }}')"
                                class="px-3 py-1 bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold rounded-lg transition">
                            + Upload
                        </button>
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Add More Card --}}
        <div class="rounded-xl border-2 border-dashed border-emerald-600 bg-slate-800 aspect-[4/3] flex items-center justify-center hover:border-emerald-400 transition cursor-pointer"
             wire:click="openAddModal">
            <div class="flex flex-col items-center justify-center text-emerald-500 gap-2">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-xs font-bold uppercase tracking-wide">Add More</span>
            </div>
        </div>
    </div>

    {{-- Replace Modal --}}
    @if($replacingSlot !== null)
        <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" wire:click.self="cancelReplace">
            <div class="bg-slate-800 border border-slate-600 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-bold text-white">Replace Image — Slot {{ $replacingSlot }}</h3>
                <p class="text-sm text-slate-400">
                    The new image will be saved as <code class="text-emerald-400">{{ $replacingSlot }}.[ext]</code>,
                    replacing the current one so the website can read it automatically.
                </p>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Choose new image</label>
                    <input type="file" wire:model="newImage" accept="image/*"
                           class="block w-full text-sm text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-700 file:text-white file:font-semibold hover:file:bg-slate-600 cursor-pointer">
                    @error('newImage') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                @if($newImage)
                    <div class="rounded-lg overflow-hidden border border-slate-600 max-h-48">
                        <img src="{{ $newImage->temporaryUrl() }}" alt="Preview" class="w-full object-contain max-h-48">
                    </div>
                @endif

                <div class="flex gap-3 justify-end pt-2">
                    <button wire:click="cancelReplace"
                            class="px-4 py-2 text-sm font-semibold text-slate-300 bg-slate-700 hover:bg-slate-600 rounded-xl transition">
                        Cancel
                    </button>
                    <button wire:click="confirmReplace" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 rounded-xl transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmReplace">✓ Replace Image</span>
                        <span wire:loading wire:target="confirmReplace">Uploading...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Add More Modal --}}
    @if($showAddModal)
        <div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4" wire:click.self="closeAddModal">
            <div class="bg-slate-800 border border-slate-600 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-bold text-white">Add New Promotion Image</h3>
                <p class="text-sm text-slate-400">
                    The image will be automatically assigned the next available slot number.
                </p>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Choose image</label>
                    <input type="file" wire:model="addImage" accept="image/*"
                           class="block w-full text-sm text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-slate-700 file:text-white file:font-semibold hover:file:bg-slate-600 cursor-pointer">
                    @error('addImage') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                @if($addImage)
                    <div class="rounded-lg overflow-hidden border border-slate-600 max-h-48">
                        <img src="{{ $addImage->temporaryUrl() }}" alt="Preview" class="w-full object-contain max-h-48">
                    </div>
                @endif

                <div class="flex gap-3 justify-end pt-2">
                    <button wire:click="closeAddModal"
                            class="px-4 py-2 text-sm font-semibold text-slate-300 bg-slate-700 hover:bg-slate-600 rounded-xl transition">
                        Cancel
                    </button>
                    <button wire:click="confirmAdd" wire:loading.attr="disabled"
                            class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmAdd">+ Add Image</span>
                        <span wire:loading wire:target="confirmAdd">Uploading...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
