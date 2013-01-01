Galleriapress.picasa =
{
    init: function()
    {
        $('.libraries-tabs .picasa').on(
            'click',
            this.calculate_grid_height);

        $('#galleriapress-libraries').on(
            'resize',
            this.calculate_grid_height);
    },

    calculate_grid_height: function()
    {
        $('#picasa-library .grid').height($('#galleriapress-libraries').height() - $('.picasa-menu').outerHeight(true));
    }
};

jQuery(document).ready(function()
											 {
													 Galleriapress.picasa.init();
											 });
