Galleriapress.picasa =
{
    init: function()
    {
        this.after_load_library_path();

        $('.libraries-tabs .picasa').on(
            'click',
            this.calculate_grid_height);

        $('#galleriapress-libraries').on(
            'resize',
            this.calculate_grid_height);
    },

    calculate_grid_height: function()
    {
        $('#picasa-library .grid').height($('#galleriapress-libraries').height() - $('.picasa-toolbar').outerHeight(true));
    },

    after_load_library_path: function()
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
    }
};

jQuery(document).ready(function()
											 {
													 Galleriapress.picasa.init();
											 });
