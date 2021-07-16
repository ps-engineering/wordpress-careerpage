function isEmpty(obj) {
    for(var key in obj) {
        if(obj.hasOwnProperty(key))
            return false;
    }
    return true;
}

jQuery(document).ready(function($) {
  
	function urldecode(url) {
		return decodeURIComponent(url.replace(/\+/g, ' '));
	}
  

	$('a.open-job-dialog').on('click', function(e){
		e.preventDefault();
		
		var title = $(this).attr('data-title');
		var handle = $(this).attr('data-handle');
		var shorthandle = $(this).attr('data-shorthandle');
		var showurl = $(this).attr('data-showurl');
		var applyurl = $(this).attr('data-applyurl');
		var applyaddress = $(this).attr('data-applyaddress');
		var bannerurl = $(this).attr('data-bannerurl');
		var templates = $(this).attr('data-templates');
		var departments = $(this).attr('data-departments');
		var cities = $(this).attr('data-cities');
		var positiontypes = $(this).attr('data-positiontypes');
		var seniorities = $(this).attr('data-seniorities');
		var headcount = $(this).attr('data-headcount');
		var instances = $(this).attr('data-instances');
		var team = $(this).attr('data-team');

		var customDataFields = $(this).attr('data-custom-data-fields');
		    customDataFields = customDataFields.split('\\\\\\"').join('\'');
		    customDataFields = customDataFields.replace(/\\u([0-9a-fA-F]{4})/g, (m,cc)=>String.fromCharCode("0x"+cc)).replace(/\\/g, "");
		    customDataFields = JSON.parse(customDataFields.substring(1, customDataFields.length-1));

		    var newCustomDataField = '<ul>';

		    Object.keys(customDataFields['custom_data_field']).forEach(function(k){
				
		        var cdfId = customDataFields['custom_data_field'][k]['id'];

		        if(typeof cdfId === 'object'){
		          cdfId = '';
		        }
		        var cdfName = customDataFields['custom_data_field'][k]['name'];
		        if(typeof cdfName === 'object'){
		          cdfName = '';
		        }
		        var cdfFormLabel = customDataFields['custom_data_field'][k]['form_label'];
		        if(typeof cdfFormLabel === 'object'){
		          cdfFormLabel = '';
		        }
		        var cdfValueId = customDataFields['custom_data_field'][k]['value_id'];
		        if(typeof cdfValueId === 'object'){
		          cdfValueId = '';
		        }
		        var cdfValue = customDataFields['custom_data_field'][k]['value'];
		        if(typeof cdfValue === 'object'){
		          cdfValue = '';
		        }
		        var cdfValueLabel = customDataFields['custom_data_field'][k]['value_label'];
		        if(typeof cdfValueLabel === 'object'){
		          cdfValueLabel = '';
		        }
		        
		        newCustomDataField += '<li>' + 'id: '+cdfId+', name: '+cdfName+', form_label: '+cdfFormLabel+', value_id: '+cdfValueId+', value: '+cdfValue+', value_label: '+cdfValueLabel+'</li>';
		    });

		    newCustomDataField += '</ul>';
		var publishedAt = $(this).attr('data-published-at');
		var startofwork = $(this).attr('data-startofwork');
		var industries = $(this).attr('data-industries');
		
		$('<div></div>').dialog({
		    modal: true,
		    title: title,
		    width: '700px',
		    open: function () {
		        var markup = '<strong>Title:</strong> '+title+'<br /><strong>Handle:</strong> '+handle+'<br /><strong>Short Handle:</strong> '+shorthandle+'<br /><strong>Show URL:</strong> <a href="'+showurl+'" target="_blank">'+showurl+'</a><br /><strong>Apply URL:</strong> <a href="'+applyurl+'" target="_blank">'+applyurl+'</a><br /><strong>Apply Address:</strong> '+applyaddress+'<br /><strong>Template:</strong> '+templates+'<br /><strong>Banner URL:</strong> <a href="'+bannerurl+'" target="_blank">'+bannerurl+'</a><br /><strong>Department:</strong> '+departments+'<br /><strong>City:</strong> '+cities+'<br /><strong>PositionType:</strong> '+positiontypes+'<br /><strong>Seniority:</strong> '+seniorities+'<br /><strong>Headcount:</strong> '+headcount+'<br /><strong>Instance:</strong> '+instances+'<br /><strong>Team:</strong> '+team+'<br /><strong>Published at:</strong> '+publishedAt+'<br /><strong>Start of Work:</strong> '+startofwork+'<br /><strong>Industry:</strong> '+industries+'<br /><strong>Custom Data Fields:</strong> '+newCustomDataField+'<br />';
		        $(this).html(markup);
		    },
		    buttons: {
		        Ok: function () {
		            $(this).dialog("close");
		        }
		    }
		});
	});
  
  

	$('a.open-instance-dialog').on('click', function(e){
		e.preventDefault();
		
		var title = $(this).attr('data-title');
		var id = $(this).attr('data-id');
		var islegal = $(this).attr('data-islegal');
		var handle = $(this).attr('data-handle');
		
		$('<div></div>').dialog({
		    modal: true,
		    title: title,
		    width: '400px',
		    open: function () {
		        var markup = 'ID: '+id+'<br />Is Legal Entity: '+islegal+'<br />Handle: '+handle;
		        $(this).html(markup);
		    },
		    buttons: {
		        Ok: function () {
		            $(this).dialog("close");
		        }
		    }
		});
	});
  

	$('a.open-team-dialog').on('click', function(e){
		e.preventDefault();
		
		var title = $(this).attr('data-title');
		var id = $(this).attr('data-id');
		
		$('<div></div>').dialog({
		    modal: true,
		    title: title,
		    width: '400px',
		    open: function () {
		        var markup = 'ID: '+id;
		        $(this).html(markup);
		    },
		    buttons: {
		        Ok: function () {
		            $(this).dialog("close");
		        }
		    }
		});
	});


	$('a.open-department-dialog').on('click', function(e){
		e.preventDefault();
		
		var title = $(this).attr('data-title');
		var id = $(this).attr('data-id');
		
		$('<div></div>').dialog({
		    modal: true,
		    title: title,
		    width: '400px',
		    open: function () {
		        var markup = 'ID: '+id;
		        $(this).html(markup);
		    },
		    buttons: {
		        Ok: function () {
		            $(this).dialog("close");
		        }
		    }
		});
	});

	$('a.open-positiontype-dialog').on('click', function(e){
		e.preventDefault();
		
		var title = $(this).attr('data-title');
		var id = $(this).attr('data-id');
		
		$('<div></div>').dialog({
		    modal: true,
		    title: title,
		    width: '400px',
		    open: function () {
		        var markup = 'ID: '+id;
		        $(this).html(markup);
		    },
		    buttons: {
		        Ok: function () {
		            $(this).dialog("close");
		        }
		    }
		});
	});


	$('a.open-city-dialog').on('click', function(e){
		e.preventDefault();
		
		var title = $(this).attr('data-title');
		var id = $(this).attr('data-id');
		var country = $(this).attr('data-country');
		var countrycode = $(this).attr('data-countrycode');
		var lat = $(this).attr('data-lat');
		var lng = $(this).attr('data-lng');
		
		$('<div></div>').dialog({
		    modal: true,
		    title: title,
		    width: '400px',
		    open: function () {
		        var markup = 'ID: '+id+'<br />Country: '+country+'<br />CountryCode: '+countrycode+'<br />Lat: '+lat+'<br />Long: '+lng;
		        $(this).html(markup);
		    },
		    buttons: {
		        Ok: function () {
		            $(this).dialog("close");
		        }
		    }
		});
	});
	
	// Setup Import/Test Buttons
	
	$('.loadjobs').on('click', function(e){
		$('.ps_career_ajax_result').html('');
		$('.ps_career_ajax_result').html('<svg style="width: 13px; height: auto; margin-right: 10px;" width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a"><stop stop-opacity="0" offset="0%"/><stop stop-opacity=".631" offset="63.146%"/><stop offset="100%"/></linearGradient></defs><g transform="translate(1 1)" fill="none" fill-rule="evenodd"><path d="M36 18c0-9.94-8.06-18-18-18" stroke="url(#a)" stroke-width="2"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></path><circle fill="#fff" cx="36" cy="18" r="1"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></circle></g></svg> Jobs werden importiert ...');
		
		var data = {
			'action': 'manually_import_jobs'
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			$('.ps_career_ajax_result').html(response);
		});
	
	});
	
	
	$('.loadcustomfields').on('click', function(e){
		
		$('.ps_career_ajax_result').html('');
		$('.ps_career_ajax_result').html('<svg style="width: 13px; height: auto; margin-right: 10px;" width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a"><stop stop-opacity="0" offset="0%"/><stop stop-opacity=".631" offset="63.146%"/><stop offset="100%"/></linearGradient></defs><g transform="translate(1 1)" fill="none" fill-rule="evenodd"><path d="M36 18c0-9.94-8.06-18-18-18" stroke="url(#a)" stroke-width="2"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></path><circle fill="#fff" cx="36" cy="18" r="1"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></circle></g></svg> Custom Fields werden importiert ...');
		
		var data = {
			'action': 'manually_import_application_custom_fields'
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			$('.ps_career_ajax_result').html(response);
		});
		
	
	});
	
	
	$('.loadcustomfieldformtemplates').on('click', function(e){
		
		$('.ps_career_ajax_result').html('');
		$('.ps_career_ajax_result').html('<svg style="width: 13px; height: auto; margin-right: 10px;" width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a"><stop stop-opacity="0" offset="0%"/><stop stop-opacity=".631" offset="63.146%"/><stop offset="100%"/></linearGradient></defs><g transform="translate(1 1)" fill="none" fill-rule="evenodd"><path d="M36 18c0-9.94-8.06-18-18-18" stroke="url(#a)" stroke-width="2"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></path><circle fill="#fff" cx="36" cy="18" r="1"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></circle></g></svg> Custom Fields werden importiert ...');
		
		var data = {
			'action': 'manually_import_application_custom_field_form_templates'
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			$('.ps_career_ajax_result').html(response);
		});
		
	
	});
	
	
	$('.loadtags').on('click', function(e){
		
		$('.ps_career_ajax_result').html('');
		$('.ps_career_ajax_result').html('<svg style="width: 13px; height: auto; margin-right: 10px;" width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a"><stop stop-opacity="0" offset="0%"/><stop stop-opacity=".631" offset="63.146%"/><stop offset="100%"/></linearGradient></defs><g transform="translate(1 1)" fill="none" fill-rule="evenodd"><path d="M36 18c0-9.94-8.06-18-18-18" stroke="url(#a)" stroke-width="2"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></path><circle fill="#fff" cx="36" cy="18" r="1"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></circle></g></svg> Tags werden importiert ...');
		
		var data = {
			'action': 'manually_import_application_tags'
		};
		
		jQuery.post(ajaxurl, data, function(response) {
			$('.ps_career_ajax_result').html(response);
		});
		
	
	});
  
	$('.loadgoogleindex').on('click', function(e){
		$('.ps_career_ajax_result').html('');
		$('.ps_career_ajax_result').html('<svg style="width: 13px; height: auto; margin-right: 10px;" width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a"><stop stop-opacity="0" offset="0%"/><stop stop-opacity=".631" offset="63.146%"/><stop offset="100%"/></linearGradient></defs><g transform="translate(1 1)" fill="none" fill-rule="evenodd"><path d="M36 18c0-9.94-8.06-18-18-18" stroke="url(#a)" stroke-width="2"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></path><circle fill="#fff" cx="36" cy="18" r="1"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></circle></g></svg> Google Index wird aktualisiert ...');
	
		var data = {
			'action': 'manually_update_google_index'
		};

		jQuery.post(ajaxurl, data, function(response) {
			$('.ps_career_ajax_result').html(response);
		});
	
	});
  
	$('.testgoogleindex').on('click', function(e){
		e.preventDefault();
		$('.ps_career_ajax_result').html('');
		$('.ps_career_ajax_result').html('<svg style="width: 13px; height: auto; margin-right: 10px;" width="38" height="38" viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient x1="8.042%" y1="0%" x2="65.682%" y2="23.865%" id="a"><stop stop-opacity="0" offset="0%"/><stop stop-opacity=".631" offset="63.146%"/><stop offset="100%"/></linearGradient></defs><g transform="translate(1 1)" fill="none" fill-rule="evenodd"><path d="M36 18c0-9.94-8.06-18-18-18" stroke="url(#a)" stroke-width="2"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></path><circle fill="#fff" cx="36" cy="18" r="1"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.9s" repeatCount="indefinite"/></circle></g></svg> Google Index wird getestet ...');
		
		var data = {
		'action': 'test_google_index'
		};

		jQuery.post(ajaxurl, data, function(response) {
			$('.ps_career_ajax_result').html(response);
		});
	
	});
  
});