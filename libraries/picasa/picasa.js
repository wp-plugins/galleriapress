Galleriapress.picasa =
{
    init: function()
    {
        $('#picasa-library .browse').live('click',
                                          function()
                                          {
                                              $('.picasa-loading').show();

                                              var request =                                                      
                                                  {
                                                      action : 'picasa_library_items',
                                                      post_id : $('#post_ID').val(),
                                                      path : $(this).data('path')
                                                  };

                                              if($('.picasa-search').length > 0)
                                                  request['picasa_search'] = $('.picasa-search').val();

                                              $.post(ajaxurl,
                                                     request,
                                                     function(response)
                                                     {
                                                         $('#picasa-library').html(response.html);
                                                         $('.picasa-loading').hide();
                                                     },
                                                     'json');
                                          });
    },

    init_draggable: function()
    {
        $('#picasa-library .grid > li').draggable(
						{
								appendTo: 'body',
								helper: 'clone',
								connectToSortable: '#galleriapress-items',
								zIndex: 10000
						});
    }

};

jQuery(document).ready(function()
											 {
													 Galleriapress.picasa.init();
											 });
