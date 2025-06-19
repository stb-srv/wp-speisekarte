jQuery(function($){
    function equalizeToggles(){
        var max = 0;
        $('.speisekarte-toggle').css('min-height','').each(function(){
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
