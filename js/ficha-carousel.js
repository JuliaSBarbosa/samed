/**
 * Carrossel da ficha médica (#fichaCarousel) — perfil, dependente e visualizar paciente.
 * Rolagem horizontal em pixels (scrollTo): evita desalinhamento de translateX(%) vs. largura da faixa.
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

        var index = 0;
        for (var i = 0; i < slides.length; i++) {
            if (slides[i].classList.contains("active")) {
                index = i;
                break;
            }
        }

        function viewportWidth() {
            return inner.clientWidth || root.clientWidth || 0;
        }

        function applySlide() {
            var w = viewportWidth();
            if (w <= 0) {
                return;
            }
            var left = Math.round(index * w);
            inner.scrollTo({ left: left, top: 0, behavior: "auto" });

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
            go(-1);
        });

        nextBtn.addEventListener("click", function (e) {
            e.preventDefault();
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

        var scrollEndTimer = null;
        inner.addEventListener("scroll", function () {
            if (scrollEndTimer) {
                clearTimeout(scrollEndTimer);
            }
            scrollEndTimer = setTimeout(function () {
                scrollEndTimer = null;
                var w = viewportWidth();
                if (w <= 0) {
                    return;
                }
                var snapped = Math.round(inner.scrollLeft / w);
                if (snapped !== index && snapped >= 0 && snapped < n) {
                    index = snapped;
                    slides.forEach(function (slide, j) {
                        slide.classList.toggle("active", j === index);
                    });
                    indicators.forEach(function (ind, j) {
                        ind.classList.toggle("active", j === index);
                    });
                }
            }, 80);
        });

        window.addEventListener("resize", function () {
            applySlide();
        });

        applySlide();
        requestAnimationFrame(function () {
            applySlide();
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initFichaCarousel);
    } else {
        initFichaCarousel();
    }
})();
