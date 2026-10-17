<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> —  Research Grant Management</title>
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/mortarboard-fill.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
    --dark:#060a12;
    --dark2:#0a1728;
    --dark3:#1b3858;
    --purple:#d4a373;
    --purple-light:#e8c9a0;
    --purple-dark:#b8895e;
    --blue:#537497;
    --blue-light:#69819c;
    --cyan:#96b2c6;
    --text:#f5e6cc;
    --text-muted:#aabbc8;
    --text-dim:#69819c;
    --glass:rgba(245,230,204,.04);
    --glass-border:rgba(245,230,204,.08);
    --glow-purple:rgba(212,163,115,.35);
    --glow-secondary:rgba(83,116,151,.35);
    --green:#65a390;
    --amber:#b8895e;
}
body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);overflow-x:hidden;scroll-behavior:smooth}
::selection{background:var(--purple);color:#fff}
::-webkit-scrollbar{width:8px}
::-webkit-scrollbar-track{background:var(--dark)}
::-webkit-scrollbar-thumb{background:var(--purple);border-radius:4px}

/* Mouse glow */
#mouseGlow{position:fixed;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(212,163,115,.12) 0%,transparent 70%);pointer-events:none;z-index:9999;transform:translate(-50%,-50%);transition:opacity .3s}

/* Navbar */
.navbar-landing{position:fixed;top:0;left:0;right:0;z-index:1000;padding:16px 0;transition:all .4s ease}
.navbar-landing.scrolled{background:rgba(6,10,18,.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--glass-border);padding:10px 0}
.navbar-landing .navbar-brand{font-weight:800;font-size:1.3rem;color:var(--text);display:flex;align-items:center;gap:10px}
.navbar-landing .navbar-brand .brand-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--purple),var(--blue));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.navbar-landing .nav-link{color:var(--text-muted)!important;font-weight:500;font-size:.9rem;padding:8px 18px!important;border-radius:10px;transition:all .3s;position:relative}
.navbar-landing .nav-link:hover{color:var(--text)!important;background:var(--glass)}
.navbar-landing .nav-link::after{content:'';position:absolute;bottom:2px;left:50%;width:0;height:2px;background:var(--purple);border-radius:2px;transition:all .3s;transform:translateX(-50%)}
.navbar-landing .nav-link:hover::after{width:20px}
.btn-glow{background:linear-gradient(135deg,var(--purple),var(--blue));color:#fff;border:none;font-weight:600;padding:10px 28px;border-radius:12px;transition:all .3s;position:relative;overflow:hidden}
.btn-glow:hover{color:#fff;transform:translateY(-2px);box-shadow:0 8px 30px var(--glow-purple)}
.btn-glow::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:linear-gradient(45deg,transparent,rgba(255,255,255,.1),transparent);transform:rotate(45deg) translateX(-100%);transition:.6s}
.btn-glow:hover::before{transform:rotate(45deg) translateX(100%)}
.btn-outline-glow{background:transparent;color:var(--text);border:1px solid var(--glass-border);font-weight:500;padding:10px 24px;border-radius:12px;transition:all .3s}
.btn-outline-glow:hover{color:var(--text);border-color:var(--purple);background:var(--glass);box-shadow:0 0 20px var(--glow-purple)}

/* Hero */
.hero-section{min-height:100vh;display:flex;align-items:center;position:relative;overflow:hidden;padding-top:80px}
.hero-section .hero-bg{position:absolute;top:0;left:0;width:100%;height:100%;z-index:0}
.hero-section .hero-bg img{width:100%;height:100%;object-fit:cover;object-position:center;filter:brightness(.25) saturate(.4);opacity:.7}
.hero-section .hero-bg .hero-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(135deg,rgba(6,10,18,.92) 0%,rgba(6,10,18,.7) 50%,rgba(6,10,18,.92) 100%),radial-gradient(ellipse at 30% 50%,rgba(212,163,115,.12) 0%,transparent 60%),radial-gradient(ellipse at 70% 50%,rgba(83,116,151,.1) 0%,transparent 60%)}
.hero-section .hero-content{position:relative;z-index:2}
.hero-badge{display:inline-flex;align-items:center;gap:8px;background:var(--glass);border:1px solid var(--glass-border);border-radius:100px;padding:8px 20px;font-size:.85rem;color:var(--text-muted);margin-bottom:24px;backdrop-filter:blur(10px)}
.hero-badge .badge-dot{width:8px;height:8px;border-radius:50%;background:var(--cyan);animation:pulse-dot 2s infinite}
@keyframes pulse-dot{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(6,182,212,.6)}50%{opacity:.7;box-shadow:0 0 0 6px rgba(6,182,212,0)}}
.hero-title{font-size:clamp(2.5rem,5vw,4.2rem);font-weight:800;line-height:1.1;margin-bottom:20px}
.hero-title .gradient-text{background:linear-gradient(135deg,var(--purple-light),var(--blue-light),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero-title .typewriter{border-right:3px solid var(--purple);padding-right:4px;animation:blink-cursor .8s step-end infinite}
@keyframes blink-cursor{50%{border-color:transparent}}
.hero-subtitle{font-size:1.1rem;color:var(--text-muted);line-height:1.8;max-width:580px;margin-bottom:32px}
.hero-actions{display:flex;flex-wrap:wrap;gap:12px}
/* Hero visual */
.hero-visual{position:relative;display:flex;align-items:center;justify-content:center}
.hero-glass-card{width:100%;max-width:520px;background:var(--glass);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid var(--glass-border);border-radius:24px;padding:30px;position:relative;overflow:hidden}
.hero-glass-card::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:conic-gradient(from 0deg,transparent,var(--purple),transparent,var(--blue),transparent);opacity:.08;animation:spin-card 8s linear infinite}
@keyframes spin-card{to{transform:rotate(360deg)}}
.hero-glass-card .card-glow{position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--purple),var(--blue),transparent)}
.hero-card-content{position:relative;z-index:1}
.hero-card-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.hero-card-dots{display:flex;gap:6px}
.hero-card-dots span{width:10px;height:10px;border-radius:50%;background:var(--text-dim)}
.hero-card-dots span:nth-child(1){background:#EF4444}
.hero-card-dots span:nth-child(2){background:#c4936f}
.hero-card-dots span:nth-child(3){background:#65a390}
.hero-card-body{display:grid;gap:12px}
.hero-card-row{display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--glass);border-radius:12px;border:1px solid var(--glass-border)}
.hero-card-row .row-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0}
.hero-card-row .row-icon.purple{background:rgba(212,163,115,.15);color:var(--purple-light)}
.hero-card-row .row-icon.blue{background:rgba(83,116,151,.15);color:var(--blue-light)}
.hero-card-row .row-icon.green{background:rgba(86,171,147,.15);color:#56ab93}
.hero-card-row .row-icon.amber{background:rgba(196,147,111,.15);color:#c4936f}
.hero-card-row .row-label{font-size:.8rem;color:var(--text-dim)}
.hero-card-row .row-value{font-size:.85rem;font-weight:600;color:var(--text)}
.hero-floating{position:absolute;width:50px;height:50px;border-radius:14px;background:var(--glass);backdrop-filter:blur(10px);border:1px solid var(--glass-border);display:flex;align-items:center;justify-content:center;font-size:1.2rem;animation:float 5s ease-in-out infinite;box-shadow:0 8px 30px rgba(0,0,0,.3)}
.hero-floating:nth-child(2){top:-20px;right:-20px;animation-delay:0s;background:rgba(212,163,115,.12);border-color:rgba(212,163,115,.2)}
.hero-floating:nth-child(3){bottom:40px;left:-30px;animation-delay:1.5s;background:rgba(83,116,151,.12);border-color:rgba(83,116,151,.2)}
.hero-floating:nth-child(4){bottom:-25px;right:30px;animation-delay:3s;background:rgba(101,163,144,.12);border-color:rgba(101,163,144,.2)}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px)}}

/* Section common */
.section-padding{padding:120px 0}
.section-label{display:inline-flex;align-items:center;gap:8px;background:var(--glass);border:1px solid var(--glass-border);border-radius:100px;padding:6px 18px;font-size:.8rem;color:var(--text-muted);margin-bottom:16px;backdrop-filter:blur(10px)}
.section-label .label-glow{width:6px;height:6px;border-radius:50%;background:var(--purple);box-shadow:0 0 8px var(--glow-purple);animation:pulse-dot 2s infinite}
.section-title{font-size:clamp(1.8rem,3vw,2.8rem);font-weight:800;margin-bottom:16px}
.section-title .gradient-text{background:linear-gradient(135deg,var(--purple-light),var(--blue-light));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.section-desc{color:var(--text-muted);font-size:1.05rem;max-width:600px;line-height:1.7}

/* Feature cards */
.feature-card{background:var(--glass);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid var(--glass-border);border-radius:20px;padding:32px 28px;transition:all .4s ease;height:100%;position:relative;overflow:hidden}
.feature-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--purple),transparent);opacity:0;transition:opacity .4s}
.feature-card:hover::before{opacity:1}
.feature-card:hover{transform:translateY(-8px);border-color:rgba(212,163,115,.2);box-shadow:0 20px 60px rgba(0,0,0,.3),0 0 40px var(--glow-purple)}
.feature-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:20px;transition:all .4s}
.feature-card:hover .feature-icon{transform:scale(1.1) rotate(-5deg)}
.feature-card h5{font-weight:700;font-size:1.1rem;margin-bottom:10px}
.feature-card p{color:var(--text-muted);font-size:.9rem;line-height:1.7;margin:0}

/* Stats */
.stats-section{position:relative;overflow:hidden}
.stats-section::before{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:500px;height:500px;background:radial-gradient(circle,rgba(212,163,115,.06) 0%,transparent 70%);pointer-events:none}
.stat-card{background:var(--glass);backdrop-filter:blur(20px);border:1px solid var(--glass-border);border-radius:20px;padding:32px 24px;text-align:center;transition:all .4s;position:relative;overflow:hidden}
.stat-card:hover{border-color:rgba(212,163,115,.2);transform:translateY(-4px);box-shadow:0 20px 60px rgba(0,0,0,.3)}
.stat-card .stat-number{font-size:clamp(2rem,3vw,3rem);font-weight:800;background:linear-gradient(135deg,var(--purple-light),var(--blue-light));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1}
.stat-card .stat-number .plus{background:inherit;-webkit-text-fill-color:initial;color:var(--text);font-weight:400}
.stat-card .stat-label{color:var(--text-muted);font-size:.85rem;margin-top:8px}

/* How it works */
.step-card{text-align:center;padding:20px;position:relative}
.step-number{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;margin:0 auto 20px;background:linear-gradient(135deg,var(--purple),var(--blue));color:#fff;position:relative;z-index:1;box-shadow:0 0 30px var(--glow-purple);transition:all .4s}
.step-card:hover .step-number{transform:scale(1.1);box-shadow:0 0 50px var(--glow-purple)}
.step-connector{position:absolute;top:44px;left:60%;width:80%;height:2px;background:linear-gradient(90deg,var(--purple),var(--blue),transparent);z-index:0}

/* Showcase / Dashboard Preview */
.showcase-section{overflow:hidden}
.mockup-wrapper{position:relative;padding:20px 0}
.mockup-browser{background:var(--dark2);border:1px solid var(--glass-border);border-radius:20px;overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.5)}
.mockup-browser .mockup-bar{display:flex;align-items:center;gap:8px;padding:14px 20px;background:var(--dark3);border-bottom:1px solid var(--glass-border)}
.mockup-browser .mockup-dot{width:12px;height:12px;border-radius:50%}
.mockup-browser .mockup-dot:nth-child(1){background:#EF4444}
.mockup-browser .mockup-dot:nth-child(2){background:#c4936f}
.mockup-browser .mockup-dot:nth-child(3){background:#65a390}
.mockup-browser .mockup-url{background:var(--dark);border-radius:8px;padding:6px 14px;margin-left:12px;flex:1;font-size:.8rem;color:var(--text-dim)}
.mockup-browser .mockup-content{padding:0;min-height:400px;position:relative}
.mockup-browser .mockup-content img{width:100%;height:auto;display:block}
.mockup-glows{position:absolute;top:20%;left:-10%;width:120%;height:80%;pointer-events:none;z-index:0}
.mockup-glows .glow-1{position:absolute;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(212,163,115,.1),transparent 70%);top:0;left:0;animation:glow-pulse 4s ease-in-out infinite}
.mockup-glows .glow-2{position:absolute;width:250px;height:250px;border-radius:50%;background:radial-gradient(circle,rgba(83,116,151,.08),transparent 70%);bottom:0;right:0;animation:glow-pulse 4s ease-in-out infinite 2s}
@keyframes glow-pulse{0%,100%{opacity:.5;transform:scale(1)}50%{opacity:1;transform:scale(1.2)}}
.dashboard-slide{padding:10px}
.dashboard-slide .slide-label{text-align:center;padding:16px;font-weight:600;font-size:.9rem;color:var(--text-muted)}

/* Testimonials */
.testimonial-card{background:var(--glass);backdrop-filter:blur(20px);border:1px solid var(--glass-border);border-radius:20px;padding:32px;transition:all .4s;height:100%}
.testimonial-card:hover{border-color:rgba(212,163,115,.2);transform:translateY(-4px);box-shadow:0 20px 60px rgba(0,0,0,.3)}
.testimonial-stars{color:#c4936f;margin-bottom:16px;font-size:.9rem}
.testimonial-text{color:var(--text-muted);font-size:.95rem;line-height:1.8;margin-bottom:24px;font-style:italic}
.testimonial-author{display:flex;align-items:center;gap:12px}
.testimonial-author img{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--glass-border)}
.testimonial-author .name{font-weight:600;font-size:.9rem}
.testimonial-author .role{color:var(--text-dim);font-size:.8rem}

/* FAQ */
.accordion-item{background:var(--glass)!important;backdrop-filter:blur(20px);border:1px solid var(--glass-border)!important;border-radius:16px!important;margin-bottom:12px;overflow:hidden}
.accordion-button{background:transparent!important;color:var(--text)!important;font-weight:600;padding:20px 24px;font-size:.95rem;box-shadow:none!important}
.accordion-button:not(.collapsed){color:var(--purple-light)!important}
.accordion-button::after{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23CBD5E1' viewBox='0 0 16 16'%3E%3Cpath d='M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z'/%3E%3C/svg%3E")!important;transition:all .3s}
.accordion-button:not(.collapsed)::after{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%237C3AED' viewBox='0 0 16 16'%3E%3Cpath d='M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z'/%3E%3C/svg%3E")!important}
.accordion-body{color:var(--text-muted);font-size:.9rem;line-height:1.7;padding:0 24px 20px}

/* Contact */
.contact-info-item{display:flex;gap:16px;padding:20px;background:var(--glass);border:1px solid var(--glass-border);border-radius:16px;transition:all .3s;margin-bottom:12px}
.contact-info-item:hover{border-color:rgba(212,163,115,.2)}
.contact-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.form-control-lg{background:var(--glass)!important;border:1px solid var(--glass-border)!important;color:var(--text)!important;border-radius:12px!important;padding:14px 18px!important;font-size:.9rem!important;transition:all .3s}
.form-control-lg:focus{background:var(--dark2)!important;border-color:var(--purple)!important;box-shadow:0 0 0 3px var(--glow-purple)!important}
.form-control-lg::placeholder{color:var(--text-dim)}
textarea.form-control-lg{min-height:120px;resize:vertical}
.form-floating>.form-control:focus~label,.form-floating>.form-control:not(:placeholder-shown)~label{color:var(--purple-light)!important;transform:scale(.85) translateY(-.75rem) translateX(.15rem)}

/* Newsletter */
.newsletter-section{background:linear-gradient(135deg,rgba(212,163,115,.08),rgba(83,116,151,.08));border:1px solid var(--glass-border);border-radius:24px;padding:60px;position:relative;overflow:hidden}
.newsletter-section::before{content:'';position:absolute;top:-50%;right:-20%;width:400px;height:400px;background:radial-gradient(circle,rgba(212,163,115,.08),transparent 70%);pointer-events:none}
.newsletter-section h3{font-weight:800;font-size:1.6rem}
.newsletter-section p{color:var(--text-muted)}

/* Footer */
.site-footer{background:var(--dark2);border-top:1px solid var(--glass-border);padding:80px 0 0}
.site-footer h5{font-weight:700;font-size:1rem;margin-bottom:20px}
.site-footer a{color:var(--text-muted);text-decoration:none;transition:all .3s;font-size:.88rem}
.site-footer a:hover{color:var(--purple-light)}
.site-footer .footer-links{list-style:none;padding:0}
.site-footer .footer-links li{margin-bottom:10px}
.site-footer .footer-links a::before{content:'\F285';font-family:'bootstrap-icons';margin-right:8px;font-size:.6rem;color:var(--purple)}
.footer-social a{width:38px;height:38px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:var(--glass);border:1px solid var(--glass-border);color:var(--text-muted);transition:all .3s;margin-right:6px;text-decoration:none}
.footer-social a:hover{background:var(--purple);border-color:var(--purple);color:#fff;transform:translateY(-3px)}
.footer-bottom{border-top:1px solid var(--glass-border);padding:20px 0;margin-top:60px}

/* Swiper overrides */
.swiper-button-next,.swiper-button-prev{color:var(--purple-light)!important;background:var(--glass);backdrop-filter:blur(10px);width:44px!important;height:44px!important;border-radius:50%;border:1px solid var(--glass-border)}
.swiper-button-next::after,.swiper-button-prev::after{font-size:1rem!important}
.swiper-pagination-bullet{background:var(--text-dim)!important;opacity:.5}
.swiper-pagination-bullet-active{background:var(--purple)!important;opacity:1;width:24px!important;border-radius:4px!important}

/* Particles */
#particles-canvas{position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:0}

/* Back to top */
.back-to-top{position:fixed;bottom:30px;right:30px;width:44px;height:44px;border-radius:12px;background:var(--glass);backdrop-filter:blur(10px);border:1px solid var(--glass-border);color:var(--text);display:flex;align-items:center;justify-content:center;font-size:1.1rem;z-index:999;cursor:pointer;transition:all .3s;opacity:0;transform:translateY(20px);text-decoration:none}
.back-to-top.visible{opacity:1;transform:translateY(0)}
.back-to-top:hover{background:var(--purple);color:#fff;box-shadow:0 0 30px var(--glow-purple)}

/* Responsive */
@media(max-width:991.98px){
    .navbar-landing .navbar-collapse{background:rgba(6,10,18,.95);backdrop-filter:blur(20px);border-radius:16px;border:1px solid var(--glass-border);padding:16px;margin-top:12px}
    .hero-section{min-height:auto;padding:120px 0 60px}
    .hero-visual{margin-top:40px}
    .step-connector{display:none}
    .newsletter-section{padding:40px 24px}
}
@media(max-width:767.98px){
    .section-padding{padding:80px 0}
    .hero-floating{display:none}
    .hero-glass-card{padding:20px}
}
</style>
</head>
<body>
<div id="mouseGlow"></div>
<canvas id="particles-canvas"></canvas>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-landing">
    <div class="container">
        <a class="navbar-brand" href="#">
            <span class="brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
            <?= SITE_NAME ?>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#landingNav" style="color:var(--text);font-size:1.5rem">
            <i class="bi bi-list"></i>
        </button>
        <div class="collapse navbar-collapse" id="landingNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
                <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Sign In</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-glow btn-sm px-4 py-2" href="register.php">Get Started <i class="bi bi-arrow-right ms-1"></i></a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="hero-section" id="home">
    <div class="hero-bg">
        <div class="hero-overlay"></div>
        <img src="https://i.pinimg.com/1200x/29/81/e3/2981e3bccd8697f5a22d255174d74704.jpg" alt="Research background" loading="lazy">
    </div>
    <div class="container">
        <div class="hero-content">
            <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    Streamlining Research Funding Worldwide
                </div>
                <h1 class="hero-title">
                    Empowering Research<br>
                    <span class="gradient-text">Through Smart Grant</span><br>
                    <span class="typewriter">Management</span>
                </h1>
                <p class="hero-subtitle">A comprehensive platform connecting researchers with funding opportunities. Simplify proposal submissions, track applications, and manage grants with powerful analytics.</p>
                <div class="hero-actions">
                    <a href="register.php" class="btn btn-glow btn-lg">
                        Get Started Free <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    <a href="#features" class="btn btn-outline-glow btn-lg">
                        <i class="bi bi-play-circle me-2"></i>See How It Works
                    </a>
                </div>
                <div class="d-flex align-items-center gap-4 mt-5 pt-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex">
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--purple),var(--blue));display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;border:2px solid var(--dark);margin-right:-8px">S</div>
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--cyan));display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;border:2px solid var(--dark);margin-right:-8px">M</div>
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#65a390,#96b2c6);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;border:2px solid var(--dark)">T</div>
                        </div>
                        <span class="text-muted" style="font-size:.85rem">Trusted by <strong style="color:var(--text)">1000+</strong> institutions</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000">
                <div class="hero-visual">
                    <div class="hero-glass-card">
                        <div class="card-glow"></div>
                        <div class="hero-card-content">
                            <div class="hero-card-header">
                                <span style="font-size:.8rem;color:var(--text-muted);font-weight:500"><i class="bi bi-grid-3x3-gap-fill me-2" style="color:var(--purple)"></i>Active Grants Overview</span>
                                <div class="hero-card-dots"><span></span><span></span><span></span></div>
                            </div>
                            <div class="hero-card-body">
                                <div class="hero-card-row">
                                    <div class="row-icon purple"><i class="bi bi-award"></i></div>
                                    <div style="flex:1"><div class="row-label">Total Active Grants</div><div class="row-value">247 Ongoing</div></div>
                                    <span style="color:#65a390;font-size:.8rem;font-weight:600">+12%</span>
                                </div>
                                <div class="hero-card-row">
                                    <div class="row-icon blue"><i class="bi bi-people"></i></div>
                                    <div style="flex:1"><div class="row-label">Registered Researchers</div><div class="row-value">1000+ Users</div></div>
                                    <span style="color:#65a390;font-size:.8rem;font-weight:600">+8.2%</span>
                                </div>
                                <div class="hero-card-row">
                                    <div class="row-icon green"><i class="bi bi-cash-stack"></i></div>
                                    <div style="flex:1"><div class="row-label">Total Funding Distributed</div><div class="row-value">$48.2M</div></div>
                                    <span style="color:#65a390;font-size:.8rem;font-weight:600">+23%</span>
                                </div>
                                <div class="hero-card-row">
                                    <div class="row-icon amber"><i class="bi bi-check-circle"></i></div>
                                    <div style="flex:1"><div class="row-label">Approved Proposals</div><div class="row-value">1,893 Projects</div></div>
                                    <span style="color:#c4936f;font-size:.8rem;font-weight:600">94.2%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-floating"><i class="bi bi-award-fill" style="color:var(--purple-light)"></i></div>
                    <div class="hero-floating"><i class="bi bi-graph-up-arrow" style="color:var(--blue-light)"></i></div>
                    <div class="hero-floating"><i class="bi bi-shield-check" style="color:#65a390"></i></div>
                </div>
            </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats -->
<section class="section-padding stats-section" id="stats">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="0">
                <div class="stat-card">
                    <div class="stat-number"><span class="counter" data-target="500">0</span><span class="plus">+</span></div>
                    <div class="stat-label"><i class="bi bi-briefcase me-1" style="color:var(--purple)"></i> Active Grants</div>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card">
                    <div class="stat-number"><span class="counter" data-target="12458">0</span><span class="plus">+</span></div>
                    <div class="stat-label"><i class="bi bi-people me-1" style="color:var(--blue)"></i> Researchers</div>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card">
                    <div class="stat-number"><span class="counter" data-target="1893">0</span><span class="plus">+</span></div>
                    <div class="stat-label"><i class="bi bi-building me-1" style="color:#65a390"></i> Projects Funded</div>
                </div>
            </div>
            <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card">
                    <div class="stat-number"><span class="counter" data-target="500">0</span><span class="plus">+</span></div>
                    <div class="stat-label"><i class="bi bi-globe me-1" style="color:#c4936f"></i> Partner Institutions</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="section-padding" id="features">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-label mx-auto"><span class="label-glow"></span>Platform Features</div>
            <h2 class="section-title">Everything You Need to <span class="gradient-text">Manage Grants</span></h2>
            <p class="section-desc mx-auto">Powerful tools designed to streamline the entire grant management lifecycle from application to reporting.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(212,163,115,.12);color:var(--purple-light)"><i class="bi bi-rocket-takeoff"></i></div>
                    <h5>Smart Grant Discovery</h5>
                    <p>AI-powered recommendations match researchers with the most relevant funding opportunities based on their profile and expertise.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(83,116,151,.12);color:var(--blue-light)"><i class="bi bi-heart-pulse"></i></div>
                    <h5>Simplified Submissions</h5>
                    <p>Intelligent application forms with auto-save, document upload, and real-time validation to make submissions effortless.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(101,163,144,.12);color:#65a390"><i class="bi bi-people"></i></div>
                    <h5>Collaborative Review</h5>
                    <p>Streamlined peer review workflow with blinded evaluations, scoring rubrics, and collaborative feedback tools.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(196,147,111,.12);color:#c4936f"><i class="bi bi-tree"></i></div>
                    <h5>Real-Time Tracking</h5>
                    <p>Dashboard analytics provide real-time visibility into grant status, budget utilization, and milestone completion.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(139,92,246,.12);color:var(--purple-light)"><i class="bi bi-gear"></i></div>
                    <h5>Automated Compliance</h5>
                    <p>Built-in compliance checks ensure all submissions meet institutional and funding agency requirements before submission.</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(6,182,212,.12);color:var(--cyan)"><i class="bi bi-book"></i></div>
                    <h5>Comprehensive Reporting</h5>
                    <p>Generate detailed reports with visual analytics, expenditure tracking, and impact assessment for stakeholders.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section-padding" id="how-it-works" style="background:var(--dark2)">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-label mx-auto"><span class="label-glow"></span>How It Works</div>
            <h2 class="section-title">From Application to <span class="gradient-text">Funding</span></h2>
            <p class="section-desc mx-auto">Four simple steps to secure research funding for your next breakthrough project.</p>
        </div>
        <div class="row position-relative">
            <div class="step-connector d-none d-lg-block"></div>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="0">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <h5 class="fw-bold mb-2">Register Account</h5>
                    <p style="color:var(--text-muted);font-size:.88rem;line-height:1.7">Create your researcher profile with your academic credentials and expertise areas in minutes.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="100">
                <div class="step-card">
                    <div class="step-number">02</div>
                    <h5 class="fw-bold mb-2">Browse Grants</h5>
                    <p style="color:var(--text-muted);font-size:.88rem;line-height:1.7">Discover funding opportunities matched to your research field and career stage.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4 mb-lg-0" data-aos="fade-up" data-aos-delay="200">
                <div class="step-card">
                    <div class="step-number">03</div>
                    <h5 class="fw-bold mb-2">Submit Proposal</h5>
                    <p style="color:var(--text-muted);font-size:.88rem;line-height:1.7">Complete your application with our guided submission process and document manager.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="step-card">
                    <div class="step-number">04</div>
                    <h5 class="fw-bold mb-2">Get Funded</h5>
                    <p style="color:var(--text-muted);font-size:.88rem;line-height:1.7">Receive funding decisions, manage awards, and track project milestones seamlessly.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Showcase -->
<section class="section-padding showcase-section" id="showcase">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-label mx-auto"><span class="label-glow"></span>Dashboard Preview</div>
            <h2 class="section-title">Beautiful Analytics <span class="gradient-text">at Your Fingertips</span></h2>
            <p class="section-desc mx-auto">Role-based dashboards designed for administrators, researchers, reviewers, and grant managers.</p>
        </div>
        <div class="mockup-wrapper" data-aos="fade-up" data-aos-duration="1200">
            <div class="mockup-glows">
                <div class="glow-1"></div>
                <div class="glow-2"></div>
            </div>
            <div class="swiper dashboardSwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="mockup-browser">
                            <div class="mockup-bar">
                                <span class="mockup-dot"></span><span class="mockup-dot"></span><span class="mockup-dot"></span>
                                <span class="mockup-url"><i class="bi bi-lock me-1" style="color:#65a390"></i>admin.researchgrants.com/dashboard</span>
                            </div>
                            <div class="mockup-content" style="background:linear-gradient(135deg,#0a1728,#1b3858);padding:30px">
                                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
                                    <div style="background:rgba(255,255,255,.04);border-radius:12px;padding:16px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="font-size:.7rem;color:var(--text-dim);margin-bottom:4px">Researchers</div>
                                        <div style="font-size:1.3rem;font-weight:700">2,458</div>
                                    </div>
                                    <div style="background:rgba(255,255,255,.04);border-radius:12px;padding:16px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="font-size:.7rem;color:var(--text-dim);margin-bottom:4px">Active Grants</div>
                                        <div style="font-size:1.3rem;font-weight:700">247</div>
                                    </div>
                                    <div style="background:rgba(255,255,255,.04);border-radius:12px;padding:16px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="font-size:.7rem;color:var(--text-dim);margin-bottom:4px">Applications</div>
                                        <div style="font-size:1.3rem;font-weight:700">1,892</div>
                                    </div>
                                    <div style="background:rgba(255,255,255,.04);border-radius:12px;padding:16px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="font-size:.7rem;color:var(--text-dim);margin-bottom:4px">Funding</div>
                                        <div style="font-size:1.3rem;font-weight:700">$48.2M</div>
                                    </div>
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                                    <div style="background:rgba(255,255,255,.04);border-radius:12px;padding:16px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="font-size:.75rem;color:var(--text-dim);margin-bottom:12px;font-weight:600">Applications by Month</div>
                                        <div style="display:flex;align-items:end;gap:4px;height:80px">
                                            <div style="flex:1;background:var(--purple);border-radius:4px 4px 0 0;height:40%;opacity:.6"></div>
                                            <div style="flex:1;background:var(--purple);border-radius:4px 4px 0 0;height:55%;opacity:.7"></div>
                                            <div style="flex:1;background:var(--purple);border-radius:4px 4px 0 0;height:35%;opacity:.6"></div>
                                            <div style="flex:1;background:var(--purple);border-radius:4px 4px 0 0;height:70%;opacity:.8"></div>
                                            <div style="flex:1;background:var(--purple);border-radius:4px 4px 0 0;height:50%;opacity:.7"></div>
                                            <div style="flex:1;background:var(--blue);border-radius:4px 4px 0 0;height:85%;opacity:.9"></div>
                                            <div style="flex:1;background:var(--blue);border-radius:4px 4px 0 0;height:65%;opacity:.8"></div>
                                            <div style="flex:1;background:var(--blue);border-radius:4px 4px 0 0;height:90%;opacity:.9"></div>
                                            <div style="flex:1;background:var(--blue);border-radius:4px 4px 0 0;height:75%;opacity:.8"></div>
                                            <div style="flex:1;background:var(--cyan);border-radius:4px 4px 0 0;height:95%;opacity:.9"></div>
                                            <div style="flex:1;background:var(--cyan);border-radius:4px 4px 0 0;height:60%;opacity:.8"></div>
                                            <div style="flex:1;background:var(--cyan);border-radius:4px 4px 0 0;height:80%;opacity:.9"></div>
                                        </div>
                                    </div>
                                    <div style="background:rgba(255,255,255,.04);border-radius:12px;padding:16px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="font-size:.75rem;color:var(--text-dim);margin-bottom:12px;font-weight:600">Approval Rate</div>
                                        <div style="text-align:center;padding:10px 0">
                                            <div style="font-size:2.5rem;font-weight:800;background:linear-gradient(135deg,var(--purple-light),var(--blue-light));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">94.2%</div>
                                            <div style="font-size:.8rem;color:var(--text-dim)">of proposals approved</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="slide-label"><i class="bi bi-shield-lock me-2" style="color:var(--purple)"></i>Admin Dashboard — Complete oversight with analytics</div>
                    </div>
                    <div class="swiper-slide">
                        <div class="mockup-browser">
                            <div class="mockup-bar">
                                <span class="mockup-dot"></span><span class="mockup-dot"></span><span class="mockup-dot"></span>
                                <span class="mockup-url"><i class="bi bi-lock me-1" style="color:#65a390"></i>researcher.researchgrants.com/my-proposals</span>
                            </div>
                            <div class="mockup-content" style="background:linear-gradient(135deg,#0a1728,#1b3858);padding:30px">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                                    <div><div style="font-weight:700">My Proposals</div><div style="font-size:.8rem;color:var(--text-dim)">Track your submissions</div></div>
                                    <div style="background:var(--purple);color:#fff;border:none;border-radius:10px;padding:8px 18px;font-size:.8rem;font-weight:600">+ New Proposal</div>
                                </div>
                                <div style="display:grid;gap:8px">
                                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:rgba(255,255,255,.04);border-radius:10px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="width:8px;height:8px;border-radius:50%;background:#c4936f;box-shadow:0 0 8px rgba(196,147,111,.5)"></div>
                                        <div style="flex:1"><div style="font-size:.85rem;font-weight:600">Quantum Computing Research</div><div style="font-size:.75rem;color:var(--text-dim)">Submitted · Mar 15, 2026</div></div>
                                        <span style="font-size:.75rem;background:rgba(196,147,111,.12);color:#c4936f;padding:4px 12px;border-radius:6px;font-weight:600">Under Review</span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:rgba(255,255,255,.04);border-radius:10px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="width:8px;height:8px;border-radius:50%;background:#65a390;box-shadow:0 0 8px rgba(101,163,144,.5)"></div>
                                        <div style="flex:1"><div style="font-size:.85rem;font-weight:600">Renewable Energy Storage</div><div style="font-size:.75rem;color:var(--text-dim)">Approved · Feb 28, 2026</div></div>
                                        <span style="font-size:.75rem;background:rgba(101,163,144,.12);color:#65a390;padding:4px 12px;border-radius:6px;font-weight:600">Approved</span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:rgba(255,255,255,.04);border-radius:10px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="width:8px;height:8px;border-radius:50%;background:#EF4444;box-shadow:0 0 8px rgba(239,68,68,.5)"></div>
                                        <div style="flex:1"><div style="font-size:.85rem;font-weight:600">AI in Healthcare</div><div style="font-size:.75rem;color:var(--text-dim)">Rejected · Jan 10, 2026</div></div>
                                        <span style="font-size:.75rem;background:rgba(239,68,68,.12);color:#EF4444;padding:4px 12px;border-radius:6px;font-weight:600">Rejected</span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:rgba(255,255,255,.04);border-radius:10px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="width:8px;height:8px;border-radius:50%;background:var(--blue);box-shadow:0 0 8px var(--glow-secondary)"></div>
                                        <div style="flex:1"><div style="font-size:.85rem;font-weight:600">Nanotechnology for Medicine</div><div style="font-size:.75rem;color:var(--text-dim)">Draft · Last saved 2 hrs ago</div></div>
                                        <span style="font-size:.75rem;background:rgba(83,116,151,.12);color:var(--blue-light);padding:4px 12px;border-radius:6px;font-weight:600">Draft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="slide-label"><i class="bi bi-person me-2" style="color:var(--blue)"></i>Researcher Dashboard — Track proposals and awards</div>
                    </div>
                    <div class="swiper-slide">
                        <div class="mockup-browser">
                            <div class="mockup-bar">
                                <span class="mockup-dot"></span><span class="mockup-dot"></span><span class="mockup-dot"></span>
                                <span class="mockup-url"><i class="bi bi-lock me-1" style="color:#65a390"></i>reviewer.researchgrants.com/evaluations</span>
                            </div>
                            <div class="mockup-content" style="background:linear-gradient(135deg,#0a1728,#1b3858);padding:30px">
                                <div style="font-weight:700;margin-bottom:16px"><i class="bi bi-chat-square-text me-2" style="color:var(--purple)"></i>Assigned for Review</div>
                                <div style="display:grid;gap:10px">
                                    <div style="padding:16px;background:rgba(255,255,255,.04);border-radius:12px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                                            <span style="font-weight:600;font-size:.9rem">Advanced Materials Research</span>
                                            <span style="font-size:.75rem;background:rgba(196,147,111,.12);color:#c4936f;padding:2px 10px;border-radius:6px;font-weight:600">Pending</span>
                                        </div>
                                        <div style="font-size:.8rem;color:var(--text-dim)">Due in 7 days · Score: -</div>
                                    </div>
                                    <div style="padding:16px;background:rgba(255,255,255,.04);border-radius:12px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                                            <span style="font-weight:600;font-size:.9rem">Climate Change Impact Study</span>
                                            <span style="font-size:.75rem;background:rgba(83,116,151,.12);color:var(--blue-light);padding:2px 10px;border-radius:6px;font-weight:600">In Progress</span>
                                        </div>
                                        <div style="font-size:.8rem;color:var(--text-dim)">Due in 14 days · Score: 78/100</div>
                                    </div>
                                    <div style="padding:16px;background:rgba(255,255,255,.04);border-radius:12px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                                            <span style="font-weight:600;font-size:.9rem">Genomic Data Analysis</span>
                                            <span style="font-size:.75rem;background:rgba(101,163,144,.12);color:#65a390;padding:2px 10px;border-radius:6px;font-weight:600">Completed</span>
                                        </div>
                                        <div style="font-size:.8rem;color:var(--text-dim)">Reviewed · Score: 92/100</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="slide-label"><i class="bi bi-chat-square-text me-2" style="color:#65a390"></i>Reviewer Dashboard — Evaluate proposals with scoring</div>
                    </div>
                    <div class="swiper-slide">
                        <div class="mockup-browser">
                            <div class="mockup-bar">
                                <span class="mockup-dot"></span><span class="mockup-dot"></span><span class="mockup-dot"></span>
                                <span class="mockup-url"><i class="bi bi-lock me-1" style="color:#65a390"></i>manager.researchgrants.com/projects</span>
                            </div>
                            <div class="mockup-content" style="background:linear-gradient(135deg,#0a1728,#1b3858);padding:30px">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                                    <div><div style="font-weight:700">Monitored Projects</div><div style="font-size:.8rem;color:var(--text-dim)">56 active projects</div></div>
                                </div>
                                <div style="display:grid;gap:10px">
                                    <div style="display:flex;align-items:center;gap:16px;padding:14px 16px;background:rgba(255,255,255,.04);border-radius:12px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="flex:1"><div style="font-size:.85rem;font-weight:600">Quantum Computing · Dr. Sarah Johnson</div><div style="font-size:.75rem;color:var(--text-dim)">75% complete · 6 milestones</div></div>
                                        <div style="width:100px;height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden"><div style="width:75%;height:100%;background:var(--purple);border-radius:3px"></div></div>
                                        <span style="font-size:.75rem;color:#65a390;font-weight:600">On Track</span>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:16px;padding:14px 16px;background:rgba(255,255,255,.04);border-radius:12px;border:1px solid rgba(255,255,255,.06)">
                                        <div style="flex:1"><div style="font-size:.85rem;font-weight:600">Renewable Energy · Prof. Michael Chen</div><div style="font-size:.75rem;color:var(--text-dim)">40% complete · 4 milestones</div></div>
                                        <div style="width:100px;height:6px;background:rgba(255,255,255,.06);border-radius:3px;overflow:hidden"><div style="width:40%;height:100%;background:#c4936f;border-radius:3px"></div></div>
                                        <span style="font-size:.75rem;color:#c4936f;font-weight:600">At Risk</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="slide-label"><i class="bi bi-kanban me-2" style="color:#c4936f"></i>Grant Manager Dashboard — Monitor project milestones</div>
                    </div>
                </div>
                <div class="swiper-pagination" style="position:relative;margin-top:24px"></div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="section-padding" id="testimonials" style="background:var(--dark2)">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-label mx-auto"><span class="label-glow"></span>Testimonials</div>
            <h2 class="section-title">What Our <span class="gradient-text">Users Say</span></h2>
            <p class="section-desc mx-auto">Hear from researchers and administrators who use our platform daily.</p>
        </div>
        <div class="swiper testimonialSwiper" data-aos="fade-up">
            <div class="swiper-wrapper">
                <div class="swiper-slide" style="padding:10px">
                    <div class="testimonial-card">
                        <div class="testimonial-stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                        <p class="testimonial-text">"This platform revolutionized how our department manages grant applications. The intuitive interface and real-time tracking have saved us countless hours."</p>
                        <div class="testimonial-author">
                            <img src="https://ui-avatars.com/api/?name=Sarah+Johnson&background=7C3AED&color=fff&size=44" alt="Sarah Johnson">
                            <div><div class="name">Dr. Sarah Johnson</div><div class="role">Principal Investigator, MIT</div></div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide" style="padding:10px">
                    <div class="testimonial-card">
                        <div class="testimonial-stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                        <p class="testimonial-text">"The collaborative review system streamlined our peer review process. We can now manage hundreds of applications with unprecedented efficiency."</p>
                        <div class="testimonial-author">
                            <img src="https://ui-avatars.com/api/?name=Michael+Chen&background=2563EB&color=fff&size=44" alt="Michael Chen">
                            <div><div class="name">Prof. Michael Chen</div><div class="role">Research Director, Stanford</div></div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide" style="padding:10px">
                    <div class="testimonial-card">
                        <div class="testimonial-stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i></div>
                        <p class="testimonial-text">"As a grant administrator, the reporting tools are invaluable. We can generate comprehensive compliance reports in minutes instead of days."</p>
                        <div class="testimonial-author">
                            <img src="https://ui-avatars.com/api/?name=Emily+Rodriguez&background=10B981&color=fff&size=44" alt="Emily Rodriguez">
                            <div><div class="name">Emily Rodriguez</div><div class="role">Grants Manager, UCLA</div></div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide" style="padding:10px">
                    <div class="testimonial-card">
                        <div class="testimonial-stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                        <p class="testimonial-text">"The AI-powered grant matching is incredibly accurate. I've discovered funding opportunities I would have never found on my own through traditional channels."</p>
                        <div class="testimonial-author">
                            <img src="https://ui-avatars.com/api/?name=James+Wilson&background=F59E0B&color=fff&size=44" alt="James Wilson">
                            <div><div class="name">Dr. James Wilson</div><div class="role">Research Fellow, Oxford</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination" style="position:relative;margin-top:24px"></div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section-padding" id="faq">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-label mx-auto"><span class="label-glow"></span>FAQ</div>
            <h2 class="section-title">Frequently Asked <span class="gradient-text">Questions</span></h2>
            <p class="section-desc mx-auto">Find answers to common questions about our grant management platform.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">How do I create an account and start applying for grants?</button></h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Creating an account is free and simple. Click the "Get Started" button, fill in your details including your academic affiliation and research areas, verify your email, and you can immediately start browsing and applying for available grants.</div></div>
                    </div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">What types of grants are available on the platform?</button></h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Our platform hosts a wide variety of funding opportunities including research project grants, travel grants, conference funding, equipment grants, postdoctoral fellowships, and collaborative international research grants across all academic disciplines.</div></div>
                    </div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">How are grant applications reviewed and evaluated?</button></h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Applications undergo a rigorous peer review process. Each submission is assigned to subject-matter experts who evaluate based on scientific merit, feasibility, impact, and budget justification. Reviewers use our standardized scoring rubric, and applicants receive detailed feedback.</div></div>
                    </div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Can I track the status of my submitted applications?</button></h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Yes, your personalized dashboard provides real-time updates on all your applications. You can see whether your proposal is under review, in revision, approved, or declined. You'll also receive email notifications at every stage of the process.</div></div>
                    </div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">Is there any cost to use the platform?</button></h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">The platform is completely free for researchers to create accounts, browse grants, and submit proposals. Funding institutions and partner organizations may have subscription plans for advanced analytics and administrative features.</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact -->
<section class="section-padding" id="contact" style="background:var(--dark2)">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <div class="section-label mx-auto"><span class="label-glow"></span>Contact Us</div>
            <h2 class="section-title">Get in <span class="gradient-text">Touch</span></h2>
            <p class="section-desc mx-auto">Have questions or need assistance? We're here to help.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="contact-info-item"><div class="contact-icon" style="background:rgba(212,163,115,.12);color:var(--purple-light)"><i class="bi bi-geo-alt"></i></div><div><h6 class="fw-bold mb-1">Our Address</h6><p style="color:var(--text-muted);font-size:.88rem;margin:0"><?= SITE_ADDRESS ?></p></div></div>
                <div class="contact-info-item"><div class="contact-icon" style="background:rgba(83,116,151,.12);color:var(--blue-light)"><i class="bi bi-envelope"></i></div><div><h6 class="fw-bold mb-1">Email Us</h6><p style="color:var(--text-muted);font-size:.88rem;margin:0"><?= SITE_EMAIL ?></p></div></div>
                <div class="contact-info-item"><div class="contact-icon" style="background:rgba(101,163,144,.12);color:#65a390"><i class="bi bi-telephone"></i></div><div><h6 class="fw-bold mb-1">Call Us</h6><p style="color:var(--text-muted);font-size:.88rem;margin:0"><?= SITE_PHONE ?></p></div></div>
                <div class="contact-info-item"><div class="contact-icon" style="background:rgba(196,147,111,.12);color:#c4936f"><i class="bi bi-clock"></i></div><div><h6 class="fw-bold mb-1">Working Hours</h6><p style="color:var(--text-muted);font-size:.88rem;margin:0">Mon - Fri: 9:00 AM - 6:00 PM</p></div></div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div style="background:var(--glass);backdrop-filter:blur(20px);border:1px solid var(--glass-border);border-radius:20px;padding:36px">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control form-control-lg" name="name" placeholder="Your Name" required><label>Your Name</label></div></div>
                            <div class="col-md-6"><div class="form-floating"><input type="email" class="form-control form-control-lg" name="email" placeholder="Your Email" required><label>Your Email</label></div></div>
                            <div class="col-12"><div class="form-floating"><input type="text" class="form-control form-control-lg" name="subject" placeholder="Subject" required><label>Subject</label></div></div>
                            <div class="col-12"><div class="form-floating"><textarea class="form-control form-control-lg" name="message" placeholder="Your Message" required style="min-height:130px"></textarea><label>Your Message</label></div></div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-glow btn-lg px-5">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="section-padding pt-0" id="newsletter">
    <div class="container">
        <div class="newsletter-section" data-aos="zoom-in">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <h3>Stay Updated with Latest Grants</h3>
                    <p class="mb-0">Subscribe to our newsletter and receive weekly updates on new funding opportunities directly in your inbox.</p>
                </div>
                <div class="col-lg-5">
                    <form class="d-flex flex-column flex-sm-row gap-2">
                        <input type="email" class="form-control form-control-lg flex-grow-1" placeholder="Enter your email" required style="border-radius:12px">
                        <button type="submit" class="btn btn-glow flex-shrink-0">
                            <i class="bi bi-envelope-paper me-2"></i>Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                    <span style="width:36px;height:36px;background:linear-gradient(135deg,var(--purple),var(--blue));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem"><i class="bi bi-mortarboard-fill"></i></span>
                    <span style="font-weight:800;font-size:1.1rem"><?= SITE_NAME ?></span>
                </div>
                <p style="color:var(--text-muted);font-size:.88rem;line-height:1.7;margin-bottom:20px">Empowering researchers worldwide with smart tools to discover, apply, and manage research grants efficiently.</p>
                <div class="footer-social">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                    <a href="#"><i class="bi bi-linkedin"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                    <a href="#"><i class="bi bi-github"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#faq">FAQ</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h5>For Researchers</h5>
                <ul class="footer-links">
                    <li><a href="register.php">Create Account</a></li>
                    <li><a href="login.php">Sign In</a></li>
                    <li><a href="#">Browse Grants</a></li>
                    <li><a href="#">Submission Guide</a></li>
                    <li><a href="#">Help Center</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5>Contact Info</h5>
                <ul class="list-unstyled" style="color:var(--text-muted);font-size:.88rem">
                    <li class="mb-2 d-flex gap-2"><i class="bi bi-geo-alt" style="color:var(--purple);flex-shrink:0"></i><?= SITE_ADDRESS ?></li>
                    <li class="mb-2 d-flex gap-2"><i class="bi bi-envelope" style="color:var(--purple);flex-shrink:0"></i><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></li>
                    <li class="mb-2 d-flex gap-2"><i class="bi bi-telephone" style="color:var(--purple);flex-shrink:0"></i><?= SITE_PHONE ?></li>
                    <li class="d-flex gap-2"><i class="bi bi-globe" style="color:var(--purple);flex-shrink:0"></i><?= SITE_URL ?></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0" style="color:var(--text-dim);font-size:.85rem">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <a href="#" style="color:var(--text-dim);font-size:.82rem;margin-right:16px">Privacy Policy</a>
                    <a href="#" style="color:var(--text-dim);font-size:.82rem;margin-right:16px">Terms of Service</a>
                    <a href="#" style="color:var(--text-dim);font-size:.82rem">Cookie Policy</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<a href="#home" class="back-to-top" id="backToTop"><i class="bi bi-arrow-up"></i></a>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
AOS.init({duration:800,once:true,offset:100});

// Mouse glow
const glow = document.getElementById('mouseGlow');
document.addEventListener('mousemove',function(e){glow.style.left=e.pageX+'px';glow.style.top=e.pageY+'px'});
document.addEventListener('mouseleave',function(){glow.style.opacity='0'});
document.addEventListener('mouseenter',function(){glow.style.opacity='1'});

// Particles
const canvas = document.getElementById('particles-canvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
const particles = [];
for(let i=0;i<80;i++){particles.push({x:Math.random()*canvas.width,y:Math.random()*canvas.height,r:Math.random()*2+1,dx:(Math.random()-.5)*.5,dy:(Math.random()-.5)*.5,o:Math.random()*.5+.1})}
function drawParticles(){ctx.clearRect(0,0,canvas.width,canvas.height);particles.forEach((p,i)=>{p.x+=p.dx;p.y+=p.dy;if(p.x<0||p.x>canvas.width)p.dx*=-1;if(p.y<0||p.y>canvas.height)p.dy*=-1;ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);ctx.fillStyle=`rgba(212,163,115,${p.o})`;ctx.fill();for(let j=i+1;j<particles.length;j++){const dx=p.x-particles[j].x,dy=p.y-particles[j].y,dist=Math.sqrt(dx*dx+dy*dy);if(dist<150){ctx.beginPath();ctx.moveTo(p.x,p.y);ctx.lineTo(particles[j].x,particles[j].y);ctx.strokeStyle=`rgba(212,163,115,${.06*(1-dist/150)})`;ctx.stroke()}}});requestAnimationFrame(drawParticles)}
drawParticles();
window.addEventListener('resize',function(){canvas.width=window.innerWidth;canvas.height=window.innerHeight});

// Navbar scroll
$(window).scroll(function(){$(window).scrollTop()>50?$('.navbar-landing').addClass('scrolled'):$('.navbar-landing').removeClass('scrolled')});

// Smooth scroll
$('a[href*="#"]').on('click',function(e){if(this.hash!==''){e.preventDefault();$('html,body').animate({scrollTop:$(this.hash).offset().top-80},800)}});

// Back to top
$(window).scroll(function(){$(this).scrollTop()>400?$('#backToTop').addClass('visible'):$('#backToTop').removeClass('visible')});

// Animated counters
function animateCounters(){const c=$('.counter');c.each(function(){const $t=$(this),target=parseInt($t.data('target')),inc=Math.ceil(target/40);let count=0;function up(){count+=inc;if(count>=target){$t.text(target)}else{$t.text(count);requestAnimationFrame(up)}}up()})}
const obs=new IntersectionObserver((entries)=>{entries.forEach(e=>{if(e.isIntersecting){animateCounters();obs.disconnect()}})},{threshold:.3});
$('.counter').length&&obs.observe(document.getElementById('stats'));

// Swiper
new Swiper('.dashboardSwiper',{slidesPerView:1,spaceBetween:30,centeredSlides:!0,loop:!0,autoplay:{delay:4000,disableOnInteraction:!1},pagination:{el:'.dashboardSwiper .swiper-pagination',clickable:!0},breakpoints:{768:{slidesPerView:1.2},992:{slidesPerView:1.3}}});
new Swiper('.testimonialSwiper',{slidesPerView:1,spaceBetween:20,loop:!0,autoplay:{delay:5000,disableOnInteraction:!1},pagination:{el:'.testimonialSwiper .swiper-pagination',clickable:!0},breakpoints:{768:{slidesPerView:2},992:{slidesPerView:3}}});
</script>
</body>
</html>
