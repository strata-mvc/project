jQuery(function(){

	var $ = jQuery;

	function createDialogFromHtml(html)
	{
		$(html).dialog({
			'dialogClass'   : 'wp-dialog',
			'modal'         : true,
			'closeOnEscape' : true,
			'width'         : 500,
			'height'        : 500,
			'buttons'       : {
				"Save": function() {
					$(this).find("form").submit();
				}
			}
		});
	}

	$('button.custom-languages.add').click(function(evt) {
		$.ajax({
			url: WpConfig.ajaxurl,
			method: 'POST',
			data: {
				action: 'addNewLocale',
				security: WpConfig.security
			}
		}).done(createDialogFromHtml);
	});

	$('table.custom-languages .edit').click(function(evt) {
		$.ajax({
			url: WpConfig.ajaxurl,
			method: 'POST',
			data: {
				action: 'editLocaleLabels',
				language: this.value,
				security: WpConfig.security
			}
		}).done(createDialogFromHtml);
	});

	$('table.custom-languages .delete').click(function(evt) {
		$.ajax({
			url: WpConfig.ajaxurl,
			method: 'POST',
			data: {
				action: 'deleteLocale',
				language: this.value,
				security: WpConfig.security
			}
		}).done(function(html){
			$(html).dialog({
				'dialogClass'   : 'wp-dialog',
				'modal'         : true,
				'closeOnEscape' : true,
				'width'         : 500,
				'height'        : 300,
				'buttons'       : {
					"Cancel": function() {
						$(this).dialog("close");
					},
					"Delete": function() {
						$(this).find("form").submit();
					}
				}
			});
		});
	});

});
