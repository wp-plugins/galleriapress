Galleriapress.picasa =
{
    init: function()
    {
        $('#picasa-library .grid > li').draggable(
						{
								appendTo: 'body',
								helper: 'clone',
								connectToSortable: '#galleriapress-items',
								zIndex: 10000,
                start: function(event, ui)
                {
                    Galleriapress.show_drag_message();
                },
                stop: function(event, ui)
                {
                    Galleriapress.hide_drag_message();
                }
						});
    },

    after_load_library_path: function()
    {
        this.init();
    }

};

jQuery(document).ready(function()
											 {
													 Galleriapress.picasa.init();
											 });
