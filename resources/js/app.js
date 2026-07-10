import './bootstrap';

import Alpine from 'alpinejs';
import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import confetti from 'canvas-confetti';
import { createIcons, Heart, Music, MapPin, CalendarDays } from 'lucide';
import Swiper from 'swiper';
import { Navigation, Pagination, EffectCoverflow } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-coverflow';

gsap.registerPlugin(ScrollTrigger);

window.Alpine = Alpine;
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;
window.confetti = confetti;
window.Swiper = Swiper;
window.SwiperModules = {
    Navigation,
    Pagination,
    EffectCoverflow,
};

Alpine.start();

createIcons({
    icons: {
        Heart,
        Music,
        MapPin,
        CalendarDays,
    },
});

const lenis = new Lenis({
    duration: 1.2,
    smoothWheel: true,
});

function smoothScroll(time) {
    lenis.raf(time);
    requestAnimationFrame(smoothScroll);
}

requestAnimationFrame(smoothScroll);

gsap.from('[data-hero-content]', {
    opacity: 0,
    y: 50,
    duration: 1.4,
    ease: 'power3.out',
});