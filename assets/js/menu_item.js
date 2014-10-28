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
            .on('change', '#menuitem-entity', menu_item.toggle_attributes);
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

        var val = $(this).val();

        // Hide all attributes
        $('.attribute').hide();
        
        // Show the attributes that belong to the selected type
        if (val) {
            $('.'+val+'-attribute').show().find('select').prop('disabled', false);              
        }
            
        // Only enable the 'menuitem-entity_id' field that is visible
        $('.attribute select').prop('disabled', true);
        $('.attribute select:visible').prop('disabled', false); 
    };
    
    return menu_item;
}(jQuery));
