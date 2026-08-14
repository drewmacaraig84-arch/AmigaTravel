@props([
    'schedule' => [],
    'selectedId' => null,
    'selectMethod' => 'selectSchedule',
])
@php
    $isSelected = (int)$selectedId === (int)($schedule['id'] ?? -1);
    $isPast     = (bool) ($schedule['is_past'] ?? false);
    $opName = $schedule['operator'] ?? '';
    $opLogoUrl = $schedule['operator_logo'] ?? null;

    $safeSelectMethod = is_string($selectMethod) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $selectMethod) ? $selectMethod : 'selectSchedule';
    $scheduleId = (int) ($schedule['id'] ?? 0);
@endphp
<button
    type="button"
    @if(!$isPast) wire:click.prevent="{{ $safeSelectMethod }}({{ $scheduleId }})" @endif
    @if($isPast) disabled @endif
    data-departure-time="{{ $schedule['departure_time_iso'] ?? '' }}"
    class="schedule-card-element relative w-full h-full min-h-[168px] sm:min-h-[190px] rounded-xl sm:rounded-2xl border p-3 sm:p-4 text-left transition duration-200 flex flex-col overflow-hidden
        {{ $isPast
            ? 'border-slate-200 bg-slate-50 text-slate-400 opacity-60 cursor-not-allowed'
            : ($isSelected
                ? 'border-[#db2777] bg-[#db2777] text-white shadow-md ring-2 ring-[#db2777]/20 ring-offset-2 ring-offset-slate-50'
                : 'border-slate-200 bg-white text-slate-900 hover:border-[#db2777]/50 hover:shadow-sm')
        }}"
>
    {{-- "Departed" ribbon badge shown on past schedules --}}
    @if($isPast)
        <div class="absolute top-0 right-0 z-10">
            <div class="bg-slate-500 text-white text-[9px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-bl-xl rounded-tr-xl shadow-sm">
                Departed
            </div>
        </div>
    @endif

    <div class="flex items-start justify-between mb-2 gap-1">
        @if($opLogoUrl)
            <div class="w-8 h-8 sm:w-10 sm:h-10 shrink-0 bg-white rounded border {{ $isSelected && !$isPast ? 'border-white/30 shadow' : 'border-slate-200' }} flex items-center justify-center p-1 overflow-hidden">
                <img src="{{ $opLogoUrl }}" alt="{{ $opName }}" class="w-full h-full object-contain {{ $isPast ? 'grayscale' : '' }}">
            </div>
        @else
            <div class="w-8 h-8 sm:w-10 sm:h-10 shrink-0 rounded border {{ $isSelected && !$isPast ? 'border-white/30 bg-white/20' : 'border-slate-200 bg-slate-50' }} flex items-center justify-center">
                @if(isset($schedule['type']) && $schedule['type'] === 'airline')
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 {{ $isSelected && !$isPast ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M3.131 12.517L21.75 5.25a.75.75 0 01.969.97l-7.267 18.619a.75.75 0 01-1.438-.243l-1.952-7.562a.75.75 0 00-.505-.505l-7.562-1.952a.75.75 0 01-.243-1.438l-1.672-1.672zm12.44-8.767l-8.61 8.61 3.173.82a2.25 2.25 0 011.516 1.516l.82 3.173 8.61-8.61-5.509-5.509z" clip-rule="evenodd"/></svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 {{ $isSelected && !$isPast ? 'text-white' : 'text-slate-400' }}" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M3.5 10.5a2.5 2.5 0 012.5-2.5h12a2.5 2.5 0 012.5 2.5v5a2.5 2.5 0 01-2.5 2.5h-12a2.5 2.5 0 01-2.5-2.5v-5zM5 14a1 1 0 100-2 1 1 0 000 2zm10 1a1 1 0 11-2 0 1 1 0 012 0zM19 14a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                @endif
            </div>
        @endif
        @php
            $ticketsCount = $schedule['tickets_available'] ?? null;
        @endphp
        @if($isPast)
            {{-- No availability badge when departed --}}
        @elseif($ticketsCount !== null && $ticketsCount > 0)
            <span class="rounded-full border px-2 py-0.5 text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider ml-auto {{ $isSelected ? 'border-white/40 bg-white/25 text-white' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                {{ $ticketsCount }} {{ \Illuminate\Support\Str::plural('ticket', $ticketsCount) }} left
            </span>
        @else
            <span class="rounded-full border px-1.5 sm:px-2 py-0.5 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider ml-auto {{ $isSelected ? 'border-white/30 bg-white/20 text-white' : 'border-slate-200 bg-slate-50 text-slate-600' }}">
                {{ $schedule['availability'] ?? 'Available' }}
            </span>
        @endif
    </div>

    <h3 class="text-sm sm:text-base font-bold leading-tight line-clamp-1 {{ $isPast ? 'line-through' : '' }}">
        {{ $schedule['service'] ?? '' }}
    </h3>
    @if (!empty($schedule['operator']))
        <p class="mt-0.5 text-[10px] sm:text-xs font-medium truncate {{ $isSelected && !$isPast ? 'text-white/80' : 'text-slate-500' }}">
            {{ $schedule['operator'] }}
        </p>
    @endif
    <p class="mt-1.5 text-[10px] sm:text-xs font-semibold {{ $isSelected && !$isPast ? 'text-white' : ($isPast ? 'text-slate-400' : 'text-slate-900') }}">
        {{ $schedule['departure'] ?? '' }} - {{ $schedule['arrival'] ?? '' }}
    </p>

    <div class="mt-auto pt-2 sm:pt-3 border-t {{ $isSelected && !$isPast ? 'border-white/20' : 'border-slate-100' }}">
        <p class="text-[10px] sm:text-xs font-medium {{ $isSelected && !$isPast ? 'text-white/90' : 'text-slate-500' }}">
            {{ $schedule['duration'] ?? '' }}
        </p>
    </div>
</button>

