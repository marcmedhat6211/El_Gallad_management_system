$(document).ready(function() {

    const qtyInput = $("input[name=qty]");
    $(".incrementer-style-1 button.plus").click(function() {
        let newVal = increment($("#items_number"));
        $("#items_number").text(newVal);
        qtyInput.attr("value", newVal);
        $(".incrementer-style-1 button.minus").prop("disabled", false);
    });

    $(".incrementer-style-1 button.minus").click(function() {
        let newVal = decrement($("#items_number"));
        if (newVal) {
            $("#items_number").text(newVal);
            qtyInput.attr("value", newVal);
        }
        if (newVal == 1) {
            $(this).prop("disabled", true);
        }
    });

    // PRODUCT DETAILS THUMBS SWIPER
    var productDetailsThumbsSwiper = new Swiper('.product-details-thumbs-swiper', {
        loop: false,
        slidesPerView: 2.2,
        centeredSlides: false,
        allowTouchMove: true,
        watchSlidesVisibility: true,
        watchSlidesProgress: true,
        lazy: {
            preloadImages: false,
            loadPrevNext: true,
            loadPrevNextAmount: 4,
        },
        breakpoints: {
            991.98: {
                slidesPerView: 4.2,
                spaceBetween: 0,
            },
        }
    });

    //PRODUCT DETAILS MAIN SLIDER
    var productDetailsMainSwiper = new Swiper('.product-details-swiper', {
        loop: false,
        slidesPerView: 1,
        centeredSlides: true,
        allowTouchMove: true,
        thumbs: {
            swiper: productDetailsThumbsSwiper
        },
        lazy: {
            preloadImages: false,
            loadPrevNext: true,
            loadPrevNextAmount: 4,
        },
    });
});

function increment(element) {
    return parseInt(element.html()) + 1;
}

function decrement(element) {
    let number = parseInt(element.html());
    if (number > 0) {
        return number - 1;
    }

    return false;
}