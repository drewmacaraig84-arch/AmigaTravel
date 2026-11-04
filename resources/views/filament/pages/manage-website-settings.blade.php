@php
    use App\Models\WebsiteSetting;

    $pages        = WebsiteSetting::PAGES;
    $currentPage  = $this->currentPage ?? 'home';
    $editMode     = $this->editMode;
    $activeSection = $this->activeSection;
    $previewUrl   = $this->getPreviewUrl();
    $sections     = $this->getPageSections();
    $data         = $this->settingsData ?? [];
    $content      = $data['content'] ?? [];
    $heroImages   = array_values($data['hero_images'] ?? []);
    $headerData   = $data['header_data'] ?? [];
    $footerData   = $data['footer_data'] ?? [];

    $imageUrl = fn ($path = null): ?string =>
        $path ? (str_starts_with((string) $path, 'http') ? (string) $path : storage_asset_path($path)) : null;

    $colorBorder = [
        'blue'    => '#3b82f6',
        'green'   => '#22c55e',
        'purple'  => '#a855f7',
        'amber'   => '#f59e0b',
        'slate'   => '#94a3b8',
        'emerald' => '#10b981',
        'pink'    => '#ec4899',
        'violet'  => '#8b5cf6',
        'sky'     => '#0ea5e9',
        'indigo'  => '#6366f1',
        'orange'  => '#f97316',
    ];
@endphp

<x-filament-panels::page>

{{-- ============================================================ --}}
{{-- STYLES                                                        --}}
{{-- ============================================================ --}}
<style>
    /* ── Page tab pills ── */
    .ws-tabs {
        display: flex; flex-wrap: wrap; gap: .5rem;
        padding: .625rem 0 .875rem;
        border-bottom: 1px solid rgba(148,163,184,.12);
        margin-bottom: 1rem;
    }
    .ws-tab {
        padding: .35rem 1rem; border-radius: 999px; font-size: .8rem;
        font-weight: 500; text-decoration: none; border: 1.5px solid transparent;
        transition: all 150ms; display: inline-flex; align-items: center; gap: .3rem;
    }
    .ws-tab.active  { background: linear-gradient(135deg,#216417,#14400e); color:#fff; box-shadow: 0 2px 8px rgba(33,100,23,.35); }
    .ws-tab:not(.active) { color: var(--fi-text-color,#64748b); border-color: rgba(148,163,184,.2); }
    .ws-tab:not(.active):hover { border-color: #216417; color: #216417; }

    /* ── Main editor shell ── */
    .ws-editor {
        display: flex; border-radius: 1.25rem; overflow: hidden;
        border: 1.5px solid rgba(148,163,184,.12); background: #0d0d0d;
        position: relative;
        height: calc(100vh - 230px); min-height: 560px;
    }

    /* ── Preview column ── */
    .ws-preview { flex: 1; position: relative; overflow: hidden; }
    .ws-iframe  { width:100%; height:100%; border:0; display:block; background:#fff; }

    /* ── Edit overlay ── */
    .ws-overlay {
        position: absolute; inset: 0; z-index: 10;
        pointer-events: all;
    }

    /* ── Section highlight buttons ── */
    .ws-sbtn {
        position: absolute; cursor: pointer; border-radius: .75rem;
        border-width: 2px; border-style: dashed; transition: all 180ms;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.03);
        opacity: 0; animation: fadeS 500ms forwards;
    }
    .ws-overlay:hover .ws-sbtn { opacity: .55; }
    .ws-sbtn:hover    { opacity: 1 !important; background: rgba(255,255,255,.07); }
    .ws-sbtn.active   { opacity: 1 !important; background: rgba(33,100,23,.15); border-style: solid; }
    .ws-sbtn.locked   { cursor: default; opacity: .2 !important; border-style: dotted; pointer-events: none; }

    @keyframes fadeS { to { opacity: .4; } }

    .ws-chip {
        background: rgba(0,0,0,.72); color: #fff; border-radius: .45rem;
        padding: .2rem .55rem; font-size: .65rem; font-weight: 600;
        display: flex; align-items: center; gap: .25rem;
        backdrop-filter: blur(6px); pointer-events: none; white-space: nowrap;
    }
    .ws-sbtn.active .ws-chip { background: #fff; color: #1e293b; box-shadow: 0 2px 12px rgba(0,0,0,.2); }
    .ws-sbtn.locked .ws-chip { background: rgba(0,0,0,.4); }

    /* ── Edit mode banner ── */
    .ws-banner {
        position: absolute; top: .875rem; left: 50%; transform: translateX(-50%);
        z-index: 20; display: flex; align-items: center; gap: .625rem;
        background: rgba(12,12,12,.88); backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.08); border-radius: 999px;
        padding: .35rem 1.25rem .35rem .875rem; font-size: .72rem;
        color: rgba(255,255,255,.75); white-space: nowrap;
        animation: slideD 280ms cubic-bezier(.4,0,.2,1);
        box-shadow: 0 4px 24px rgba(0,0,0,.35);
    }
    .ws-banner .dot { width:.45rem; height:.45rem; border-radius:50%; background:#22c55e; animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    @keyframes slideD { from{opacity:0;transform:translateX(-50%) translateY(-10px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
    .ws-banner-btn { background:none; border:none; cursor:pointer; color:rgba(255,255,255,.4); font-size:.72rem; margin-left:.25rem; padding: 0; }
    .ws-banner-btn:hover { color: rgba(255,255,255,.75); }

    /* ── FAB ── */
    .ws-fab {
        position: absolute; bottom: 1.125rem; right: 1.125rem; z-index: 30;
        border-radius: 999px; padding: .55rem 1.125rem; font-size: .775rem; font-weight: 600;
        cursor: pointer; border: none; display: flex; align-items: center; gap: .4rem;
        transition: all 200ms; box-shadow: 0 4px 20px rgba(0,0,0,.3);
    }
    .ws-fab.off { background: linear-gradient(135deg,#216417,#14400e); color:#fff; }
    .ws-fab.off:hover { box-shadow: 0 6px 24px rgba(33,100,23,.45); transform: translateY(-1px); }
    .ws-fab.on  { background: #1e293b; color:#fff; border:1.5px solid rgba(255,255,255,.1); box-shadow: 0 4px 20px rgba(0,0,0,.15); }
    .ws-fab.on:hover { background: #0f172a; }

    /* ── Slide-in panel ── */
    .ws-panel {
        width: 370px; flex-shrink: 0;
        background: #111827;
        border-left: 1px solid rgba(255,255,255,.05);
        display: flex; flex-direction: column;
        animation: slideP 240ms cubic-bezier(.4,0,.2,1);
        overflow: hidden;
    }
    @keyframes slideP { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }

    .ws-ph {
        display: flex; align-items: center; gap: .625rem;
        padding: .875rem 1rem; border-bottom: 1px solid rgba(255,255,255,.05);
        flex-shrink: 0;
    }
    .ws-ph-back {
        width: 1.875rem; height: 1.875rem; border-radius: .5rem;
        border: 1px solid rgba(255,255,255,.1); background: transparent;
        color: rgba(255,255,255,.55); cursor: pointer;
        display: flex; align-items: center; justify-content: center; font-size: .9rem;
        transition: all 150ms; flex-shrink:0;
    }
    .ws-ph-back:hover { background: rgba(255,255,255,.08); color:#fff; }
    .ws-ph-info { flex:1; min-width:0; }
    .ws-ph-title { font-size: .8rem; font-weight: 600; color: rgba(255,255,255,.88); }
    .ws-ph-desc  { font-size: .65rem; color: rgba(255,255,255,.3); margin-top:.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

    .ws-save-btn {
        padding: .45rem .875rem; border-radius: .5rem; font-size: .72rem; font-weight: 600;
        background: linear-gradient(135deg,#216417,#14400e); color:#fff; cursor:pointer; border:none;
        flex-shrink:0; transition:all 150ms; white-space:nowrap;
    }
    .ws-save-btn:hover { box-shadow:0 2px 12px rgba(33,100,23,.4); transform:translateY(-.5px); }

    .ws-pb { flex:1; overflow-y:auto; padding:1rem; scrollbar-width:thin; scrollbar-color:rgba(255,255,255,.08) transparent; }

    /* ── Panel form atoms ── */
    .ws-label { display:block; font-size:.65rem; font-weight:600; color:rgba(255,255,255,.35); margin-bottom:.3rem; text-transform:uppercase; letter-spacing:.05em; }
    .ws-field  { margin-bottom:1rem; }
    .ws-field:last-child { margin-bottom:0; }
    .ws-input,.ws-textarea {
        width:100%; border-radius:.6rem; padding:.55rem .8rem; font-size:.78rem;
        border:1.5px solid rgba(255,255,255,.07); background:rgba(255,255,255,.04);
        color:rgba(255,255,255,.85); outline:none; transition:border-color 150ms;
        box-sizing:border-box; font-family:inherit; line-height:1.55;
    }
    .ws-input:focus,.ws-textarea:focus { border-color:rgba(33,100,23,.55); background:rgba(255,255,255,.06); }
    .ws-textarea { resize:vertical; min-height:4.5rem; }
    .ws-hr { height:1px; background:rgba(255,255,255,.05); margin:1rem 0; }
    .ws-sh { font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:rgba(255,255,255,.25); margin-bottom:.75rem; }
    .ws-note {
        background:rgba(33,100,23,.1); border:1px solid rgba(33,100,23,.2); border-radius:.6rem;
        padding:.65rem .875rem; font-size:.7rem; color:#4ade80; margin-bottom:.875rem; line-height:1.5;
    }
    .ws-lock-note {
        display:flex; align-items:center; gap:.4rem;
        padding:.6rem .875rem; background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.05); border-radius:.6rem;
        color:rgba(255,255,255,.3); font-size:.7rem; margin-bottom:.75rem;
    }

    /* ── Image grid ── */
    .ws-imgrid { display:grid; grid-template-columns:1fr 1fr; gap:.4rem; margin-bottom:.625rem; }
    .ws-imgitem {
        position:relative; border-radius:.6rem; overflow:hidden;
        border:1.5px solid rgba(255,255,255,.07); aspect-ratio:9/16;
        background:rgba(255,255,255,.04);
    }
    .ws-imgitem img { width:100%; height:100%; object-fit:cover; display:block; }
    .ws-img-del {
        position:absolute; top:.3rem; right:.3rem; width:1.4rem; height:1.4rem;
        border-radius:50%; background:rgba(239,68,68,.85); border:none; cursor:pointer;
        display:flex; align-items:center; justify-content:center; font-size:.7rem; color:#fff;
        transition:all 150ms;
    }
    .ws-img-del:hover { background:rgb(220,38,38); transform:scale(1.1); }
    .ws-img-empty {
        aspect-ratio:9/16; border:2px dashed rgba(255,255,255,.08); border-radius:.6rem;
        display:flex; align-items:center; justify-content:center;
        color:rgba(255,255,255,.18); font-size:.7rem; text-align:center; padding:1rem;
        grid-column:span 2;
    }

    /* ── Repeater ── */
    .ws-rep { border:1px solid rgba(255,255,255,.05); border-radius:.7rem; padding:.75rem; margin-bottom:.5rem; background:rgba(255,255,255,.02); }
    .ws-rep-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:.6rem; }
    .ws-rep-num { font-size:.62rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:rgba(255,255,255,.25); }
    .ws-rep-del {
        width:1.3rem; height:1.3rem; border-radius:.35rem;
        border:1px solid rgba(239,68,68,.25); background:rgba(239,68,68,.08);
        color:rgba(239,68,68,.65); cursor:pointer; font-size:.65rem;
        display:flex; align-items:center; justify-content:center; transition:all 150ms;
    }
    .ws-rep-del:hover { background:rgba(239,68,68,.25); color:rgb(239,68,68); }
    .ws-add {
        display:flex; align-items:center; justify-content:center; gap:.3rem; width:100%;
        padding:.55rem; border-radius:.6rem; border:1.5px dashed rgba(255,255,255,.08);
        background:transparent; color:rgba(255,255,255,.3); font-size:.72rem; font-weight:500;
        cursor:pointer; transition:all 150ms; margin-top:.3rem;
    }
    .ws-add:hover { border-color:rgba(33,100,23,.4); color:#4ade80; background:rgba(33,100,23,.07); }

    /* ── Panel footer save btn ── */
    .ws-panel-footer { padding:1rem; border-top:1px solid rgba(255,255,255,.05); flex-shrink:0; }
    .ws-save-full { width:100%; padding:.65rem; border-radius:.625rem; font-size:.78rem; font-weight:600; background:linear-gradient(135deg,#216417,#14400e); color:#fff; cursor:pointer; border:none; transition:all 150ms; }
    .ws-save-full:hover { box-shadow:0 2px 14px rgba(33,100,23,.4); }

    /* ── Advanced settings ── */
    .ws-adv { margin-top:1rem; border:1px solid rgba(148,163,184,.1); border-radius:1rem; overflow:hidden; }
    .ws-adv summary {
        list-style:none; padding:.875rem 1.25rem; cursor:pointer; display:flex;
        align-items:center; justify-content:space-between; font-size:.8rem; font-weight:500;
        color:var(--fi-text-color,#64748b); background:var(--fi-page-background-color);
        transition:background 150ms; user-select:none;
    }
    .ws-adv summary:hover { background:rgba(255,255,255,.02); }
    .ws-adv summary::-webkit-details-marker { display:none; }
    .ws-adv-body { padding:1.25rem; }
</style>

{{-- ============================================================ --}}
{{-- PAGE TAB SELECTOR                                             --}}
{{-- ============================================================ --}}
<div class="ws-tabs">
    @foreach($pages as $key => $label)
        <a href="?page={{ $key }}" class="ws-tab {{ $currentPage === $key ? 'active' : '' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- ============================================================ --}}
{{-- SCHEDULES VISIBILITY CONTROL BAR                             --}}
{{-- ============================================================ --}}
@php
    $schedulesEnabled = WebsiteSetting::areSchedulesEnabled();
@endphp
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-3.5 mb-4 rounded-xl border transition-all duration-300 {{ $schedulesEnabled ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-rose-500/15 border-rose-500/40' }}">
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $schedulesEnabled ? 'bg-emerald-600 text-white shadow-sm' : 'bg-rose-600 text-white shadow-sm animate-pulse' }}">
            <span class="w-2 h-2 rounded-full bg-white"></span>
            {{ $schedulesEnabled ? 'Schedules: ACTIVE (ON)' : 'Schedules: TURNED OFF' }}
        </span>
        <div class="text-xs {{ $schedulesEnabled ? 'text-slate-300' : 'text-rose-200 font-medium' }}">
            @if($schedulesEnabled)
                All schedules are currently visible and bookable on both the <strong>Booking Page</strong> and <strong>Schedules Page</strong>.
            @else
                ⚠️ All schedules are currently <strong>TURNED OFF</strong>. Customers cannot see or select any schedules on the Booking Page or Schedules Page.
            @endif
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button type="button"
                wire:click="toggleSchedulesVisibility"
                wire:confirm="{{ $schedulesEnabled ? 'Are you sure you want to TURN OFF all schedules? All schedules will be hidden from both the Booking Page and the Schedules Page.' : 'Are you sure you want to TURN ON schedules? They will be visible again on the Booking Page and the Schedules Page.' }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-xs font-bold transition shadow-md {{ $schedulesEnabled ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
            @if($schedulesEnabled)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                Turn Off Schedules
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Turn On Schedules
            @endif
        </button>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MAIN EDITOR                                                   --}}
{{-- ============================================================ --}}
<div class="ws-editor"
     x-on:refresh-preview.window="
         const fr = document.getElementById('ws-iframe'); 
         if(fr) { fr.src = fr.src; }
     "
     x-init="
         window.addEventListener('message', (e) => {
             if (e.data && e.data.type === 'open-editor' && e.data.section) {
                 $wire.setActiveSection(e.data.section);
             }
         });
     ">

    {{-- ── Preview ── --}}
    <div class="ws-preview">
        <iframe id="ws-iframe"
                src="{{ $previewUrl }}"
                class="ws-iframe"
                @load="
                    try {
                        let iframe = $event.target;
                        let p = iframe.contentWindow.location.pathname;
                        if (p && p !== 'blank') {
                            $wire.syncPage(p);
                        }
                        let doc = iframe.contentDocument || iframe.contentWindow.document;
                        if (doc) {
                            let style = doc.createElement('style');
                            style.textContent = 'html, body { scrollbar-width: none !important; } html::-webkit-scrollbar, body::-webkit-scrollbar { display: none !important; }';
                            doc.head.appendChild(style);
                        }
                        try {
                            iframe.contentWindow.addEventListener('open-editor', (evt) => {
                                if (evt.detail && evt.detail.section) {
                                    $wire.setActiveSection(evt.detail.section);
                                }
                            });
                        } catch(innerErr) {}
                    } catch(e) {}
                "
                title="Website Preview — {{ $pages[$currentPage] ?? $currentPage }}">
        </iframe>

        {{-- Edit Overlay --}}
        @if($editMode)
            <div class="ws-overlay">
                @foreach($sections as $sectionId => $section)
                    @php
                        $isLocked  = $section['locked'] ?? false;
                        $isActive  = $activeSection === $sectionId;
                        $borderColor = $colorBorder[$section['color'] ?? 'blue'] ?? '#3b82f6';
                        $bgActive    = $isActive ? 'background:rgba(33,100,23,.12);' : '';
                        $posStyle    = $section['pos'] ?? 'top: 1rem; left: 1rem;';
                        $label       = $section['label'] ?? $sectionId;
                        $icon        = $section['icon'] ?? '⚙️';
                    @endphp

                    @if($isLocked)
                        <div class="ws-sbtn locked"
                             style="{{ $posStyle }};border-color:rgba(255,255,255,.12);">
                            <div class="ws-chip">🔒 {{ $label }}</div>
                        </div>
                    @else
                        <button class="ws-sbtn {{ $isActive ? 'active' : '' }}"
                                wire:click="setActiveSection('{{ $sectionId }}')"
                                style="{{ $posStyle }};border-color:{{ $borderColor }};{{ $bgActive }}"
                                title="{{ $section['description'] ?? '' }}">
                            <div class="ws-chip" style="{{ $isActive ? 'background:#fff;color:#1e293b;' : '' }}">
                                {{ $icon }} {{ $label }}
                            </div>
                        </button>
                    @endif
                @endforeach
            </div>

            {{-- Banner --}}
            <div class="ws-banner">
                <div class="dot"></div>
                <span>Edit Mode &mdash; click a highlighted section</span>
                <button wire:click="toggleEditMode" class="ws-banner-btn">Done</button>
            </div>
        @endif

        {{-- FAB --}}
        <button wire:click="toggleEditMode"
                class="ws-fab {{ $editMode ? 'on' : 'off' }}">
            @if($editMode)
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Exit Edit Mode
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            @endif
        </button>
    </div>{{-- end ws-preview --}}

    {{-- ── Side Panel ── --}}
    @if($activeSection && isset($sections[$activeSection]))
    @php
        $ps = $sections[$activeSection];
    @endphp
    <div class="ws-panel">
        {{-- Panel Header --}}
        <div class="ws-ph">
            <button class="ws-ph-back" wire:click="closePanel">←</button>
            <div class="ws-ph-info">
                <div class="ws-ph-title">{{ $ps['icon'] }} {{ $ps['label'] }}</div>
                @if(!empty($ps['description']))
                    <div class="ws-ph-desc">{{ $ps['description'] }}</div>
                @endif
            </div>
            <button class="ws-save-btn" wire:click="saveSectionDirect">Save</button>
        </div>

        {{-- Panel Body --}}
        <div class="ws-pb">

            {{-- ======================================= --}}
            {{-- PROMOTION IMAGES                         --}}
            {{-- ======================================= --}}
            @if($activeSection === 'promotion_images')
                <div class="ws-note">
                    Portrait images (9:16 ratio) look best. They appear as a rotating carousel on the home page.
                </div>

                <div class="ws-sh">Current Images ({{ count($heroImages) }})</div>

                @if(count($heroImages) > 0)
                    <div class="ws-imgrid">
                        @foreach($heroImages as $idx => $img)
                            <div class="ws-imgitem">
                                <img src="{{ $imageUrl($img) }}" alt="Promo {{ (int)$idx + 1 }}">
                                <button class="ws-img-del" wire:click="removeHeroImage({{ (int)$idx }})" title="Remove">&times;</button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="ws-imgrid">
                        <div class="ws-img-empty">
                            <div>
                                <div style="font-size:1.75rem;margin-bottom:.5rem">🖼</div>
                                No promotional images yet
                            </div>
                        </div>
                    </div>
                @endif

                <div class="ws-hr"></div>
                <div class="ws-lock-note">
                    💡 To <strong style="color:rgba(255,255,255,.55)">add</strong> new images, expand <em>Advanced Settings</em> below and go to the "Promotion &amp; Hero" tab.
                </div>

            {{-- ======================================= --}}
            {{-- WELCOME SECTION                          --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'welcome_section')
                <div class="ws-field">
                    <label class="ws-label">Welcome Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.welcome_title"
                           placeholder="Welcome to Amiga Gracia">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Welcome Subtitle</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.welcome_subtitle" rows="4"
                              placeholder="Your journey deserves more than a destination - it deserves an exceptional experience"></textarea>
                </div>

            {{-- ======================================= --}}
            {{-- FEATURED PROMOTIONS HEADER               --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'promo_gallery')
                <div class="ws-field">
                    <label class="ws-label">Featured Promotions Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.promo_gallery_title" placeholder="Featured Promotions">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Featured Promotions Subtitle</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.promo_gallery_subtitle" rows="3"
                              placeholder="Browse three highlighted offers from our latest deals."></textarea>
                </div>

            {{-- ======================================= --}}
            {{-- REQUEST TRAVEL BOOKINGS HEADER           --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'booking_section')
                <div class="ws-field">
                    <label class="ws-label">Section Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.booking_section_title" placeholder="Request Travel Bookings">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Section Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.booking_section_description" rows="3"
                              placeholder="Kay Amiga, Hassle Free Ka! Select a booking category to start your transaction request."></textarea>
                </div>

            {{-- ======================================= --}}
            {{-- BOOKING CATEGORY CARDS                   --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'booking_cards')
                <div class="ws-note">
                    🃏 To manage or reorder the 6 booking category cards (2GO, Starlite, Cebu Pacific, PAL, AirAsia, Tour Packages), expand <strong>Advanced Settings</strong> below and switch to the "Request Bookings" tab.
                </div>

            {{-- ======================================= --}}
            {{-- CANCEL BOOKING MODAL                     --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'cancel_modal')
                <div class="ws-field">
                    <label class="ws-label">Modal Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.cancel_modal_title" placeholder="Want to cancel your booking?">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Modal Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.cancel_modal_desc" rows="3"
                              placeholder="We received your proof of payment..."></textarea>
                </div>
                <div class="ws-field">
                    <label class="ws-label">Cancel Button Text</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.cancel_modal_btn_cancel" placeholder="Maybe later">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Confirm Button Text</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.cancel_modal_btn_confirm" placeholder="Start cancellation">
                </div>

            {{-- ======================================= --}}
            {{-- HEADER CONFIG                            --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'header_config')
                <div class="ws-field">
                    <label class="ws-label">Company Name</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.header_data.company_name" placeholder="Amiga Gracia">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Phone</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.header_data.phone" placeholder="+63 xxx xxx xxxx">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Email</label>
                    <input type="email" class="ws-input" wire:model.blur="settingsData.header_data.email" placeholder="info@amiga.com">
                </div>
                <div class="ws-hr"></div>
                <div class="ws-lock-note">🔒 Navigation links are part of the site template and cannot be changed here.</div>
                <div class="ws-lock-note">🔒 Logo upload is available in <em>Advanced Settings → Header tab</em>.</div>

            {{-- ======================================= --}}
            {{-- FOOTER CONFIG                            --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'footer_config')
                <div class="ws-note">The footer appears at the bottom of every page on the site.</div>
                <div class="ws-field">
                    <label class="ws-label">About Text</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.footer_data.about" rows="4"
                              placeholder="Brief description of the company…"></textarea>
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">Contact Details</div>
                <div class="ws-field">
                    <label class="ws-label">Phone</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.footer_data.phone" placeholder="+63 xxx xxx xxxx">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Email</label>
                    <input type="email" class="ws-input" wire:model.blur="settingsData.footer_data.email" placeholder="info@amiga.com">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Address</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.footer_data.address" rows="2" placeholder="Street, City, Province"></textarea>
                </div>
                <div class="ws-field">
                    <label class="ws-label">Website URL</label>
                    <input type="url" class="ws-input" wire:model.blur="settingsData.footer_data.website" placeholder="https://…">
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">Social Media Links</div>
                @foreach($footerData['social_links'] ?? [] as $li => $link)
                    <div class="ws-rep">
                        <div class="ws-rep-hd">
                            <span class="ws-rep-num">Link {{ $li + 1 }}</span>
                            <button class="ws-rep-del" wire:click="removeSocialLink({{ $li }})">×</button>
                        </div>
                        <div class="ws-field">
                            <label class="ws-label">Platform</label>
                            <input type="text" class="ws-input" wire:model.blur="settingsData.footer_data.social_links.{{ $li }}.platform" placeholder="Facebook">
                        </div>
                        <div class="ws-field" style="margin-bottom:0">
                            <label class="ws-label">URL</label>
                            <input type="url" class="ws-input" wire:model.blur="settingsData.footer_data.social_links.{{ $li }}.url" placeholder="https://facebook.com/…">
                        </div>
                    </div>
                @endforeach
                <button class="ws-add" wire:click="addSocialLink">+ Add Social Link</button>

            {{-- ======================================= --}}
            {{-- ABOUT CONTENT                            --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'about_content')
                <div class="ws-field">
                    <label class="ws-label">Page Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.title" placeholder="About Us">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.description" rows="6"
                              placeholder="Tell visitors about your company…"></textarea>
                </div>

            {{-- ======================================= --}}
            {{-- QUICK FACTS                              --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'quick_facts')
                <div class="ws-note">Quick facts appear as highlighted cards on the About page.</div>
                @foreach($content['quick_facts'] ?? [] as $fi => $fact)
                    <div class="ws-rep">
                        <div class="ws-rep-hd">
                            <span class="ws-rep-num">Fact {{ $fi + 1 }}</span>
                            <button class="ws-rep-del" wire:click="removeQuickFact({{ $fi }})">×</button>
                        </div>
                        <div class="ws-field">
                            <label class="ws-label">Label</label>
                            <input type="text" class="ws-input" wire:model.blur="settingsData.content.quick_facts.{{ $fi }}.label" placeholder="Established">
                        </div>
                        <div class="ws-field" style="margin-bottom:0">
                            <label class="ws-label">Value</label>
                            <textarea class="ws-textarea" wire:model.blur="settingsData.content.quick_facts.{{ $fi }}.value" rows="2" placeholder="July 2017 in Oriental Mindoro"></textarea>
                        </div>
                    </div>
                @endforeach
                <button class="ws-add" wire:click="addQuickFact">+ Add Quick Fact</button>

            {{-- ======================================= --}}
            {{-- SERVICES HEADER                          --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'services_header')
                <div class="ws-field">
                    <label class="ws-label">Page Badge</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.badge" placeholder="Services">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.title" placeholder="Our Services">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.description" rows="3"></textarea>
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">Call to Action Banner</div>
                <div class="ws-field">
                    <label class="ws-label">CTA Badge</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.service_cta.badge">
                </div>
                <div class="ws-field">
                    <label class="ws-label">CTA Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.service_cta.title">
                </div>
                <div class="ws-field">
                    <label class="ws-label">CTA Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.service_cta.description" rows="2"></textarea>
                </div>
                <div class="ws-field">
                    <label class="ws-label">CTA Button Text</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.service_cta.button_text">
                </div>

            {{-- ======================================= --}}
            {{-- SERVICE CARDS                            --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'service_cards')
                @php $sc = count($content['service_cards'] ?? []); @endphp
                <div class="ws-note">{{ $sc }} service {{ $sc === 1 ? 'card' : 'cards' }} configured. Edit card titles below; use Advanced Settings for full editing.</div>
                @foreach($content['service_cards'] ?? [] as $si => $card)
                    <div class="ws-field">
                        <label class="ws-label">Card {{ $si + 1 }} Title</label>
                        <input type="text" class="ws-input" wire:model.blur="settingsData.content.service_cards.{{ $si }}.title">
                    </div>
                @endforeach

            {{-- ======================================= --}}
            {{-- TOUR HEADER                              --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'tour_header')
                <div class="ws-field">
                    <label class="ws-label">Page Badge</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.badge" placeholder="Tour Packages">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.title">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.description" rows="3"></textarea>
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">Tabs & Destinations</div>
                <div class="ws-field">
                    <label class="ws-label">Domestic Tab</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.tab_domestic_label">
                </div>
                <div class="ws-field">
                    <label class="ws-label">International Tab</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.tab_international_label">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Destinations Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.destinations_title">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Destinations Desc</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.destinations_desc" rows="2"></textarea>
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">CTA Section</div>
                <div class="ws-field">
                    <label class="ws-label">CTA Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.cta_title">
                </div>
                <div class="ws-field">
                    <label class="ws-label">CTA Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.cta_desc" rows="2"></textarea>
                </div>
                <div class="ws-field">
                    <label class="ws-label">CTA Button</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.cta_button">
                </div>

            {{-- ======================================= --}}
            {{-- TOUR PACKAGES                            --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'tour_packages')
                @php
                    $dc = count(data_get($content, 'tour_packages.domestic', []));
                    $ic = count(data_get($content, 'tour_packages.international', []));
                @endphp
                <div class="ws-note">Manage tour packages in Advanced Settings below. Images and destinations are configured there.</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    <div style="border:1px solid rgba(255,255,255,.05);border-radius:.75rem;padding:1rem;text-align:center;">
                        <div style="font-size:1.4rem;margin-bottom:.25rem;">🛳</div>
                        <div style="font-size:1.25rem;font-weight:700;color:rgba(255,255,255,.85);">{{ $dc }}</div>
                        <div style="font-size:.62rem;color:rgba(255,255,255,.3);margin-top:.1rem;">Domestic</div>
                    </div>
                    <div style="border:1px solid rgba(255,255,255,.05);border-radius:.75rem;padding:1rem;text-align:center;">
                        <div style="font-size:1.4rem;margin-bottom:.25rem;">✈️</div>
                        <div style="font-size:1.25rem;font-weight:700;color:rgba(255,255,255,.85);">{{ $ic }}</div>
                        <div style="font-size:.62rem;color:rgba(255,255,255,.3);margin-top:.1rem;">International</div>
                    </div>
                </div>

            {{-- ======================================= --}}
            {{-- CONTACT INFO                             --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'contact_info')
                <div class="ws-field">
                    <label class="ws-label">Page Badge</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.badge" placeholder="Contact Us">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.title" placeholder="Get in Touch">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.description" rows="3"></textarea>
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">Contact Details</div>
                <div class="ws-field">
                    <label class="ws-label">Phone</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.phone" placeholder="+63 xxx xxx xxxx">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Email</label>
                    <input type="email" class="ws-input" wire:model.blur="settingsData.content.email" placeholder="info@amiga.com">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Address</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.address" rows="3"></textarea>
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">Social Links</div>
                @foreach($content['social_links'] ?? [] as $sli => $sl)
                    <div class="ws-rep">
                        <div class="ws-rep-hd">
                            <span class="ws-rep-num">{{ data_get($sl,'platform','Link ' . ($sli+1)) }}</span>
                            <button class="ws-rep-del" wire:click="removeSocialLink({{ $sli }}, 'contact')">×</button>
                        </div>
                        <div class="ws-field">
                            <label class="ws-label">Platform</label>
                            <input type="text" class="ws-input" wire:model.blur="settingsData.content.social_links.{{ $sli }}.platform" placeholder="Facebook">
                        </div>
                        <div class="ws-field" style="margin-bottom:0">
                            <label class="ws-label">URL</label>
                            <input type="url" class="ws-input" wire:model.blur="settingsData.content.social_links.{{ $sli }}.url" placeholder="https://…">
                        </div>
                    </div>
                @endforeach
                <button class="ws-add" wire:click="addSocialLink('contact')">+ Add Social Link</button>

            {{-- ======================================= --}}
            {{-- FAQs CONTENT                             --}}
            {{-- ======================================= --}}
            @elseif(in_array($activeSection, ['faqs_content', 'faqs_list']))
                <div class="ws-field">
                    <label class="ws-label">Page Badge</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.badge" placeholder="FAQs">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.title" placeholder="Frequently Asked Questions">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.description" rows="3"></textarea>
                </div>
                <div class="ws-field">
                    <label class="ws-label">Empty State Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.empty_title">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Empty State Desc</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.empty_desc" rows="2"></textarea>
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">Call to Action</div>
                <div class="ws-field">
                    <label class="ws-label">CTA Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.cta_title">
                </div>
                <div class="ws-field">
                    <label class="ws-label">CTA Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.cta_desc" rows="2"></textarea>
                </div>
                <div class="ws-field">
                    <label class="ws-label">CTA Button</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.cta_btn">
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">Q&A Items</div>
                @foreach($content['faqs'] ?? [] as $fi => $faq)
                    <div class="ws-rep">
                        <div class="ws-rep-hd">
                            <span class="ws-rep-num">Question {{ $fi + 1 }}</span>
                            <button class="ws-rep-del" wire:click="removeFaq({{ $fi }})">×</button>
                        </div>
                        <div class="ws-field">
                            <label class="ws-label">Question</label>
                            <input type="text" class="ws-input" wire:model.blur="settingsData.content.faqs.{{ $fi }}.question" placeholder="How can I book a ticket?">
                        </div>
                        <div class="ws-field" style="margin-bottom:0">
                            <label class="ws-label">Answer</label>
                            <textarea class="ws-textarea" wire:model.blur="settingsData.content.faqs.{{ $fi }}.answer" rows="3" placeholder="Answer here..."></textarea>
                        </div>
                    </div>
                @endforeach
                <button class="ws-add" wire:click="addFaq">+ Add Question</button>

            {{-- ======================================= --}}
            {{-- DOWNLOAD CONTENT                         --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'download_content')
                <div class="ws-field">
                    <label class="ws-label">Page Badge</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.badge" placeholder="Download App">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.title" placeholder="Download Our App">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.description" rows="4"></textarea>
                </div>
                <div class="ws-field">
                    <label class="ws-label">iOS Button Text</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.btn_ios">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Android Button Text</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.btn_android">
                </div>
                <div class="ws-hr"></div>
                <div class="ws-sh">How It Works Section</div>
                <div class="ws-field">
                    <label class="ws-label">Section Label</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.how_it_works_label" placeholder="How It Works">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Section Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.how_it_works_title" placeholder="Install in 3 Easy Steps">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Section Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.how_it_works_description" rows="3"></textarea>
                </div>

            {{-- ======================================= --}}
            {{-- DOWNLOAD STEPS                           --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'download_steps')
                <div class="ws-note">{{ count($content['download_steps'] ?? []) }} download steps configured.</div>
                @foreach($content['download_steps'] ?? [] as $dsi => $step)
                    <div class="ws-rep">
                        <div class="ws-rep-hd">
                            <span class="ws-rep-num">Step {{ $dsi + 1 }}</span>
                        </div>
                        <div class="ws-field">
                            <label class="ws-label">Title</label>
                            <input type="text" class="ws-input" wire:model.blur="settingsData.content.download_steps.{{ $dsi }}.title">
                        </div>
                        <div class="ws-field" style="margin-bottom:0">
                            <label class="ws-label">Description</label>
                            <textarea class="ws-textarea" wire:model.blur="settingsData.content.download_steps.{{ $dsi }}.description" rows="2"></textarea>
                        </div>
                    </div>
                @endforeach

            {{-- ======================================= --}}
            {{-- SCHEDULES HERO                           --}}
            {{-- ======================================= --}}
            @elseif($activeSection === 'schedules_hero')
                <div class="ws-field" style="margin-bottom:1.25rem;">
                    <label class="ws-label">Schedules Global Visibility</label>
                    <div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(255,255,255,.08);border-radius:.75rem;padding:.875rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;">
                            <span style="font-size:.78rem;font-weight:700;color:{{ \App\Models\WebsiteSetting::areSchedulesEnabled() ? '#4ade80' : '#f87171' }};">
                                {{ \App\Models\WebsiteSetting::areSchedulesEnabled() ? '● Schedules are Visible (ON)' : '● Schedules are Hidden (OFF)' }}
                            </span>
                        </div>
                        <p style="font-size:.68rem;color:rgba(255,255,255,.45);margin-bottom:.75rem;line-height:1.4;">
                            Turn off all schedules on both the Booking Page and the Schedules Page.
                        </p>
                        <button type="button"
                                wire:click="toggleSchedulesVisibility"
                                wire:confirm="{{ \App\Models\WebsiteSetting::areSchedulesEnabled() ? 'Are you sure you want to TURN OFF all schedules? All schedules will be hidden from both the Booking Page and the Schedules Page.' : 'Are you sure you want to TURN ON schedules?' }}"
                                style="width:100%;padding:.5rem;border-radius:.5rem;font-size:.75rem;font-weight:700;border:none;cursor:pointer;color:#fff;background:{{ \App\Models\WebsiteSetting::areSchedulesEnabled() ? '#dc2626' : '#16a34a' }};">
                            {{ \App\Models\WebsiteSetting::areSchedulesEnabled() ? 'Turn Off Schedules' : 'Turn On Schedules' }}
                        </button>
                    </div>
                </div>
                <div class="ws-field">
                    <label class="ws-label">Hero Badge Text</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.schedules_badge"
                           placeholder="Real-time schedules">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Page Title</label>
                    <input type="text" class="ws-input" wire:model.blur="settingsData.content.schedules_title"
                           placeholder="Schedule and Routes">
                </div>
                <div class="ws-field">
                    <label class="ws-label">Description</label>
                    <textarea class="ws-textarea" wire:model.blur="settingsData.content.schedules_description" rows="3"
                              placeholder="Browse available ferry and airline routes with live pricing, departure times, and accommodation options."></textarea>
                </div>

            @else
                <div class="ws-lock-note">Select a highlighted section in the preview to edit it.</div>
            @endif

        </div>{{-- end ws-pb --}}

        {{-- Panel Footer Save --}}
        <div class="ws-panel-footer">
            <button class="ws-save-full" wire:click="saveSectionDirect">
                ✓ Save &amp; Publish Changes
            </button>
        </div>
    </div>{{-- end ws-panel --}}
    @endif

</div>{{-- end ws-editor --}}

{{-- ============================================================ --}}
{{-- ADVANCED SETTINGS (full Filament form in collapsible)         --}}
{{-- ============================================================ --}}
<div class="ws-adv" x-data="{ advancedOpen: false }" wire:ignore.self>
    <button type="button" @click="advancedOpen = !advancedOpen" class="w-full flex items-center justify-between px-5 py-3.5 text-[0.8rem] font-medium text-[var(--fi-text-color,#64748b)] bg-[var(--fi-page-background-color)] hover:bg-white/5 transition-colors cursor-pointer select-none border-b border-[rgba(148,163,184,.1)]">
        <span style="display:flex;align-items:center;gap:.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
            Advanced Settings — Full Form Editor
        </span>
        <div class="flex items-center gap-4">
            <span style="font-size:.65rem;opacity:.4">Image uploads · Repeaters · SEO</span>
            <svg :class="{'rotate-180': advancedOpen}" class="w-4 h-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </div>
    </button>
    <div x-show="advancedOpen" style="display: none;">
        <div class="ws-adv-body">
            {{ $this->form }}
            <div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-700/40">
                <x-filament::button type="button" color="gray" tag="a" :href="route('filament.admin.pages.dashboard')">
                    Cancel
                </x-filament::button>
                <x-filament::button type="button" wire:click="save">
                    Save All Settings
                </x-filament::button>
            </div>
        </div>
    </div>
</div>

</x-filament-panels::page>
