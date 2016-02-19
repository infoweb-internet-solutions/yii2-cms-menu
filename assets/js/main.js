$(document).ready(function() {
    // Disable all readonly select element options, except for the selected option.
    $('select[readonly] option:not(:selected)').attr('disabled', true);

    menu_item.init();

    // Get anchor
    var anchor = menu_item.getParameterByName('anchor');
    // Scroll to anchor
    menu_item.scroll_to_element('#' + anchor);
    // Hightlight anchor
    menu_item.highlight(anchor);


    // @todo Move to cms
    $.pjax.defaults = {
        timeout: 3000
    };



    $(document).on('click', '#create-page-url', function(event) {
        event.preventDefault();
        $('#create-page-modal').modal('show');
    });

    $(document).on("beforeSubmit", "#page-form", function (event) {

        //event.preventDefault();

        var form = $(this),
            data = form.serialize();

        data.save = '';

        var request = $.post(form.attr('action'), data);

        request.done(function(response) {
            if (response.status == 1) {
                // Update dropdown content with pjax
                $.pjax.reload({container: '#pages-dropdown'});


                // Update pages dropdown after pjax reload
                $(document).on('pjax:complete', function() {
                    // Set the pages dropdown value
                    $('#menuitem-entity_id').val(response.id);
                    // Update the pages dropdown
                    $('#menuitem-entity_id').trigger('change');
                });

                // Hide modal
                $('#create-page-modal').modal('hide');
            }

        });

        return false;

    });
});