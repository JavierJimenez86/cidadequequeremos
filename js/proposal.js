var RecaptchaOptions = {
	theme: 'custom',
	custom_theme_widget: 'recaptcha_widget'
};

$(document).ready(function()
{	
	$(document).scroll(function()
	{
		var document_top = $(window).scrollTop();
		var element_top = $('#comments').offset().top;

		if (element_top < document_top)
		{
			$('#proposal-title').show();	
		}
		else
		{
			$('#proposal-title').hide();	
		}
	});
		
	//hash
	if (hasErrors)
	{
		window.location.hash = 'comment';	
	}
	
	if (commentId != 0)
	{
		window.location.hash = 'comment-' + commentId;
		
		$('#comment-' + commentId).addClass('comment-highlight');
		
		setTimeout(function()
		{
			$('#comment-' + commentId).removeClass('comment-highlight');	
		}, 2000);
	}
	
	//image 
	$('.proposal-picture a').click(function(e)
	{
		e.preventDefault();
		showPicture(this);	
	});
	
	//close by key
	$(document).keydown(function(e)
	{
		if (e.which == 27)
		{
			$('.picture-view').remove();
			$('.picture-overlay').remove();
		}
	});
});

function showPicture(a)
{
	var legend = $(a).parent().children('figcaption').text();
	var src = $(a).attr('href')
	
	$('.picture-view').remove();
	$('.picture-overlay').remove();
	
	$(document.body).append(
		$('<div></div>').addClass('picture-overlay')
	);
	
	$(document.body).append(
		$('<div></div>')
		.addClass('picture-view')
		.append(
			$('<a></a>')
			.attr('href', 'javascript:void(0)')
			.attr('title', 'Fechar')
			.text('Fechar')
			.click(function()
			{
				$('.picture-view').remove();
				$('.picture-overlay').remove();		
			})
		).append(
			$('<img/>').attr('src', src).attr('width', '900')
		).append(
			$('<span><span/>').text(legend)
		)
	);	
	
	$('.picture-view').css('top', $(document.body).scrollTop() + $(window).height() * 0.05);	
}