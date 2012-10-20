jQuery(document).ready(function()
											 {
				                   $('#youtube-library .grid > li').draggable(
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
											 });
