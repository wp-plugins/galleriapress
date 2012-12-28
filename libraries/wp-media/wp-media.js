Galleriapress.wp_media =
{
    init: function()
    {
        this.after_load_library_path();

        $('.libraries-tabs .wp_media').on(
            'click',
            this.calculate_grid_height);

        $('#galleriapress-libraries').on(
            'resize',
            this.calculate_grid_height);

        this.calculate_grid_height();
    },

    calculate_grid_height: function()
    {
        $('#wp_media-library .grid').height($('#galleriapress-libraries').height() - $('.wp_media-toolbar').outerHeight(true));
    },

    after_load_library_path: function()
    {
				$('#wp_media-library .grid > li').draggable(
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

    }

};

jQuery(document).ready(function()
											 {
													 Galleriapress.wp_media.init();
											 });
