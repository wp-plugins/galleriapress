Galleriapress.wp_media =
{
    init: function()
    {
				// custom size
				$('.size select, .gallery-size select').change(function()
																											 {
																													 var form_table = $(this).parents('.form-table');
																													 custom_size = $('.custom-size', form_table);

																													 if($(this).val() == 'custom')
																													 {
																															 custom_size.slideDown();
																													 }
																													 else
																													 {
																															 custom_size.slideUp();
																													 }
																											 }).change();


        this.after_load_library_path();
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
