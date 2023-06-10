var $ = jQuery;
$('#event-city').on('change', function() {
    var city_filter_value = this.value;
    $.ajax({
        type: 'GET',
        dataType: 'html',
        url: the_ajax_script.ajaxurl,
        data: { action: 'get_events_ajax', city_name: city_filter_value },
        success: function( response ) {
            if( response.trim() !== '' ) {
                $( '#event-table' ).html( response );
            }
        }
    });
});