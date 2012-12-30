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

        $('.libraries-menu a').click(
            function() 
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
                handles: 's'
            });

        var update_box_height = function (grid)
        {
            var items_per_row = Math.round($(grid).innerWidth() / $(grid).children('li:first-child').outerWidth(true));
            var num_rows = Math.ceil($(grid).children('li').length / items_per_row);
            var height = $(grid).children('li:first-child').outerHeight(true) * (num_rows + 1);

            if(height < 110)
                height = 110;

            $(grid).height(height);
        }

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

            update_box_height($('#galleriapress-items'));
        };

		    $('#galleriapress-items').sortable(
				    { 
						    connectWith: '.connected-sortable',
						    placeholder: 'ui-sortable-placeholder',
						    zIndex: 10000,
						    appendTo: 'body',
						    helper: 'clone',
                tolerance: 'pointer'
				    }).disableSelection();

        $('#gallery-box .grid > li').on(
            'click',
            function()
            {
                $(this).toggleClass('selected');
            });

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

        $('.add-to-gallery').on(
            'click',
            function(e)
            {
                $('#galleriapress-libraries .selected').appendTo('#galleriapress-items');
                reset_items_data();
            });

        $('.clear-library-selected').on(
            'click',
            function(e)
            {
                $('#galleriapress-libraries .selected').removeClass('selected');
            });


        $('.clear-gallery-selected').on(
            'click',
            function(e)
            {
                $('#galleriapress-items .selected').removeClass('selected');
            });

        $('.remove-gallery-selected').on(
            'click',
            function(e)
            {
                $('#galleriapress-items .selected').remove();
                reset_items_data();
            });
            

				$('.remove-all').click(function(e)
															 {
                                   e.preventDefault();

																	 $('#galleriapress-items').html('');
																	 reset_items_data();
															 });

        $('.galleriapress-items-container').on(
            'mouseover',
            '#galleriapress-items li',
            function()
            {
                $('.item-info .title').text($(this).children('img').attr('title'));
                $('.item-info .library').text($('.libraries-tabs .current img').attr('title'));
            });

        $('.galleriapress-items-container').on(
            'mouseout',
            '#galleriapress-items li',
            function()
            {
                $('.item-info span').text('');
            });


        $('#galleriapress-items .delete').live('click',
                                               function(e)
                                               {
                                                   $(this).parent().remove();
                                                   $('.item-info span').text('');

                                                   reset_items_data();
                                               });

        update_box_height($('#galleriapress-items'));


				// apply profile box
				$('#profiles-box input.submit').click(function()
																							{
																									$.post(ajaxurl,
																												 {
																														 action: 'galleriapress_profile',
																														 profile: $('#profiles-box .choose-profile').val(),
																														 gallery_id: $('#profiles-box .gallery-id').val(),
																														 link_profile: $('#profile-box .link-profile').val()
																														 
																												 },
																												 function(response)
																												 {
																														 if(response)
																														 {
																																 for(name in response)
																																 {
																																		 $('input[name=' + name + '], select[name=' + name + ']').val(response[name]);
																																 }
																														 }
																												 },
																												 'json');
																							});


        $(document).on(
            'submit',
            'form.library-action',
            function(e)
            {
                e.preventDefault();

                var postdata = $(this).serializeArray();
                postdata['library_action'] = $(this).data('action');
                postdata['library'] = $(this).data('library');
                postdata['action'] = 'galleriapress_library_form';

                $.post(ajaxurl,
                       postdata,
                       function(response)
                       {
                           //                       Galleriapress.load_view();
                       });
            });

        $(document).on(
            'click',
            '.library-path',
            function(e)
            {
                e.preventDefault();

                var path = $(this).data('path');
                var parts = path.split('/');
                var parsed = new Array();

                for(var i = 0; i < parts.length; i++)
                {
                    var matches = parts[i].match(/\{(.*)\}/);
                    if(matches && matches.length > 0)
                    {
                        matches.shift();
                        parsed.push($('#' + matches[0]).val());
                    }
                    else
                    {
                        parsed.push(parts[i]);
                    }
                }

                var parsed_path = parsed.join('/');

                Galleriapress.load_library_path($(this).data('library'), parsed_path);
            });
    },

    load_library_path: function(library, library_path)
    {
        $.post(ajaxurl,
               {
                   action: 'galleriapress_library_path',
                   library: library,
                   path: library_path,
                   post_id: $('#post_ID').val()
               },
               function(response)
               {
                   $('#' + library + '-library').html(response);
                   if(Galleriapress[library] && Galleriapress[library].after_load_library_path)
                       Galleriapress[library].after_load_library_path();
               });
    }

};

        
$(document).ready(function()
                  {
                      Galleriapress.init();
									});
