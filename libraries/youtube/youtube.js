jQuery(document).ready(function()
											 {
				                   $('#youtube-library .grid > li').draggable(
						                   {
								                   appendTo: 'body',
								                   helper: 'clone',
								                   connectToSortable: '#galleriapress-items',
								                   zIndex: 10000
						                   });
											 });
