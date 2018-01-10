/*
@author: Hedi Minin
@version: 1.1.4
@date 31/08/2014
*/
var _datepicker = { 
	  dateFormat: 'dd/mm/yy',
	  monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto','Setembro','Outubro', 'Novembro', 'Dezembro'],
	  dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
	  prevText: 'Anterior',
	  nextText: 'Próximo'
};

$(document).ready(function()
{	
	jform_parse();
	
	$('#jform-add').click(function()
	{	
		$('#jform').toggle();
	});
});

function jform_parse()
{
	if ($('.jform').is('[enctype]'))
	{
		$('.jform').find('input:submit').parent().append(
			$('<span></span>').addClass('jform-upload-loader').text('Aguarde...')	
		);
		
		$('.jform').submit(function()
		{
			$('.jform-upload-loader').css('visibility', 'visible'); //.attr('value', 'Enviando...');
		});
	}
	
	setTimeout(function()
	{
		$('.jform-messages').animate({'opacity': 0}, 1000, function(){ $(this).remove(); });	

	}, 4000);

	$('.jform input[disabled]').each(function()
	{
		$(this).addClass('jform-input-disabled');	
	});
	
	$('.jform-errors, .jform-warnings').append(
		$('<a></a>')
		.attr('Title', 'Fechar mensagem')
		.attr('href', 'javascript:void(0)')
		.click(function()
		{
			$(this).parent().remove();		
		})
	);

	jform_parse_radio();
	jform_parse_checkbox();	
	jform_parse_file();
	jform_parse_select();
	jform_tree();
}

function jform_check_all(parent)
{	
	var inputs = $(parent).find('input:checked');
	
	if ($(inputs).length == 0)
	{
		$(parent).find('input:checkbox').each(function()
		{	
			$(this).prop('checked', true);
			$(this).parent().removeClass('jform-checkbox-unchecked').addClass('jform-checkbox-checked');
		});	
	}
	else
	{
		$(parent).find('input:checkbox').each(function()
		{	
			$(this).prop('checked', false);
			$(this).parent().removeClass('jform-checkbox-checked').addClass('jform-checkbox-unchecked');
		});
	}
}

function jform_tree()
{
	$('.jform-tree-list a').click(function()
	{	
		$(this).parent().children('div').toggle();
		$(this).toggleClass('jform-tree-open');
	});
}

function jform_parse_radio()
{
	$('.jform input:radio').each(function()
	{	
		$(this).css('opacity', 0);
		$(this).parent().removeClass('jform-radio-checked').addClass('jform-label-input').addClass('jform-radio-unchecked');
		$(this).parent().find('input:checked').parent().removeClass('jform-radio-unchecked').addClass('jform-radio-checked');

		$(this).focus(function()
		{
			$(this).parent().addClass('jform-label-input-focus');	
		});
		
		$(this).blur(function()
		{
			$(this).parent().removeClass('jform-label-input-focus');	
		});
		
		$(this).parent().click(function()
		{ 
			$(this).removeClass('jform-label-input-focus'); 
		});

	});	
	
	$('.jform label').click(function()
	{	
		if($(this).find('input:radio').length > 0)
		{
			$(this).parent().find('label:not([for])').removeClass('jform-radio-checked').addClass('jform-radio-unchecked'); //not
			$(this).find('input:checked').parent().removeClass('jform-radio-unchecked').addClass('jform-radio-checked');

		}
	});
}

function jform_parse_checkbox()
{
	$('.jform input:checkbox').each(function()
	{	
		$(this).css('opacity', 0);
		$(this).parent().removeClass('jform-checked').addClass('jform-label-input').addClass('jform-checkbox-unchecked');
		$(this).parent().find('input:checked').parent().removeClass('jform-checkbox-unchecked').addClass('jform-checkbox-checked');
		
		$(this).focus(function()
		{
			$(this).parent().addClass('jform-label-input-focus');	
		});
		
		$(this).blur(function()
		{
			$(this).parent().removeClass('jform-label-input-focus');	
		});
		
		$(this).parent().click(function()
		{ 
			$(this).removeClass('jform-label-input-focus'); 
		});

	});
	
	$('.jform label').click(function()
	{
		if($(this).find('input:checkbox').length > 0)
		{
			$(this).removeClass('jform-checkbox-checked').addClass('jform-checkbox-unchecked');
			$(this).find('input:checked').parent().removeClass('jform-checkbox-unchecked').addClass('jform-checkbox-checked');	
		}
	});
}

function jform_parse_select()
{
	$('.jform select').each(function()
	{
		$(this)
		.change(function()
		{
			$(this).parent().children('input').val($('option:selected', this).text());	
		})
		.keyup(function()
		{
			$(this).parent().children('input').val($('option:selected', this).text());	
		})
		.focus(function()
		{
			$(this).parent().children('input').addClass('jform-select-focus');		
		})
		.blur(function()
		{
			$(this).parent().children('input').removeClass('jform-select-focus');	
		})
		.css('opacity', 0)
		.parent()
		.append(
			$('<input/>')
			.attr('type', 'text')
			.attr('readonly', 'readonly')
			.attr('disabled', 'disabled')
			.addClass('jform-select')
			//.css('width', '80%')
			.val($('option:selected', this).text())
		);
	})	
}

function jform_parse_file()
{
	$('.jform input:file').each(function()
	{	
		var label = $(this).is('[multiple]') ? 'Escolher arquivos...' : 'Escolher arquivo...';
	
		$(this)
		.css('visibility', 'hidden')
		.css('position', 'absolute')
		.css('top', 0)
		.css('left', 0)
		.change(function()
		{
			var text = $(this).val().split('\\').pop();

			if ($(this).is('[multiple]'))
			{
				if (this.files.length > 0)
				{
					text = this.files.length == 1 ? '1 arquivo selecionado' : this.files.length + ' arquivos selecionados';
				}
			}

			$(this).parent().children('span').text(text ? text : 'Nenhum arquivo selecionado');
		})
		.parent()
		.append(
			$('<a></a>')
			.attr('href', 'javascript:void(0)')
			.attr('title', $(this).prop('title'))
			.text(label)
			.addClass('jform-button')
			.addClass('jform-file')
			.click(function()
			{
				$(this).parent().children('input:file').click();	
			})
		)
		.append(
			$('<span></span>').text('Nenhum arquivo selecionado')
		);
	})	
}

jQuery.fn.extend({
	jFormRequestResult: function(options)
	{
		var defaults = {
			has_errors: false,
			has_messages: false,
			has_warnings: false,
			errors: [],
			messages: [],
			warnings: []
		};	
		
		var opt = $.extend(defaults, options);
		
		if (opt.has_errors)
		{
			var ul = $('<ul></ul>').addClass('jform-errors');
			
			for(var i in opt.errors)
			{
				$(ul).append(
					$('<li></li>').text(opt.errors[i])
				);	
			}
			
			$(this).append(ul);
		}
		
		if (opt.has_messages)
		{
			var ul = $('<ul></ul>').addClass('jform-messages');
			
			for(var i in opt.messages)
			{
				$(ul).append(
					$('<li></li>').text(opt.messages[i])
				);	
			}
			
			$(this).append(ul);
		}
		
		if (opt.has_warnings)
		{
			var ul = $('<ul></ul>').addClass('jform-warnings');
			
			for(var i in opt.warnings)
			{
				$(ul).append(
					$('<li></li>').text(opt.warnings[i])
				);	
			}
			
			$(this).append(ul);
		}
	}		
});

jQuery.fn.extend({
	selectLoad: function(opt)
	{
		var options = {
			url: null,
			data: null,
			required: true,
			empty: null,
			key: null,
			label: null,
			hkey: 0,
			hlabel: null
		};

		if (typeof(opt) == 'object') options = $.extend(options, opt);
	
		var select = this;
		
		this.displayLoader = function()
		{
			$(select).parent().children('input').val('Carregando...');
			$(select).parent().children('label').addClass('jform-select-loader');	
		}
		
		this.hideLoader = function()
		{
			$(select).parent().children('input').val($('option:selected', select).text());
			$(select).parent().children('label').removeClass('jform-select-loader');	
		}
		
		this.addOption = function(value, label)
		{
			$(select).append($('<option></option>').attr('value', value).html(label));	
		}
		
		$(select).empty();
		select.displayLoader();
		
		$.ajax({
			url: options.url,
			type: 'POST',
			data: $.extend(options.data, {jsonResult: true}),
			success: function(result)
			{
				if (result.hasErrors)
				{
					$(select).empty();
					select.addOption(0, 'Falha ao carregar Itens');
					select.hideLoader();
					alert(result.errors[0]);
					return true;	
				}
				
				var header_label = options.required ? 'Selecione' : 'Não informado';

				if (options.hlabel)
				{
					header_label = options.hlabel;	
				}

				if (options.empty)
				{
					header_label = result.length == 0 ? options.empty : header_label;	
				}
				
				if (options.hlabel != false)
				{
					select.addOption(options.hkey, header_label);	
				}
	
				if (options.tpl)
				{
					for(var i in result)
					{
						var line = result[i];
						var label = options.tpl.replace(/ /g, '\u00a0');
						var labels = options.label.split(',');

						for (var j in labels)
						{
							label = label.replace('{' + labels[j] + '}', line[labels[j]]);
						}
						
						select.addOption(line[options.key], label);
					}	
				}
				else
				{
					if (options.key)
					{
						for (var i in result)
						{
							var line = result[i];
							select.addOption(line[options.key], line[options.label]);	
						}
					}
					else
					{
						for (var i in result)
						{
							select.addOption(i, result[i]);	
						}
					}
				}

				select.hideLoader();
			},
			error: function()
			{
				$(select).empty();
				select.addOption(0, 'Falha ao carregar Itens');
				select.hideLoader();
			}
		});
	}
});

jQuery.fn.extend({
	listLoad: function(opt)
	{
		var options = {
			url: null,
			data: null,
			empty: null,
			key: null,
			label: null,
			name: null,
			type: 'checkbox',
			onchange: function() {}
		};

		if (typeof(opt) == 'object') options = $.extend(options, opt);
	
		var list = this;

		this.addLabel = function(value, label)
		{
			$(list).append(
				$('<label></label>')
				.append(
					$('<input/>').attr('type', options.type).attr('name', options.name).attr('value', value).change(
					function() 
					{ 
						options.onchange(this); 
					})
				)
				.append(label)
			);	
		}
		
		$(list).empty().text('Carregando...');
		
		$.ajax({
			url: options.url,
			type: 'POST',
			data: $.extend(options.data, {jsonResult: true}),
			success: function(result)
			{
				$(list).empty();
				
				if (result.hasErrors)
				{
					alert(result.errors[0]);
					return true;	
				}

				if (result.length == 0)
				{
					$(list).text(options.empty);	
				}

				if (options.tpl)
				{
					for (var i in result)
					{
						var entity = result[i];
						var label = options.tpl.replace(/ /g, '\u00a0');
						var labels = options.label.split(',');

						for (var j in labels)
						{
							label = label.replace('{' + labels[j] + '}', entity[labels[j]]);
						}
						
						list.addLabel(entity[options.key], label);
					}	
				}
				else
				{
					if (options.key)
					{
						for (var i in result)
						{
							var line = result[i];
							list.addLabel(line[options.key], line[options.label]);	
						}
					}
					else
					{
						for (var i in result)
						{
							list.addLabel(i, result[i]);	
						}
					}
				}
				
				jform_parse_checkbox();
				jform_parse_radio();
			},
			error: function()
			{
				$(list).empty().text('Falha ao carregar Lista');
			}
		});
	}
});