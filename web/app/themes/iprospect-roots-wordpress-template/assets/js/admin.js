jQuery(function(){

	var $ = jQuery,
		spinner = "<div class='spinner' style='float:none; visibility:visible;'></div>";

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
					showSpinner($(this).find(".ui-dialog-buttonset button:last"));
					$(this).find("form").submit();
				}
			}
		});
	}

	function showSpinner(el)
	{
		el.after($(spinner));
	}

	function hideSpinner(el)
	{
		el.parent().find(".spinner").remove();
	}

	$('button.custom-languages.add').click(function(evt) {
		var el = $(this);
		showSpinner(el);
		$.ajax({
			url: WpConfig.ajaxurl,
			method: 'POST',
			data: {
				action: 'addNewLocale',
				security: WpConfig.security
			}
		}).done(function(html) {
			hideSpinner(el);
			createDialogFromHtml(html);
		});
	});

	$('table.custom-languages .edit').click(function(evt) {
		var el = $(this);
		showSpinner(el);
		$.ajax({
			url: WpConfig.ajaxurl,
			method: 'POST',
			data: {
				action: 'editLocaleLabels',
				language: this.value,
				security: WpConfig.security
			}
		}).done(function(html) {
			hideSpinner(el);
			createDialogFromHtml(html);
		});
	});

	$('table.custom-languages .delete').click(function(evt) {
		var el = $(this);
		showSpinner(el);
		$.ajax({
			url: WpConfig.ajaxurl,
			method: 'POST',
			data: {
				action: 'deleteLocale',
				language: this.value,
				security: WpConfig.security
			}
		}).done(function(html){
			hideSpinner(el);
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
						showSpinner($(this).find(".ui-dialog-buttonset button:last"));
						$(this).find("form").submit();
					}
				}
			});
		});
	});

	/**
	 * Custom post listing page
	 * When user clicks on the button, pop a window so user confirm it will duplicate
	 * the original post.
	 */
	$('table.amnet-global-posts a.untranslated, #amnet-language-metabox a.untranslated').click(function(event){
		event.preventDefault();
		var el = $(this);
		showSpinner(el);
		$.ajax({
			url: WpConfig.ajaxurl,
			method: 'POST',
			data: {
				action: 'confirmDuplication',
				security: WpConfig.security,
				postId: parseInt($(this).attr("data-pid"), 10),
				language: $(this).attr("data-lang"),
			}
		}).done(function(html){
			hideSpinner(el);
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
					"Duplicate": function() {
						$(this).find("form").submit();
					}
				}
			});
		});
	});

	// Post edit page
	$('#wpml-language-switch').change(function(evt) {
		window.location = $(this).val();
	});

	/**
	 * Region association droppables
	 */
	if ($('.manage-regions').length > 0) {
		$('.manage-regions .drag').draggable({revert: true});
	    $('.manage-regions .drop').droppable({
		    drop: function(ev, ui) {

		    	var locale = $(ui.draggable);

		    	// Validate no duplicates
		    	if ($(this).find('input[value="'+ locale.find("input").val() +'"]').length > 0) {
		    		return false;
		    	}

		    	var el = locale.clone(true),
		    		input = el.find("input"),
		    		drop = $(this),
		    		regionSlug = drop.attr("data-region-slug"),
		    		subregionIdx = drop.attr("data-subregion-idx");

	    		el.removeClass("drag");
	    		el.find("span").before('<span class="close">&times; </span>');

		    	if (input.attr("name").match(/regionMap\[\]/)) {
	    			input.attr("name", input.attr("name").replace("regionMap[]", "regionMap["+regionSlug+"]"));
	    			input.attr("name", input.attr("name").replace("[0]", "[" + subregionIdx + "]"));
		       	} else {
		       		input.attr("name", input.attr("name").replace(/regionMap\[[\w\d-]+\]/, "regionMap["+regionSlug+"]"));
	    			input.attr("name", input.attr("name").replace(/\[\d+\]/, "[" + subregionIdx + "]"));
	    		}

		        el.css({top: 0,left: 0}).appendTo(drop);
		    }
		});

		// Allow removal of locale pills
		$('.manage-regions .drop').click(function(evt){
			var target = $(evt.target);
			if (target.hasClass("close")) {
				target.parents(".locale-pill").remove();
			}
		});
	}
});
