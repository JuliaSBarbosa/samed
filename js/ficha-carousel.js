/**
 * Carrossel da ficha médica (#fichaCarousel) — perfil, dependente e visualizar paciente.
 * Usa transform na faixa (.carousel-inner) para compatibilidade entre navegadores.
 */
(function () {
    function initFichaCarousel() {
        var root = document.getElementById("fichaCarousel");
        if (!root || root.getAttribute("data-ficha-carousel-init") === "1") {
            return;
        }

        var inner = root.querySelector(".carousel-inner");
        var slides = root.querySelectorAll(".carousel-item");
        var indicators = root.querySelectorAll(".carousel-indicators span");
        var prevBtn = root.querySelector(".carousel-control.prev");
        var nextBtn = root.querySelector(".carousel-control.next");

        if (!inner || !slides.length || !prevBtn || !nextBtn) {
            return;
        }

        root.setAttribute("data-ficha-carousel-init", "1");

        var n = slides.length;
        root.style.setProperty("--ficha-slide-count", String(n));

        var index = 0;
        for (var i = 0; i < slides.length; i++) {
            if (slides[i].classList.contains("active")) {
                index = i;
                break;
            }
        }

        function applySlide() {
            var pct = (100 * index) / n;
            inner.style.transform = "translateX(-" + pct + "%)";

            slides.forEach(function (slide, j) {
                slide.classList.toggle("active", j === index);
            });
            indicators.forEach(function (ind, j) {
                ind.classList.toggle("active", j === index);
            });
        }

        function go(delta) {
            var next = index + delta;
            if (next < 0) {
                next = n - 1;
            }
            if (next >= n) {
                next = 0;
            }
            index = next;
            applySlide();
        }

        prevBtn.type = "button";
        nextBtn.type = "button";

        prevBtn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            go(-1);
        });

        nextBtn.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            go(1);
        });

        indicators.forEach(function (ind, j) {
            ind.addEventListener("click", function (e) {
                e.preventDefault();
                var ds = ind.getAttribute("data-slide");
                index = ds !== null && ds !== "" ? parseInt(ds, 10) : j;
                if (index < 0 || index >= n || isNaN(index)) {
                    index = j;
                }
                applySlide();
            });
        });

        applySlide();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initFichaCarousel);
    } else {
        initFichaCarousel();
    }
})();
