(function (root, factory) {
    root.menu_item = factory;
})(this, function($) {

    'use strict';

    var menu_item = {};

    menu_item.init = function() {
        menu_item.set_eventhandlers();
    };

    menu_item.set_eventhandlers = function() {
        $(document)
            .on('change', '#menuitem-entity', menu_item.toggle_attributes)
            .on('change', '#menuitem-entity_id', menu_item.togglePageHtmlAnchors);
            //.on('click', '#delete-100', menu_item.delete);
    };
    /*
    menu_item.delete = function(e) {

        e.preventDefault();
        var val = $(this).attr('id').replace('delete-', '');

        console.log(val);
    };
   */

    menu_item.getParameterByName = function(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    };

    /**
     * Scrolls to a element with a given speed
     *
     * @param   string      The selector
     * @return  void
     */
    menu_item.scroll_to_element = function(selector, speed) {
        // Select the element (limit to the first)
        var el = $(selector)[0],
            speed = speed || 150;

        // Check for the existance of the element
        if($(el).length == 0)
            return false;

        // Scroll
        $('html,body').animate({scrollTop: $(el).offset().top - 100}, speed);
    };

    /**
     * Hightlight an element
     *
     * @param anchor
     * @returns {boolean}
     */
    menu_item.highlight = function(anchor) {

        if (anchor.length <= 0) {
            return false;
        }

        // Save background color
        var backgroundColor = $('#' + anchor + ' > div').css('backgroundColor');

        $('#' + anchor + ' > div').animate({
            // Change background color
            backgroundColor: '#fcf8e3'
        }, 200, function() {
            $(this).delay(3000).animate({
                // Reset background color
                backgroundColor: backgroundColor
            }, 2000);
        });
    };

    menu_item.toggle_attributes = function(e) {

        // Get the current value
        var val = $(this).val(),
            parts = val.split('\\');
        val = parts[parts.length - 1];

        // Hide all attributes
        $('.attribute').hide();

        // Show the attributes that belong to the selected type
        if (val) {
            $('.'+val+'-attribute').show().find('select').show().prop('disabled', false);
        }

        // Only enable the 'menuitem-entity_id' field that is visible
        $('.attribute select').prop('disabled', true);
        $('.attribute select:visible').prop('disabled', false);

        // Reset the values of the entities
        $('#menuitem-entity_id').val('');
        $('#menuitem-url').val('');

        // Hide the anchors dropdown for a 'page' entity
        $('.menu-item-anchor-container').hide();
    };

    /**
     * Toggles the dropdown of a page's html anchors
     *
     * @param   Event
     */
    menu_item.togglePageHtmlAnchors = function(e) {
        // Get the current value
        var val = $('#menuitem-entity').val(),
            parts = val.split('\\');
        val = parts[parts.length - 1];

        // If the entity is a 'page', refresh the anchors dropdown and show it
        if (val == 'page' && $(this).val()) {

            var request = $.get('get-page-html-anchors', {page: $(this).val()});

            request.done(function(response) {
                if (response.status == 1) {
                    // Remove current anchors from the dropdown
                    $('#menuitem-anchor option').remove();

                    // Add the anchors
                    $.each(response.anchors, function(i) {
                        $('#menuitem-anchor').append('<option value="'+i+'">'+this+'</option>');
                    });

                    // Show the dropdown if it contains options
                    if ($('#menuitem-anchor option').length > 1) {
                        $('.menu-item-anchor-container').show();
                    } else {
                        $('.menu-item-anchor-container').hide();
                    }
                }
            });

        } else {
            $('.menu-item-anchor-container').hide();
        }
    };

    return menu_item;
}(jQuery));
