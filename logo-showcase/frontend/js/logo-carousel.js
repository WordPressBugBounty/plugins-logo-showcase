jQuery(document).ready(function ($) {
    "use strict";
    $(".logo-showcase-wrap").each(function () {

        const $el = $(this);

        $el.owlCarousel({
            lazyLoad: true,
            loop: $el.data("loop") == 1,
            margin: parseInt($el.data("margin")) || 0,
            autoplay: $el.data("autoplay") == 1,
            autoplaySpeed: parseInt($el.data("autoplay-speed")) || 500,
            autoplayTimeout: parseInt($el.data("autoplay-timeout")) || 5000,
            autoplayHoverPause: $el.data("hover-pause") == 1,
            nav: $el.data("nav") == 1,
            dots: $el.data("dots") == 1,
            navText: [
                '<i class="fa fa-angle-right"></i>',
                '<i class="fa fa-angle-left"></i>'
            ],
            smartSpeed: 450,
            clone: true,

            responsive: {
                0: {
                    items: parseInt($el.data("items-mobile")) || 1
                },
                678: {
                    items: parseInt($el.data("items-tablet")) || 2
                },
                980: {
                    items: parseInt($el.data("items-desktop")) || 3
                },
                1199: {
                    items: parseInt($el.data("items-large")) || 4
                }
            }
        });

    });

    // Loop through each carousel instance on the page
    $('.logo-showcase-wrap').each(function() {
        var $showcase = $(this);
        
        // Check if tooltips are enabled for this specific instance (1 = true)
        var tooltipsEnabled = $showcase.data('tooltip-enabled') == 1;

        if (tooltipsEnabled) {
            // Find only the tooltip triggers inside THIS specific instance
            $showcase.find('.js-tipso-trigger').tipso({
                useTitle   : false,
                width      : 150,
                delay      : 200,
                speed      : 400,
                // Extract instance-specific styles from the data-attributes
                position   : $showcase.data('tipso-position'),
                color      : $showcase.data('tipso-color'),
                background : $showcase.data('tipso-bgcolor')
            });
        }
    });

});