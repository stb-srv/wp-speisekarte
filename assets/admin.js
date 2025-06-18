jQuery(function($){
    $('.bild_upload').on('click', function(e){
        e.preventDefault();
        var button = $(this);
        var custom_uploader = wp.media({
            title: 'Bild auswählen',
            button: { text: 'Verwenden' },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            button.siblings('.bild_id').val(attachment.id);
            button.siblings('.bild_preview').html('<img src="'+attachment.url+'" style="height:32px;">');
        }).open();
    });

    $('.speisen-sortable').sortable({
        update: function(e, ui){
            var ids = $(this).children().map(function(){ return $(this).data('id'); }).get();
            var kat_id = $(this).data('kat');
            $.post(speisekarteAjax.ajax_url, {
                action: 'update_speisen_order',
                ids: ids,
                kat_id: kat_id,
                nonce: speisekarteAjax.nonce
            });
        }
    });
});
