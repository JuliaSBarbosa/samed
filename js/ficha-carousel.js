/**
 * Carrossel #fichaCarousel — um slide visível (CSS .carousel-item.active).
 * Delegação em document (capture): funciona mesmo se o alvo do clique for filho do botão
 * ou se querySelector direto nos botões falhar em alguns browsers.
 */
(function () {
    function closest(el, sel) {
        if (!el || typeof el.closest === "function") {
            return el ? el.closest(sel) : null;
        }
        while (el && el.nodeType === 1) {
            if (el.matches && el.matches(sel)) {
                return el;
            }
            el = el.parentElement;
        }
        return null;
    }

    function applySlide(root, index) {
        var slides = root.querySelectorAll(".carousel-item");
        var indicators = root.querySelectorAll(".carousel-indicators span");
        var n = slides.length;
        if (!n) {
            return;
        }
        index = ((index % n) + n) % n;

        var inner = root.querySelector(".carousel-inner");
        if (inner) {
            inner.style.removeProperty("transform");
            inner.style.removeProperty("transition");
            inner.scrollLeft = 0;
            inner.style.setProperty("display", "block", "important");
            inner.style.setProperty("overflow", "hidden", "important");
            inner.style.setProperty("flex-direction", "unset", "important");
            inner.style.setProperty("flex-wrap", "unset", "important");
        }

        slides.forEach(function (slide, j) {
            var on = j === index;
            slide.classList.toggle("active", on);
            slide.style.removeProperty("flex");
            slide.style.removeProperty("flex-grow");
            slide.style.removeProperty("flex-shrink");
            slide.style.removeProperty("flex-basis");
            slide.style.removeProperty("min-width");
            if (on) {
                slide.style.setProperty("display", "flex", "important");
                slide.style.setProperty("flex-direction", "column", "important");
                slide.style.setProperty("align-items", "center", "important");
                slide.style.setProperty("justify-content", "center", "important");
                slide.style.setProperty("width", "100%", "important");
                slide.style.setProperty("box-sizing", "border-box", "important");
            } else {
                slide.style.setProperty("display", "none", "important");
            }
        });
        indicators.forEach(function (ind, j) {
            ind.classList.toggle("active", j === index);
        });
        root.setAttribute("data-ficha-slide-index", String(index));
    }

    function readIndex(root) {
        var slides = root.querySelectorAll(".carousel-item");
        var n = slides.length;
        var fromAttr = parseInt(root.getAttribute("data-ficha-slide-index") || "", 10);
        if (!isNaN(fromAttr) && fromAttr >= 0 && fromAttr < n) {
            return fromAttr;
        }
        for (var i = 0; i < slides.length; i++) {
            if (slides[i].classList.contains("active")) {
                return i;
            }
        }
        return 0;
    }

    function bindDelegateOnce() {
        if (window.__samedFichaCarouselBound) {
            return;
        }
        window.__samedFichaCarouselBound = true;

        document.addEventListener(
            "click",
            function (e) {
                var root = document.getElementById("fichaCarousel");
                if (!root) {
                    return;
                }

                var t = e.target;
                if (t && t.nodeType === 3) {
                    t = t.parentElement;
                }
                if (!t || t.nodeType !== 1) {
                    return;
                }

                var ind = closest(t, ".carousel-indicators span");
                if (ind && root.contains(ind)) {
                    e.preventDefault();
                    var spans = root.querySelectorAll(".carousel-indicators span");
                    var j = -1;
                    for (var k = 0; k < spans.length; k++) {
                        if (spans[k] === ind) {
                            j = k;
                            break;
                        }
                    }
                    if (j < 0) {
                        return;
                    }
                    var ds = ind.getAttribute("data-slide");
                    if (ds !== null && ds !== "") {
                        var parsed = parseInt(ds, 10);
                        if (!isNaN(parsed)) {
                            j = parsed;
                        }
                    }
                    applySlide(root, j);
                    return;
                }

                var btn = closest(t, ".carousel-control.prev, .carousel-control.next");
                if (!btn || !root.contains(btn)) {
                    return;
                }

                e.preventDefault();

                var slides = root.querySelectorAll(".carousel-item");
                if (!slides.length) {
                    return;
                }

                var idx = readIndex(root);
                if (btn.classList.contains("prev")) {
                    applySlide(root, idx - 1);
                } else {
                    applySlide(root, idx + 1);
                }
            },
            true
        );
    }

    function init() {
        bindDelegateOnce();

        var root = document.getElementById("fichaCarousel");
        if (!root) {
            return;
        }

        var inner = root.querySelector(".carousel-inner");
        if (inner) {
            inner.style.transform = "";
            inner.style.transition = "";
            inner.scrollLeft = 0;
        }

        var slides = root.querySelectorAll(".carousel-item");
        if (!slides.length) {
            return;
        }

        var start = 0;
        for (var i = 0; i < slides.length; i++) {
            if (slides[i].classList.contains("active")) {
                start = i;
                break;
            }
        }
        applySlide(root, start);

        var prevBtn = root.querySelector(".carousel-control.prev");
        var nextBtn = root.querySelector(".carousel-control.next");
        if (prevBtn) {
            prevBtn.type = "button";
        }
        if (nextBtn) {
            nextBtn.type = "button";
        }
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
