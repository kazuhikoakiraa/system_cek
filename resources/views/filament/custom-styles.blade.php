@php
    $faviconPng = file_exists(public_path('favicon.png')) ? asset('favicon.png') : null;
    $faviconIco = asset('favicon.ico');
@endphp

<link rel="icon" type="image/png" href="{{ $faviconPng ?? $faviconIco }}">
<link rel="shortcut icon" href="{{ $faviconPng ?? $faviconIco }}">
<link rel="apple-touch-icon" href="{{ $faviconPng ?? $faviconIco }}">

<style>
    /* Posisikan badge lebih dekat ke icon lonceng */
    .fi-topbar-database-notifications-btn {
        position: relative !important;
    }

    .fi-topbar-database-notifications-btn .fi-icon-btn-badge-ctn {
        position: absolute !important;
        top: 5px !important;
        right: 5px !important;
        z-index: 10 !important;
    }

    /* Notification badge - warna merah menyala dengan glow effect */
    .fi-topbar-database-notifications-btn .fi-badge {
        background-color: #ef4444 !important; /* Merah menyala */
        color: white !important;
        font-weight: 700 !important;
        min-width: 1.25rem !important;
        height: 1.25rem !important;
        padding: 0.125rem 0.375rem !important;
        font-size: 0.65rem !important;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5), 0 0 12px rgba(239, 68, 68, 0.3) !important;
        border: 2px solid white !important;
        border-radius: 9999px !important;
        animation: pulse-red 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    /* Dark mode */
    .dark .fi-topbar-database-notifications-btn .fi-badge {
        border-color: rgb(31 41 55) !important;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.6), 0 0 15px rgba(239, 68, 68, 0.4) !important;
    }

    /* Animasi pulse */
    @keyframes pulse-red {
        0%, 100% {
            opacity: 1;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5), 0 0 12px rgba(239, 68, 68, 0.3);
        }
        50% {
            opacity: 0.9;
            transform: scale(1.08);
            box-shadow: 0 3px 10px rgba(239, 68, 68, 0.6), 0 0 18px rgba(239, 68, 68, 0.5);
        }
    }
</style>
