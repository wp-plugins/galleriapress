$ = jQuery.noConflict();

$(document).ready(function()
									{
											$('#gallery-settings').load(ajaxurl,
																									{
																											action: 'galleriapress_wordpress_gallery_options',
																									},
																									function(response)
																									{
																											var gallery_update = function()
																											{
																												var t = wpgallery, ed = t.editor, all = '', s;
																												var gid = $('select[name=gid]').val();
																												setUserSetting('gid', gid);

																												if(gid > 0)
																													s = '[gallery'+ ' gid=' + gid + ']';
																												else
																													s = '[gallery]';

																												if ( ! t.mcemode || ! t.is_update ) 
																												{
																													t.getWin().send_to_editor(s);
																													return;
																												}

																												if (t.el.nodeName != 'IMG') return;

																												if(gid > 0)
																													all = 'gallery'+ ' gid=' + gid + '';
																												else
																													all = 'gallery';

																												ed.dom.setAttrib(t.el, 'title', all);
																												t.getWin().tb_remove();
																											}

																											if(getUserSetting('gid'))
																												$('select[name=gid]').val(getUserSetting('gid'));

																											$('#update-gallery').mousedown(gallery_update);

																											if(!wpgallery.is_update)
																												$('#update-gallery').val('Insert Gallery');
																									});
									});