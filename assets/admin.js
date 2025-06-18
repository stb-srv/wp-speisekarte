jQuery(function($){
    $('.kat_edit').on('click', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        $('#kat_form [name="kat_id"]').val(row.data('id'));
        $('#kat_form [name="kat_name"]').val(row.data('name'));
    });

    $('.speise_edit').on('click', function(e){
        e.preventDefault();
        var li = $(this).closest('li');
        $('#speise_form [name="speise_id"]').val(li.data('id'));
        $('#speise_form [name="kategorie_id"]').val(li.data('kategorie'));
        $('#speise_form [name="nr"]').val(li.data('nr'));
        $('#speise_form [name="name"]').val(li.data('name'));
        $('#speise_form [name="beschreibung"]').val(li.data('beschreibung'));
        $('#speise_form [name="inhaltsstoffe"]').val(li.data('inhaltsstoffe'));
        $('#speise_form .bild_id').val(li.data('bild'));
        var img = li.find('img').first();
        if(img.length){
            $('#speise_form .bild_preview').html('<img src="'+img.attr('src')+'" style="height:32px;">');
        } else {
            $('#speise_form .bild_preview').empty();
        }
    });
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
