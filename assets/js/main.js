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
});