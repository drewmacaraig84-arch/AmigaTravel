<script>
window.adminNotificationBell = function (config) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    return {
        notifications: config.initialNotifications ?? [],
        totalCount:    config.initialTotalCount ?? 0,
        unreadCount:   config.initialUnreadCount ?? 0,
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
                const res = await fetch('/admin/notifications/dropdown', {
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
            try {
                const res = await fetch(url, {
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

        async markRead(ids = null)   { await this.sendAction('/admin/notifications/api/mark-read',   'POST',   ids ?? this.selectedIds); },
        async markAllRead() {
            this.busy = true;
            try {
                const res = await fetch('/admin/notifications/api/mark-all-read', {
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
        async markUnread(ids = null) { await this.sendAction('/admin/notifications/api/mark-unread', 'POST',   ids ?? this.selectedIds); },
        async confirmDelete()        { await this.sendAction('/admin/notifications/api',             'DELETE', this.deleteTargetIds); },

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

                const res = await fetch(`/admin/notifications/api/list?${params}`, {
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
            try {
                const res = await fetch(url, {
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

        async markRead(ids = null)   { await this.sendAction('/admin/notifications/api/mark-read',   'POST',   ids ?? this.selectedIds); },
        async markAllRead() {
            this.busy = true;
            try {
                const res = await fetch('/admin/notifications/api/mark-all-read', {
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
        async markUnread(ids = null) { await this.sendAction('/admin/notifications/api/mark-unread', 'POST',   ids ?? this.selectedIds); },
        async confirmDelete()        { await this.sendAction('/admin/notifications/api',             'DELETE', this.deleteTargetIds); },

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
