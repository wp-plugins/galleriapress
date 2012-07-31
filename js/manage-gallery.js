$ = jQuery.noConflict();

Galleriapress = 
{
    init: function()
    {
				if($('galleriapress_items_data').length > 1)
						Galleriapress.items = JSON.parse($('#galleriapress_items_data').val());

        $('.libraries-tabs a').click(
            function(e)
            {
                e.preventDefault();

                $('#galleriapress-libraries .library.active').removeClass('active').hide();
                $('#' + $(this).data('library') + '-library').addClass('active');

                $('#galleriapress-libraries .library-settings.active').removeClass('active').hide();
                $('#' + $(this).data('library') + '-settings').addClass('active');

                if($('.libraries-menu .current').data('show') == 'items')
                    $('#galleriapress-libraries .library.active').show();
                else
                    $('#galleriapress-libraries .library-settings.active').show();

                $('#galleriapress_library').val($(this).data('library'));
                
                $('.libraries-tabs a.current').removeClass('current');
                $(this).addClass('current');
            });

        $('.libraries-menu a').click(function() 
                                          {
                                              $('.libraries-menu .current').removeClass('current');
                                              $(this).addClass('current');

                                              if($(this).data('show') == 'items')
                                              {
                                                  $('#galleriapress-libraries .library-settings.active').hide();
                                                  $('#galleriapress-libraries .library.active').show();
                                              }
                                              else
                                              {
                                                 $('#galleriapress-libraries .library.active').hide();
                                                 $('#galleriapress-libraries .library-settings.active').show();
                                              }
                                          });


        $('.libraries-tabs li:first-child a').click();
        
        $('#galleriapress-libraries').resizable(
            {
                handles: 's',
                resize: function(event, ui)
                {
                    $('#galleriapress-libraries .library').height(ui.size.height);
                }
            });

        var reset_items_data = function(event, ui)
        {
            var new_items = new Array();

            $('#galleriapress-items > li').each(
                function(i, elem)
                {
										if($(elem).data('itemid') === undefined)
												return;

                    var item = {id: $(elem).data('itemid'),
                                thumb: $('img', $(elem)).attr('src'),
                                library: $(elem).data('library') };

                    if($('img', elem).attr('title'))
                        item.title = $('img', elem).attr('title');

                    // store any data-* attributes
                    var elem_data = $('img', elem).data();
                    for(var key in elem_data)
                        item[key] = elem_data[key];

                    $(elem).addClass(item.library);

                    if($('.delete', elem).length == 0)
                        $(elem).append('<span class="delete"></span>');
                    
                    new_items.push(item);                            
                });
            Galleriapress.items = new_items;
            $('#galleriapress_items_data').val(JSON.stringify(Galleriapress.items));
        };

		    $('#galleriapress-items').sortable(
				    { 
						    connectWith: '.connected-sortable',
						    placeholder: 'ui-sortable-placeholder',
						    zIndex: 10000,
						    appendTo: 'body',
						    helper: 'clone',
						    receive: function(event, ui) 
                {
                    var item = { id: ui.item.data('itemid'),
                                 thumb: $('img', $(ui.item)).attr('src'),
                                 library: ui.item.data('library') };

                    if($('img', ui.item).attr('title'))
                        item.title = $('img', ui.item).attr('title');

										if(!Galleriapress.items)
												Galleriapress.items = new Array();

                    Galleriapress.items.push(item);
                    $('#galleriapress_items_data').val(JSON.stringify(Galleriapress.items));
						    },
                remove: reset_items_data,
                update: reset_items_data
				    }).disableSelection();

				$('.remove-all').click(function(e)
															 {
																	 $('#galleriapress-items').html('');
																	 reset_items_data();
															 });

        $('#galleriapress-items .delete').live('click',
                                               function(e)
                                               {
                                                   $(this).parent().remove();
                                                   reset_items_data();
                                               });

    }
};

        
$(document).ready(function()
                  {
                      Galleriapress.init();
									});