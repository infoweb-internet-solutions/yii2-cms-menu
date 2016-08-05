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

    /**
     * ADD ENTITY
     */
    var modalElement = $('#create-entity-modal');
    var modalBodyElement = modalElement.find('.modal-body');

    $(document)
        .on('click', '.create-entity-links-link', function(event) {
            event.preventDefault();

            modalBodyElement.html('<div style="min-height:300px;height:300px;" class="element-loading"></div>');
            modalElement.modal('show');

            $.ajax({
                url: $(this).data('entity-create-url'),
                context: document.body
            }).done(function(data) {
                modalBodyElement.html(data);
                modalBodyElement.find('button[name="save-close"], button[name="save-add"]').hide();
                modalElement.modal('show');

                // Fixes: duplicateable not working but creates a new bug scrollingbar.
                // Init the duplicateable jquery plugin:
                modalElement.find('[data-duplicateable="true"]').duplicateable();
            });
        })
        .on('click', '#create-entity-modal .modal-body button[name="save"]', function(event) {
            event.preventDefault();

            CMS.addLoaderClass(modalBodyElement);

            formdata = modalBodyElement.find('form').serialize();

            $.ajax({
                method: "POST",
                url: modalBodyElement.find('form').attr('action'),
                context: document.body,
                data: modalBodyElement.find('form').serialize() + '&saveModel=1'
            }).done(function(response) {
                // Update dropdown content with pjax
                $.pjax.reload({
                    container: '#pjax-linkableentities'
                }).done(function() {                  
                    if(response.status == 200) {
                        $('#menuitem-entity').trigger('change');

                        var entityID = $('#menuitem-entity').val();
                        entityID = entityID.split('\\');
                        entityID = entityID[entityID.length-1];

                        // Set the pages dropdown value & update the pages dropdown
                        $('#'+entityID+'-select2').val(response.id).trigger('change').prop('disabled', false);
                    }

                    CMS.removeLoaderClass(modalBodyElement);
                    modalElement.modal('hide');
                });
            });
        })
        .on('click', '#create-entity-modal .modal-body a[name="close"]', function(event) {
            event.preventDefault();
            modalElement.modal('hide');
        })
        .on("change", "#menuitem-entity", function(event) {
            $('.create-entity-links .create-entity-links-link').addClass('hidden');
            var entity = $(this).find('option:selected').val();
            $('.create-entity-links .create-entity-links-link[data-entity="' + entity.split('\\').join('\\\\') + '"]').removeClass('hidden');
        });
});