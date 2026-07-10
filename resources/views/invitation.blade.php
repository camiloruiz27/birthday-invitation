<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Una invitación especial de cumpleaños creada con amor para Isabella Figueroa.">
    <meta name="theme-color" content="#1a0a2e">
    <title>Para Isabella 🎂</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ===== RESET & BASE ===== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gold: #f5c842;
            --gold-light: #ffe880;
            --gold-dark: #c9a227;
            --purple-deep: #1a0a2e;
            --purple-mid: #2d1154;
            --purple-light: #4a1a7a;
            --pink-hot: #ff4da6;
            --pink-light: #ff80c5;
            --teal: #00e5cc;
            --white: #ffffff;
            --text-light: rgba(255,255,255,0.85);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--purple-deep);
            color: var(--white);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ===== FLOATING PARTICLES ===== */
        #particles-canvas {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        /* ===== MUSIC PLAYER ===== */
        #music-btn {
            position: fixed;
            top: 20px; right: 20px;
            z-index: 1000;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 50%;
            width: 52px; height: 52px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            font-size: 22px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        #music-btn:hover { transform: scale(1.08); background: rgba(255,255,255,0.14); }
        #music-btn.playing { border-color: rgba(245,200,66,0.4); }

        /* ===== SECTIONS BASE ===== */
        section { position: relative; z-index: 1; }

        /* ===== HERO SECTION ===== */
        #hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px 20px;
            background: radial-gradient(ellipse at 50% 0%, rgba(74,26,122,0.8) 0%, transparent 70%),
                        radial-gradient(ellipse at 100% 100%, rgba(255,77,166,0.15) 0%, transparent 50%),
                        linear-gradient(180deg, #1a0a2e 0%, #0d0520 100%);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(245,200,66,0.15);
            border: 1px solid rgba(245,200,66,0.4);
            border-radius: 50px;
            padding: 8px 22px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 10vw, 7rem);
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--white) 0%, var(--gold-light) 50%, var(--pink-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-family: 'Dancing Script', cursive;
            font-size: clamp(1.4rem, 4vw, 2.2rem);
            color: var(--pink-light);
            margin-bottom: 16px;
        }

        .hero-desc {
            font-size: 1rem;
            color: var(--text-light);
            max-width: 500px;
            line-height: 1.8;
            margin-bottom: 50px;
        }

        .hero-hearts {
            display: flex;
            gap: 12px;
            font-size: 2rem;
            justify-content: center;
            margin-bottom: 40px;
        }
        .hero-hearts span { animation: float-heart 3s ease-in-out infinite; }
        .hero-hearts span:nth-child(2) { animation-delay: 0.5s; }
        .hero-hearts span:nth-child(3) { animation-delay: 1s; }
        @keyframes float-heart {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        .scroll-hint {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
        }
        .scroll-arrow {
            width: 30px; height: 50px;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 15px;
            position: relative;
        }
        .scroll-arrow::after {
            content: '';
            position: absolute;
            width: 6px; height: 6px;
            background: var(--gold);
            border-radius: 50%;
            left: 50%; top: 8px;
            transform: translateX(-50%);
            animation: scroll-dot 1.5s ease-in-out infinite;
        }
        @keyframes scroll-dot {
            0% { top: 8px; opacity: 1; }
            100% { top: 30px; opacity: 0; }
        }

        /* ===== GALLERY SECTION ===== */
        #gallery {
            padding: 80px 20px;
            background: linear-gradient(180deg, #0d0520 0%, #1a0a2e 50%, #0d0520 100%);
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 14px;
            display: block;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.2rem, 6vw, 4rem);
            font-weight: 700;
            line-height: 1.15;
            background: linear-gradient(135deg, #fff 0%, var(--gold-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gallery-subtitle {
            font-family: 'Dancing Script', cursive;
            font-size: 1.4rem;
            color: var(--pink-light);
            margin-top: 10px;
        }

        /* Swiper gallery */
        .gallery-swiper {
            width: 100%;
            padding: 20px 0 60px !important;
        }

        .gallery-swiper .swiper-slide {
            width: 300px;
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            transition: transform 0.4s ease;
        }

        .gallery-swiper .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-swiper .swiper-slide-active {
            transform: scale(1.04);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            outline: 1px solid rgba(245,200,66,0.35);
        }

        .gallery-swiper .swiper-pagination-bullet {
            background: rgba(255,255,255,0.3) !important;
            width: 8px !important;
            height: 8px !important;
        }
        .gallery-swiper .swiper-pagination-bullet-active {
            background: var(--gold) !important;
            width: 24px !important;
            border-radius: 4px !important;
        }
        .gallery-swiper .swiper-button-next,
        .gallery-swiper .swiper-button-prev {
            color: var(--gold) !important;
        }
        .gallery-swiper .swiper-button-next::after,
        .gallery-swiper .swiper-button-prev::after {
            font-size: 20px !important;
        }

        /* ===== GAME SECTION ===== */
        #game {
            padding: 80px 20px;
            background: radial-gradient(ellipse at 50% 50%, rgba(74,26,122,0.6) 0%, transparent 70%),
                        linear-gradient(180deg, #0d0520 0%, #1a0a2e 50%, #0d0520 100%);
        }

        .game-container {
            max-width: 720px;
            margin: 0 auto;
        }

        /* Memory Game */
        .memory-intro {
            text-align: center;
            margin-bottom: 40px;
        }

        .memory-intro p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-top: 16px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .game-stats {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin-bottom: 32px;
        }

        .stat-box {
            text-align: center;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 16px 28px;
            backdrop-filter: blur(10px);
        }

        .stat-label {
            font-size: 10px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            margin-bottom: 4px;
        }

        .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--gold);
        }

        .memory-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin: 0 auto;
            max-width: 560px;
        }

        @media (max-width: 480px) {
            .memory-grid { grid-template-columns: repeat(3, 1fr); gap: 8px; }
        }

        .memory-card {
            aspect-ratio: 3/4;
            border-radius: 14px;
            cursor: pointer;
            perspective: 600px;
            position: relative;
        }

        .memory-card-inner {
            width: 100%; height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 14px;
        }

        .memory-card.flipped .memory-card-inner,
        .memory-card.matched .memory-card-inner {
            transform: rotateY(180deg);
        }

        .card-face {
            position: absolute;
            width: 100%; height: 100%;
            border-radius: 14px;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            overflow: hidden;
        }

        .card-back {
            background: linear-gradient(135deg, var(--purple-mid) 0%, var(--purple-light) 100%);
            border: 1px solid rgba(245,200,66,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(1.5rem, 4vw, 2rem);
        }

        .card-back .card-pattern {
            font-size: clamp(1.2rem, 3vw, 1.8rem);
            opacity: 0.6;
        }

        .card-front {
            transform: rotateY(180deg);
            border: 2px solid transparent;
        }

        .card-front img {
            width: 100%; height: 100%;
            object-fit: cover;
        }

        .memory-card.matched .card-front {
            border-color: rgba(245,200,66,0.6);
            box-shadow: none;
        }

        .memory-card.wrong .memory-card-inner {
            animation: wrong-shake 0.4s ease;
        }
        @keyframes wrong-shake {
            0%, 100% { transform: rotateY(180deg) translateX(0); }
            25% { transform: rotateY(180deg) translateX(-5px); }
            75% { transform: rotateY(180deg) translateX(5px); }
        }

        /* ===== REVEAL SECTION ===== */
        #reveal {
            display: none;
            padding: 80px 20px;
            text-align: center;
            background: radial-gradient(ellipse at 50% 0%, rgba(245,200,66,0.1) 0%, transparent 60%),
                        linear-gradient(180deg, #0d0520 0%, #1a0a2e 100%);
        }

        #reveal.visible { display: block; }

        .reveal-card {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(245,200,66,0.3);
            border-radius: 32px;
            padding: 60px 40px;
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
        }

        .reveal-card::before { display: none; }

        .reveal-time {
            font-family: 'Playfair Display', serif;
            font-size: clamp(4rem, 15vw, 8rem);
            font-weight: 900;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 50%, #fff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .reveal-time-label {
            font-size: 0.85rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .reveal-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .reveal-detail-item {
            display: flex;
            align-items: center;
            gap: 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 18px 24px;
            text-align: left;
        }

        .detail-icon {
            font-size: 2rem;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(245,200,66,0.1);
            border-radius: 12px;
            flex-shrink: 0;
        }

        .detail-content { flex: 1; }
        .detail-title {
            font-size: 10px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 4px;
        }
        .detail-text {
            font-size: 1rem;
            font-weight: 500;
            color: var(--white);
        }

        /* ===== MESSAGE SECTION ===== */
        #message-section {
            padding: 80px 20px 100px;
            background: linear-gradient(180deg, #0d0520 0%, #080314 100%);
        }

        .message-card {
            max-width: 680px;
            margin: 0 auto;
            position: relative;
        }

        .message-envelope {
            background: linear-gradient(145deg, rgba(45,17,84,0.9) 0%, rgba(26,10,46,0.95) 100%);
            border: 1px solid rgba(245,200,66,0.3);
            border-radius: 32px;
            padding: 60px 50px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(20px);
        }

        .message-envelope::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f5c842' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .message-quote {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.6rem, 4vw, 2.4rem);
            font-style: italic;
            line-height: 1.5;
            color: var(--white);
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }

        .message-quote::before {
            content: '"';
            font-size: 6rem;
            color: var(--gold);
            opacity: 0.3;
            position: absolute;
            top: -30px;
            left: -20px;
            font-family: 'Playfair Display', serif;
            line-height: 1;
        }

        .message-body {
            font-size: 1rem;
            line-height: 1.9;
            color: var(--text-light);
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .message-body p { margin-bottom: 16px; }
        .message-body strong { color: var(--gold); }

        .message-signature {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 1;
            padding-top: 32px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .signature-heart {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, var(--pink-hot), var(--pink-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            animation: heartbeat 1.5s ease-in-out infinite;
            flex-shrink: 0;
        }
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.15); }
            45% { transform: scale(1); }
            65% { transform: scale(1.1); }
        }

        .signature-text { flex: 1; }
        .signature-name {
            font-family: 'Dancing Script', cursive;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .signature-sub {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
            letter-spacing: 0.1em;
        }

        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--pink-hot);
            color: white;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            padding: 18px 40px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            margin-top: 40px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            text-decoration: none;
            position: relative;
            z-index: 1;
            letter-spacing: 0.03em;
        }
        .cta-btn:hover {
            transform: translateY(-2px);
            background: #e63d94;
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
        }
        .cta-btn:active { transform: translateY(0); }

        /* ===== YEAR COUNTER SECTION ===== */
        #years-section {
            padding: 60px 20px;
            background: linear-gradient(180deg, #1a0a2e 0%, #0d0520 100%);
        }

        .years-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 20px;
            max-width: 700px;
            margin: 0 auto 50px;
        }

        .year-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(245,200,66,0.2);
            border-radius: 20px;
            padding: 30px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: default;
        }
        .year-card:hover {
            background: rgba(245,200,66,0.06);
            border-color: rgba(245,200,66,0.35);
            transform: translateY(-3px);
        }

        .year-number {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 8px;
        }

        .year-label {
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
        }

        /* ===== WINNER BADGE ===== */
        .winner-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(245,200,66,0.12);
            border: 1px solid rgba(245,200,66,0.35);
            color: var(--gold);
            font-weight: 600;
            font-size: 0.85rem;
            padding: 12px 28px;
            border-radius: 50px;
            letter-spacing: 0.05em;
            margin-top: 20px;
            box-shadow: none;
        }

        /* ===== FOOTER ===== */
        footer {
            padding: 40px 20px;
            text-align: center;
            background: #080314;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        footer p {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.25);
            letter-spacing: 0.1em;
        }

        /* ===== GAME WIN MODAL ===== */
        #win-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        #win-modal.show { display: flex; }
        #win-modal::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(8,3,20,0.8);
            backdrop-filter: blur(8px);
        }

        .win-modal-card {
            position: relative;
            z-index: 1;
            background: linear-gradient(145deg, rgba(45,17,84,0.98), rgba(26,10,46,0.99));
            border: 1px solid rgba(245,200,66,0.5);
            border-radius: 32px;
            padding: 60px 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: modal-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 30px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(245,200,66,0.2);
        }
        @keyframes modal-in {
            from { opacity: 0; transform: scale(0.7) translateY(40px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .win-modal-emoji { font-size: 4rem; margin-bottom: 20px; display: block; }
        .win-modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
        }
        .win-modal-text {
            color: var(--text-light);
            line-height: 1.7;
            margin-bottom: 32px;
            font-size: 0.95rem;
        }

        .win-modal-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--gold);
            color: var(--purple-deep);
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            padding: 16px 36px;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
        }
        .win-modal-btn:hover { transform: translateY(-2px); background: var(--gold-light); box-shadow: 0 6px 18px rgba(0,0,0,0.3); }

        /* ===== ANIMATIONS ===== */
        .fade-in-up {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger delays */
        .delay-1 { transition-delay: 0.1s; }
        .delay-2 { transition-delay: 0.2s; }
        .delay-3 { transition-delay: 0.3s; }
        .delay-4 { transition-delay: 0.4s; }
        .delay-5 { transition-delay: 0.5s; }

            .game-stats { gap: 16px; }
            .stat-box { padding: 12px 18px; }
        }

        /* ===== MOBILE RESPONSIVE ===== */

        /* Tablet (≤768px) */
        @media (max-width: 768px) {
            #hero { padding: 60px 24px 40px; }

            .hero-badge {
                font-size: 10px;
                padding: 7px 16px;
                letter-spacing: 0.1em;
                text-align: center;
            }

            .hero-desc { font-size: 0.9rem; margin-bottom: 36px; }

            #gallery { padding: 60px 0; }
            .section-header { padding: 0 20px; margin-bottom: 40px; }
            .gallery-subtitle { font-size: 1.2rem; }

            .gallery-swiper .swiper-slide {
                width: 240px;
                height: 320px;
            }

            #game { padding: 60px 16px; }
            #reveal { padding: 60px 16px; }
            #message-section { padding: 60px 16px 80px; }
            #years-section { padding: 50px 16px; }

            .years-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px;
            }

            .year-card { padding: 24px 16px; }
            .year-number { font-size: 2.4rem; }

            .winner-badge {
                font-size: 0.8rem;
                padding: 10px 18px;
                text-align: center;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 6px;
                max-width: 320px;
            }

            .reveal-card { padding: 40px 24px; border-radius: 24px; }
            .reveal-time-label { letter-spacing: 0.15em; }

            .reveal-detail-item { gap: 12px; padding: 14px 18px; }
            .detail-icon { width: 42px; height: 42px; font-size: 1.5rem; }
            .detail-text { font-size: 0.9rem; }

            .message-envelope { padding: 44px 28px; border-radius: 24px; }
            .message-quote::before { font-size: 4rem; top: -20px; left: -10px; }

            .win-modal-card { padding: 44px 28px; border-radius: 24px; }
            .win-modal-title { font-size: 1.8rem; }
        }

        /* Phone (≤480px) */
        @media (max-width: 480px) {
            #hero { padding: 80px 20px 40px; min-height: 100svh; }

            .hero-badge {
                font-size: 9px;
                padding: 6px 14px;
                margin-bottom: 20px;
                max-width: 280px;
                white-space: normal;
                text-align: center;
                line-height: 1.5;
            }

            .hero-hearts { font-size: 1.6rem; margin-bottom: 24px; gap: 10px; }
            .hero-desc { font-size: 0.88rem; margin-bottom: 30px; }
            .scroll-hint { margin-top: 30px; }

            .section-title { line-height: 1.2; }
            .gallery-subtitle { font-size: 1.1rem; }

            .gallery-swiper .swiper-slide {
                width: 200px;
                height: 266px;
                border-radius: 16px;
            }
            /* Hide nav arrows on small phones */
            .gallery-swiper .swiper-button-next,
            .gallery-swiper .swiper-button-prev { display: none; }

            .memory-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
                max-width: 100%;
            }

            .game-stats { gap: 10px; }
            .stat-box { padding: 10px 14px; border-radius: 12px; }
            .stat-value { font-size: 1.5rem; }
            .stat-label { font-size: 9px; }

            .years-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .year-card { padding: 20px 12px; border-radius: 16px; }
            .year-number { font-size: 2rem; }
            .year-label { font-size: 0.65rem; }

            .winner-badge {
                font-size: 0.75rem;
                padding: 10px 16px;
                max-width: 290px;
                line-height: 1.6;
            }

            .reveal-card { padding: 32px 18px; border-radius: 20px; }
            .reveal-time-label { font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 28px; }
            .reveal-details { gap: 12px; margin-bottom: 28px; }
            .reveal-detail-item { gap: 12px; padding: 12px 14px; border-radius: 14px; }
            .detail-icon { width: 38px; height: 38px; font-size: 1.2rem; border-radius: 10px; }
            .detail-text { font-size: 0.85rem; }

            .message-envelope { padding: 36px 20px; border-radius: 20px; }
            .message-quote { font-size: 1.4rem; margin-bottom: 24px; }
            .message-quote::before { font-size: 3rem; top: -14px; left: -6px; }
            .message-body { font-size: 0.9rem; line-height: 1.8; }
            .signature-name { font-size: 1.6rem; }
            .cta-btn { font-size: 0.9rem; padding: 16px 28px; }

            .win-modal-card { padding: 36px 22px; border-radius: 20px; }
            .win-modal-emoji { font-size: 3rem; }
            .win-modal-title { font-size: 1.6rem; }
            .win-modal-text { font-size: 0.88rem; }
            .win-modal-btn { font-size: 0.9rem; padding: 14px 28px; }

            footer p { font-size: 0.72rem; letter-spacing: 0.05em; }

            /* Touch improvements */
            .memory-card { cursor: default; }
            .cta-btn, .win-modal-btn { -webkit-tap-highlight-color: transparent; }
        }

        /* Very small phones (≤360px) */
        @media (max-width: 360px) {
            .memory-grid { gap: 6px; }
            .hero-badge { font-size: 8px; }
            .game-stats { flex-wrap: wrap; }
            .stat-box { flex: 1 1 80px; }
            .winner-badge { max-width: 250px; font-size: 0.7rem; }
        }

    </style>
</head>
<body>

<!-- Floating particles canvas -->
<canvas id="particles-canvas"></canvas>

<!-- Music button -->
<button id="music-btn" title="Reproducir nuestra canción" aria-label="Reproducir música">🎵</button>
<audio id="bg-music" loop preload="none">
    <source src="{{ asset('audio/cancion.mp3') }}" type="audio/mpeg">
    <!-- Nota: añade el archivo de música si lo tienes -->
</audio>

<!-- ===== HERO ===== -->
<section id="hero">
    <div class="hero-badge" data-hero-badge>
        <span>✨</span>
        Domingo 12 de Julio &nbsp;•&nbsp; 2026
    </div>

    <div class="hero-hearts" data-hero-hearts>
        <span>🌸</span>
        <span>💛</span>
        <span>🌸</span>
    </div>

    <h1 class="hero-title" data-hero-title>
        Isabella,<br>esto es para ti
    </h1>

    <p class="hero-subtitle" data-hero-subtitle>
        Tengo algo muy especial planeado...
    </p>

    <p class="hero-desc" data-hero-desc>
        Antes de revelarte todo, quiero que primero
        pases por un pequeño reto. ¿Estás lista? 💛
    </p>

    <div class="scroll-hint" data-scroll-hint>
        <div class="scroll-arrow"></div>
        <span>Desplázate hacia abajo</span>
    </div>
</section>

<!-- ===== AÑOS JUNTOS ===== -->
<section id="years-section">
    <div class="section-header fade-in-up">
        <span class="section-label">Nuestros momentos</span>
        <h2 class="section-title">4 años a tu lado</h2>
        <p class="gallery-subtitle">y cada uno mejor que el anterior</p>
    </div>

    <div class="years-grid">
        <div class="year-card fade-in-up delay-1">
            <div class="year-number">4</div>
            <div class="year-label">Años juntos</div>
        </div>
        <div class="year-card fade-in-up delay-2">
            <div class="year-number">4°</div>
            <div class="year-label">Cumpleaños a tu lado</div>
        </div>
        <div class="year-card fade-in-up delay-3">
            <div class="year-number">∞</div>
            <div class="year-label">Momentos bonitos</div>
        </div>
        <div class="year-card fade-in-up delay-4">
            <div class="year-number">1°</div>
            <div class="year-label">En tu corazón 🏆</div>
        </div>
    </div>

    <div style="text-align:center;" class="fade-in-up delay-5">
        <div class="winner-badge">
            🏆 &nbsp; Camilo: la persona que más tiempo ha pasado contigo — ¡y ya gané!
        </div>
    </div>
</section>

<!-- ===== GALLERY ===== -->
<section id="gallery">
    <div class="section-header fade-in-up">
        <span class="section-label">Nuestra historia</span>
        <h2 class="section-title">Momentos que me hacen<br>inmensamente feliz</h2>
        <p class="gallery-subtitle">🎵 Algo que se quede — Grupo Niche</p>
    </div>

    <div class="swiper gallery-swiper">
        <div class="swiper-wrapper" id="gallery-wrapper">
            <!-- Cards inserted by JS -->
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</section>

<!-- ===== GAME ===== -->
<section id="game">
    <div class="game-container">
        <div class="section-header fade-in-up">
            <span class="section-label">¡Tu reto!</span>
            <h2 class="section-title">Juego de memoria</h2>
            <p class="gallery-subtitle">Encuentra todos los pares para revelar el secreto 🔐</p>
        </div>

        <div class="memory-intro fade-in-up">
            <p>
                Debajo están nuestras fotos escondidas. Voltea las cartas de dos en dos
                y encuentra cada par. Cuando completes el juego, te revelaré todo el plan
                de tu noche especial. ¡Vamos! ✨
            </p>
        </div>

        <div class="game-stats fade-in-up">
            <div class="stat-box">
                <div class="stat-label">Intentos</div>
                <div class="stat-value" id="attempts-count">0</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Parejas</div>
                <div class="stat-value" id="pairs-count">0 / 6</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">⏱ Tiempo</div>
                <div class="stat-value" id="timer-display">0:00</div>
            </div>
        </div>

        <div class="memory-grid" id="memory-grid">
            <!-- Cards inserted by JS -->
        </div>

        <div style="text-align:center; margin-top: 30px;" class="fade-in-up">
            <button onclick="resetGame()" style="
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.15);
                color: rgba(255,255,255,0.6);
                padding: 10px 28px;
                border-radius: 50px;
                font-family: 'Poppins', sans-serif;
                font-size: 0.85rem;
                cursor: pointer;
                transition: all 0.3s;
            " onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                🔄 Reiniciar juego
            </button>
        </div>
    </div>
</section>

<!-- ===== REVEAL (hidden until game complete) ===== -->
<section id="reveal">
    <div class="section-header fade-in-up">
        <span class="section-label">🎉 ¡Lo lograste!</span>
        <h2 class="section-title">El plan de tu noche</h2>
    </div>

    <div class="reveal-card fade-in-up">
        <div class="reveal-time">7:00</div>
        <div class="reveal-time-label">PM &nbsp;•&nbsp; Este domingo</div>

        <div class="reveal-details">
            <div class="reveal-detail-item">
                <div class="detail-icon">🚗</div>
                <div class="detail-content">
                    <div class="detail-title">¿Cómo llego?</div>
                    <div class="detail-text">Yo te recojo a las 7:00 PM</div>
                </div>
            </div>
            <div class="reveal-detail-item">
                <div class="detail-icon">🎂</div>
                <div class="detail-content">
                    <div class="detail-title">¿Qué haremos?</div>
                    <div class="detail-text">Una cena de cumpleaños solo para nosotros</div>
                </div>
            </div>
            <div class="reveal-detail-item">
                <div class="detail-icon">📅</div>
                <div class="detail-content">
                    <div class="detail-title">¿Cuándo?</div>
                    <div class="detail-text">Domingo 12 de Julio, 2026</div>
                </div>
            </div>
            <div class="reveal-detail-item">
                <div class="detail-icon">🎁</div>
                <div class="detail-content">
                    <div class="detail-title">¿Qué llevar?</div>
                    <div class="detail-text">Solo tu sonrisa y muchas ganas de pasarla bien</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== MESSAGE ===== -->
<section id="message-section">
    <div class="section-header fade-in-up">
        <span class="section-label">Con todo mi amor</span>
        <h2 class="section-title">Para ti, mi hermosa</h2>
    </div>

    <div class="message-card fade-in-up">
        <div class="message-envelope">
            <div class="message-quote">
                Este año el regalo más grande eres tú.
            </div>

            <div class="message-body">
                <p>
                    Isabella, hoy celebramos <strong>tu cumpleaños</strong> — y ya van <strong>4 años</strong>
                    en los que he tenido el privilegio de estar a tu lado. Cada año contigo
                    ha sido mejor que el anterior, y este no será la excepción.
                </p>
                <p>
                    Me siento <strong>inmensamente feliz</strong> de poder celebrarlo contigo.
                    Ahora ya es oficial: soy la persona que más tiempo ha pasado contigo
                    en este cumpleaños, y eso me hace el ganador más afortunado del mundo. 🏆
                </p>
                <p>
                    Esta noche está preparada especialmente para ti. Espero que sea
                    una noche que <strong>siempre recuerdes</strong> con una sonrisa.
                </p>
            </div>

            <div class="message-signature">
                <div class="signature-heart">❤️</div>
                <div class="signature-text">
                    <div class="signature-name">Camilo</div>
                    <div class="signature-sub">Tu persona favorita 😄</div>
                </div>
            </div>

            <div style="text-align:center; position:relative; z-index:1;">
                <button class="cta-btn" onclick="launchFinalConfetti()">
                    🎊 &nbsp; ¡Feliz Cumpleaños, Isabella!
                </button>
            </div>
        </div>
    </div>
</section>

<footer>
    <p>Hecho con 💛 por Camilo — para Isabella Figueroa &nbsp;•&nbsp; 12 Julio 2026</p>
</footer>

<!-- Win Modal -->
<div id="win-modal" role="dialog" aria-modal="true" aria-labelledby="win-title">
    <div class="win-modal-card">
        <span class="win-modal-emoji">🎉</span>
        <h2 class="win-modal-title" id="win-title">¡Lo lograste!</h2>
        <p class="win-modal-text">
            ¡Eres increíble, Isa! Completaste el juego de memoria.
            Ahora te revelo el plan de tu noche especial. ✨
        </p>
        <button class="win-modal-btn" onclick="closeWinModal()">
            🔓 &nbsp; Revelar el plan
        </button>
    </div>
</div>

<script>
// ===== PARTICLES =====
(function() {
    const canvas = document.getElementById('particles-canvas');
    const ctx = canvas.getContext('2d');
    let particles = [];
    let W, H;

    function resize() {
        W = canvas.width = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    const EMOJIS = ['✨', '💛', '🌸', '⭐', '💫', '🎊'];

    for (let i = 0; i < 30; i++) {
        particles.push({
            x: Math.random() * window.innerWidth,
            y: Math.random() * window.innerHeight,
            size: Math.random() * 14 + 6,
            speedX: (Math.random() - 0.5) * 0.4,
            speedY: Math.random() * -0.5 - 0.2,
            opacity: Math.random() * 0.5 + 0.1,
            emoji: EMOJIS[Math.floor(Math.random() * EMOJIS.length)]
        });
    }

    function animate() {
        ctx.clearRect(0, 0, W, H);
        particles.forEach(p => {
            ctx.globalAlpha = p.opacity;
            ctx.font = `${p.size}px serif`;
            ctx.fillText(p.emoji, p.x, p.y);
            p.x += p.speedX;
            p.y += p.speedY;
            if (p.y < -30) { p.y = H + 10; p.x = Math.random() * W; }
            if (p.x < -30) p.x = W + 10;
            if (p.x > W + 30) p.x = -10;
        });
        ctx.globalAlpha = 1;
        requestAnimationFrame(animate);
    }
    animate();
})();

// ===== MUSIC =====
const musicBtn = document.getElementById('music-btn');
const audio = document.getElementById('bg-music');
let playing = false;

musicBtn.addEventListener('click', () => {
    if (playing) {
        audio.pause();
        musicBtn.textContent = '🎵';
        musicBtn.classList.remove('playing');
        playing = false;
    } else {
        audio.play().catch(() => {});
        musicBtn.textContent = '🔇';
        musicBtn.classList.add('playing');
        playing = true;
    }
});

// ===== GALLERY SWIPER =====
const IMAGE_BASE_URL = @json(asset('images'));

const GALLERY_IMAGES = [
    { n: '1', ext: 'jfif' },
    { n: '2', ext: 'PNG' },
    { n: '3', ext: 'jpg' },
    { n: '5', ext: 'jpeg' },
    { n: '6', ext: 'jpeg' },
    { n: '7', ext: 'jpeg' },
    { n: '8', ext: 'jpeg' },
    { n: '9', ext: 'jpeg' },
    { n: '10', ext: 'jpeg' },
    { n: '11', ext: 'jpeg' },
    { n: '12', ext: 'jpeg' },
    { n: '13', ext: 'jpeg' },
    { n: '14', ext: 'jpeg' },
    { n: '15', ext: 'jpeg' },
    { n: '16', ext: 'jpeg' },
];

const galleryWrapper = document.getElementById('gallery-wrapper');
GALLERY_IMAGES.forEach(img => {
    const slide = document.createElement('div');
    slide.className = 'swiper-slide';
    slide.innerHTML = `<img src="${IMAGE_BASE_URL}/${img.n}.${img.ext}" alt="Foto ${img.n} de Isabella y Camilo" loading="lazy">`;
    galleryWrapper.appendChild(slide);
});

// Init gallery swiper after DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new window.Swiper('.gallery-swiper', {
        modules: [window.SwiperModules.Navigation, window.SwiperModules.Pagination, window.SwiperModules.EffectCoverflow],
        effect: 'coverflow',
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        coverflowEffect: {
            rotate: 35,
            stretch: 0,
            depth: 100,
            modifier: 1,
            slideShadows: true,
        },
        loop: true,
        autoplay: { delay: 3500, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    });
});

// ===== MEMORY GAME =====
// Use 6 pairs from the 15 images
const GAME_IMAGES = [
    { n: '5', ext: 'jpeg' },
    { n: '6', ext: 'jpeg' },
    { n: '7', ext: 'jpeg' },
    { n: '8', ext: 'jpeg' },
    { n: '9', ext: 'jpeg' },
    { n: '10', ext: 'jpeg' },
];

let gameCards = [];
let flippedCards = [];
let matchedPairs = 0;
let attempts = 0;
let canFlip = true;
let gameTimer = null;
let gameSeconds = 0;
let gameStarted = false;

function shuffle(arr) {
    const a = [...arr];
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

function startTimer() {
    if (gameTimer) return;
    gameTimer = setInterval(() => {
        gameSeconds++;
        const m = Math.floor(gameSeconds / 60);
        const s = gameSeconds % 60;
        document.getElementById('timer-display').textContent = `${m}:${s.toString().padStart(2, '0')}`;
    }, 1000);
}

function resetGame() {
    if (gameTimer) { clearInterval(gameTimer); gameTimer = null; }
    gameSeconds = 0;
    gameStarted = false;
    matchedPairs = 0;
    attempts = 0;
    flippedCards = [];
    canFlip = true;
    document.getElementById('attempts-count').textContent = '0';
    document.getElementById('pairs-count').textContent = '0 / 6';
    document.getElementById('timer-display').textContent = '0:00';
    buildGrid();
}

function buildGrid() {
    const grid = document.getElementById('memory-grid');
    grid.innerHTML = '';

    const pairs = shuffle([...GAME_IMAGES, ...GAME_IMAGES]);
    gameCards = [];

    pairs.forEach((img, i) => {
        const card = document.createElement('div');
        card.className = 'memory-card';
        card.dataset.img = img.n;
        card.dataset.index = i;
        card.innerHTML = `
            <div class="memory-card-inner">
                <div class="card-face card-back">
                    <span class="card-pattern">💛</span>
                </div>
                <div class="card-face card-front">
                    <img src="${IMAGE_BASE_URL}/${img.n}.${img.ext}" alt="Foto secreta" loading="lazy">
                </div>
            </div>
        `;
        card.addEventListener('click', () => flipCard(card));
        grid.appendChild(card);
        gameCards.push(card);
    });
}

function flipCard(card) {
    if (!canFlip) return;
    if (card.classList.contains('flipped') || card.classList.contains('matched')) return;
    if (flippedCards.length >= 2) return;

    if (!gameStarted) { gameStarted = true; startTimer(); }

    card.classList.add('flipped');
    flippedCards.push(card);

    if (flippedCards.length === 2) {
        attempts++;
        document.getElementById('attempts-count').textContent = attempts;
        canFlip = false;
        checkMatch();
    }
}

function checkMatch() {
    const [a, b] = flippedCards;
    const isMatch = a.dataset.img === b.dataset.img;

    setTimeout(() => {
        if (isMatch) {
            a.classList.add('matched');
            b.classList.add('matched');
            a.classList.remove('flipped');
            b.classList.remove('flipped');
            matchedPairs++;
            document.getElementById('pairs-count').textContent = `${matchedPairs} / 6`;

            // Small confetti burst on match
            window.confetti({
                particleCount: 25,
                spread: 50,
                origin: { y: 0.6 },
                colors: ['#f5c842', '#ff4da6', '#ffffff']
            });

            if (matchedPairs === 6) {
                setTimeout(onGameWin, 600);
            }
        } else {
            a.classList.add('wrong');
            b.classList.add('wrong');
            setTimeout(() => {
                a.classList.remove('flipped', 'wrong');
                b.classList.remove('flipped', 'wrong');
            }, 700);
        }
        flippedCards = [];
        canFlip = true;
    }, 800);
}

function onGameWin() {
    clearInterval(gameTimer);

    // Big confetti
    window.confetti({ particleCount: 200, spread: 120, origin: { y: 0.5 }, colors: ['#f5c842', '#ff4da6', '#ffffff', '#00e5cc'] });
    setTimeout(() => {
        window.confetti({ particleCount: 150, spread: 100, angle: 60, origin: { x: 0, y: 0.6 } });
        window.confetti({ particleCount: 150, spread: 100, angle: 120, origin: { x: 1, y: 0.6 } });
    }, 500);

    // Show win modal
    document.getElementById('win-modal').classList.add('show');
}

function closeWinModal() {
    document.getElementById('win-modal').classList.remove('show');

    // Show reveal section
    const reveal = document.getElementById('reveal');
    reveal.classList.add('visible');
    setTimeout(() => {
        reveal.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, 300);
}

function launchFinalConfetti() {
    const duration = 4000;
    const end = Date.now() + duration;
    const colors = ['#f5c842', '#ff4da6', '#ffffff', '#00e5cc', '#ff80c5'];
    (function frame() {
        window.confetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 }, colors });
        window.confetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 }, colors });
        if (Date.now() < end) requestAnimationFrame(frame);
    })();
}

// ===== SCROLL ANIMATIONS =====
const observer = new IntersectionObserver((entries) => {
    entries.forEach(el => {
        if (el.isIntersecting) {
            el.target.classList.add('visible');
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));

// ===== INIT =====
buildGrid();
</script>
</body>
</html>
