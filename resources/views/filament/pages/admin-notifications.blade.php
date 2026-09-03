@php use App\Filament\Pages\AdminNotifications; @endphp

<x-filament-panels::page>
    <div x-data="adminNotificationsPage()" x-init="init()" class="space-y-6">

        {{-- ─────────────────── Toast ─────────────────── --}}
        <div
            x-show="successMessage"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl border border-emerald-200 dark:border-emerald-800/80 bg-white dark:bg-gray-900 px-5 py-3.5 shadow-xl"
        >
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="successMessage"></span>
        </div>

        {{-- ─────────────────── Page Header Card ─────────────────── --}}
        <div class="rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 ring-1 ring-amber-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Admin Notifications</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Real-time alerts for incoming bookings, refund requests, cancellations, and inquiries.</p>
                    </div>
                </div>

                {{-- Header Actions & Stat Pills --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Mark All Read Button --}}
                    <button
                        type="button"
                        x-show="unreadCount > 0"
                        x-cloak
                        @click.prevent="markAllRead()"
                        :disabled="busy"
                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 dark:border-emerald-800/80 bg-emerald-50 dark:bg-emerald-950/40 px-4 py-2 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-all shadow-xs"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Mark all as read</span>
                    </button>

                    {{-- Total Counter --}}
                    <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/60 px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300">
                        <span class="text-gray-400 dark:text-gray-500">Total:</span>
                        <span class="font-bold text-gray-900 dark:text-white text-sm" x-text="totalCount"></span>
                    </div>

                    {{-- Unread Counter with Pulse Dot --}}
                    <div class="inline-flex items-center gap-2 rounded-xl border border-amber-300/80 dark:border-amber-700/60 bg-amber-50/90 dark:bg-amber-950/40 px-4 py-2 text-xs font-bold text-amber-800 dark:text-amber-300 shadow-xs">
                        <span class="relative flex h-2 w-2">
                            <span x-show="unreadCount > 0" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span x-text="unreadCount"></span>
                        <span>Unread</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─────────────────── Main Card ─────────────────── --}}
        <div class="rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">

            {{-- ── Tabs + Toolbar Header ── --}}
            <div class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-900/60">

                {{-- Tab navigation --}}
                <div class="flex items-center justify-between px-6 pt-5 pb-3">
                    <div class="flex items-center gap-2 p-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200/60 dark:border-gray-700/60">
                        <button
                            type="button"
                            @click="switchTab('all')"
                            :class="activeTab === 'all'
                                ? 'bg-white dark:bg-gray-900 text-gray-950 dark:text-white font-bold shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium'"
                            class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-xs transition-all"
                        >
                            <span>All</span>
                            <span class="rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:text-gray-300" x-text="totalCount"></span>
                        </button>
                        <button
                            type="button"
                            @click="switchTab('unread')"
                            :class="activeTab === 'unread'
                                ? 'bg-amber-500 text-white dark:text-gray-950 font-bold shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium'"
                            class="inline-flex items-center gap-2 rounded-lg px-4 py-1.5 text-xs transition-all"
                        >
                            <span>Unread</span>
                            <span
                                x-show="unreadCount > 0"
                                :class="activeTab === 'unread' ? 'bg-white/20 text-white dark:text-gray-950' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400'"
                                class="rounded-full px-2 py-0.5 text-[10px] font-bold leading-none"
                                x-text="unreadCount"
                            ></span>
                        </button>
                    </div>

                    {{-- Quick refresh button --}}
                    <button
                        type="button"
                        @click.prevent="loadNotifications(page)"
                        :disabled="busy"
                        title="Refresh notifications"
                        class="p-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                    >
                        <svg :class="busy ? 'animate-spin' : ''" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                </div>

                {{-- Toolbar with Selection & Search --}}
                <div class="flex flex-col gap-4 px-6 py-3.5 sm:flex-row sm:items-center sm:justify-between border-t border-gray-100 dark:border-gray-800/80 bg-white dark:bg-gray-900">
                    {{-- Left: Selection controls --}}
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer select-none text-xs font-semibold text-gray-700 dark:text-gray-300">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-amber-500 focus:ring-amber-500 focus:ring-offset-0 dark:border-gray-700 dark:bg-gray-800 accent-amber-500"
                                @click="toggleSelectAll()"
                                :checked="allSelected"
                                :indeterminate="selectedCount > 0 && !allSelected"
                            >
                            <span>Select all on page</span>
                        </label>

                        <template x-if="selectedCount > 0">
                            <div class="flex flex-wrap items-center gap-2 pl-2 border-l border-gray-200 dark:border-gray-700">
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400" x-text="selectedCount + ' selected'"></span>

                                <button type="button"
                                    @click.prevent="markRead()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Mark read
                                </button>

                                <button type="button"
                                    @click.prevent="markUnread()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Mark unread
                                </button>

                                <button type="button"
                                    @click.prevent="deleteSelected()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 dark:border-rose-900/60 bg-rose-50 dark:bg-rose-950/30 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:text-rose-400 hover:bg-rose-100 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Right: Search Box --}}
                    <div class="relative w-full sm:w-72">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="search"
                            placeholder="Search client, transaction #..."
                            x-model.debounce.400ms="search"
                            @input.debounce.400ms="searchNotifications()"
                            @keydown.enter.prevent="searchNotifications()"
                            style="padding-left: 2.4rem !important;"
                            class="w-full pr-8 py-2 rounded-xl border border-gray-200 dark:border-gray-700/80 bg-gray-50/50 dark:bg-gray-800/80 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20 transition"
                        >
                    </div>
                </div>
            </div>

            {{-- ── Notification List Items ── --}}
            <div class="divide-y divide-gray-100 dark:divide-gray-800/80">

                {{-- Loading Spinner --}}
                <div x-show="busy && notifications.length === 0" class="flex flex-col items-center justify-center py-20">
                    <svg class="h-8 w-8 animate-spin text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="mt-3 text-xs text-gray-400 font-medium">Loading notifications...</p>
                </div>

                {{-- Empty State --}}
                <template x-if="!busy && notifications.length === 0">
                    <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-500 ring-1 ring-amber-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341A6.002 6.002 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white"
                            x-text="activeTab === 'unread' ? 'You are all caught up!' : (search ? 'No notifications found' : 'No notifications yet')"></h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm"
                           x-text="activeTab === 'unread' ? 'There are no unread notifications right now. Check back later or switch to All.' : (search ? 'No notifications matched your search query. Try clearing the search.' : 'New activity will automatically appear here as bookings and inquiries come in.')"></p>
                    </div>
                </template>

                {{-- Notification Row --}}
                <template x-for="notification in notifications" :key="notification.id">
                    <div
                        class="group relative flex items-start gap-4 px-6 py-4.5 transition-all"
                        :class="!notification.is_read
                            ? 'bg-amber-500/[0.04] dark:bg-amber-500/[0.03] hover:bg-amber-500/[0.08] dark:hover:bg-amber-500/[0.06] border-l-4 border-amber-500'
                            : 'hover:bg-gray-50/80 dark:hover:bg-gray-800/40 border-l-4 border-transparent'"
                    >
                        {{-- Row Checkbox --}}
                        <div class="shrink-0 pt-2">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-amber-500 focus:ring-amber-500 focus:ring-offset-0 dark:border-gray-700 dark:bg-gray-800 accent-amber-500"
                                :value="notification.id"
                                @change="toggleSelection(notification.id)"
                                :checked="selectedIds.includes(notification.id)"
                            >
                        </div>

                        {{-- Category Icon Badge --}}
                        <div class="shrink-0 pt-0.5">
                            {{-- Booking Icon --}}
                            <template x-if="notification.type === 'new_booking'">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 ring-1 ring-emerald-500/20 shadow-xs">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                </div>
                            </template>

                            {{-- Rebooking Icon --}}
                            <template x-if="notification.type === 'rebooking' || notification.type === 'operator_reschedule_request'">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400 ring-1 ring-amber-500/20 shadow-xs">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                            </template>

                            {{-- Refund Icon --}}
                            <template x-if="notification.type === 'refund_request' || notification.type === 'refund_completed'">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-400 ring-1 ring-purple-500/20 shadow-xs">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </template>

                            {{-- Cancellation Icon --}}
                            <template x-if="notification.type === 'cancellation'">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400 ring-1 ring-rose-500/20 shadow-xs">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </template>

                            {{-- Inquiry Icon --}}
                            <template x-if="notification.type === 'inquiry'">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-400 ring-1 ring-sky-500/20 shadow-xs">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                </div>
                            </template>

                            {{-- Default Notice Icon --}}
                            <template x-if="!['new_booking','rebooking','operator_reschedule_request','refund_request','refund_completed','cancellation','inquiry'].includes(notification.type)">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 ring-1 ring-gray-500/20 shadow-xs">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </template>
                        </div>

                        {{-- Notification Content --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    {{-- Title + Type Badge + Unread Indicator --}}
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-gray-950 dark:text-white" x-text="notification.title"></span>

                                        {{-- Type Chip --}}
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold"
                                            :class="{
                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800': notification.type === 'new_booking',
                                                'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800': notification.type === 'rebooking' || notification.type === 'operator_reschedule_request',
                                                'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800': notification.type === 'refund_request' || notification.type === 'refund_completed',
                                                'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800': notification.type === 'cancellation',
                                                'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300 border border-sky-200 dark:border-sky-800': notification.type === 'inquiry',
                                                'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700': !['new_booking','rebooking','operator_reschedule_request','refund_request','refund_completed','cancellation','inquiry'].includes(notification.type)
                                            }"
                                            x-text="notification.type === 'new_booking' ? 'Booking' : (notification.type === 'rebooking' || notification.type === 'operator_reschedule_request' ? 'Rebooking' : (notification.type.includes('refund') ? 'Refund' : (notification.type === 'cancellation' ? 'Cancelled' : (notification.type === 'inquiry' ? 'Inquiry' : 'Alert'))))"
                                        ></span>

                                        {{-- New / Unread Badge --}}
                                        <span
                                            x-show="!notification.is_read"
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-500/15 border border-amber-500/30 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            New
                                        </span>
                                    </div>

                                    {{-- Body Message with nice high-contrast styling --}}
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed font-normal" x-text="notification.message"></p>
                                </div>

                                {{-- Timestamp with Clock Icon --}}
                                <div class="shrink-0 flex items-center gap-1.5 text-xs font-medium"
                                     :class="!notification.is_read ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-gray-400 dark:text-gray-500'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span x-text="formatTimeAgo(notification.created_at)"></span>
                                </div>
                            </div>

                            {{-- Action Buttons Toolbar --}}
                            <div class="mt-3.5 flex flex-wrap items-center gap-2.5">
                                {{-- Primary Action: Open --}}
                                <a
                                    :href="notification.url"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white dark:text-gray-950 dark:font-bold px-3.5 py-1.5 text-xs font-semibold shadow-xs hover:shadow transition-all"
                                >
                                    <span>Open</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>

                                {{-- Toggle Read / Unread Button --}}
                                <button
                                    type="button"
                                    @click.prevent="notification.is_read ? markUnread([notification.id]) : markRead([notification.id])"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-600 transition-colors shadow-xs"
                                >
                                    <template x-if="notification.is_read">
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Mark as unread</span>
                                        </div>
                                    </template>
                                    <template x-if="!notification.is_read">
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Mark as read</span>
                                        </div>
                                    </template>
                                </button>

                                {{-- Delete Button --}}
                                <button
                                    type="button"
                                    @click.prevent="deleteNotification(notification.id)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200/80 dark:border-rose-900/60 bg-rose-50/50 dark:bg-rose-950/20 px-3 py-1.5 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-100/80 dark:hover:bg-rose-900/40 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ── Pagination ── --}}
            <div class="flex flex-col gap-3 border-t border-gray-100 dark:border-gray-800 px-6 py-4 sm:flex-row sm:items-center sm:justify-between bg-gray-50/40 dark:bg-gray-900/40">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <template x-if="notifications.length === 0">
                        <span>No alerts to display.</span>
                    </template>
                    <template x-if="notifications.length > 0">
                        <span>Showing <span class="font-bold text-gray-900 dark:text-white" x-text="notifications.length"></span> items on page <span class="font-bold text-gray-900 dark:text-white" x-text="page"></span> of <span class="font-bold text-gray-900 dark:text-white" x-text="lastPage"></span> (<span x-text="totalCount"></span> total).</span>
                    </template>
                </p>
                <div class="flex items-center gap-2">
                    <button type="button"
                        @click.prevent="changePage(page - 1)"
                        :disabled="page <= 1 || busy"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-xs"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Previous
                    </button>
                    <button type="button"
                        @click.prevent="changePage(page + 1)"
                        :disabled="page >= lastPage || busy"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-xs"
                    >
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ─────────────────── Delete Confirmation Modal ─────────────────── --}}
        <div
            x-show="confirmingDelete"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4 py-6"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-6 shadow-2xl"
            >
                <div class="flex items-start gap-4">
                    <div class="h-11 w-11 shrink-0 rounded-2xl bg-rose-100 dark:bg-rose-950/60 flex items-center justify-center text-rose-600 dark:text-rose-400 ring-1 ring-rose-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-bold text-gray-950 dark:text-white">Confirm deletion</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 leading-relaxed" x-text="deleteTitle"></p>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button"
                        @click.prevent="confirmingDelete = false; deleteTargetIds = []"
                        :disabled="busy"
                        class="flex-1 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 transition-colors"
                    >Cancel</button>
                    <button type="button"
                        @click.prevent="confirmDelete()"
                        :disabled="busy"
                        class="flex-1 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-70 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors"
                    >Delete</button>
                </div>
            </div>
        </div>

    </div>

    @include('filament.admin.notification-scripts')
</x-filament-panels::page>
