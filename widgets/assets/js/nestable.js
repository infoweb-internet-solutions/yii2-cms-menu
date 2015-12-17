$(document).ready(function() {
    nestable.init();
});

var nestable = (function() {
    var nestable = {},
        defaultSettings = {},
        settings = $.extend(true, defaultSettings, nestableSettings || {});

    /**
     * Initializes the nestable tree
     */
    nestable.init = function() {
        nestable.unblock();

        // Init the nestable plugin
        $('.dd').nestable({
            maxDepth: settings.maxDepth,
            callback: function(list,element) {
                nestable.block();

                // Update the positions
                nestable.updatePositions().done(function(response) {
                    // Reload the container if someting went wrong
                    if (response.status == 0) {
                        nestable.reload().done(function(response) {
                            nestable.init();
                        });
                    } else {
                        nestable.unblock();
                    }
                });
            }
        });
    };

    nestable.setEventHandlers = function() {
        $(document)
            .on('click', '[data-toggler="active"]', nestable.toggleActive)
            .on('click', '[data-toggler="public"]', nestable.togglePublic);
    };

    /**
     * Reloads the nestable tree via pjax
     * @return Promise
     */
    nestable.reload = function(timeout) {
        var timeout = timeout || 3000;
        return $.pjax.reload({container: '#'+settings.pjaxId, timeout: timeout});
    }

    /**
     * Blocks the nestable tree ui
     */
    nestable.block = function() {
        CMS.addLoaderClass($('#'+settings.pjaxId));
    }

    /**
     * Unblocks the nestable tree
     */
    nestable.unblock = function() {
        CMS.removeLoaderClass($('#'+settings.pjaxId));
    }

    /**
     * Updates the positions of the items in the database
     * @return Promise
     */
    nestable.updatePositions = function() {
        return $.post('update-positions', {items: $('.dd').nestable('serialize')});
    };

    /**
     * Toggles the active state of an item
     * @param  Event
     */
    nestable.toggleActive = function(event) {
        event.preventDefault();
        nestable.block();
        var id = $(this).data('id'),
            uri = $(this).data('uri'),
            toggleActive = $.post(uri, {id: id});

        toggleActive.done(function(response) {
            if (response.status == 1) {
                nestable.reload().done(function() {
                    nestable.init();
                });
            }
        });
    }

    /**
     * Toggles the public state of an item
     * @param  Event
     */
    nestable.togglePublic = function(event) {
        event.preventDefault();
        nestable.block();
        var id = $(this).data('id'),
            uri = $(this).data('uri'),
            togglePublic = $.post(uri, {id: id});

        togglePublic.done(function(response) {
            if (response.status == 1) {
                nestable.reload().done(function() {
                    nestable.init();
                });
            }
        });
    }

    // Set eventhandlers
    nestable.setEventHandlers();

    return nestable;
})();