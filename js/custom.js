// ZERO Clipboard
ZeroClipboard.setDefaults( { moviePath: BASE_URL + '/js/ZeroClipboard/ZeroClipboard.swf' } );

$(function() {
	
	// ZERO Clipboard
	var clip = new ZeroClipboard( $('.d_clip_button') );
	
	clip.on( 'complete', function(client, args) {
		var t = $(this);
		t.text('Copied');
	});

	// Bootstrap Datepicker
	$('.datepicker').datepicker({
		'format' : 'mm/dd/yyyy'
	});

	// Show/Hide sections
	$('.manageSection').click(function(e){
		var section = $(e.currentTarget).attr('data-section');
		var section_child = section + '_child';

		if(section == 'is_recurring')
		{
			if($("input[name='is_recurring']").prop("checked"))
			{
				$("input[name='has_split_pay']").prop( "checked", false );
				$('#has_split_pay_child').addClass('hide');
				$('#has_split_pay_section').addClass('hide');
			}
			else
			{
				$('#has_split_pay_section').removeClass('hide');
			}
		}

		$('#' + section_child).toggleClass('hide');
	});
});

$(function(){
	$("#mt_product_id").change(function(e){
		var product_id = $(e.currentTarget).val();

		var plan_menu = $('#mt_plan_id');

		// Clear all old values
		plan_menu.empty();

		// Show loading
		plan_menu.append($('<option>').text('Loading...').attr('value', ''));

		$.ajax({
		  type: "GET",
		  url: BASE_URL + '/admin/products/get-plans-by-product/' + product_id,
		  data: '',
		  dataType: 'json',
		  success: function(plans)
		  {
		  	// Clear all old values
			plan_menu.empty();

		  	// Add first option
		  	plan_menu.append($('<option>').text('Select').attr('value', ''));

		  	// Loop all other values
		  	$.each(plans, function( index, value ) 
		  	{
			  plan_menu.append($('<option>').text(value).attr('value', index));
			});
		  }
		});
	});
});