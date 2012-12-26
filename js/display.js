if(typeof Galleriapress === 'undefined') Galleriapress = {};

Galleriapress.init_galleries = function()
{
    $ = jQuery.noConflict();

		$('.galleria').each(function()
												{
														var gid = jQuery(this).data('gallery_id');
														var options = galleriapress_options[gid];
														var galleria_elem = this;
														var extend;

														if(options.captionOpen || options.captionPosition)
														{
																options.extend = function()
																{
																		if(options.captionOpen)
																		{
																				jQuery('.galleria-info-text, .galleria-info-close', jQuery(galleria_elem)).show();
																				jQuery('.galleria-info-link', jQuery(galleria_elem)).hide();
																		}
																		if(options.captionPosition)
																		{
																				jQuery('.galleria-info', jQuery(galleria_elem)).addClass(options['caption_position']);
																		}
																}
														};

                            if(options.gallery_size == 'custom')
                            {
                                if(options.custom_gallery_size_w_unit == '%')
                                {
                                    var parent_width = $(galleria_elem).parent().width();
                                    options.width = parent_width * options.width / 100;
                                }

                                if(options.custom_gallery_size_h_unit == '%')
                                {
                                    var parent_height = $(galleria_elem).parent().height();
                                    options.height = parent_height * options.height / 100;
                                }
                            }

                            Galleria.run('#galleria-' + gid, options);
												});
};