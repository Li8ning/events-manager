jQuery(document).ready(function($){
    $('#event_date').datepicker({
        dateFormat: "dd/mm/yy"
    });

    var event_city = $('input[name="tax_input[cities][]"]:checked');

    if( event_city.length <= 0 ){
        $('#publish').prop('disabled', true);
    }

    $('input[name="tax_input[cities][]"]').change(function(){
        if( $(this).parents().find('#citieschecklist label input:checked').length > 0 ){
            $('#publish').prop('disabled', false);
        }
        else if( $(this).parents().find('#citieschecklist label input:checked').length <= 0 ){
            $('#publish').prop('disabled', true);
        }
    });
});