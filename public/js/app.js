(function(){
    'use strict';

	var version = 'v1';

	// Clear alerts
	function clearAlerts()
	{
		$('.alert').remove();
	}

	// Add alerts
	function addAlert(json)
	{
		var alert;

        if (json.code === 0)
		{ 
			// Clear input fields
			$('form')[0].reset();
			$('.form-group').removeClass('has-success has-feedback');
			
			alert = '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Success! ' + json.message + '</div>';
		}
        
		if (json.code === 1)
		{ 
			alert = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>Oops! ' + json.message + '</div>';
		}
		
		$(alert).insertBefore('form');
	}

	// Populate Retailer drop down list  
    $.getJSON('api/v1/retailers', function(json) 
	{
		$.each(json, function(key, val) 
		{
			$("#select-store").append("<option value=" + json[key].id + ">" + json[key].name + "</option>");
		});
    });
	
	// Populate Location drop down list 
    $.getJSON('api/v1/retailers/1', function(json) 
	{
		$.each(json['locations'], function(key, val) 
		{
			$('#select-location').append('<option value=' + val.id + '>' + val.state + ' - ' + val.city + '</option>');
		});
    });


	// Event handler for signup button
	$('#btn-signup').click(function() {
		$.post('', $('form').serialize(), function(json) {
			//$('div.jumbotron').html("<p class=alert-success>Success!  You've signed up!</p>");
			//console.log($('form').serialize());
        });

	});	

	// Validate form
	$('form').validate({
		rules: {
			retailer_id: 
			{
            	required: true
          	},
			location_id: 
			{
            	required: true
          	},
			phone: {
      			required: true,
				phoneUS: true,
    		},
			product_number: {
				required: true,
				minlength: 5
			}
  		},
		highlight: function(element) 
		{
        	$(element).closest('.form-group').removeClass('has-success').addClass('has-error'); 	
    	},
    	unhighlight: function(element) 
		{
        	$(element).closest('.form-group').removeClass('has-error').addClass('has-success');    	
		},
		errorElement: 'span',
        errorClass: 'help-block',
        errorPlacement: function(error, element) {
            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        },
		submitHandler: function (form)
		{	
			$.post('api/v1/subscriptions', $('form').serialize(), function(json) {
				console.log("Submithandler called " + $('form').serialize());
				console.log("JSON server response: " + json.code + ' ' + json.message);
				clearAlerts();
           		addAlert(json);
        	});
		}
	});


}());

