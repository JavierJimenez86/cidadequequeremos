$(document).ready(function()
{	
	var upload = new Upload();
	
	$('#select-picture').click(function()
	{
		$('#picture-file').click();		
	});
	
	$('#picture-uploaded a').click(function()
	{
		$(this).parent().remove();		
	})
});

function Upload()
{
	var parent = this;
	
	$('#picture-file').change(function()
	{
		files = document.getElementById('picture-file').files;	
		current_file = 0;
		parent.upload();	
	});

	var current_file = 0;
	var files = null; 
	var progress_bar = [];
	var display_width = 200;

	this.upload = function()
	{	
		if(current_file == 0)
		{
			for(var i = 0; i < files.length; i++)
			{
				progress_bar[i] = $('<div></div>').addClass('picture-upload').css('width', display_width).append($('<span></span>'));	
				
				$('#picture-uploaded').append(progress_bar[i]);
			}
		}
	
		if(current_file < files.length)
		{
			parent.sendFile(files[current_file]);	
		} 
	
	}
	
	this.sendFile = function(file)
	{	
		var xhr = new XMLHttpRequest();
				
		var fd = new FormData();
		fd.append('file', file);
		//fd.append('upload_folder', folder);

		xhr.upload.addEventListener('progress', this.uploadProgress, false);
		xhr.addEventListener('load', this.uploadComplete, false);
		xhr.addEventListener('error', this.uploadFailed, false);
		xhr.addEventListener('abort', this.uploadCanceled, false);
	
		xhr.open('POST', ROOT_URL + '/picture-upload.php');
		xhr.send(fd);	
	}
	
	this.uploadProgress = function(evt) 
	{
		if (evt.lengthComputable) 
		{
			var percentComplete = Math.round(evt.loaded * display_width / evt.total);
			
			if(percentComplete == display_width){}

			$(progress_bar[current_file]).children('span').css('width', percentComplete);
		}
	}
	
	this.uploadComplete = function(evt) 
	{  
		if(evt.target.responseText.indexOf('file:') == -1)
		{
			$(progress_bar[current_file]).html('<em>' + evt.target.responseText + '</em>');
				
		} else {	
			var picture = evt.target.responseText.replace('file:', '');
			
			$(progress_bar[current_file]).find('span').remove();	
			$(progress_bar[current_file]).append(
				$('<img/>')
				.attr('src', FILES_URL + '/temp/thumbnail/' + picture)
				.attr('title', picture)
				.attr('width', display_width)
			).append(
				$('<label><label/>')
				.attr('for', 'legends[]')
				.text('Legenda (opcional)')
			).append(
				$('<textarea/>')
				.attr('name', 'legends[]')
			).append(
				$('<input/>')
				.attr('type', 'hidden')
				.attr('name', 'pictures[]')
				.attr('value', picture)
			).append(
				$('<a></a>')
				.attr('href', 'javascript:void(0)')
				.attr('title', 'Remover')
				.text('Remover')
				.click(function()
				{
					$(this).parent().remove();		
				})
			);
		}
		
		parent.nextUpload();
	}
	
	this.uploadFailed = function(evt) 
	{
		$(progress_bar[current_file]).text(evt.responseText);
		parent.nextUpload();
	}
	
	this.uploadCanceled = function(evt) 
	{
		$(progress_bar[current_file]).text(evt.responseText);
		parent.nextUpload();
	}
	
	this.nextUpload = function()
	{
		current_file++;
		parent.upload();	
	}	
}