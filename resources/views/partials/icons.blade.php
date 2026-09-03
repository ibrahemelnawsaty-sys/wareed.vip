{{--
    ملصقة الأيقونات الموحّدة لواجهة وريد — تُدرج مرة واحدة في التخطيط.
    كل الأيقونات على شبكة 24×24 بسماكة خطّ واحدة ونهايات دائرية، وتأخذ لونها من
    currentColor فتتبع ألوان الهوية. لا إيموجي في أي شاشة.
    الاستخدام: <x-w-icon name="store" /> — والأسماء مسجّلة في App\Support\Icons::AVAILABLE
--}}
<svg xmlns="http://www.w3.org/2000/svg" class="icon-sprite" aria-hidden="true" focusable="false"><defs>

    {{-- الخدمات الثلاث --}}
    <symbol id="ic-store" viewBox="0 0 24 24"><path d="M4 9.5h16l-1.5-5H5.5z"/><path d="M5.6 9.5v9.9h12.8V9.5"/><path d="M9.6 19.4v-5.3h4.8v5.3"/><path d="M9.3 4.5 8.7 9.5M14.7 4.5l.6 5"/></symbol>
    <symbol id="ic-layers" viewBox="0 0 24 24"><path d="m12 3.2 8.4 4.3-8.4 4.3-8.4-4.3z"/><path d="m4.4 11.7 7.6 3.9 7.6-3.9"/><path d="m4.4 15.8 7.6 3.9 7.6-3.9"/></symbol>
    <symbol id="ic-academy" viewBox="0 0 24 24"><path d="m12 3.8 9 4.3-9 4.3-9-4.3z"/><path d="M6.6 10.6v4.7c0 1.6 2.4 2.9 5.4 2.9s5.4-1.3 5.4-2.9v-4.7"/><path d="M20.6 8.9v5.2"/></symbol>

    {{-- المميّزات --}}
    <symbol id="ic-bolt" viewBox="0 0 24 24"><path d="M13.4 3 5.8 13.2h5.2L10.6 21l7.6-10.2H13z"/></symbol>
    <symbol id="ic-card" viewBox="0 0 24 24"><rect x="2.8" y="5.2" width="18.4" height="13.6" rx="2.8"/><path d="M2.8 9.9h18.4M6.6 14.7h3.6"/></symbol>
    <symbol id="ic-truck" viewBox="0 0 24 24"><path d="M2.8 6.6h10.4v9.6H2.8z"/><path d="M13.2 10h3.6l3.4 3.3v2.9h-7z"/><circle cx="7.2" cy="18.1" r="1.9"/><circle cx="16.6" cy="18.1" r="1.9"/><path d="M9.1 18.1h5.6"/></symbol>
    <symbol id="ic-search" viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.4"/><path d="m15.5 15.5 4.7 4.7"/></symbol>
    <symbol id="ic-chart" viewBox="0 0 24 24"><path d="M4 20h16"/><path d="M6.9 20v-7.6M12 20V6.6M17.1 20v-5.2"/></symbol>
    <symbol id="ic-shield" viewBox="0 0 24 24"><path d="M12 3.2 4.8 6.1v5.2c0 4.4 3 8.1 7.2 9.5 4.2-1.4 7.2-5.1 7.2-9.5V6.1z"/><path d="m9.2 11.9 2.1 2.1 4-4.2"/></symbol>
    <symbol id="ic-settings" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3.1"/><path d="M19.5 14.6a1.6 1.6 0 0 0 .3 1.8l.1.1a1.9 1.9 0 1 1-2.7 2.7l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5v.2a1.9 1.9 0 1 1-3.8 0v-.1a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1a1.9 1.9 0 1 1-2.7-2.7l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1h-.2a1.9 1.9 0 1 1 0-3.8h.1a1.6 1.6 0 0 0 1.5-1 1.6 1.6 0 0 0-.3-1.8l-.1-.1a1.9 1.9 0 1 1 2.7-2.7l.1.1a1.6 1.6 0 0 0 1.8.3h.1a1.6 1.6 0 0 0 1-1.5v-.2a1.9 1.9 0 1 1 3.8 0v.1a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1a1.9 1.9 0 1 1 2.7 2.7l-.1.1a1.6 1.6 0 0 0-.3 1.8v.1a1.6 1.6 0 0 0 1.5 1h.2a1.9 1.9 0 1 1 0 3.8h-.1a1.6 1.6 0 0 0-1.5 1z"/></symbol>
    <symbol id="ic-bank" viewBox="0 0 24 24"><path d="m3.4 9.4 8.6-5.2 8.6 5.2"/><path d="M5.8 9.4v8.8M10 9.4v8.8M14 9.4v8.8M18.2 9.4v8.8"/><path d="M3.2 20.4h17.6"/></symbol>
    <symbol id="ic-lock" viewBox="0 0 24 24"><rect x="4.6" y="10.2" width="14.8" height="10.2" rx="2.8"/><path d="M8.2 10.2V7.7a3.8 3.8 0 0 1 7.6 0v2.5"/><path d="M12 14.4v2.2"/></symbol>
    <symbol id="ic-cloud" viewBox="0 0 24 24"><path d="M17.6 18.4H6.9a4.4 4.4 0 0 1-.5-8.8 6.1 6.1 0 0 1 11.7 1.6 3.7 3.7 0 0 1-.5 7.2z"/></symbol>
    <symbol id="ic-support" viewBox="0 0 24 24"><path d="M4.6 13.6v-2.2a7.4 7.4 0 0 1 14.8 0v2.2"/><rect x="3.1" y="12.8" width="3.9" height="5.4" rx="1.9"/><rect x="17" y="12.8" width="3.9" height="5.4" rx="1.9"/><path d="M19 18.2v.5a2.3 2.3 0 0 1-2.3 2.3h-2.4"/></symbol>
    <symbol id="ic-cpu" viewBox="0 0 24 24"><rect x="6.6" y="6.6" width="10.8" height="10.8" rx="2.2"/><rect x="10" y="10" width="4" height="4" rx="1.1"/><path d="M9.6 3.2v3.4M14.4 3.2v3.4M9.6 17.4v3.4M14.4 17.4v3.4M3.2 9.6h3.4M3.2 14.4h3.4M17.4 9.6h3.4M17.4 14.4h3.4"/></symbol>
    <symbol id="ic-server" viewBox="0 0 24 24"><rect x="3.4" y="4.2" width="17.2" height="6.2" rx="2.1"/><rect x="3.4" y="13.6" width="17.2" height="6.2" rx="2.1"/><path d="M6.8 7.3h1.6M6.8 16.7h1.6"/></symbol>
    <symbol id="ic-palette" viewBox="0 0 24 24"><path d="M12 3.2a8.8 8.8 0 0 0 0 17.6c.9 0 1.6-.7 1.6-1.6 0-.4-.2-.8-.4-1.1a1.6 1.6 0 0 1 1.2-2.6h1.9a5.5 5.5 0 0 0 5.5-5.5c0-3.8-4.4-6.8-9.8-6.8z"/><path d="M8 9.2h.01M12.2 7.3h.01M16.2 9.6h.01M7.6 14h.01"/></symbol>
    <symbol id="ic-database" viewBox="0 0 24 24"><ellipse cx="12" cy="6" rx="7.4" ry="2.9"/><path d="M4.6 6v11.9c0 1.6 3.3 2.9 7.4 2.9s7.4-1.3 7.4-2.9V6"/><path d="M4.6 12c0 1.6 3.3 2.9 7.4 2.9s7.4-1.3 7.4-2.9"/></symbol>
    <symbol id="ic-monitor" viewBox="0 0 24 24"><rect x="2.8" y="4.2" width="18.4" height="12.2" rx="2.6"/><path d="M9 20.2h6M12 16.4v3.8"/></symbol>
    <symbol id="ic-presenter" viewBox="0 0 24 24"><rect x="3.4" y="3.2" width="17.2" height="9.8" rx="2.2"/><path d="M12 13v1.9"/><circle cx="12" cy="16.7" r="1.7"/><path d="M8.8 20.9a3.4 3.4 0 0 1 6.4 0"/></symbol>
    <symbol id="ic-trend" viewBox="0 0 24 24"><path d="m3.6 16.4 5.4-5.4 3.6 3.6 7.4-7.4"/><path d="M15.4 7.2h4.6v4.6"/></symbol>
    <symbol id="ic-mobile" viewBox="0 0 24 24"><rect x="6.6" y="2.8" width="10.8" height="18.4" rx="2.8"/><path d="M10.8 18.4h2.4"/></symbol>
    <symbol id="ic-idea" viewBox="0 0 24 24"><path d="M15.1 17a6.2 6.2 0 1 0-6.2 0v1.6h6.2z"/><path d="M9.8 21.2h4.4"/></symbol>
    <symbol id="ic-rocket" viewBox="0 0 24 24"><path d="M12.6 17.1 6.9 11.4c-.4-.4-.4-1 0-1.4C10.2 6.7 14.4 3.9 19.4 4c.4 0 .7.3.7.7.1 5-2.7 9.2-6 12.5-.4.4-1.1.4-1.5-.1z"/><circle cx="15.1" cy="8.9" r="1.5"/><path d="M8.7 15.4c-1.3.5-2.2 1.5-2.6 2.8-.3 1-.4 2-.4 2s1-.1 2-.4c1.3-.4 2.3-1.3 2.8-2.6"/></symbol>
    <symbol id="ic-users" viewBox="0 0 24 24"><circle cx="9.4" cy="8.2" r="3.4"/><path d="M3.4 19.6a6 6 0 0 1 12 0"/><path d="M16.4 5.3a3.4 3.4 0 0 1 0 5.8M17.6 14.2a6 6 0 0 1 3 5.4"/></symbol>
    <symbol id="ic-target" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.2"/><circle cx="12" cy="12" r="4.6"/><path d="M12 10.8h.01"/></symbol>
    <symbol id="ic-box" viewBox="0 0 24 24"><path d="m12 3.2 8.2 4.2v9.2L12 20.8 3.8 16.6V7.4z"/><path d="m3.8 7.4 8.2 4.3 8.2-4.3M12 11.7v9.1"/></symbol>
    <symbol id="ic-code" viewBox="0 0 24 24"><path d="m8.6 8.4-4.4 3.6 4.4 3.6M15.4 8.4l4.4 3.6-4.4 3.6M13.4 5.2l-2.8 13.6"/></symbol>

    {{-- تواصل وواجهة --}}
    <symbol id="ic-mail" viewBox="0 0 24 24"><rect x="2.8" y="5" width="18.4" height="14" rx="2.8"/><path d="m3.8 7.2 7.2 4.9c.6.4 1.4.4 2 0l7.2-4.9"/></symbol>
    <symbol id="ic-phone" viewBox="0 0 24 24"><path d="M16.1 21.2c-1.9 0-4.4-1.3-6.8-3.7C6.3 14.9 4 11.4 4 9c0-1 .3-1.7.9-2.3l1.3-1.3c.6-.6 1.5-.6 2 0l2 2c.6.6.6 1.5 0 2l-1 1c-.2.2-.3.6-.1.9.4.9 1.1 1.9 2 2.8s1.9 1.6 2.8 2c.3.2.7.1.9-.1l1-1c.6-.6 1.5-.6 2 0l2 2c.6.6.6 1.5 0 2l-1.3 1.3c-.6.6-1.3.9-2.4.9z"/></symbol>
    <symbol id="ic-pin" viewBox="0 0 24 24"><path d="M12 21.2s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/></symbol>
    <symbol id="ic-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.4"/><path d="M12 7.4V12l3.2 1.9"/></symbol>
    <symbol id="ic-spark" viewBox="0 0 24 24"><path d="M12 3.2 14.1 9.9 20.8 12 14.1 14.1 12 20.8 9.9 14.1 3.2 12 9.9 9.9z"/></symbol>
    <symbol id="ic-sparkles" viewBox="0 0 24 24"><path d="M10 3.4 11.6 8.4 16.6 10 11.6 11.6 10 16.6 8.4 11.6 3.4 10 8.4 8.4z"/><path d="M17.6 14.2 18.5 16.9 21.2 17.8 18.5 18.7 17.6 21.4 16.7 18.7 14 17.8 16.7 16.9z"/></symbol>
    <symbol id="ic-check" viewBox="0 0 24 24"><path d="M20 6.5 9.4 17.1 4 11.7"/></symbol>
    <symbol id="ic-arrow-left" viewBox="0 0 24 24"><path d="M19.4 12H4.6M11.4 5.2 4.6 12l6.8 6.8"/></symbol>
    <symbol id="ic-arrow-down" viewBox="0 0 24 24"><path d="M12 4.6v14.8M5.2 12.6 12 19.4l6.8-6.8"/></symbol>
    <symbol id="ic-chevron-down" viewBox="0 0 24 24"><path d="m6.4 9.4 5.6 5.6 5.6-5.6"/></symbol>
    <symbol id="ic-plus" viewBox="0 0 24 24"><path d="M12 5.4v13.2M5.4 12h13.2"/></symbol>
    <symbol id="ic-minus" viewBox="0 0 24 24"><path d="M5.4 12h13.2"/></symbol>
    <symbol id="ic-menu" viewBox="0 0 24 24"><path d="M4 6.8h16M4 12h16M4 17.2h16"/></symbol>
    <symbol id="ic-close" viewBox="0 0 24 24"><path d="M6.4 6.4 17.6 17.6M17.6 6.4 6.4 17.6"/></symbol>
    <symbol id="ic-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.4"/><path d="M3.6 12h16.8"/><path d="M12 3.6a13.2 13.2 0 0 1 0 16.8 13.2 13.2 0 0 1 0-16.8z"/></symbol>
    <symbol id="ic-whatsapp" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.737-.97a9.86 9.86 0 00.241.263zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.767.967-.94 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></symbol>

</defs></svg>
