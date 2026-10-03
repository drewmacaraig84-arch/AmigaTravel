<script>
window.__adminNotificationBaseUrl = @js(url('/admin/notifications'));

// Web Audio API synthesized notification chime (no external MP3 needed, zero latency)
let _adminAudioCtx = null;
function getAdminAudioContext() {
    try {
        if (!_adminAudioCtx) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) {
                _adminAudioCtx = new AudioCtx();
            }
        }
        if (_adminAudioCtx && _adminAudioCtx.state === 'suspended') {
            _adminAudioCtx.resume();
        }
        return _adminAudioCtx;
    } catch (e) {
        return null;
    }
}
document.addEventListener('click', () => getAdminAudioContext(), { once: true, passive: true });
document.addEventListener('keydown', () => getAdminAudioContext(), { once: true, passive: true });

window.playNotificationChime = function (force = false) {
    if (!force && localStorage.getItem('admin_notification_sound_muted') === 'true') {
        return;
    }
    try {
        const ctx = getAdminAudioContext();
        if (!ctx) return;
        const now = ctx.currentTime;

        // Tone 1: 587.33 Hz (D5)
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(587.33, now);
        gain1.gain.setValueAtTime(0.18, now);
        gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.35);

        // Tone 2: 880.00 Hz (A5)
        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(880.00, now + 0.12);
        gain2.gain.setValueAtTime(0.22, now + 0.12);
        gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.12);
        osc2.stop(now + 0.55);
    } catch (e) {
        console.warn('Audio chime playback inhibited:', e);
    }
};

window.showAdminNotificationToast = function (item) {
    if (!item || !item.title) return;
    let container = document.getElementById('admin-realtime-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'admin-realtime-toast-container';
        container.style.cssText = 'position:fixed;top:1.25rem;right:1.25rem;z-index:99999;display:flex;flex-direction:column;gap:0.5rem;max-width:24rem;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = 'pointer-events:auto;background:rgba(17,24,39,0.96);backdrop-filter:blur(8px);border:1px solid rgba(251,191,36,0.4);border-radius:0.75rem;padding:0.75rem 1rem;color:#fff;box-shadow:0 10px 25px -5px rgba(0,0,0,0.4);display:flex;align-items:flex-start;gap:0.75rem;cursor:pointer;transition:all 0.25s ease;transform:translateX(100%);opacity:0;';
    
    toast.innerHTML = `
        <div style="flex-shrink:0;margin-top:2px;">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:9999px;background:#f59e0b;color:#111827;font-weight:bold;font-size:12px;">🔔</span>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:13px;color:#f3f4f6;line-height:1.2;">${String(item.title).replace(/</g, '&lt;')}</div>
            <div style="font-size:12px;color:#9ca3af;margin-top:2px;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${String(item.message || '').replace(/</g, '&lt;')}</div>
        </div>
        <button type="button" style="color:#6b7280;background:none;border:none;cursor:pointer;font-size:16px;line-height:1;padding:0 2px;">&times;</button>
    `;

    toast.querySelector('button').onclick = (e) => {
        e.stopPropagation();
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 250);
    };

    toast.onclick = () => {
        if (item.url) window.location.href = item.url;
    };

    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    });

    setTimeout(() => {
        if (toast.isConnected) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 250);
        }
    }, 5500);
};

window.adminNotificationBell = function (config) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    return {
        baseUrl:       window.__adminNotificationBaseUrl || '/admin/notifications',
        notifications: config.initialNotifications ?? [],
        totalCount:    config.initialTotalCount ?? 0,
        unreadCount:   config.initialUnreadCount ?? 0,
        soundMuted:    localStorage.getItem('admin_notification_sound_muted') === 'true',
        isRinging:     false,
        lastHeartbeatVersion: null,
        lastUnreadCount: config.initialUnreadCount ?? 0,
        lastKnownLatestId: null,
        heartbeatTimer: null,
        selectedIds:   [],
        dropdownOpen:  false,
        actionMenuOpen: false,
        itemMenuOpen:  null,
        confirmingDelete: false,
        deleteTargetIds: [],
        deleteTitle:   '',
        successMessage: '',
        bulkMode:      false,
        activeTab:     'all',
        dropdownStyles: { position: 'fixed', left: '-9999px', top: '-9999px', width: '340px', opacity: 0, pointerEvents: 'none' },
        updateDropdownPositionBound: null,
        busy:          false,

        formatTimeAgo(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now  = new Date();
            const ms   = now - date;
            if (isNaN(ms) || ms < 0) return 'just now';
            const mins  = Math.floor(ms / 60000);
            if (mins < 1)  return 'just now';
            if (mins === 1) return '1 min ago';
            if (mins < 60)  return mins + ' min ago';
            const hrs = Math.floor(mins / 60);
            if (hrs === 1)  return '1 hour ago';
            if (hrs < 24)   return hrs + ' hours ago';
            const days = Math.floor(hrs / 24);
            if (days === 1) return 'yesterday';
            if (days < 7)   return days + ' days ago';
            const weeks = Math.floor(days / 7);
            if (weeks === 1) return '1 week ago';
            return weeks + ' weeks ago';
        },

        init() {
            this.selectedIds = [];
            this.fetchDropdown();
            if (!this.heartbeatTimer) {
                this.heartbeatTimer = setInterval(() => {
                    this.checkHeartbeat();
                }, 3500);
            }
        },

        triggerBellRing() {
            this.isRinging = true;
            setTimeout(() => { this.isRinging = false; }, 2200);
        },

        toggleSound() {
            this.soundMuted = !this.soundMuted;
            localStorage.setItem('admin_notification_sound_muted', this.soundMuted ? 'true' : 'false');
            if (!this.soundMuted) {
                window.playNotificationChime(true);
                this.showSuccess('Notification sound enabled');
            } else {
                this.showSuccess('Notification sound muted');
            }
        },

        async checkHeartbeat() {
            try {
                const res = await fetch(`${this.baseUrl}/heartbeat`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = await res.json();
                
                const isVersionChanged = this.lastHeartbeatVersion !== null && data.version !== this.lastHeartbeatVersion;
                const hasNewUnread = this.lastHeartbeatVersion !== null && (data.unread > this.lastUnreadCount || (data.latest_id && data.latest_id !== this.lastKnownLatestId && data.unread > 0));

                if (hasNewUnread) {
                    this.triggerBellRing();
                    window.playNotificationChime();
                    window.showAdminNotificationToast({
                        title: data.latest_title || 'New Admin Activity',
                        message: data.latest_message || 'A new update requires your attention',
                        url: data.latest_url || '/admin',
                    });
                }

                if (isVersionChanged || hasNewUnread) {
                    window.dispatchEvent(new CustomEvent('admin-data-updated', { detail: data }));
                    if (window.Livewire) {
                        window.Livewire.dispatch('refresh');
                    }
                    if (this.dropdownOpen) {
                        await this.fetchDropdown();
                    }
                }

                this.unreadCount = data.unread;
                this.totalCount = data.total;
                this.lastHeartbeatVersion = data.version;
                this.lastUnreadCount = data.unread;
                if (data.latest_id) {
                    this.lastKnownLatestId = data.latest_id;
                }
            } catch (e) {
                // Silently handle transient connection issues
            }
        },

        get selectedCount() {
            return this.selectedIds.length;
        },

        get visibleNotifications() {
            if (this.activeTab === 'unread') {
                return this.notifications.filter(n => !n.is_read);
            }
            return this.notifications;
        },

        get allSelected() {
            const vis = this.visibleNotifications;
            return vis.length > 0 && vis.every(n => this.selectedIds.includes(n.id));
        },

        toggleSelectAll() {
            const vis = this.visibleNotifications;
            const allSel = vis.every(n => this.selectedIds.includes(n.id));
            if (allSel) {
                const visIds = vis.map(n => n.id);
                this.selectedIds = this.selectedIds.filter(id => !visIds.includes(id));
            } else {
                const newIds = vis.map(n => n.id).filter(id => !this.selectedIds.includes(id));
                this.selectedIds = [...this.selectedIds, ...newIds];
            }
        },

        toggleSelection(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            } else {
                this.selectedIds = [...this.selectedIds, id];
            }
        },

        async fetchDropdown() {
            if (this.busy) return;
            this.busy = true;
            try {
                const res = await fetch(`${this.baseUrl}/dropdown`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = await res.json();
                this.notifications = data.notifications;
                this.totalCount    = data.total;
                this.unreadCount   = data.unread;
                this.selectedIds   = [];
            } finally {
                this.busy = false;
            }
        },

        async sendAction(url, method, ids) {
            if (!ids.length) return;
            this.busy = true;
            const targetUrl = url.startsWith('/admin/notifications') ? url.replace('/admin/notifications', this.baseUrl) : url;
            try {
                const res = await fetch(targetUrl, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ ids }),
                });
                if (!res.ok) return;
                const data = await res.json();
                await this.fetchDropdown();
                if (data.unread !== undefined) this.unreadCount = data.unread;
                if (data.total  !== undefined) this.totalCount  = data.total;
                this.selectedIds = [];
                this.showSuccess(data.message || 'Done.');
            } finally {
                this.busy = false;
                this.confirmingDelete = false;
                this.deleteTargetIds  = [];
            }
        },

        async markRead(ids = null)   { await this.sendAction(`${this.baseUrl}/api/mark-read`,   'POST',   ids ?? this.selectedIds); },
        async markAllRead() {
            this.busy = true;
            try {
                const res = await fetch(`${this.baseUrl}/api/mark-all-read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                await this.fetchDropdown();
                this.unreadCount = 0;
                this.selectedIds = [];
                this.actionMenuOpen = false;
                this.showSuccess('All notifications marked as read.');
            } finally {
                this.busy = false;
            }
        },
        async markUnread(ids = null) { await this.sendAction(`${this.baseUrl}/api/mark-unread`, 'POST',   ids ?? this.selectedIds); },
        async confirmDelete()        { await this.sendAction(`${this.baseUrl}/api`,             'DELETE', this.deleteTargetIds); },

        deleteSelected() {
            if (!this.selectedCount) return;
            this.deleteTitle    = `Delete ${this.selectedCount} selected notification${this.selectedCount > 1 ? 's' : ''}?`;
            this.deleteTargetIds = [...this.selectedIds];
            this.confirmingDelete = true;
        },

        deleteNotification(id) {
            this.deleteTitle    = 'Delete this notification?';
            this.deleteTargetIds = [id];
            this.confirmingDelete = true;
        },

        toggleDropdown() {
            this.dropdownOpen = !this.dropdownOpen;

            if (this.dropdownOpen) {
                this.$nextTick(() => {
                    this.updateDropdownPosition();
                    this.updateDropdownPositionBound = this.updateDropdownPosition.bind(this);
                    window.addEventListener('resize', this.updateDropdownPositionBound);
                    window.addEventListener('scroll', this.updateDropdownPositionBound, true);
                });
            } else {
                if (this.updateDropdownPositionBound) {
                    window.removeEventListener('resize', this.updateDropdownPositionBound);
                    window.removeEventListener('scroll', this.updateDropdownPositionBound, true);
                    this.updateDropdownPositionBound = null;
                }
            }
        },
        updateDropdownPosition() {
            const trigger = document.getElementById('adminNotificationBellBtn');
            const panel = document.getElementById('adminNotificationDropdown');
            if (!trigger || !panel) return;

            const triggerRect = trigger.getBoundingClientRect();
            const panelWidth = Math.min(360, window.innerWidth - 32);
            const idealLeft = triggerRect.right - panelWidth;
            const left = Math.max(16, Math.min(idealLeft, window.innerWidth - panelWidth - 16));
            const rawTop = triggerRect.bottom + 8;
            const top = rawTop + panel.clientHeight > window.innerHeight ? Math.max(16, triggerRect.top - panel.clientHeight - 8) : rawTop;

            this.dropdownStyles = {
                position: 'fixed',
                left: `${left}px`,
                top: `${top}px`,
                width: `${panelWidth}px`,
                maxHeight: 'min(88dvh, 560px)',
                zIndex: 11000,
                opacity: 1,
                pointerEvents: 'auto',
            };
        },
        openNotification(n)         { window.location.href = n.url; },
        showSuccess(msg) {
            this.successMessage = msg;
            setTimeout(() => { this.successMessage = ''; }, 3000);
        },
    };
};

/* ------------------------------------------------------------------ */
/* Full notifications page component                                    */
/* ------------------------------------------------------------------ */
window.adminNotificationsPage = function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    return {
        baseUrl:          window.__adminNotificationBaseUrl || '/admin/notifications',
        notifications:    [],
        totalCount:       0,
        unreadCount:      0,
        perPage:          10,
        page:             1,
        lastPage:         1,
        search:           '',
        activeTab:        'all',
        selectedIds:      [],
        actionMenuOpen:   false,
        itemMenuOpen:     null,
        confirmingDelete: false,
        deleteTargetIds:  [],
        deleteTitle:      '',
        successMessage:   '',
        busy:             false,

        init() { this.loadNotifications(); },

        get selectedCount() { return this.selectedIds.length; },

        get allSelected() {
            return this.notifications.length > 0
                && this.selectedCount === this.notifications.length;
        },

        toggleSelectAll() {
            if (this.allSelected) { this.selectedIds = []; return; }
            this.selectedIds = this.notifications.map(n => n.id);
        },

        toggleSelection(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            } else {
                this.selectedIds = [...this.selectedIds, id];
            }
        },

        formatTimeAgo(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now  = new Date();
            const ms   = now - date;
            if (isNaN(ms) || ms < 0) return 'just now';
            const mins  = Math.floor(ms / 60000);
            if (mins < 1)  return 'just now';
            if (mins === 1) return '1 min ago';
            if (mins < 60)  return mins + ' min ago';
            const hrs = Math.floor(mins / 60);
            if (hrs === 1)  return '1 hour ago';
            if (hrs < 24)   return hrs + ' hours ago';
            const days = Math.floor(hrs / 24);
            if (days === 1) return 'yesterday';
            if (days < 7)   return days + ' days ago';
            const weeks = Math.floor(days / 7);
            if (weeks === 1) return '1 week ago';
            return weeks + ' weeks ago';
        },

        async loadNotifications(page = this.page) {
            if (this.busy) return;
            this.busy = true;
            try {
                const params = new URLSearchParams();
                params.set('page',     String(page));
                params.set('per_page', String(this.perPage));
                params.set('search',   this.search);
                if (this.activeTab === 'unread') params.set('unread_only', '1');

                const res = await fetch(`${this.baseUrl}/api/list?${params}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                const data = await res.json();
                this.notifications = data.notifications;
                this.totalCount    = data.total;
                this.unreadCount   = data.unread;
                this.perPage       = data.per_page;
                this.page          = data.page;
                this.lastPage      = data.last_page;
                this.selectedIds   = [];
            } finally {
                this.busy = false;
            }
        },

        async switchTab(tab) {
            if (this.activeTab === tab) return;
            this.activeTab = tab;
            this.page      = 1;
            this.selectedIds = [];
            await this.loadNotifications(1);
        },

        async sendAction(url, method, ids) {
            if (!ids.length) return;
            this.busy = true;
            const targetUrl = url.startsWith('/admin/notifications') ? url.replace('/admin/notifications', this.baseUrl) : url;
            try {
                const res = await fetch(targetUrl, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ ids }),
                });
                if (!res.ok) return;
                const data = await res.json();
                await this.loadNotifications(1);
                this.selectedIds = [];
                if (data.unread !== undefined) this.unreadCount = data.unread;
                if (data.total  !== undefined) this.totalCount  = data.total;
                this.showSuccess(data.message || 'Done.');
            } finally {
                this.busy = false;
                this.confirmingDelete = false;
                this.deleteTargetIds  = [];
            }
        },

        async markRead(ids = null)   { await this.sendAction(`${this.baseUrl}/api/mark-read`,   'POST',   ids ?? this.selectedIds); },
        async markAllRead() {
            this.busy = true;
            try {
                const res = await fetch(`${this.baseUrl}/api/mark-all-read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;
                await this.loadNotifications(1);
                this.unreadCount = 0;
                this.selectedIds = [];
                this.showSuccess('All notifications marked as read.');
            } finally {
                this.busy = false;
            }
        },
        async markUnread(ids = null) { await this.sendAction(`${this.baseUrl}/api/mark-unread`, 'POST',   ids ?? this.selectedIds); },
        async confirmDelete()        { await this.sendAction(`${this.baseUrl}/api`,             'DELETE', this.deleteTargetIds); },

        deleteSelected() {
            if (!this.selectedCount) return;
            this.deleteTitle     = `Delete ${this.selectedCount} selected notification${this.selectedCount > 1 ? 's' : ''}?`;
            this.deleteTargetIds = [...this.selectedIds];
            this.confirmingDelete = true;
        },

        deleteNotification(id) {
            this.deleteTitle     = 'Delete this notification?';
            this.deleteTargetIds = [id];
            this.confirmingDelete = true;
        },

        async changePage(page) {
            if (page < 1 || page > this.lastPage || page === this.page) return;
            this.page = page;
            await this.loadNotifications(page);
        },

        async searchNotifications() {
            this.page = 1;
            await this.loadNotifications(1);
        },

        openNotification(n) { window.location.href = n.url; },

        showSuccess(msg) {
            this.successMessage = msg;
            setTimeout(() => { this.successMessage = ''; }, 3500);
        },
    };
};
</script>
