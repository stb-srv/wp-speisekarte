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

    function applySpeisenSearch(){
        var query = $('#speisekarte_search').val().toLowerCase();
        $('.speisekarte-kat').each(function(){
            var block = $(this);
            var toggle = block.find('.speisekarte-toggle');
            var content = block.find('.speisekarte-content');
            var anyVisible = false;

            block.find('.speisekarte-item').each(function(){
                var item = $(this);
                var text = (
                    item.data('nr') + ' ' +
                    item.data('name') + ' ' +
                    item.data('beschreibung') + ' ' +
                    item.data('inhaltsstoffe')
                ).toLowerCase();
                var match = !query || text.indexOf(query) !== -1;
                item.toggle(match);
                if(match) anyVisible = true;
            });

            if(query){
                block.toggle(anyVisible);
                if(anyVisible){
                    if(!toggle.hasClass('active')) toggle.addClass('active');
                    content.show();
                } else {
                    toggle.removeClass('active');
                    content.hide();
                }
            } else {
                block.show();
                toggle.removeClass('active');
                content.hide();
            }
        });
    }

    $('#speisekarte_search').on('keyup change', applySpeisenSearch);
    applySpeisenSearch();
});
