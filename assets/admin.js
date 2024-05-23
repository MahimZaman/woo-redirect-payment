$ = jQuery ;

$(document).ready(function(){
    const store = $('.wss_store_type:checked').val();

    if(store === 'receiver'){
        $('#wss_receiver_fields').show()
        $('#wss_supplier_fields').hide()
    }else if(store === 'supplier'){
        $('#wss_receiver_fields').hide()
        $('#wss_supplier_fields').show()
    }

    $('.wss_store_type').change(function(){
        const store = $('.wss_store_type:checked').val();

        if(store === 'receiver'){
            $('#wss_receiver_fields').show()
            $('#wss_supplier_fields').hide()
        }else if(store === 'supplier'){
            $('#wss_receiver_fields').hide()
            $('#wss_supplier_fields').show()
        }
    })
})