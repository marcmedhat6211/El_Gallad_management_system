$(document).ready(function() {
    var currentPageURL = window.location.href;

    $("#facebook_link").attr("href", 'https://www.facebook.com/share.php?u=' + currentPageURL);
    $("#whatsapp_link").attr("href", 'https://wa.me/?text' + currentPageURL);
    $("#twitter_link").attr("href", 'https://twitter.com/intent/tweet?url=' + currentPageURL);
    $("#linkedin_link").attr("href", 'https://www.linkedin.com/sharing/share-offsite/?url=' + currentPageURL);
    $("#pinterest_link").attr("href", 'http://pinterest.com/pin/create/button/?url=' + currentPageURL);
    $("#gmail_link").attr("href", 'mailto:?Body=' + currentPageURL);

    // PROJECT DETAILS THUMBS SWIPER
    var projectDetailsThumbsSwiper = new Swiper('.project-details-thumbs-swiper', {
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

    //PROJECT DETAILS MAIN SLIDER
    var projectDetailsMainSwiper = new Swiper('.project-details-swiper', {
        loop: false,
        slidesPerView: 1,
        centeredSlides: true,
        allowTouchMove: true,
        thumbs: {
            swiper: projectDetailsThumbsSwiper
        },
        lazy: {
            preloadImages: false,
            loadPrevNext: true,
            loadPrevNextAmount: 4,
        },
    });
});