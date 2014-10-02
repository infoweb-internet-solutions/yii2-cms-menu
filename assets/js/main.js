$(function(){

    /*
    $('table').floatThead({
        floatTableClass:  'kv-table-float',
        floatContainerClass: 'kv-thead-float'
    });
    */

    /**
     * Create/update menu item
     */
    menu_item.init();

    /**
     * Hightlight and scroll to anchor
     *
     * @type {*|jQuery}
     */

    // Get anchor
    var anchor = menu_item.getParameterByName('anchor');
    // Scroll to anchor
    menu_item.scroll_to_element('#' + anchor);
    // Hightlight anchor
    menu_item.highlight(anchor);

    /**
     * Active toggle
     */
    $(document).on('click', '[data-toggle-active-menu-items]', function(e){

        e.preventDefault();

        var id = $(this).data('toggle-active-menu-items');

        $.ajax({
            url: 'active',
            type: 'POST',
            data: {'id': id},
            dataType: "json",
            success: function(data) {
                if (data.active == 1)
                {
                    $('#list-' + id + ' [data-toggle-active-menu-items]').html('<span class="glyphicon glyphicon-eye-open"></span>');
                } else {
                    $('#list-' + id + ' [data-toggle-active-menu-items]').html('<span class="glyphicon glyphicon-eye-close"></span>');
                }
            }
        });
    });

});