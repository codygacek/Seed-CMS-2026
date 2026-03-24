$(document).ready(function(){
    $('[data-fancybox]').fancybox({
        loop: true,
        transitionEffect : "slide",
    });

    $('.menu-drop-icon').on('click', function(event){
        event.preventDefault();
        var trigger = $(this);

        trigger.children().toggleClass('fa-angle-down fa-angle-up').closest('.nav-item-link').siblings().toggleClass('is-open');
    });

    $('.menu-toggle').on('click', function(event){
        $(this).children().toggleClass('fa-bars fa-times');
        $('body').toggleClass('menu-open');
    });
});

var swiper = new Swiper('.swiper-container', {
    loop: true,
    pagination: {
        el: '.swiper-pagination',
        clickable: true
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    autoplay: {
        delay: 5000,
    }
});