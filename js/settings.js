jQuery(document).ready(function()
											 {
													 galleriapress_init_profiles();
													 galleriapress_init_controls();

													 jQuery('.galleriapress-settings .choose-profile').change();
											 });


function galleriapress_init_controls()
{
		jQuery('.size select, .gallery-size select').change(function()
																												{
																														var form_table = jQuery(this).parents('.form-table');
																														custom_size = jQuery('.custom-size', form_table);

																														if(jQuery(this).val() == 'custom')
																														{
																																custom_size.slideDown();
																														}
																														else
																														{
																																custom_size.slideUp();
																														}
																												}).trigger('change');

		jQuery('.themes li').click(function()
															 {
																	 jQuery('.themes li').removeClass('active');
																	 jQuery(this).addClass('active');
																	 jQuery('input[name=theme]').val(jQuery(this).data('theme'));
															 });

}

function galleriapress_init_profiles()
{
		jQuery('.galleriapress-settings .choose-profile').change(function()
										 {
												 jQuery('#galleriapress-options .loader').css('display', 'inline-block');

												 jQuery.post(ajaxurl,
																		 {
																				 action: 'galleriapress_profile_settings',
																				 method: 'get',
																				 profile: jQuery(this).val()
																		 },
 																		 function(response)
																		 {
																				 jQuery('.galleriapress-settings').replaceWith(response);

																				 jQuery('.galleriapress-settings').show();

																				 galleriapress_init_profiles();
																				 galleriapress_init_controls();
																		 });
										 });

		jQuery('.galleriapress-settings input[type=button], .galleriapress-settings input[type=submit]').click(function()
										 {
												 form = jQuery(this).closest('form');
												 jQuery('.galleriapress-settings input[name=method]').val(jQuery(this).data('method'));

												 jQuery(form).submit();
										 });


		jQuery('.galleriapress-settings').submit(function(e)
																	 {
																			 e.preventDefault();

																			 jQuery('#galleriapress-options .loader').css('display', 'inline-block');

																			 var form_data = jQuery('.galleriapress-settings').serialize();

																			 jQuery.post(ajaxurl,
																									 form_data,
																									 function(response)
																									 {
																											 jQuery('.galleriapress-settings').replaceWith(response);

																											 jQuery('#galleriapress-options .loader').hide();

																											 galleriapress_init_profiles();
																											 galleriapress_init_controls();
																									 });
																	 });


}