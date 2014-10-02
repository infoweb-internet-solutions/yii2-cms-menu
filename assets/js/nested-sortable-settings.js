/**
 * Nested sortable menu items
 */
$('ol.sortable').nestedSortable({
    tabSize: 20,
    forcePlaceholderSize: true,
    // @todo remove this by after loading this script on index only
    maxLevels: (typeof maxLevels != 'undefined') ? maxLevels : 0,
    handle: 'div .sort',
    helper: 'clone',
    items: 'li',
    opacity: .6,
    placeholder: 'placeholder',
    revert: 250,
    tolerance: 'pointer',
    toleranceElement: '> div',
    update: function(event, ui) {
        //document.body.style.cursor = 'wait';
        //var icon = '<img src="../../admin/images/icons/loading.gif" alt="Loading" title="Loading" />';
        //$('#icon').html(icon);

        //var serialized = $('ol.sortable').nestedSortable('serialize');
        //var hiered = $('ol.sortable').nestedSortable('toHierarchy', {startDepthCount: 0});
        
        var arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});

        $.ajax(
        {
            type        : 'POST',
            url         : 'position',
            data        : {ids:arraied},
            dataType    : 'json',
            success : function(data)
            {
                if(data.status == 1)
                {
                    //var icon = '<img src="../../admin/images/icons/tick.png" alt="Saved" title="Saved" />';
                    //$('#icon').html(icon);
                }
            }
        });
    },
    stop: function(event, ui) {
        /*  Reset and add odd and even classes     */       
        $("li:even > div").removeClass("odd even").addClass("odd");
        $("li:odd > div").removeClass("odd even").addClass("even");
    }
});

$("li:even > div").removeClass("odd even").addClass("odd");
$("li:odd > div").removeClass("odd even").addClass("even");
