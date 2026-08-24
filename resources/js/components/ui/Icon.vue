<script setup>
// Satu komponen ikon untuk seluruh aplikasi — tanpa dependency luar (ringan, cepat dimuat).
// Pemakaian: <Icon name="box" class="w-5 h-5 text-indigo-600" />
defineProps({
    name: { type: String, required: true },
    strokeWidth: { type: [Number, String], default: 1.75 },
});
</script>

<template>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" :stroke-width="strokeWidth" stroke-linecap="round" stroke-linejoin="round">
        <template v-if="name === 'home'">
            <polyline points="4 11 12 4 20 11" />
            <path d="M6 10v9a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-9" />
            <path d="M10 20v-6h4v6" />
        </template>

        <template v-else-if="name === 'box'">
            <path d="M21 8 12 3 3 8l9 5 9-5Z" />
            <path d="M3 8v8l9 5 9-5V8" />
            <path d="M12 13v8" />
        </template>

        <template v-else-if="name === 'tag'">
            <path d="M12.5 3H4a1 1 0 0 0-1 1v8.5a1 1 0 0 0 .29.71l9 9a1 1 0 0 0 1.42 0l8.5-8.5a1 1 0 0 0 0-1.42l-9-9A1 1 0 0 0 12.5 3Z" />
            <circle cx="8" cy="8" r="1.5" />
        </template>

        <template v-else-if="name === 'swap'">
            <path d="M4 7h13" /><path d="M14 3.5 17.5 7 14 10.5" />
            <path d="M20 17H7" /><path d="M10 13.5 6.5 17 10 20.5" />
        </template>

        <template v-else-if="name === 'star'">
            <path d="M12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2Z" />
        </template>

        <template v-else-if="name === 'users'">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </template>

        <template v-else-if="name === 'logout'">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
        </template>

        <template v-else-if="name === 'menu'">
            <line x1="3" y1="6" x2="21" y2="6" />
            <line x1="3" y1="12" x2="21" y2="12" />
            <line x1="3" y1="18" x2="21" y2="18" />
        </template>

        <template v-else-if="name === 'x'">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
        </template>

        <template v-else-if="name === 'plus'">
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
        </template>

        <template v-else-if="name === 'pencil'">
            <path d="M4 16.5V20h3.5L18.5 9 15 5.5 4 16.5Z" />
            <path d="M13.5 7 17 10.5" />
        </template>

        <template v-else-if="name === 'trash'">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
            <line x1="10" y1="11" x2="10" y2="17" />
            <line x1="14" y1="11" x2="14" y2="17" />
        </template>

        <template v-else-if="name === 'search'">
            <circle cx="11" cy="11" r="7" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </template>

        <template v-else-if="name === 'chevron-left'">
            <polyline points="15 18 9 12 15 6" />
        </template>

        <template v-else-if="name === 'chevron-right'">
            <polyline points="9 18 15 12 9 6" />
        </template>

        <template v-else-if="name === 'chevron-down'">
            <polyline points="6 9 12 15 18 9" />
        </template>

        <template v-else-if="name === 'check-circle'">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
        </template>

        <template v-else-if="name === 'clock'">
            <circle cx="12" cy="12" r="9" />
            <polyline points="12 7 12 12 15.5 14" />
        </template>

        <template v-else-if="name === 'refresh'">
            <path d="M20 12a8 8 0 1 1-3-6.24" />
            <polyline points="21 3 21 7 17 7" />
        </template>

        <template v-else-if="name === 'filter'">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
        </template>

        <template v-else-if="name === 'image'">
            <rect x="3" y="3" width="18" height="18" rx="2" />
            <circle cx="8.5" cy="8.5" r="1.5" />
            <polyline points="21 15 16 10 5 21" />
        </template>

        <template v-else-if="name === 'bell'">
            <path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6Z" />
            <path d="M10 19a2 2 0 0 0 4 0" />
        </template>

        <template v-else-if="name === 'chat'">
            <path d="M3 5h18v11H9l-4 4v-4H3Z" />
        </template>

        <template v-else-if="name === 'inbox'">
            <rect x="3" y="8" width="18" height="12" rx="2" />
            <path d="M3 13h5l2 3h4l2-3h5" />
        </template>

        <template v-else-if="name === 'alert'">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            <line x1="12" y1="9" x2="12" y2="13" />
            <line x1="12" y1="17" x2="12.01" y2="17" />
        </template>

        <template v-else-if="name === 'calendar'">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
        </template>

        <template v-else-if="name === 'mail'">
            <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
            <polyline points="22 6 12 13 2 6" />
        </template>

        <template v-else-if="name === 'lock'">
            <rect x="3" y="11" width="18" height="11" rx="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </template>

        <template v-else-if="name === 'sparkles'">
            <path d="M12 2 13.8 9.2 21 11 13.8 12.8 12 20 10.2 12.8 3 11 10.2 9.2Z" />
        </template>

        <template v-else-if="name === 'grid'">
            <rect x="3" y="3" width="7" height="7" rx="1" />
            <rect x="14" y="3" width="7" height="7" rx="1" />
            <rect x="3" y="14" width="7" height="7" rx="1" />
            <rect x="14" y="14" width="7" height="7" rx="1" />
        </template>

        <template v-else-if="name === 'list'">
            <line x1="8" y1="6" x2="21" y2="6" />
            <line x1="8" y1="12" x2="21" y2="12" />
            <line x1="8" y1="18" x2="21" y2="18" />
            <line x1="3" y1="6" x2="3.01" y2="6" />
            <line x1="3" y1="12" x2="3.01" y2="12" />
            <line x1="3" y1="18" x2="3.01" y2="18" />
        </template>

        <template v-else-if="name === 'camera'">
            <rect x="3" y="7" width="18" height="13" rx="2" />
            <path d="M8 7 9.5 4.5h5L16 7" />
            <circle cx="12" cy="13.5" r="3.5" />
        </template>

        <template v-else-if="name === 'trending-up'">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
            <polyline points="17 6 23 6 23 12" />
        </template>

        <template v-else-if="name === 'briefcase'">
            <rect x="2" y="7" width="20" height="14" rx="2" />
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
        </template>

        <template v-else-if="name === 'hash'">
            <line x1="4" y1="9" x2="20" y2="9" />
            <line x1="4" y1="15" x2="20" y2="15" />
            <line x1="10" y1="3" x2="8" y2="21" />
            <line x1="16" y1="3" x2="14" y2="21" />
        </template>

        <template v-else-if="name === 'sun'">
            <circle cx="12" cy="12" r="5" />
            <line x1="12" y1="1" x2="12" y2="3" />
            <line x1="12" y1="21" x2="12" y2="23" />
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
            <line x1="1" y1="12" x2="3" y2="12" />
            <line x1="21" y1="12" x2="23" y2="12" />
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
        </template>

        <template v-else-if="name === 'moon'">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
        </template>

        <template v-else>
            <circle cx="12" cy="12" r="9" />
        </template>
    </svg>
</template>
