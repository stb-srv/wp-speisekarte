jQuery(function($){
    function equalizeToggles(){
        var custom = parseInt($('.speisekarte-accordion').data('tile-height')) || 0;
        $('.speisekarte-toggle').css('min-height','');
        var max = custom;
        $('.speisekarte-toggle').each(function(){
            var h = $(this).outerHeight();
            if(h > max) max = h;
        });
        if(max){
            $('.speisekarte-toggle').css('min-height', max);
        }
    }

    equalizeToggles();
    $(window).on('resize', equalizeToggles);

    $('.speisekarte-toggle').on('click', function(){
        $(this).toggleClass('active').next('.speisekarte-content').slideToggle();
    });
});
