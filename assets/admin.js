jQuery(function($){
    if ($('.color-picker').length) {
        $('.color-picker').wpColorPicker();
    }
    $('.kat_edit').on('click', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        $('#kat_form [name="kat_id"]').val(row.data('id'));
        $('#kat_form [name="kat_name"]').val(row.data('name'));
    });

    $('.inh_edit').on('click', function(e){
        e.preventDefault();
        var row = $(this).closest('tr');
        $('#inh_form [name="inh_id"]').val(row.data('id'));
        $('#inh_form [name="code"]').val(row.data('code'));
        $('#inh_form [name="name"]').val(row.data('name'));
    });

    var allInhaltsstoffe = [];
    $('#inh_dropdown option').each(function(){
        var val = $(this).val();
        var text = $(this).text();
        if(val) allInhaltsstoffe.push({value: val, text: text});
    });

    function refreshDropdown(filter){
        var selected = $('.inh-selected .inh-tag').map(function(){ return $(this).data('value'); }).get();
        var options = '<option value="">Inhaltsstoff wählen</option>';
        allInhaltsstoffe.forEach(function(o){
            if(selected.indexOf(o.value) === -1 && (!filter || o.text.toLowerCase().indexOf(filter) !== -1)){
                options += '<option value="'+o.value+'">'+o.text+'</option>';
            }
        });
        $('#inh_dropdown').html(options);
    }

    function addTag(val, text){
        var tag = $('<span class="inh-tag" data-value="'+val+'" data-text="'+text+'">'+text+' <a href="#" class="inh-remove">&times;</a><input type="hidden" name="inhaltsstoffe[]" value="'+val+'"></span>');
        $('.inh-selected').append(tag);
    }

    $(document).on('click', '.inh-remove', function(e){
        e.preventDefault();
        $(this).parent().remove();
        refreshDropdown($('#inh_filter').val().toLowerCase());
    });

    $('#inh_dropdown').on('change', function(){
        var val = $(this).val();
        if(!val) return;
        var text = $(this).find('option:selected').text();
        addTag(val, text);
        $(this).val('');
        refreshDropdown($('#inh_filter').val().toLowerCase());
    });

    $('#inh_filter').on('keyup change', function(){
        refreshDropdown($(this).val().toLowerCase());
    });

    refreshDropdown('');

    $('.speise_edit').on('click', function(e){
        e.preventDefault();
        var li = $(this).closest('li');
        $('#speise_form [name="speise_id"]').val(li.data('id'));
        $('#speise_form [name="kategorie_id"]').val(li.data('kategorie'));
        $('#speise_form [name="nr"]').val(li.data('nr'));
        $('#speise_form [name="name"]').val(li.data('name'));
        $('#speise_form [name="beschreibung"]').val(li.data('beschreibung'));
        $('#speise_form [name="preis"]').val(li.data('preis'));
        var inh = li.data('inhaltsstoffe').toString().split(',');
        $('.inh-selected').empty();
        inh.forEach(function(c){
            var obj = allInhaltsstoffe.find(function(o){ return o.value === c; });
            if(obj) addTag(obj.value, obj.text);
        });
        refreshDropdown($('#inh_filter').val().toLowerCase());
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

    function applySpeisenFilter(){
        var kat = $('#speisen_kat_filter').val();
        var query = $('#speisen_search').val().toLowerCase();
        $('.speisen-kat-block').each(function(){
            var block = $(this);
            var katId = block.data('kat').toString();
            var showKat = !kat || katId === kat;
            var anyVisible = false;
            block.find('li.speise-item').each(function(){
                var li = $(this);
                var text = (
                    li.data('nr') + ' ' +
                    li.data('name') + ' ' +
                    li.data('beschreibung') + ' ' +
                    li.data('inhaltsstoffe')
                ).toLowerCase();
                var match = !query || text.indexOf(query) !== -1;
                var show = showKat && match;
                li.toggle(show);
                if(show) anyVisible = true;
            });
            block.toggle(anyVisible);
        });
    }

    $('#speisen_kat_filter').on('change', applySpeisenFilter);
    $('#speisen_search').on('keyup change', applySpeisenFilter);
    applySpeisenFilter();

});
