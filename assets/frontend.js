jQuery(function($){
    $('.speisekarte-toggle').on('click', function(){
        $(this).toggleClass('active').next('.speisekarte-content').slideToggle();
    });
});
