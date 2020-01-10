function _getAllValuesCustomClose() {
	var options = {};
	options['color'] = jQuery('.doclosebtn-text-color').val();
	options['backgroundColor'] = jQuery('.doclosebtn-bg-color').val();
	options['fontsize'] = parseFloat( jQuery('.post_doclosebtn_fontsize').val() );
	options['borderRadius'] = parseFloat( jQuery('.post_doclosebtn_borderradius').val() );
	options['padding'] = parseFloat( jQuery('.post_doclosebtn_padding').val() );
	
	return options;
};

jQuery(document).ready(function( $ ) {
    
	CustomClose = new CustomClose( _getAllValuesCustomClose() );
	CustomClose.update();
	
	
	$('.doclosebtn-text-color').wpColorPicker({
		clear: function() {

			CustomClose.color = '';
			CustomClose.update();
		},
		change: function(event, ui) {
			
			var hexcolor = jQuery( this ).wpColorPicker( 'color' );
			
			CustomClose.color = hexcolor;
			CustomClose.update();
		}
	});
	
	
	$('.doclosebtn-bg-color').wpColorPicker({
		clear: function() {

			CustomClose.backgroundColor = '';
			CustomClose.update();
		},
		change: function(event, ui) {
			
			var hexcolor = jQuery( this ).wpColorPicker( 'color' );
			
			CustomClose.backgroundColor = hexcolor;
			CustomClose.update();
		}
	});
	
	
	/* Close Button cookie */
	$('#slider-doclosebtn-cookie').slider({
		value: 0,
		min: 0,
		max: 99,
		step: 1,
		slide: function(event, ui) {
			var val = do_getFromField(ui.value, 0, 99);
			
			$('.dov_closebtn_cookie').val(val);
		}
	});
	
	/* Close Button font size */
	$('#slider-doclosebtn-fontsize').slider({
		value: 25,
		min: 25,
		max: 250,
		step: 1,
		slide: function(event, ui) {
			var val = do_getFromField(ui.value, 25, 250);
			CustomClose.fontsize = val;
			CustomClose.update();
			
			$('.post_doclosebtn_fontsize').val(val);
		}
	});
	
	var default_val = $('.post_doclosebtn_fontsize').val();
	$('#slider-doclosebtn-fontsize').slider('value', default_val);
	
	
	/* Close Button border radius */
	$('#slider-doclosebtn-borderradius').slider({
		value: 0,
		min: 0,
		max: 50,
		step: 1,
		slide: function(event, ui) {
			var val = do_getFromField(ui.value, 0, 50);
			CustomClose.borderRadius = val;
			CustomClose.update();
			
			$('.post_doclosebtn_borderradius').val(val);
		}
	});
	
	var default_val = $('.post_doclosebtn_borderradius').val();
	$('#slider-doclosebtn-borderradius').slider('value', default_val);
	
	
	/* Close Button padding */
	$('#slider-doclosebtn-padding').slider({
		value: 0,
		min: 0,
		max: 99,
		step: 1,
		slide: function(event, ui) {
			var val = do_getFromField(ui.value, 0, 99);
			CustomClose.padding = val;
			CustomClose.update();
			
			$('.post_doclosebtn_padding').val(val);
		}
	});
	
	default_val = $('.post_doclosebtn_padding').val();
	$('#slider-doclosebtn-padding').slider('value', default_val);
});

jQuery( function ( $ ) {
	
	if ( $('#do_displaylocations_meta_box1').length ) {
	
		$(".chosen").select2({
			width: '100%',
			theme: "classic",
			minimumResultsForSearch: Infinity
		});
		
		$(".do-list-pages").select2({
			dropdownParent: $('#do_displaylocations_meta_box1'),
			width: '100%',
			theme: "bootstrap",
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				delay: 250,
				method: 'POST',
				data: function (params) {
				  return {
					action: 'ajax_do_listposts',
					q: params.term,
					page: params.page,
					json: 1
				  };
				},
				processResults: function (data, params) {
				  params.page = params.page || 1;
				  
				  return {
					results: data.items,
					pagination: {
					  more: (params.page * 7) < data.total_count
					}
				  };
				},
				cache: true
			},
			minimumInputLength: 1,
			escapeMarkup: function (markup) { return markup; },
			templateResult: doformatPostResults,
			templateSelection: doformatPostTitle
		});
		
		$(".do-list-categories").select2({
			dropdownParent: $('#do_displaylocations_meta_box1'),
			width: '100%',
			theme: "bootstrap",
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				delay: 250,
				method: 'POST',
				data: function (params) {
				  return {
					action: 'ajax_do_listcategories',
					q: params.term,
					page: params.page,
					json: 1
				  };
				},
				processResults: function (data, params) {
				  params.page = params.page || 1;
				  
				  return {
					results: data.items,
					pagination: {
					  more: (params.page * 7) < data.total_count
					}
				  };
				},
				cache: true
			},
			closeOnSelect: false,
			minimumInputLength: 1,
			escapeMarkup: function (markup) { return markup; },
			templateResult: doformatCatResults,
			templateSelection: doformatCatName
		});
		
		$(".do-list-tags").select2({
			dropdownParent: $('#do_displaylocations_meta_box1'),
			width: '100%',
			theme: "bootstrap",
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				delay: 250,
				method: 'POST',
				data: function (params) {
				  return {
					action: 'ajax_do_listtags',
					q: params.term,
					page: params.page,
					json: 1
				  };
				},
				processResults: function (data, params) {
				  params.page = params.page || 1;
				  
				  return {
					results: data.items,
					pagination: {
					  more: (params.page * 7) < data.total_count
					}
				  };
				},
				cache: true
			},
			closeOnSelect: false,
			minimumInputLength: 1,
			escapeMarkup: function (markup) { return markup; },
			templateResult: doformatTagResults,
			templateSelection: doformatTagName
		});
		
		
		$('body').on('click','.enableurltrigger', function(event){
			
			if( $(this).is(':checked') ) {
			
				$('.enableurltrigger_filters').addClass('do-show');
				
			} else {
				
				$('.enableurltrigger_filters').removeClass('do-show');
			}
		});
		
		$('body').on('change','.do-filter-by-pages, .do-filter-by-categories, .do-filter-by-tags', function(event){
			
			var type_pages = $(this).val();
			
			if ( type_pages == 'specific' ) {
				
				$(this).parent().find('.do-list-pages-container').addClass('do-show');
			}
			else {
				
				$(this).parent().find('.do-list-pages-container').removeClass('do-show');
			}
		});
		
		
		$('body').on('click','.enable_custombtn', function(event){
			
			if( $(this).is(':checked') ) {
			
				$('.enable_customizations').addClass('do-show');
				
			} else {
				
				$('.enable_customizations').removeClass('do-show');
			}
		});
		
		
		$('body').on('change','.post_overlay_automatictrigger', function(event){
			
			var type_at = $(this).val();
			
			if ( type_at == 'overlay-timed' ) {
				
				$('.divi_automatictrigger_timed').addClass('do-show');
			}
			else {
				
				$('.divi_automatictrigger_timed').removeClass('do-show');
			}
			
			
			if ( type_at == 'overlay-scroll' ) {
				
				$('.divi_automatictrigger_scroll').addClass('do-show');
			}
			else {
				
				$('.divi_automatictrigger_scroll').removeClass('do-show');
			}
			
			
			if ( type_at.length > 1 ) {
				
				$('.do-at-devices').addClass('do-show');
				$('.do-at-onceperload').addClass('do-show');
				$('.do-at-scheduling').addClass('do-show');
				
			} else {
				
				$('.do-at-devices').removeClass('do-show');
				$('.do-at-onceperload').removeClass('do-show');
				$('.do-at-scheduling').removeClass('do-show');
			}
		});
		
		$('body').on('click','[data-showhideblock]', function(event){
			
			var block_content = $(this).data('showhideblock');
			
			if ( $(this).is(':checked') ) {
			
				$( block_content ).addClass('do-show');
				
			} else {
				
				$( block_content ).removeClass('do-show');
			}
		});
		
		
		$('[data-dropdownshowhideblock]').change(function() {
			
			var dropdown_id = $(this).attr('id')
			, showhideblock = $(this).find(':selected').data('showhideblock');
			
			$(this).find('option[data-showhideblock]').each(function() {
				
				var elemRef = $(this).data('showhideblock');
				
				$( elemRef ).removeClass('do-show');
			});
			
			if ( showhideblock !== undefined ) {
				
				$( showhideblock ).addClass('do-show');
			}
		});
	}
	
	
	// Scheduling
	var select = 'select[name="dov_settings[dov_timezone]"]';
	if ( $( select ).length ) {
		
		var timezones = moment.tz.names(), timezone_title;
		
		for (var i = 0; i < timezones.length; i++) {
			
			timezone_title = timezones[i].replace('_', ' ');
			
			$( select ).append('<option value="' + timezones[i] + '">' + timezone_title + '</option>');
		}
		
		$( select ).selectpicker({
			
			liveSearch: true
			
		}).on('loaded.bs.select', function (e) {
			
			var default_value = $( select ).data('defaultvalue');
			
			if ( default_value != '' ) {
			
				$( select ).selectpicker('val', default_value);
			}
		});
	}
	
    $('input[name="do_date_start"]').datetimepickerr({
        sideBySide: true,
		showTodayButton: true,
		stepping: 30, // Number of minutes the up/down arrow's will move the minutes value in the time picker
		widgetPositioning: {
			horizontal: 'auto',
			vertical: 'top'
		},
		minDate: moment().subtract(1, 'days'),
		format: 'MM/DD/YYYY h:mm A',
		showClear: true,
		toolbarPlacement: 'bottom',
		defaultDate : moment()
    }, 
    function(start, end, label) {
		
		
	});
	
    $('input[name="do_date_end"]').datetimepickerr({
		sideBySide: true,
		stepping: 30, // Number of minutes the up/down arrow's will move the minutes value in the time picker
		widgetPositioning: {
			horizontal: 'auto',
			vertical: 'top'
		},
		minDate: moment().subtract(1, 'days'),
		format: 'MM/DD/YYYY h:mm A',
		showClear: true,
		toolbarPlacement: 'bottom',
		defaultDate : moment(),
		useCurrent: false
    }, 
    function(start, end, label) {
		
		
	});
	
	$('input[name="do_time_start"]').datetimepickerr({
		format: 'LT',
		stepping: 30, // Number of minutes the up/down arrow's will move the minutes value in the time picker
		widgetPositioning: {
			horizontal: 'auto',
			vertical: 'top'
		},
		showClear: true,
		format: 'h:mm A',
		toolbarPlacement: 'bottom'
	}, 
    function(start, end, label) {
		
		
	});
	
	$('input[name="do_time_end"]').datetimepickerr({
		format: 'LT',
		stepping: 30, // Number of minutes the up/down arrow's will move the minutes value in the time picker
		widgetPositioning: {
			horizontal: 'auto',
			vertical: 'top'
		},
		showClear: true,
		format: 'h:mm A',
		toolbarPlacement: 'bottom'
	}, 
    function(start, end, label) {
		
		
	});
	
	$('input[name="do_date_start"],input[name="do_date_end"],input[name="do_time_start"],input[name="do_time_end"]').on("dp.change", function (e) {
		
		$('.do-recurring-user-msg').removeClass('do-show');
	});
	
	$('.post-type-divi_overlay form#post').submit(function(e) {
		
		var recurring_msg = $('.do-recurring-user-msg'), recurring_msg_position;
		
		// Check Start & End Time date values
		if ( $('.divioverlay-enable-scheduling option:selected').val() == 1 ) {
			
			var do_date_start = $('input[name="do_date_start"]'),
			do_date_start_value = do_date_start.val(),
			do_date_end = $('input[name="do_date_end"]')
			do_date_end_value = do_date_end.val(),
			date_start = '',
			date_end = '';
			
			if ( do_date_start_value != '' && do_date_end_value != '' ) {
				
				date_start = moment( do_date_start_value, 'MM/DD/YYYY h:mm A');
				date_end = moment( do_date_end_value, 'MM/DD/YYYY h:mm A');
				
				if ( date_start.isBefore( date_end ) === false ) {
					
					do_date_end.focus();
					
					recurring_msg.html('<p><strong>"End date"</strong> field should be greater than <strong>"Start date"</strong> field.</p>').addClass('do-show').removeClass('do-hide');
					
					recurring_msg_position = recurring_msg.offset().top - 100;
					$('html,body').animate({ scrollTop: recurring_msg_position }, 500);
					
					return false;
				}
			}
		}
		
		// Check Recurring Scheduling time values
		if ( $('.divioverlay-enable-scheduling option:selected').val() == 2 ) {
		
			var do_time_start = $('input[name="do_time_start"]')
			do_time_start_value = do_time_start.val(),
			do_time_end = $('input[name="do_time_end"]'),
			do_time_end_value = do_time_end.val(),
			time_start = '',
			time_end = '';
			
			if ( do_time_start_value != '' && do_time_end_value != '' ) {
				
				time_start = moment( do_time_start_value, 'h:mm A');
				time_end = moment( do_time_end_value, 'h:mm A');
				
				if ( time_start.isBefore( time_end ) === false ) {
					
					do_time_end.focus();
					
					recurring_msg.html('<p><strong>"End Time"</strong> field should be greater than <strong>"Start Time"</strong> field.</p>').addClass('do-show').removeClass('do-hide');
					
					recurring_msg_position = recurring_msg.offset().top - 100;
					$('html,body').animate({ scrollTop: recurring_msg_position  }, 500);
					
					return false;
				}
			}
		}
	});
});

function doformatPostResults ( post ) {
	
	var post_title = doformatPostTitle( post );
	
	if ( post.loading ) {
		return post.text;
	}
	
    if ( typeof post_title === 'undefined' ) {
		post_title = 'Post without Title';
    }

	var markup = "<div class='select2-result-post clearfix'>" +
	"<div class='select2-result-post__meta'>" +
	  "<div class='select2-result-post__title'>" + post.id + " : " + post_title + "</div>";

	markup += "</div></div>";
	
	if ( post.id == 0 ) {
		
		markup = post_title;
	}

	return markup;
}

function doformatPostTitle (post) {
	return post.post_title || post.text;
}


function doformatCatResults ( category ) {
	
	var cat_name = doformatCatName( category );
	
	if ( category.loading ) {
		return category.text;
	}
	
    if ( typeof cat_name === 'undefined' ) {
		cat_name = 'Category without Name';
    }

	var markup = "<div class='select2-result-cat clearfix'>" +
	"<div class='select2-result-cat__meta'>" +
	  "<div class='select2-result-cat__name'>" + category.id + " : " + cat_name + "</div>";

	markup += "</div></div>";
	
	if ( category.id == 0 ) {
		
		markup = cat_name;
	}

	return markup;
}

function doformatCatName ( category ) {
	return category.name || category.text;
}


function doformatTagResults ( tag ) {
	
	var tag_name = doformatTagName( tag );
	
	if ( tag.loading ) {
		return tag.text;
	}
	
    if ( typeof tag_name === 'undefined' ) {
		tag_name = 'Tag without Name';
    }

	var markup = "<div class='select2-result-tag clearfix'>" +
	"<div class='select2-result-tag__meta'>" +
	  "<div class='select2-result-tag__name'>" + tag.id + " : " + tag_name + "</div>";

	markup += "</div></div>";
	
	if ( tag.id == 0 ) {
		
		markup = tag_name;
	}

	return markup;
}

function doformatTagName ( tag ) {
	return tag.name || tag.text;
}


function CustomClose (options) {
    this.htmlElement = jQuery('.overlay-customclose-btn');
    this.color = options['color'] || '#333333';
	this.backgroundColor = options['backgroundColor'] || '';
	this.fontsize = options['fontsize'] || 25;
	this.borderRadius = options['borderRadius'] || 0;
    this.padding = options['padding'] || 0;
    
};

CustomClose.prototype.update = function () {
	this.htmlElement.css('color', this.color, 'important');
	this.htmlElement.css('background-color', this.backgroundColor, 'important');
	this.htmlElement.css('-moz-border-radius', this.borderRadius + '%', 'important');
	this.htmlElement.css('-webkit-border-radius', this.borderRadius + '%', 'important');
	this.htmlElement.css('-khtml-border-radius', this.borderRadius + '%', 'important');
	this.htmlElement.css('font-size', this.fontsize + 'px', 'important');
	this.htmlElement.css('border-radius', this.borderRadius + '%', 'important');
	this.htmlElement.css('padding', this.padding + 'px', 'important');
};

function do_getFromField(value, min, max, elem) {
	var val = parseFloat(value);
	if (isNaN(val) || val < min) {
		val = 0;
	} else if (val > max) {
		val = max;
	}

	if (elem)
		elem.val(val);

	return val;
}