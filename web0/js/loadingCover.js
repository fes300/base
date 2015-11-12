$(window).on('load', function() {
    fadeoutLoadingCover();    
});
$(document).ready(function(){
    $('#loadingCover').click(function(){
        fadeoutLoadingCover();
    });
});

function fadeoutLoadingCover() {
    $("#loadingCover").fadeOut(500,
        function(){
            $("#loadingCover").hide();
            loadIframes();
        }
    );
}

function loadIframes() {
	$('.iframePlaceholder').each(function(){
		$($(this).val()).appendTo($(this).parent());
		$(this).remove();
	});
    $('.objectPlaceholder').each(function(){
        $($(this).val()).appendTo($(this).parent());
        $(this).remove();
    });
}