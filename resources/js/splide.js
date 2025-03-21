import Splide from "@splidejs/splide";
import "@splidejs/splide/css";

export default function initSplide() {

    document.querySelectorAll(".splide.widget-styles").forEach((slider) => {
        const splide = new Splide(slider, {
            width: "100%",
            type: "loop",
            perPage: 1,
            gap: "1rem",
            pagination: false,
            arrows: false,
        }).mount();

        const prevButton = slider.querySelector(".widget-style-prev");
        const nextButton = slider.querySelector(".widget-style-next");

        prevButton.addEventListener("click", () => splide.go("-1"));
        nextButton.addEventListener("click", () => splide.go("+1"));
    });
}