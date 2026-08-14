<div class="space-y-4"
     x-data="{ toast: null }"
     x-on:notify.window="toast = $event.detail.message; setTimeout(() => toast = null, 3500)">

    {{-- Toast --}}
    <div x-show="toast" x-transition.opacity
         class="px-4 py-2 rounded-lg bg-green-700 text-white text-sm font-semibold">
        <span x-text="toast"></span>
    </div>

    {{-- Info --}}
    <div class="flex items-start gap-2 text-xs text-gray-400 bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2">
        <svg class="w-4 h-4 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>
            Images are saved with their filenames — the homepage carousel reads them automatically in alphabetical order.
            Local images (📁) cannot be deleted on Railway — replace them to migrate to persistent storage.
        </span>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">

        @foreach($displayFiles as $filename)
            @php
                $info       = $images[$filename] ?? null;
                $hasImage   = $info !== null;
            @endphp

            {{-- wire:key ensures Livewire morphs instead of full-replacing → no scroll jump --}}
            <div wire:key="promo-file-{{ $filename }}"
                 x-data="{ open: {{ $replacingFile === $filename ? 'true' : 'false' }} }"
                 class="rounded-xl border {{ $hasImage ? 'border-gray-600' : 'border-dashed border-gray-600' }} bg-gray-900 overflow-hidden flex flex-col">

                {{-- Thumbnail --}}
                <div class="relative aspect-[4/3] bg-gray-800 flex items-center justify-center overflow-hidden">
                    @if($hasImage)
                        @php $infoFile = $info['file']; @endphp
                        <img src="{{ $info['url'] }}"
                             alt="{{ $filename }}"
                             class="w-full h-full object-cover"
                             loading="lazy"
                             onerror="this.style.opacity='0.3'; this.insertAdjacentHTML('afterend','<div class=&quot;absolute inset-0 flex items-center justify-center text-red-400 text-xs font-bold&quot;>⚠ Load error</div>')">
                        {{-- Source badge --}}
                        <span class="absolute top-1 right-1 text-[9px] font-bold px-1.5 py-0.5 rounded
                              {{ $info['source'] === 'storage' ? 'bg-blue-600' : 'bg-yellow-600' }} text-white">
                            {{ $info['source'] === 'storage' ? '☁' : '📁' }}
                        </span>
                    @else
                        <div class="flex flex-col items-center gap-1 text-gray-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="text-[10px] text-gray-500">Empty</span>
                        </div>
                    @endif
                </div>

                {{-- Card footer --}}
                <div class="px-2 py-2 flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-400 truncate pr-2" title="{{ $filename }}">
                            {{ $filename }}
                        </span>
                        @if($hasImage && $info['source'] === 'storage')
                            <button wire:click="deleteImage('{{ $filename }}')"
                                    wire:confirm="Delete {{ $filename }}? Cannot be undone."
                                    wire:loading.attr="disabled"
                                    class="text-[10px] text-red-400 hover:text-red-300 font-semibold transition shrink-0">
                                ✕ Del
                            </button>
                        @endif
                    </div>

                    {{-- Replace / Upload button — uses Alpine to toggle panel, also sets Livewire --}}
                    <button @click="open = !open; if(open) $wire.startReplace('{{ $filename }}')"
                            class="w-full text-xs py-1.5 rounded-lg font-semibold transition
                                {{ $hasImage ? 'bg-blue-700 hover:bg-blue-600 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-200' }}">
                        <span x-show="!open">{{ $hasImage ? '✏ Replace' : '+ Upload' }}</span>
                        <span x-show="open">✕ Cancel</span>
                    </button>
                </div>

                {{-- Inline replace panel --}}
                <div x-show="open" x-transition class="border-t border-blue-700 bg-gray-800/60 p-3 space-y-2">
                    <p class="text-[10px] text-blue-300 font-semibold leading-tight">
                        Pick a new image to replace<br><span class="text-blue-100">{{ $filename }}</span>
                    </p>
                    <input type="file" wire:model="newImage" accept="image/*"
                           class="block w-full text-[10px] text-gray-300
                                  file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0
                                  file:bg-blue-700 file:text-white file:text-[10px] file:font-semibold
                                  hover:file:bg-blue-600 cursor-pointer">
                    @error('newImage')
                        <p class="text-[10px] text-red-400">{{ $message }}</p>
                    @enderror

                    <div wire:loading wire:target="newImage" class="text-[10px] text-blue-300">Uploading preview…</div>

                    @if($newImage && $replacingFile === $filename)
                        <img src="{{ $newImage->temporaryUrl() }}"
                             class="h-20 w-full object-contain rounded border border-gray-600 bg-gray-900">
                    @endif

                    <div class="flex gap-2 pt-1">
                        <button @click="open = false; $wire.cancelReplace()"
                                class="flex-1 text-[10px] py-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold transition">
                            Cancel
                        </button>
                        <button wire:click="confirmReplace"
                                wire:loading.attr="disabled"
                                class="flex-1 text-[10px] py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="confirmReplace">✓ Save</span>
                            <span wire:loading wire:target="confirmReplace">Saving…</span>
                        </button>
                    </div>
                </div>

            </div>
        @endforeach

        {{-- Add More card --}}
        <div wire:key="promo-add-more"
             x-data="{ open: false }"
             class="rounded-xl border-2 border-dashed border-emerald-700 bg-gray-900 overflow-hidden flex flex-col min-h-[140px]">

            <button x-show="!open" @click="open = true; $wire.openAddModal()"
                    class="flex-1 flex flex-col items-center justify-center gap-2 text-emerald-500 hover:text-emerald-400 transition p-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-xs font-bold uppercase tracking-wide">Add Image</span>
            </button>

            <div x-show="open" x-transition class="p-3 space-y-2">
                <p class="text-xs text-emerald-400 font-semibold">New promotion image:</p>
                <input type="file" wire:model="addImage" accept="image/*"
                       class="block w-full text-xs text-gray-300
                              file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0
                              file:bg-emerald-700 file:text-white file:text-xs file:font-semibold
                              hover:file:bg-emerald-600 cursor-pointer">
                @error('addImage') <p class="text-[10px] text-red-400">{{ $message }}</p> @enderror

                <div wire:loading wire:target="addImage" class="text-[10px] text-emerald-300">Uploading…</div>

                @if($addImage)
                    <img src="{{ $addImage->temporaryUrl() }}"
                         class="h-20 w-full object-contain rounded border border-gray-600 bg-gray-900">
                @endif

                <div class="flex gap-2 pt-1">
                    <button @click="open = false; $wire.closeAddModal()"
                            class="flex-1 text-xs py-1.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-gray-200 font-semibold transition">
                        Cancel
                    </button>
                    <button wire:click="confirmAdd" wire:loading.attr="disabled"
                            class="flex-1 text-xs py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="confirmAdd">+ Add</span>
                        <span wire:loading wire:target="confirmAdd">Saving…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-x-4 gap-y-1 text-[10px] text-gray-500 pt-1">
        <span><span class="inline-block w-2 h-2 rounded-sm bg-blue-600 mr-1 align-middle"></span>☁ Volume — persistent on Railway, survives redeploys</span>
        <span><span class="inline-block w-2 h-2 rounded-sm bg-yellow-600 mr-1 align-middle"></span>📁 Local — in public/images (read-only on Railway, replace to migrate)</span>
    </div>

</div>
