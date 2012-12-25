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



        

        $('#wp_media-library .page-nav a').click(function(e)
                                                 {
                                                     e.preventDefault();

                                                     $.post(ajaxurl,
                                                            {action: 'galleriapress_wp_media_library_items',
                                                             page: $(this).data('page') },
                                                            function(response)
                                                            {
                                                                if(response)
                                                                    $('#wp_media-library').html(response);
                                                            },
                                                            'json');
                                                 });

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
