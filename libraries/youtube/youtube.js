Galleriapress.youtube = 
{
    init: function()
    {
        this.after_load_library_path();
    },

    after_load_library_path: function()
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

        $('#youtube-library .grid').height($('#galleriapress-libraries').height() - $('.youtube-toolbar').outerHeight(true) - 29);
		}

}

$(document).ready(
    function()
    {
        Galleriapress.youtube.init();
    });