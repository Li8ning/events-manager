var $ = jQuery;
var pageNumber = 1;
var total = $('#totalpages').val();
$('#event-city').on('change', function() {
    var city_filter_value = this.value;
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: the_ajax_script.ajaxurl,
        data: { action: 'get_events_ajax', city_name: city_filter_value },
        success: function( response ) {
            if( response.success ) {
                $( '#event-table' ).html( response.data.html );
                $('#totalpages').val( response.data.total_pages );
                if( response.data.total_pages > 1 ) {
                    $("#more_posts").show();
                }
                else {
                    $("#more_posts").hide();
                }
                pageNumber = 1;
            }
        }
    });
});

$('#more_posts').on( 'click', function() {
    var city_filter_value = $('#event-city').value;
    var total = $('#totalpages').val();
    $("#more_posts").attr("disabled", true);
    pageNumber++;
    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: the_ajax_script.ajaxurl,
        data: { action: 'load_more_events_ajax', pageNumber: pageNumber, city_name: city_filter_value },
        success: function ( response ) {
            if (response.length) {
                $("#event-table").append(response);
                $("#more_posts").attr("disabled", false);
            } else {
                $("#more_posts").attr("disabled", true);
            }                
            if (total < pageNumber) {
                $("#more_posts").hide();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $loader.html(jqXHR + " :: " + textStatus + " :: " + errorThrown);
        }
    });
});