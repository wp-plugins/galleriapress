(function()
 {
     tinymce.create('tinymce.plugins.galleriapress',
                    {
                        init: function(ed, url)
                        {
                            ed.addButton('galleriapress',
                                         {
                                             title: 'Galleriapress',
                                             image: url + '/icon.png',
                                             onclick: function()
                                             {
                                                 var pid = $('input#post_ID').val();

                                                 ed.windowManager.open(
                                                     {
                                                         file: ajaxurl + '?action=galleriapress_tiny_mce_dialog&pid=' + pid,
                                                         width: 500,
                                                         height: 200,
                                                         inline: 1
                                                     });

                                             }
                                         });
                        },
                        createControl: function(n, cm)
                        {
                            return null;
                        }
                    });

     tinymce.PluginManager.add('galleriapress', tinymce.plugins.galleriapress);

 })();
                        

