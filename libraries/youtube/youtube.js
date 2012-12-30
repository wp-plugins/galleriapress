Galleriapress.youtube = 
{
    init: function()
    {
        $('.libraries-tabs .youtube').on(
            'click',
            this.calculate_grid_height);


        $('#galleriapress-libraries').on(
            'resize',
            this.calculate_grid_height);
    },

    calculate_grid_height: function()
    {
        $('#youtube-library .grid').height($('#galleriapress-libraries').height() - $('.youtube-toolbar').outerHeight(true));
    }
}

$(document).ready(
    function()
    {
        Galleriapress.youtube.init();
    });