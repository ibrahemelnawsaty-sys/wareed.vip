{{--
    خط ثمانية مستضاف ذاتياً لشاشات عرض السعر (صفحات مستقلة عن تخطيط الموقع).
    الأوزان المستخدمة هنا أربعة فقط — لا داعي لتحميل الخفيف في هذه الشاشات.
--}}
<link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/thmanyah/ThmanyahSans-Regular.woff2') }}" crossorigin>
<link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/thmanyah/ThmanyahSans-Bold.woff2') }}" crossorigin>
<style>
    @font-face { font-family: 'Thmanyah'; font-style: normal; font-weight: 400; font-display: swap; src: url('{{ asset('fonts/thmanyah/ThmanyahSans-Regular.woff2') }}') format('woff2'); }
    @font-face { font-family: 'Thmanyah'; font-style: normal; font-weight: 500; font-display: swap; src: url('{{ asset('fonts/thmanyah/ThmanyahSans-Medium.woff2') }}') format('woff2'); }
    @font-face { font-family: 'Thmanyah'; font-style: normal; font-weight: 700; font-display: swap; src: url('{{ asset('fonts/thmanyah/ThmanyahSans-Bold.woff2') }}') format('woff2'); }
    @font-face { font-family: 'Thmanyah'; font-style: normal; font-weight: 900; font-display: swap; src: url('{{ asset('fonts/thmanyah/ThmanyahSans-Black.woff2') }}') format('woff2'); }
</style>
