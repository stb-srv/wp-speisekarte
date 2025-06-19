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
            } else {
                block.show();
            }
        });
    }

    $('#speisekarte_search').on('keyup change', applySpeisenSearch);
    applySpeisenSearch();
});
