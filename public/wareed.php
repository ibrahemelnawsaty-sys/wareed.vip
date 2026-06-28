<?php $year = date('Y');
$wa = '201000000000';
$email = 'info@wareed.com'; ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>وريد — شريان السوق الرقمي في مصر | متاجر إلكترونية وحلول تقنية وتدريب</title>
<meta name="description" content="وريد منصّة تقنية مصرية فاخرة: أنشئ متجرك الإلكتروني من ضغطة زر، حلول Hardware وSoftware متكاملة للجهات الحكومية والشركات، ومسارات تدريب احترافية في الذكاء الاصطناعي والبرمجة.">
<meta name="theme-color" content="#0A1B3D">
<meta property="og:title" content="وريد — شريان السوق الرقمي في مصر">
<meta property="og:description" content="متاجر إلكترونية من ضغطة زر، حلول تقنية متكاملة، وتدريب احترافي. منصّة مصرية فاخرة بمعايير عالمية.">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_EG">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Reem+Kufi:wght@500;600;700&family=El+Messiri:wght@500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  :root{
    --lapis:#0A1B3D;
    --lapis-2:#0E2349;
    --surface:#12264F;
    --border:#1E3A6E;
    --gold:#C8A24B;
    --gold-light:#E7C873;
    --gold-deep:#9C7A2E;
    --teal:#2DB3A6;
    --ivory:#F4ECDB;
    --muted:#B9C4DA;
    --focus:#6FA8FF;
    --grad-gold:linear-gradient(135deg,#E7C873 0%,#C8A24B 50%,#9C7A2E 100%);
    --shadow-warm:0 18px 50px -18px rgba(200,162,75,.45);
    --r:18px;
    --maxw:1200px;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
  body{
    font-family:'Tajawal',system-ui,sans-serif;
    background:var(--lapis);
    color:var(--ivory);
    line-height:1.85;
    font-size:1.05rem;
    overflow-x:hidden;
  }
  /* Ambient glow on a fixed, GPU-composited layer (no scroll repaint) */
  body::after{
    content:"";position:fixed;inset:0;z-index:0;pointer-events:none;
    transform:translateZ(0);
    background-image:
      radial-gradient(1200px 700px at 85% -10%, rgba(200,162,75,.10), transparent 60%),
      radial-gradient(1000px 800px at 0% 20%, rgba(45,179,166,.07), transparent 55%),
      linear-gradient(180deg,transparent 0%, rgba(14,35,73,.6) 100%);
  }
  bdi{font-variant-numeric:tabular-nums}
  a{color:inherit;text-decoration:none}
  img,svg{display:block;max-width:100%}
  ::selection{background:rgba(200,162,75,.3);color:var(--ivory)}

  /* Mashrabiya signature texture (very subtle) */
  body::before{
    content:"";position:fixed;inset:0;z-index:0;pointer-events:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='90' height='90' viewBox='0 0 90 90'%3E%3Cg fill='none' stroke='%23C8A24B' stroke-width='1' stroke-opacity='0.055'%3E%3Cpath d='M45 5 55 25 75 25 60 40 67 60 45 48 23 60 30 40 15 25 35 25z'/%3E%3Crect x='25' y='25' width='40' height='40' transform='rotate(45 45 45)'/%3E%3C/g%3E%3C/svg%3E");
    background-size:130px 130px;
    -webkit-mask-image:radial-gradient(120% 110% at 50% 25%, #000 35%, transparent 80%);
    mask-image:radial-gradient(120% 110% at 50% 25%, #000 35%, transparent 80%);
  }

  .wrap{max-width:var(--maxw);margin:0 auto;padding:0 clamp(18px,4vw,40px);position:relative;z-index:1}
  section{position:relative;z-index:1}

  /* Focus visibility (AAA-leaning) */
  a:focus-visible,button:focus-visible{
    outline:3px solid var(--focus);
    outline-offset:3px;
    border-radius:8px;
  }
  .skip{
    position:absolute;right:-9999px;top:8px;z-index:200;
    background:var(--gold);color:#1a1303;padding:10px 18px;border-radius:10px;font-weight:700;
  }
  .skip:focus{right:16px}

  /* ===== Navbar ===== */
  header{
    position:sticky;top:0;z-index:100;
    transition:background .35s ease, backdrop-filter .35s ease, border-color .35s ease, box-shadow .35s ease;
    border-bottom:1px solid transparent;
  }
  header.scrolled{
    background:rgba(10,27,61,.72);
    backdrop-filter:blur(14px) saturate(140%);
    -webkit-backdrop-filter:blur(14px) saturate(140%);
    border-bottom:1px solid rgba(200,162,75,.38);
    box-shadow:0 10px 30px -20px rgba(0,0,0,.7);
  }
  .nav{display:flex;align-items:center;justify-content:space-between;height:72px}
  .brand{display:flex;align-items:center;gap:12px;font-family:'Reem Kufi',serif;font-size:1.5rem;font-weight:700;letter-spacing:-.5px}
  .brand .mark{width:38px;height:38px;flex:0 0 38px}
  .brand .dot{fill:var(--teal)}
  .nav-links{display:flex;align-items:center;gap:6px}
  .nav-links a{
    padding:9px 16px;border-radius:12px;color:var(--muted);font-weight:500;font-size:.98rem;
    transition:color .25s, background .25s;
  }
  .nav-links a:hover{color:var(--ivory);background:rgba(200,162,75,.10)}
  .nav-cta{
    background:var(--grad-gold);color:#1a1303 !important;font-weight:700;
    padding:10px 20px !important;border-radius:12px !important;
    box-shadow:0 8px 22px -10px rgba(200,162,75,.6);
  }
  .nav-cta:hover{background:var(--grad-gold) !important;transform:translateY(-1px)}
  .menu-btn{display:none;background:none;border:1px solid var(--border);color:var(--ivory);
    width:46px;height:46px;border-radius:12px;cursor:pointer;align-items:center;justify-content:center}

  /* ===== Buttons ===== */
  .btn{
    display:inline-flex;align-items:center;gap:10px;justify-content:center;
    padding:14px 28px;border-radius:14px;font-weight:700;font-size:1.02rem;
    font-family:'Tajawal',sans-serif;cursor:pointer;border:1px solid transparent;
    transition:transform .25s, box-shadow .35s, background .3s, border-color .3s;
  }
  .btn:hover,.btn:focus-visible{will-change:transform}
  .btn-gold{background:var(--grad-gold);color:#1a1303;box-shadow:var(--shadow-warm)}
  .btn-gold:hover,.btn-gold:focus-visible{transform:translateY(-2px);box-shadow:0 24px 60px -18px rgba(231,200,115,.6)}
  .btn-ghost{background:rgba(255,255,255,.02);color:var(--gold-light);border:1px solid var(--gold)}
  .btn-ghost:hover,.btn-ghost:focus-visible{background:rgba(200,162,75,.12);transform:translateY(-2px);box-shadow:0 16px 40px -22px rgba(200,162,75,.5)}
  .btn svg{width:20px;height:20px}

  /* ===== Hero ===== */
  .hero{padding:clamp(40px,8vh,90px) 0 clamp(50px,7vh,80px);position:relative}
  .hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:clamp(30px,5vw,64px);align-items:center}
  .eyebrow{
    display:inline-flex;align-items:center;gap:10px;
    background:rgba(45,179,166,.10);border:1px solid rgba(45,179,166,.35);
    color:#7fe4da;padding:7px 16px;border-radius:999px;font-size:.9rem;font-weight:500;margin-bottom:22px;
  }
  .pulse-dot{width:9px;height:9px;border-radius:50%;background:var(--teal);box-shadow:0 0 0 0 rgba(45,179,166,.6);animation:pulse 2.2s infinite}
  @keyframes pulse{
    0%{box-shadow:0 0 0 0 rgba(45,179,166,.55);transform:scale(1)}
    70%{box-shadow:0 0 0 12px rgba(45,179,166,0);transform:scale(1.15)}
    100%{box-shadow:0 0 0 0 rgba(45,179,166,0);transform:scale(1)}
  }
  h1{
    font-family:'Reem Kufi',serif;font-weight:700;letter-spacing:-.5px;
    font-size:clamp(2.4rem,6vw,5rem);line-height:1.12;margin-bottom:22px;
  }
  h1 .accent{
    background:var(--grad-gold);-webkit-background-clip:text;background-clip:text;
    -webkit-text-fill-color:transparent;color:var(--gold-light);
  }
  .hero p.lead{font-size:clamp(1.05rem,2.2vw,1.28rem);color:var(--muted);max-width:38ch;margin-bottom:34px}
  .hero-cta{display:flex;flex-wrap:wrap;gap:16px;margin-bottom:40px}

  /* Hero floating glass stats card */
  .hero-visual{position:relative;min-height:380px}
  .artery-stage{position:relative;width:100%;height:100%;min-height:380px}
  .artery-svg{position:absolute;inset:0;width:100%;height:100%}
  .stats-card{
    position:absolute;left:6%;bottom:8%;z-index:3;
    background:rgba(18,38,79,.7);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
    border:1px solid rgba(200,162,75,.32);border-radius:18px;
    padding:18px 20px;display:flex;gap:22px;box-shadow:0 20px 50px -24px rgba(0,0,0,.8);
  }
  .stats-card .si{text-align:center}
  .stats-card .sv{font-family:'Reem Kufi',serif;font-size:clamp(1.15rem,4.4vw,1.5rem);font-weight:700;color:var(--gold-light);line-height:1.1;font-variant-numeric:tabular-nums}
  .stats-card .sl{font-size:.78rem;color:var(--muted)}
  .stats-card .sep{width:1px;background:linear-gradient(180deg,transparent,rgba(200,162,75,.45),transparent)}

  .scroll-hint{display:flex;flex-direction:column;align-items:center;gap:8px;color:var(--muted);font-size:.85rem;margin-top:6px}
  .scroll-hint .arr{animation:bob 2s ease-in-out infinite}
  @keyframes bob{0%,100%{transform:translateY(0)}50%{transform:translateY(7px)}}

  /* Section heading */
  .sec{padding:clamp(56px,9vh,110px) 0}
  .sec-head{text-align:center;max-width:720px;margin:0 auto clamp(36px,5vh,56px)}
  .kicker{color:var(--teal);font-weight:700;font-size:.95rem;letter-spacing:.5px;display:inline-flex;align-items:center;gap:8px;margin-bottom:14px}
  .kicker::before,.kicker::after{content:"";width:26px;height:1px;background:linear-gradient(90deg,transparent,var(--teal))}
  h2{font-family:'El Messiri',serif;font-weight:700;font-size:clamp(1.8rem,4vw,3rem);line-height:1.25;margin-bottom:16px}
  .sec-head p{color:var(--muted);font-size:1.08rem}

  /* Decorative divider */
  .divider{height:60px;display:flex;align-items:center;justify-content:center;opacity:.85}
  .divider svg{width:min(420px,80%);height:auto}

  /* ===== Services ===== */
  .services{display:grid;grid-template-columns:repeat(3,1fr);gap:26px}
  .card{
    position:relative;background:linear-gradient(160deg,var(--surface),var(--lapis-2));
    border:1px solid var(--border);border-radius:var(--r);padding:34px 28px 30px;overflow:hidden;
    transition:transform .35s cubic-bezier(.22,1,.36,1), border-color .35s, box-shadow .35s;
    /* clipped corner (Egyptian arch nod) */
    clip-path:polygon(0 0, calc(100% - 26px) 0, 100% 26px, 100% 100%, 0 100%);
  }
  .card::after{
    content:"";position:absolute;inset:-40% 30% auto auto;width:240px;height:240px;border-radius:50%;
    background:radial-gradient(circle,rgba(200,162,75,.16),transparent 65%);
    opacity:0;transition:opacity .4s;pointer-events:none;
  }
  .card:hover{transform:translateY(-8px);border-color:rgba(200,162,75,.5);box-shadow:0 30px 70px -34px rgba(200,162,75,.4)}
  .card:hover::after{opacity:1}
  .card .num{
    position:absolute;top:6px;left:18px;font-family:'Reem Kufi',serif;font-weight:700;font-size:5.5rem;line-height:1;
    color:transparent;-webkit-text-stroke:1.4px rgba(200,162,75,.28);pointer-events:none;user-select:none;
  }
  .card .icon{
    width:62px;height:62px;border-radius:16px;display:flex;align-items:center;justify-content:center;
    background:rgba(200,162,75,.10);border:1px solid rgba(200,162,75,.32);margin-bottom:20px;position:relative;z-index:1;
  }
  .card .icon svg{width:32px;height:32px;color:var(--gold-light);stroke-width:1.5}
  .card h3{font-family:'El Messiri',serif;font-weight:700;font-size:1.5rem;margin-bottom:12px;position:relative;z-index:1}
  .card p{color:var(--muted);font-size:1rem;margin-bottom:18px;position:relative;z-index:1}
  .card ul{list-style:none;display:flex;flex-direction:column;gap:10px;position:relative;z-index:1}
  .card li{display:flex;align-items:flex-start;gap:10px;font-size:.96rem;color:var(--ivory)}
  .card li svg{width:18px;height:18px;color:var(--teal);flex:0 0 18px;margin-top:5px}
  .card .more{display:inline-flex;align-items:center;gap:8px;margin-top:20px;color:var(--gold-light);font-weight:700;font-size:.95rem;position:relative;z-index:1}
  .card .more svg{width:16px;height:16px;transition:transform .25s}
  .card:hover .more svg{transform:translateX(-5px)}

  /* ===== Why ===== */
  .why{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}
  .feat{
    background:rgba(18,38,79,.45);border:1px solid var(--border);border-radius:16px;padding:26px 22px;
    transition:transform .3s, border-color .3s;
  }
  .feat:hover{transform:translateY(-5px);border-color:rgba(45,179,166,.4)}
  .feat .fi{width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;
    background:rgba(45,179,166,.10);border:1px solid rgba(45,179,166,.3);margin-bottom:16px}
  .feat .fi svg{width:26px;height:26px;color:var(--teal);stroke-width:1.5}
  .feat h3{font-family:'El Messiri',serif;font-size:1.2rem;font-weight:700;margin-bottom:8px}
  .feat p{color:var(--muted);font-size:.96rem}

  /* ===== Steps ===== */
  .steps{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;position:relative}
  .steps::before{
    content:"";position:absolute;top:36px;right:8%;left:8%;height:2px;
    background:repeating-linear-gradient(90deg,rgba(200,162,75,.4) 0 10px,transparent 10px 20px);
    z-index:0;
  }
  .step{text-align:center;position:relative;z-index:1}
  .step .sn{
    width:72px;height:72px;margin:0 auto 18px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-family:'Reem Kufi',serif;font-weight:700;font-size:1.6rem;color:#1a1303;
    background:var(--grad-gold);box-shadow:0 14px 34px -14px rgba(200,162,75,.6);
    border:4px solid var(--lapis);
  }
  .step h3{font-family:'El Messiri',serif;font-size:1.22rem;font-weight:700;margin-bottom:8px}
  .step p{color:var(--muted);font-size:.96rem}

  /* ===== CTA band ===== */
  .cta-band{
    position:relative;margin:0 auto;border-radius:26px;overflow:hidden;
    background:linear-gradient(135deg,var(--surface),var(--lapis-2));
    border:1px solid rgba(200,162,75,.35);
    padding:clamp(40px,7vw,72px) clamp(24px,5vw,60px);text-align:center;
  }
  .cta-band::before{
    content:"";position:absolute;inset:0;z-index:0;opacity:.5;
    background:
      radial-gradient(500px 300px at 85% 20%, rgba(200,162,75,.18), transparent 60%),
      radial-gradient(500px 300px at 10% 90%, rgba(45,179,166,.14), transparent 60%);
  }
  .cta-band > *{position:relative;z-index:1}
  .cta-band h2{margin-bottom:14px}
  .cta-band p{color:var(--muted);max-width:54ch;margin:0 auto 30px;font-size:1.1rem}
  .cta-actions{display:flex;flex-wrap:wrap;gap:16px;justify-content:center}

  /* ===== Footer ===== */
  footer{border-top:1px solid var(--border);margin-top:clamp(50px,8vh,90px);padding:54px 0 30px;background:rgba(10,27,61,.5)}
  .foot-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:34px;margin-bottom:38px}
  .foot-brand .brand{margin-bottom:14px}
  .foot-brand p{color:var(--muted);font-size:.96rem;max-width:34ch}
  .foot-col h3{font-family:'El Messiri',serif;font-size:1.05rem;font-weight:700;margin-bottom:16px;color:var(--gold-light)}
  .foot-col a{display:block;color:var(--muted);padding:6px 0;font-size:.95rem;transition:color .2s}
  .foot-col a:hover{color:var(--ivory)}
  .socials{display:flex;gap:12px;margin-top:18px}
  .socials a{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;
    background:rgba(255,255,255,.03);border:1px solid var(--border);transition:.25s}
  .socials a:hover{border-color:var(--gold);background:rgba(200,162,75,.1)}
  .socials svg{width:20px;height:20px;color:var(--gold-light)}
  .foot-bottom{border-top:1px solid var(--border);padding-top:24px;display:flex;flex-wrap:wrap;gap:12px;justify-content:space-between;align-items:center;color:var(--muted);font-size:.9rem}
  .foot-bottom .heart{color:var(--teal)}

  /* Floating WhatsApp */
  .wa-float{
    position:fixed;left:22px;bottom:22px;z-index:90;width:58px;height:58px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,#25D366,#128C7E);box-shadow:0 14px 30px -10px rgba(37,211,102,.6);
    transition:transform .25s;
  }
  .wa-float:hover{transform:scale(1.08)}
  .wa-float svg{width:30px;height:30px;color:#fff}

  /* ===== Reveal ===== */
  .reveal{opacity:0;transform:translateY(16px);transition:opacity .7s cubic-bezier(.22,1,.36,1),transform .7s cubic-bezier(.22,1,.36,1)}
  .reveal.in{opacity:1;transform:none}

  /* ===== Responsive ===== */
  @media(max-width:980px){
    .hero-grid{grid-template-columns:1fr;text-align:center}
    .hero p.lead{margin-inline:auto}
    .hero-cta{justify-content:center}
    .hero-visual{order:-1;min-height:320px}
    .services{grid-template-columns:1fr 1fr}
    .why{grid-template-columns:1fr 1fr}
    .steps{grid-template-columns:1fr 1fr;gap:36px 24px}
    .steps::before{display:none}
    .foot-grid{grid-template-columns:1fr 1fr}
  }
  /* Tablet portrait + phones: collapse nav into the menu (covers 768px) */
  @media(max-width:820px){
    .nav-links{
      position:fixed;inset:72px 0 auto 0;flex-direction:column;align-items:stretch;
      background:rgba(10,27,61,.97);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
      border-bottom:1px solid rgba(200,162,75,.3);padding:14px 18px 22px;gap:6px;
      transform:translateY(-130%);transition:transform .38s cubic-bezier(.22,1,.36,1);
      box-shadow:0 24px 50px -24px rgba(0,0,0,.8);
    }
    .nav-links.open{transform:translateY(0)}
    .nav-links a{padding:13px 14px;font-size:1.05rem}
    .nav-cta{text-align:center}
    .menu-btn{display:flex}
  }
  @media(max-width:680px){
    .services{grid-template-columns:1fr}
    .why{grid-template-columns:1fr}
    .steps{grid-template-columns:1fr}
    .foot-grid{grid-template-columns:1fr}
    .stats-card{left:50%;transform:translateX(-50%);bottom:0;gap:16px;padding:14px 16px}
  }
  @media(max-width:400px){
    .stats-card{gap:10px;padding:12px;max-width:calc(100% - 24px)}
    .stats-card .sl{font-size:.7rem}
  }

  @media(prefers-reduced-motion:reduce){
    *{animation:none !important;scroll-behavior:auto !important}
    .reveal{opacity:1;transform:none;transition:none}
    .artery-flow{stroke-dashoffset:0 !important;animation:none !important}
    .pulse-dot{animation:none !important}
    html{scroll-behavior:auto}
  }
</style>
</head>
<body>
<a class="skip" href="#main">تخطَّ إلى المحتوى الرئيسي</a>

<!-- ===== Header ===== -->
<header id="top">
  <div class="wrap nav">
    <a class="brand" href="#top" aria-label="وريد — الصفحة الرئيسية">
      <svg class="mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
        <circle cx="20" cy="20" r="18.5" stroke="#C8A24B" stroke-opacity=".5"/>
        <path d="M9 20 H15 L17 12 L22 28 L25 20 H31" stroke="#E7C873" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        <circle class="dot" cx="9" cy="20" r="2.6"/>
      </svg>
      <span>وريد</span>
    </a>
    <nav class="nav-links" id="navLinks" aria-label="التنقل الرئيسي">
      <a href="#services">الخدمات</a>
      <a href="#why">لماذا وريد</a>
      <a href="#how">كيف نعمل</a>
      <a href="#contact">تواصل</a>
      <a class="nav-cta" href="https://wa.me/<?= $wa ?>?text=<?= rawurlencode('مرحباً وريد، أرغب في إنشاء متجري الإلكتروني.') ?>" target="_blank" rel="noopener">ابدأ الآن</a>
    </nav>
    <button class="menu-btn" id="menuBtn" aria-label="فتح القائمة" aria-expanded="false" aria-controls="navLinks">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/></svg>
    </button>
  </div>
</header>

<main id="main">

<!-- ===== Hero ===== -->
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <span class="eyebrow"><span class="pulse-dot" aria-hidden="true"></span> منصّة مصرية تنبض بالتقنية</span>
      <h1>وريد — <span class="accent">شريان السوق الرقمي</span> في مصر</h1>
      <p class="lead">من ضغطة زر إلى علامة رقمية كاملة. نُنشئ متجرك، ونبني حلولك التقنية، وندرّب فريقك — بمعايير عالمية وروحٍ مصرية أصيلة.</p>
      <div class="hero-cta">
        <a class="btn btn-gold" href="https://wa.me/<?= $wa ?>?text=<?= rawurlencode('مرحباً وريد، أرغب في إنشاء متجري الإلكتروني الآن.') ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.82 9.82 0 001.523 5.252l-.999 3.648 3.965-1.04zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.148-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.247-.694.247-1.289.173-1.413z"/></svg>
          ابدأ متجرك الآن
        </a>
        <a class="btn btn-ghost" href="mailto:<?= $email ?>?subject=<?= rawurlencode('استفسار عن خدمات وريد') ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
          تواصل معنا
        </a>
      </div>
      <div class="scroll-hint" aria-hidden="true">
        <span>اكتشف خدماتنا</span>
        <svg class="arr" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2"><path d="M12 5v14M6 13l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
    </div>

    <!-- Hero visual: branching golden artery -->
    <div class="hero-visual">
      <div class="artery-stage">
        <svg class="artery-svg" viewBox="0 0 460 400" fill="none" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
          <defs>
            <linearGradient id="gGold" x1="1" y1="0" x2="0" y2="1">
              <stop offset="0" stop-color="#E7C873"/><stop offset=".5" stop-color="#C8A24B"/><stop offset="1" stop-color="#9C7A2E"/>
            </linearGradient>
            <radialGradient id="gNode" cx="50%" cy="50%" r="50%">
              <stop offset="0" stop-color="#E7C873"/><stop offset="1" stop-color="#9C7A2E"/>
            </radialGradient>
            <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
              <feGaussianBlur stdDeviation="4" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
            </filter>
          </defs>
          <!-- faint mashrabiya star at center -->
          <g stroke="#C8A24B" stroke-opacity=".12" stroke-width="1" fill="none">
            <path d="M420 200 l-28 60 -60 28 28 60-60 60-60-28-60 28-60-60 28-60-60-28 28-60-28-60 60-60 60 28 60-28 60 60z" transform="translate(-130 -30) scale(.55)"/>
          </g>
          <!-- main artery trunk starts from RIGHT (RTL reading) -->
          <path id="trunk" class="artery-base"
            d="M440 60 C 380 70, 360 130, 300 150 C 250 168, 240 220, 230 220"
            stroke="url(#gGold)" stroke-width="2.4" stroke-linecap="round"/>
          <!-- branch to node 1 (top) -->
          <path class="artery-base" d="M230 220 C 180 215, 150 150, 90 130" stroke="url(#gGold)" stroke-width="1.6"/>
          <!-- branch to node 2 (mid) — ECG pulse style -->
          <path class="artery-base" d="M230 220 C 170 226, 150 226, 120 226 L108 226 102 208 92 250 84 226 60 226" stroke="url(#gGold)" stroke-width="1.6" stroke-linejoin="round"/>
          <!-- branch to node 3 (bottom) -->
          <path class="artery-base" d="M230 220 C 180 240, 150 300, 90 320" stroke="url(#gGold)" stroke-width="1.6"/>

          <!-- animated flowing light over the whole path system -->
          <path class="artery-flow" d="M440 60 C 380 70, 360 130, 300 150 C 250 168, 240 220, 230 220 C 180 215, 150 150, 90 130" stroke="#FFF4D6" stroke-width="2.6" stroke-linecap="round"/>
          <path class="artery-flow f2" d="M230 220 C 180 240, 150 300, 90 320" stroke="#FFF4D6" stroke-width="2" stroke-linecap="round"/>

          <!-- origin life dot (right) -->
          <circle cx="440" cy="60" r="6" fill="url(#gNode)" filter="url(#glow)"/>
          <circle class="origin-pulse" cx="440" cy="60" r="6" fill="none" stroke="#2DB3A6" stroke-width="2"/>

          <!-- three glowing service nodes -->
          <circle cx="90" cy="130" r="10" fill="url(#gNode)" filter="url(#glow)"/>
          <circle cx="74" cy="226" r="10" fill="url(#gNode)" filter="url(#glow)"/>
          <circle cx="90" cy="320" r="10" fill="url(#gNode)" filter="url(#glow)"/>

          <!-- service labels in the SAME coordinate space as the nodes (always aligned) -->
          <g font-family="'El Messiri','Tajawal',sans-serif" font-weight="600" font-size="16" fill="#F4ECDB" text-anchor="end" direction="rtl" xml:lang="ar">
            <text x="72" y="126">متاجر</text>
            <text x="56" y="222">حلول</text>
            <text x="72" y="316">تدريب</text>
          </g>
        </svg>

        <!-- floating glass stats -->
        <div class="stats-card" role="group" aria-label="مزايا وريد">
          <div class="si"><div class="sv" data-count="3"><bdi>0</bdi></div><div class="sl">خدمات متكاملة</div></div>
          <div class="sep" aria-hidden="true"></div>
          <div class="si"><div class="sv" data-count="48"><bdi>0</bdi></div><div class="sl">ساعة حتى الإطلاق</div></div>
          <div class="sep" aria-hidden="true"></div>
          <div class="si"><div class="sv" data-count="100" data-suffix="%"><bdi>0</bdi></div><div class="sl">هوية عربية فاخرة</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- decorative divider -->
<div class="divider wrap reveal" aria-hidden="true">
  <svg viewBox="0 0 420 30" fill="none">
    <line x1="0" y1="15" x2="170" y2="15" stroke="#C8A24B" stroke-opacity=".35"/>
    <path d="M210 4 l8 11 -8 11 -8-11z" stroke="#C8A24B" stroke-opacity=".7" fill="none"/>
    <circle cx="210" cy="15" r="3" fill="#2DB3A6"/>
    <line x1="250" y1="15" x2="420" y2="15" stroke="#C8A24B" stroke-opacity=".35"/>
  </svg>
</div>

<!-- ===== Services ===== -->
<section class="sec" id="services">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="kicker">خدماتنا</span>
      <h2>ثلاثة أوردة تصل عملك بالعالم الرقمي</h2>
      <p>منظومة متكاملة تنقل فكرتك من الورق إلى السوق، ومن السوق إلى الريادة.</p>
    </div>

    <div class="services">
      <!-- 1 -->
      <article class="card reveal">
        <span class="num" aria-hidden="true">01</span>
        <div class="icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l1.5-5h15L21 9M3 9v10a1 1 0 001 1h16a1 1 0 001-1V9M3 9h18M9 13a3 3 0 006 0" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h3>المتاجر الإلكترونية</h3>
        <p>متجر «من ضغطة زر» على غرار سلّة — منصّة متعددة المتاجر بإنشاء فوري وعزل آمن لكل متجر.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> إنشاء فوري وإطلاق سريع</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> إدارة المنتجات والطلبات والمدفوعات</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> عزل آمن وكامل لبيانات كل متجر</li>
        </ul>
        <a class="more" href="https://wa.me/<?= $wa ?>?text=<?= rawurlencode('أريد إنشاء متجر إلكتروني مع وريد.') ?>" target="_blank" rel="noopener">اطلب متجرك
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </article>

      <!-- 2 -->
      <article class="card reveal">
        <span class="num" aria-hidden="true">02</span>
        <div class="icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="7" y="7" width="10" height="10" rx="1.5"/><rect x="10" y="10" width="4" height="4" rx="1"/><path d="M10 3v2M14 3v2M10 19v2M14 19v2M3 10h2M3 14h2M19 10h2M19 14h2" stroke-linecap="round"/></svg>
        </div>
        <h3>الحلول التقنية</h3>
        <p>أنظمة Hardware وSoftware متكاملة للجهات الحكومية والشركات، مبنية على بنية تحتية موثوقة.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> أنظمة مخصّصة حسب احتياجك</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> بنية تحتية وتجهيزات متكاملة</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> دعم وصيانة واستشارات مستمرة</li>
        </ul>
        <a class="more" href="https://wa.me/<?= $wa ?>?text=<?= rawurlencode('أرغب باستشارة حول الحلول التقنية من وريد.') ?>" target="_blank" rel="noopener">اطلب استشارة
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </article>

      <!-- 3 -->
      <article class="card reveal">
        <span class="num" aria-hidden="true">03</span>
        <div class="icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 10L12 5 2 10l10 5 10-5z" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 12v5c0 1.1 2.7 2.5 6 2.5s6-1.4 6-2.5v-5M20 11v5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h3>التدريب التقني</h3>
        <p>مسارات احترافية في الذكاء الاصطناعي والبرمجة وقواعد البيانات والخوادم، وتأهيل المدرّبين.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> ذكاء اصطناعي وتطوير Backend وFrontend</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> قواعد البيانات وإدارة الخوادم</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg> برنامج «تدريب المدرّبين» المعتمد</li>
        </ul>
        <a class="more" href="https://wa.me/<?= $wa ?>?text=<?= rawurlencode('أريد الالتحاق بمسارات التدريب التقني في وريد.') ?>" target="_blank" rel="noopener">انضمّ لمساراتنا
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </article>
    </div>
  </div>
</section>

<!-- ===== Why Wareed ===== -->
<section class="sec" id="why">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="kicker">لماذا وريد</span>
      <h2>فخامة عالمية بجذرٍ مصري أصيل</h2>
      <p>نجمع بين الأناقة والأداء والأمان، لتكون تجربتك الرقمية كنزاً يليق بطموحك.</p>
    </div>
    <div class="why">
      <div class="feat reveal">
        <div class="fi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>سرعة فائقة</h3>
        <p>أداء خفيف محسَّن يخدم تجربتك ويصدّر صفحاتك في بحث جوجل مصر.</p>
      </div>
      <div class="feat reveal">
        <div class="fi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-4z" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>أمان وعزل</h3>
        <p>عزل بنيوي صارم لبيانات كل متجر وكل عميل، بمعايير موثوقة.</p>
      </div>
      <div class="feat reveal">
        <div class="fi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6" stroke-linecap="round"/></svg></div>
        <h3>دعم بشري حقيقي</h3>
        <p>فريق مصري يفهم سوقك ويرافقك خطوة بخطوة، لا روبوتات فقط.</p>
      </div>
      <div class="feat reveal">
        <div class="fi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 12h4l3 8 4-16 3 8h4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>نموّ مستدام</h3>
        <p>حلول قابلة للتوسّع تكبر مع أعمالك من أول متجر إلى منظومة كاملة.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== How we work ===== -->
<section class="sec" id="how">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="kicker">كيف نعمل</span>
      <h2>أربع نبضات حتى الانطلاق</h2>
      <p>رحلة واضحة وسلسة من أول تواصل إلى إطلاقٍ ناجح ونموٍّ مستمر.</p>
    </div>
    <div class="steps">
      <div class="step reveal">
        <div class="sn" aria-hidden="true">١</div>
        <h3>نستمع لرؤيتك</h3>
        <p>جلسة تواصل نفهم فيها هدفك وجمهورك ومتطلباتك بدقّة.</p>
      </div>
      <div class="step reveal">
        <div class="sn" aria-hidden="true">٢</div>
        <h3>نُصمّم الحل</h3>
        <p>نخطّط الخدمة المناسبة — متجر أو نظام أو مسار تدريبي — ونرسم خارطة التنفيذ.</p>
      </div>
      <div class="step reveal">
        <div class="sn" aria-hidden="true">٣</div>
        <h3>نُنفّذ ونُطلق</h3>
        <p>نبني بسرعة وجودة عالية، ونطلق حلّك جاهزاً للعمل في السوق.</p>
      </div>
      <div class="step reveal">
        <div class="sn" aria-hidden="true">٤</div>
        <h3>ندعم وننمّي</h3>
        <p>متابعة وصيانة وتطوير مستمر لنضمن نموّك على المدى الطويل.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== CTA ===== -->
<section class="sec" id="contact">
  <div class="wrap">
    <div class="cta-band reveal">
      <span class="kicker" style="justify-content:center">ابدأ رحلتك</span>
      <h2>دع شريانك الرقمي ينبض اليوم</h2>
      <p>سواء أردت متجراً إلكترونياً، أو حلاً تقنياً متكاملاً، أو مساراً تدريبياً احترافياً — وريد جاهزة لتحويل فكرتك إلى واقعٍ يتنفّس النجاح.</p>
      <div class="cta-actions">
        <a class="btn btn-gold" href="https://wa.me/<?= $wa ?>?text=<?= rawurlencode('مرحباً وريد، أرغب في بدء مشروعي معكم.') ?>" target="_blank" rel="noopener">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.82 9.82 0 001.523 5.252l-.999 3.648 3.965-1.04zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.148-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.247-.694.247-1.289.173-1.413z"/></svg>
          تواصل عبر واتساب
        </a>
        <a class="btn btn-ghost" href="mailto:<?= $email ?>?subject=<?= rawurlencode('طلب مشروع جديد مع وريد') ?>&body=<?= rawurlencode('مرحباً فريق وريد،%0Aأرغب في مناقشة مشروع جديد.') ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
          راسلنا بالبريد
        </a>
      </div>
    </div>
  </div>
</section>

</main>

<!-- ===== Footer ===== -->
<footer>
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <a class="brand" href="#top" aria-label="وريد">
          <svg class="mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
            <circle cx="20" cy="20" r="18.5" stroke="#C8A24B" stroke-opacity=".5"/>
            <path d="M9 20 H15 L17 12 L22 28 L25 20 H31" stroke="#E7C873" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            <circle class="dot" cx="9" cy="20" r="2.6"/>
          </svg>
          <span>وريد</span>
        </a>
        <p>شريان السوق الرقمي في مصر. نبني، ونربط، وننمّي — بفخامة عالمية وروحٍ مصرية أصيلة.</p>
        <div class="socials">
          <a href="https://wa.me/<?= $wa ?>" target="_blank" rel="noopener" aria-label="واتساب وريد">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.82 9.82 0 001.523 5.252l-.999 3.648 3.965-1.04z"/></svg>
          </a>
          <a href="mailto:<?= $email ?>" aria-label="البريد الإلكتروني لوريد">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
          </a>
        </div>
      </div>
      <div class="foot-col">
        <h3>الخدمات</h3>
        <a href="#services">المتاجر الإلكترونية</a>
        <a href="#services">الحلول التقنية</a>
        <a href="#services">التدريب التقني</a>
      </div>
      <div class="foot-col">
        <h3>روابط</h3>
        <a href="#why">لماذا وريد</a>
        <a href="#how">كيف نعمل</a>
        <a href="#contact">تواصل معنا</a>
      </div>
      <div class="foot-col">
        <h3>تواصل</h3>
        <a href="https://wa.me/<?= $wa ?>" target="_blank" rel="noopener" dir="ltr" style="text-align:right"><bdi>+<?= $wa ?></bdi></a>
        <a href="mailto:<?= $email ?>" dir="ltr" style="text-align:right"><bdi><?= $email ?></bdi></a>
      </div>
    </div>
    <div class="foot-bottom">
      <span>© <bdi><?= $year ?></bdi> وريد. جميع الحقوق محفوظة.</span>
      <span>صُنع بـ<span class="heart" aria-label="حب">❤</span> في مصر</span>
    </div>
  </div>
</footer>

<a class="wa-float" href="https://wa.me/<?= $wa ?>?text=<?= rawurlencode('مرحباً وريد!') ?>" target="_blank" rel="noopener" aria-label="تواصل عبر واتساب">
  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.82 9.82 0 001.523 5.252l-.999 3.648 3.965-1.04zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.148-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.247-.694.247-1.289.173-1.413z"/></svg>
</a>

<style>
  /* artery flow animation (kept here, disabled by reduced-motion block above) */
  .artery-flow{
    stroke-dasharray:60 320;
    stroke-dashoffset:380;
    opacity:.85;
    filter:drop-shadow(0 0 5px rgba(231,200,115,.8));
    animation:flow 6s linear infinite;
  }
  .artery-flow.f2{animation-delay:1.4s;animation-duration:6.5s}
  @keyframes flow{to{stroke-dashoffset:-380}}
  .origin-pulse{transform-box:fill-box;transform-origin:center;animation:opulse 2.4s ease-out infinite}
  @keyframes opulse{0%{r:6;opacity:.9}100%{r:18;opacity:0}}
</style>

<script>
(function(){
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Sticky header style on scroll */
  var header = document.getElementById('top');
  function onScroll(){
    if(window.scrollY > 24){ header.classList.add('scrolled'); }
    else{ header.classList.remove('scrolled'); }
  }
  onScroll();
  window.addEventListener('scroll', onScroll, {passive:true});

  /* Mobile menu (accessible: inert when hidden, Esc to close, focus management) */
  var menuBtn = document.getElementById('menuBtn');
  var navLinks = document.getElementById('navLinks');
  var menuMq = window.matchMedia('(max-width: 820px)');

  function syncInert(open){
    // Off-screen links must not be focusable on mobile; never inert on desktop.
    if(menuMq.matches){
      if(open){ navLinks.removeAttribute('inert'); }
      else { navLinks.setAttribute('inert',''); }
    } else {
      navLinks.removeAttribute('inert');
    }
  }
  function setMenu(open){
    navLinks.classList.toggle('open', open);
    menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    menuBtn.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
    syncInert(open);
    if(open){ var first = navLinks.querySelector('a'); if(first) first.focus(); }
  }
  syncInert(false);
  menuMq.addEventListener('change', function(){ syncInert(navLinks.classList.contains('open')); });

  menuBtn.addEventListener('click', function(){
    setMenu(!navLinks.classList.contains('open'));
  });
  navLinks.addEventListener('click', function(e){
    if(e.target.closest('a')){ setMenu(false); }
  });
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape' && navLinks.classList.contains('open')){
      setMenu(false); menuBtn.focus();
    }
  });

  /* Scroll reveal with stagger */
  var revealEls = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
  if(reduce || !('IntersectionObserver' in window)){
    revealEls.forEach(function(el){ el.classList.add('in'); });
  } else {
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(en){
        if(en.isIntersecting){
          var sibs = Array.prototype.slice.call(en.target.parentNode.children).filter(function(c){return c.classList.contains('reveal');});
          var idx = sibs.indexOf(en.target);
          en.target.style.transitionDelay = (idx>0 ? Math.min(idx,5)*80 : 0) + 'ms';
          en.target.classList.add('in');
          io.unobserve(en.target);
        }
      });
    }, {threshold:0.12, rootMargin:'0px 0px -8% 0px'});
    revealEls.forEach(function(el){ io.observe(el); });
  }

  /* Animated counters */
  function formatNum(n){ try{ return n.toLocaleString('ar-EG'); }catch(e){ return String(n); } }
  function animateCount(el){
    var target = parseFloat(el.getAttribute('data-count')) || 0;
    var suffix = el.getAttribute('data-suffix') || '';
    var prefix = el.getAttribute('data-prefix') || '';
    var bdi = el.querySelector('bdi');
    if(reduce){ bdi.textContent = prefix + formatNum(target) + (suffix && !/^[٪%+]/.test(suffix) ? ' '+suffix : suffix); return; }
    var dur = 1400, start = null;
    function tick(ts){
      if(!start) start = ts;
      var p = Math.min((ts-start)/dur, 1);
      var eased = 1 - Math.pow(1-p, 3);
      var val = Math.round(target*eased);
      var sfx = suffix && !/^[٪%+]/.test(suffix) ? ' '+suffix : suffix;
      bdi.textContent = prefix + formatNum(val) + sfx;
      if(p<1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  var counters = Array.prototype.slice.call(document.querySelectorAll('[data-count]'));
  if(!('IntersectionObserver' in window)){
    counters.forEach(animateCount);
  } else {
    var cio = new IntersectionObserver(function(entries){
      entries.forEach(function(en){
        if(en.isIntersecting){ animateCount(en.target); cio.unobserve(en.target); }
      });
    }, {threshold:0.4});
    counters.forEach(function(el){ cio.observe(el); });
  }
})();
</script>
</body>
</html>