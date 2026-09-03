{{--
    نظام أيقونات وريد الموحّد — خطوط هندسية رسمية (لا إيموجي).
    كل الرموز: إطار 24×24، بلا تعبئة، حدّ currentColor بسماكة 1.6، أطراف دائرية.
    الاستخدام: <svg class="ic"><use href="#i-cart"/></svg>
--}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        <g id="wareed-icons" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></g>

        {{-- وضع المتجر --}}
        <symbol id="i-idea" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.3.3.5.7.5 1.1v.5h6v-.5c0-.4.2-.8.5-1.1A6 6 0 0 0 12 3Z"/>
        </symbol>
        <symbol id="i-storefront" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 10v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-9M3 6l1.2-2.4A1 1 0 0 1 5.1 3h13.8a1 1 0 0 1 .9.6L21 6M3 6h18v1a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0V6ZM9.5 20v-5h5v5"/>
        </symbol>
        <symbol id="i-social" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="6" y="2.5" width="12" height="19" rx="2.6"/><path d="M10.5 5.5h3M12 18.2h.01"/>
            <path d="M9.5 11.2a2.5 2.5 0 1 0 5 0 2.5 2.5 0 0 0-5 0Z"/>
        </symbol>

        {{-- مجالات المتاجر --}}
        <symbol id="i-apparel" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 3.5 4 6.5V11h2.5v9.5h11V11H20V6.5l-5-3M9 3.5a3 3 0 0 0 6 0"/>
        </symbol>
        <symbol id="i-beauty" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 2.8h4v3h-4zM9.2 5.8h5.6a3 3 0 0 1 3 3v9.4a3 3 0 0 1-3 3H9.2a3 3 0 0 1-3-3V8.8a3 3 0 0 1 3-3ZM6.4 11.5h11.2"/>
        </symbol>
        <symbol id="i-food" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5.5 8.5h11l-1 11a2 2 0 0 1-2 1.8H8.5a2 2 0 0 1-2-1.8l-1-11ZM16.5 10.5h1.8a2.7 2.7 0 0 1 0 5.4h-1.3M8 5.8c0-1.4 1.4-1.4 1.4-2.8M12 5.8c0-1.4 1.4-1.4 1.4-2.8"/>
        </symbol>
        <symbol id="i-electronics" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="6.5" y="6.5" width="11" height="11" rx="2"/><rect x="10" y="10" width="4" height="4" rx="1"/>
            <path d="M9.5 3.2v3.3M14.5 3.2v3.3M9.5 17.5v3.3M14.5 17.5v3.3M3.2 9.5h3.3M3.2 14.5h3.3M17.5 9.5h3.3M17.5 14.5h3.3"/>
        </symbol>
        <symbol id="i-furniture" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 11.5V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3.5M4 11.5a2 2 0 0 1 2 2v2.5h12v-2.5a2 2 0 0 1 2-2 2 2 0 0 1 0 4v3.5H4v-3.5a2 2 0 0 1 0-4ZM7.5 6v5.5M16.5 6v5.5"/>
        </symbol>
        <symbol id="i-health" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.5 12.5h-4l-2 4.5-4-11-2 6.5H3.5"/>
            <path d="M20.3 10.2a4.3 4.3 0 0 0-7.4-3 4.3 4.3 0 0 0-7.3 2.2"/>
        </symbol>
        <symbol id="i-gift" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3.5 8.5h17v3.5h-17zM5 12v7.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V12M12 8.5v12"/>
            <path d="M12 8.5S10.5 3.5 8 3.5a2.2 2.2 0 0 0 0 5M12 8.5s1.5-5 4-5a2.2 2.2 0 0 1 0 5"/>
        </symbol>
        <symbol id="i-grid" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.6"/>
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.6"/><path d="M17 14.5v5.5M14.2 17.2h5.6"/>
        </symbol>

        {{-- عدد المنتجات --}}
        <symbol id="i-box" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.5 7.8v8.4a1.6 1.6 0 0 1-.85 1.4l-6.9 3.6a1.6 1.6 0 0 1-1.5 0l-6.9-3.6a1.6 1.6 0 0 1-.85-1.4V7.8a1.6 1.6 0 0 1 .85-1.4l6.9-3.6a1.6 1.6 0 0 1 1.5 0l6.9 3.6a1.6 1.6 0 0 1 .85 1.4Z"/>
            <path d="m3.9 7 8.1 4.2L20.1 7M12 20.6v-9.4"/>
        </symbol>
        <symbol id="i-boxes" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2.8" y="12.5" width="8.4" height="8.2" rx="1.4"/><rect x="12.8" y="12.5" width="8.4" height="8.2" rx="1.4"/>
            <rect x="7.8" y="3.3" width="8.4" height="8.2" rx="1.4"/>
        </symbol>
        <symbol id="i-warehouse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 20.5V9.2a1.5 1.5 0 0 1 .95-1.4l7.5-2.9a1.5 1.5 0 0 1 1.1 0l7.5 2.9a1.5 1.5 0 0 1 .95 1.4v11.3"/>
            <path d="M2 20.5h20M7.5 20.5v-6.2h9v6.2M7.5 17.2h9"/>
        </symbol>
        <symbol id="i-stack" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 2.8 9 4.6-9 4.6-9-4.6 9-4.6ZM3 12.2l9 4.6 9-4.6M3 16.9l9 4.6 9-4.6"/>
        </symbol>

        {{-- الهوية البصرية --}}
        <symbol id="i-verified" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 2.6 2.5 1.9 3.1-.2.5 3 2.6 1.7-1.4 2.8 1.4 2.8-2.6 1.7-.5 3-3.1-.2L12 21.4l-2.5-1.9-3.1.2-.5-3L3.3 15l1.4-2.8L3.3 9.4 5.9 7.7l.5-3 3.1.2L12 2.6Z"/>
            <path d="m8.8 12.1 2.2 2.2 4.2-4.4"/>
        </symbol>
        <symbol id="i-logo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2.9 20.4 7.7v9.6L12 22.1 3.6 17.3V7.7L12 2.9Z"/><circle cx="12" cy="12.5" r="3.2"/>
        </symbol>
        <symbol id="i-palette" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3a9 9 0 1 0 0 18c1.2 0 2-.9 2-2 0-.6-.2-1-.6-1.4-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h1.9A5.2 5.2 0 0 0 22 11c0-4.4-4.5-8-10-8Z"/>
            <path d="M7.2 12.4h.01M9.6 8.2h.01M14.4 8.2h.01M17 11h.01"/>
        </symbol>

        {{-- الخدمات --}}
        <symbol id="i-cart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2.8 3.5h2.4l2.3 11.1a1.7 1.7 0 0 0 1.7 1.4h8.3a1.7 1.7 0 0 0 1.7-1.3l1.6-6.8H6"/>
            <circle cx="9.5" cy="20" r="1.3"/><circle cx="17.5" cy="20" r="1.3"/>
        </symbol>
        <symbol id="i-camera" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 8.6a2 2 0 0 1 2-2h2.2l1.3-2.2a1 1 0 0 1 .86-.5h5.28a1 1 0 0 1 .86.5l1.3 2.2H19a2 2 0 0 1 2 2v9.3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8.6Z"/>
            <circle cx="12" cy="13" r="3.6"/>
        </symbol>
        <symbol id="i-payment" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2.6" y="5" width="18.8" height="14" rx="2.4"/><path d="M2.6 9.8h18.8M6.4 15h3.4"/>
        </symbol>
        <symbol id="i-shipping" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2.6 6.4a1.4 1.4 0 0 1 1.4-1.4h8.6a1.4 1.4 0 0 1 1.4 1.4v9.9H2.6V6.4ZM14 9.4h3.4a1.4 1.4 0 0 1 1.2.7l2.2 3.6v2.6H14V9.4Z"/>
            <circle cx="6.6" cy="18.2" r="1.7"/><circle cx="17.4" cy="18.2" r="1.7"/>
        </symbol>
        <symbol id="i-marketing" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3.5 9.4v4.2a1.6 1.6 0 0 0 1.6 1.6h2.3l7.8 4.3a1 1 0 0 0 1.5-.87V4.35a1 1 0 0 0-1.5-.87L7.4 7.8H5.1a1.6 1.6 0 0 0-1.6 1.6Z"/>
            <path d="M7.4 15.2v3.4a1.6 1.6 0 0 0 1.6 1.6h1M19.4 9.4a3.2 3.2 0 0 1 0 4.2"/>
        </symbol>
        <symbol id="i-seo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="10.8" cy="10.8" r="7.3"/><path d="m16.2 16.2 4.3 4.3"/><path d="M7.8 12.3l2-2.4 2 1.6 2.4-3"/>
        </symbol>
        <symbol id="i-mobile" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="6" y="2.5" width="12" height="19" rx="2.6"/><path d="M10.4 5.6h3.2M12 18.2h.01"/>
        </symbol>
        <symbol id="i-consult" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.5 12.4c0 3.9-3.8 7-8.5 7-1 0-2-.14-2.9-.4L3.5 20.5l1.6-4.2A6.6 6.6 0 0 1 3.5 12.4c0-3.9 3.8-7 8.5-7s8.5 3.1 8.5 7Z"/>
            <path d="M8.6 12.4h.01M12 12.4h.01M15.4 12.4h.01"/>
        </symbol>

        {{-- الميزانية --}}
        <symbol id="i-coin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="8.6"/><path d="M12 7.4v9.2M14.3 9.4a2.6 2.6 0 0 0-4.6 1.4c0 2.6 4.6 1.4 4.6 4a2.6 2.6 0 0 1-4.6 1.4"/>
        </symbol>
        <symbol id="i-banknote" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2.6" y="6" width="18.8" height="12" rx="2.2"/><circle cx="12" cy="12" r="2.8"/><path d="M6.2 9.6h.01M17.8 14.4h.01"/>
        </symbol>
        <symbol id="i-wallet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3.4 8.2a2.2 2.2 0 0 1 2.2-2.2h11.6a1.6 1.6 0 0 1 1.6 1.6v1.4M3.4 8.2v9.4a2.2 2.2 0 0 0 2.2 2.2h13.2a1.8 1.8 0 0 0 1.8-1.8v-2.4M3.4 8.2h15.4a1.8 1.8 0 0 1 1.8 1.8v1.6"/>
            <path d="M20.6 11.6h-3.4a2 2 0 0 0 0 4h3.4"/>
        </symbol>
        <symbol id="i-vault" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2.8" y="4" width="18.4" height="16" rx="2.2"/><circle cx="11" cy="12" r="3.8"/>
            <path d="M11 8.2v-.8M11 16.6v-.8M14.8 12h.8M6.4 12h.8M18 16.4v1.8M18 5.8v1.8"/>
        </symbol>
        <symbol id="i-advisor" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3.4v17.2M4.6 6.6h14.8M7.6 6.6 4.6 13h6l-3-6.4ZM16.4 6.6 13.4 13h6l-3-6.4ZM8.6 20.6h6.8"/>
        </symbol>

        {{-- موعد الإطلاق --}}
        <symbol id="i-bolt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M13.2 2.8 4.8 13.4h6.2l-.2 7.8 8.4-10.6h-6.2l.2-7.8Z"/>
        </symbol>
        <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3.4" y="5.2" width="17.2" height="15.4" rx="2.2"/><path d="M3.4 10h17.2M8.2 3v4M15.8 3v4"/>
        </symbol>
        <symbol id="i-timeline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3.4 12h17.2"/><circle cx="6.6" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="17.4" cy="12" r="2"/>
            <path d="M6.6 8.4V5M17.4 19v-3.4"/>
        </symbol>
        <symbol id="i-compass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="8.6"/><path d="m15.4 8.6-2 5.4-5.4 2 2-5.4 5.4-2Z"/>
        </symbol>

        {{-- رموز الواجهة --}}
        <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="m5 12.5 4.6 4.6L19 7.4"/>
        </symbol>
        <symbol id="i-arrow-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 19.5V5M5.4 11.6 12 5l6.6 6.6"/>
        </symbol>
        <symbol id="i-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 12H5M11.6 5.4 5 12l6.6 6.6"/>
        </symbol>
        <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 20.4h8.4M16.2 3.6a2.2 2.2 0 0 1 3.2 3.2L8 18.2l-4.2 1 1-4.2L16.2 3.6Z"/>
        </symbol>
        <symbol id="i-download" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3.6v11.2M7.6 10.4 12 14.8l4.4-4.4M4 17v2.2a1.2 1.2 0 0 0 1.2 1.2h13.6a1.2 1.2 0 0 0 1.2-1.2V17"/>
        </symbol>
        <symbol id="i-document" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M13.6 2.8H6.8a1.8 1.8 0 0 0-1.8 1.8v14.8a1.8 1.8 0 0 0 1.8 1.8h10.4a1.8 1.8 0 0 0 1.8-1.8V8.2l-5.4-5.4Z"/>
            <path d="M13.4 3v5.2h5.2M8.4 13h7.2M8.4 16.6h4.8"/>
        </symbol>
        <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="8.6"/><path d="M12 7.2V12l3.2 2.2"/>
        </symbol>
        <symbol id="i-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2.8 4.6 5.8v5.4c0 4.4 3.1 8.4 7.4 9.8 4.3-1.4 7.4-5.4 7.4-9.8V5.8L12 2.8Z"/>
            <path d="m9.2 11.8 2 2 3.6-3.8"/>
        </symbol>
        <symbol id="i-list" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 6.4h11.4M9 12h11.4M9 17.6h11.4M4.2 6.4h.01M4.2 12h.01M4.2 17.6h.01"/>
        </symbol>
        <symbol id="i-whatsapp" viewBox="0 0 24 24" fill="currentColor" stroke="none">
            <path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 0 1 8.413 3.488 11.82 11.82 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.737-.97a9.86 9.86 0 0 0 .241.263z"/>
        </symbol>
        <symbol id="i-external" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 4.4h5.6V10M19.6 4.4 11 13M17.4 13.6v5a1.6 1.6 0 0 1-1.6 1.6H5.4a1.6 1.6 0 0 1-1.6-1.6V8.2a1.6 1.6 0 0 1 1.6-1.6h5"/>
        </symbol>
        <symbol id="i-sparkle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3.2 13.9 9l5.9 1.9-5.9 1.9L12 18.6l-1.9-5.8L4.2 10.9 10.1 9 12 3.2ZM18.8 16.6l.7 2.1 2.1.7-2.1.7-.7 2.1-.7-2.1-2.1-.7 2.1-.7.7-2.1Z"/>
        </symbol>
        <symbol id="i-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="8.6"/><path d="M12 11.2v5M12 8h.01"/>
        </symbol>
        <symbol id="i-alert" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.7 3.9 2.5 18.1a1.5 1.5 0 0 0 1.3 2.3h16.4a1.5 1.5 0 0 0 1.3-2.3L13.3 3.9a1.5 1.5 0 0 0-2.6 0Z"/>
            <path d="M12 9.2v4.4M12 17.2h.01"/>
        </symbol>
        <symbol id="i-mail" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2.8" y="5" width="18.4" height="14" rx="2.2"/><path d="m3.4 7 8.6 6 8.6-6"/>
        </symbol>
        <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 4.8v14.4M4.8 12h14.4"/>
        </symbol>
        <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4.4 6.8h15.2M9.2 6.8V4.6a1.2 1.2 0 0 1 1.2-1.2h3.2a1.2 1.2 0 0 1 1.2 1.2v2.2M6.8 6.8l.8 12.6a1.6 1.6 0 0 0 1.6 1.5h5.6a1.6 1.6 0 0 0 1.6-1.5l.8-12.6"/>
            <path d="M10.2 10.8v6M13.8 10.8v6"/>
        </symbol>
    </defs>
</svg>
