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


    //HANDLING ADDING TO CART
    $(document.body).on("submit", "#add_to_cart_form", function(e) {
        e.preventDefault();
        let $this = $(this);
        let submitBtn = $this.find("button[type='submit']");
        let link = $this.data("link");
        let productUuid = $this.data("uuid");
        let qty = $this.find("input[name='qty']").val();
        let cartCanvas = $("#cart_sidebar_canvas");
        submitBtn.attr("disabled", "disabled").text(LANG.Loading);
        let productItem = {
            uuid: productUuid,
            qty: qty
        }

        $.post(link, productItem, function(json) {
            if (!json.error) {
                updateCart(json);
                cartCanvas.find(".canvas-sidebar").addClass("show");
                cartCanvas.find("card-header p").text("");
                updateCartEmptyMessage();
                popupMessage(json.message, "success");
            } else {
                popupMessage(json.message, "error");
            }
            submitBtn.removeAttr("disabled").text(LANG.addToCart);
        });
    })
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

function updateCart(json) {
    let ItemsContainer = $("#cart_sidebar_canvas .card-body");
    ItemsContainer.empty();
    productsPrices = [];
    json.cartItems.forEach(function(cartItem) {
        let item = $("<div>", { class: "mb-6 d-flex" });
        let removeItemBtn = $("<a>", { class: "d-block mr-4", href: "#" }).attr({
            "data-uuid": cartItem.product.uuid,
            "data-link": cartItem.removeItemUrl,
        });
        let removeItemIcon = ($("<i>", { class: "fal fa-times" }));
        let itemContainer = $("<div>", { class: "media" });
        let itemImageContainer = $("<div>", { class: "w-70px mr-4" });
        let itemImage = $('<img />', {
            src: cartItem.product.imageUrl ? cartItem.product.imageUrl : "",
            alt: cartItem.product.title
        });
        let itemDetailsContainer = $("<div>", { class: "media-body" });
        let categoryTitle = ($("<p>", { class: "text-muted fs-12 mb-0 text-uppercase letter-spacing-05 lh-1 mb-1", text: cartItem.product.category.title }));
        let productTitleLink = $("<a>", { class: "font-weight-bold mb-3 d-block", href: cartItem.product.productUrl, text: cartItem.product.title });
        let incrementerBox = $("<div>", { class: "d-flex align-items-center" });
        let incrementerBtnsContainer = $("<div>", { class: "input-group position-relative" });
        let incrementerMinusBtn = $("<a>", { class: "down position-absolute pos-fixed-left-center pl-2", href: "#" });
        let incrementerMinusIcon = ($("<i>", { class: "far fa-minus" }));
        let incrementerInput = $('<input>').attr({
            type: 'number',
            class: "w-100 px-6 text-center",
            value: cartItem.qty,
            "data-uuid": cartItem.product.uuid,
            "data-link": cartItem.updateUrl,
        });
        let incrementerPlusBtn = $("<a>", { class: "up position-absolute pos-fixed-right-center pr-2", href: "#" });
        let incrementerPlusIcon = ($("<i>", { class: "far fa-plus" }));
        let itemTotalPrice = ($("<p>", { class: "mb-0 ml-12 text-primary", text: Intl.NumberFormat().format(cartItem.totalPrice) + "  " + LANG.EGP }));


        removeItemIcon.appendTo(removeItemBtn);
        removeItemBtn.appendTo(item);
        itemImage.appendTo(itemImageContainer);
        itemImageContainer.appendTo(itemContainer);
        categoryTitle.appendTo(itemDetailsContainer);
        productTitleLink.appendTo(itemDetailsContainer);
        incrementerMinusIcon.appendTo(incrementerMinusBtn);
        incrementerPlusIcon.appendTo(incrementerPlusBtn);
        incrementerMinusBtn.appendTo(incrementerBtnsContainer);
        incrementerInput.appendTo(incrementerBtnsContainer);
        incrementerPlusBtn.appendTo(incrementerBtnsContainer);
        incrementerBtnsContainer.appendTo(incrementerBox);
        itemTotalPrice.appendTo(incrementerBox);
        incrementerBox.appendTo(itemDetailsContainer);
        itemDetailsContainer.appendTo(itemContainer);
        itemContainer.appendTo(item);
        item.appendTo(ItemsContainer);
        productsPrices.push(cartItem.totalPrice);
    });
    updateTotalPrice(json.grandTotal);
    updateCartNumber(json.noOfItem);
}