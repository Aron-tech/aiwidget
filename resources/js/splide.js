import Splide from '@splidejs/splide';
import '@splidejs/splide/css';

export default function initSplide() {

    const splideInstances = new Map();

    const initSingleSplide = (el) => {
        if (splideInstances.has(el)) {
            splideInstances.get(el).destroy();
            splideInstances.delete(el);
        }

        const splide = new Splide(el, {
            width: '100%',
            type: 'loop',
            perPage: 1,
            gap: '1rem',
            pagination: false,
            arrows: false,
        }).mount();

        const prevButton = el.querySelector('.widget-style-prev');
        const nextButton = el.querySelector('.widget-style-next');

        prevButton?.addEventListener('click', () => splide.go('-1'));
        nextButton?.addEventListener('click', () => splide.go('+1'));

        splideInstances.set(el, splide);
        return splide;
    };

    function initializeAll() {
        document.querySelectorAll('.splide.widget-styles').forEach(initSingleSplide);
    }

    initializeAll();
}