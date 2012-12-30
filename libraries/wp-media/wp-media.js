Galleriapress.wp_media =
{
    init: function()
    {
        $('.libraries-tabs .wp_media').on(
            'click',
            this.calculate_grid_height);

        $('#galleriapress-libraries').on(
            'resize',
            this.calculate_grid_height);

        this.calculate_grid_height();
    },

    calculate_grid_height: function()
    {
        $('#wp_media-library .grid').height($('#galleriapress-libraries').height() - $('.wp_media-toolbar').outerHeight(true));
    }
};

jQuery(document).ready(function()
											 {
													 Galleriapress.wp_media.init();
											 });
