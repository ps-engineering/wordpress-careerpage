function getBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
  });
}

function isJson(item) {
    item = typeof item !== "string"
        ? JSON.stringify(item)
        : item;

    try {
        item = JSON.parse(item);
    } catch (e) {
        return false;
    }

    if (typeof item === "object" && item !== null) {
        return true;
    }

    return false;
}

jQuery(document).ready(function($) {


	// Handle File Uploads and Drag and Drop Mechanism
	
	
	$('input[type="file"]').on('click', function() {
		var originalTitle = $(this).parent().find(".file_names").attr('data-originaltitle');
		$(this).parent().find(".file_names").html(originalTitle);
	});
	
	if ($('input[type="file"][name="avatar"]')[0]) {
		var fileInputAv = document.querySelector('label[for="avatar"]');
		let fileInputAvElement = document.querySelector('input[type="file"][name="avatar"]');
		
		fileInputAv.ondragover = function() {
			this.className = "file_avatar changed";
			return false;
		}
		fileInputAv.ondragleave = function() {
			this.className = "file_avatar";
			return false;
		}
		fileInputAv.ondrop = function(e) {
			e.preventDefault();
			fileInputAvElement.files = e.dataTransfer.files;
			var fileNamesAv = e.dataTransfer.files;
			for (var x = 0; x < fileNamesAv.length; x++) {
				console.log('Dragndrop:'+fileNamesAv[x].name);
				$('label[for="avatar"] .file_names').html(fileNamesAv[x].name);
			}
			$('.avatar .file__upload__inner label').addClass('uploaded');
		}
		$('#avatar').change(function() {
			var fileNamesAv = $('#avatar')[0].files[0].name;
			$('label[for="avatar"] .file_names').html(fileNamesAv);
			if(fileNamesAv.length > 0){
				$('.avatar .file__upload__inner label').addClass('uploaded');
			}
		});
	}

	if ($('input[type="file"][name="cv_file"]')[0]) {
		var fileInputCv = document.querySelector('label[for="cv_file"]');
		let fileInputCvElement = document.querySelector('input[type="file"][name="cv_file"]');
		
		fileInputCv.ondragover = function() {
			this.className = "file_cv_file changed";
			return false;
		}
		fileInputCv.ondragleave = function() {
			this.className = "file_cv_file";
			return false;
		}
		fileInputCv.ondrop = function(e) {
			e.preventDefault();
			fileInputCvElement.files = e.dataTransfer.files;
			var fileNamesCv = e.dataTransfer.files;
			for (var x = 0; x < fileNamesCv.length; x++) {
				console.log('Dragndrop:'+fileNamesCv[x].name);
				$('label[for="cv_file"] .file_names').html(fileNamesCv[x].name);
			}
			$('.cv_file .file__upload__inner label').addClass('uploaded');
		}
		$('#cv_file').change(function() {
			var fileNamesCv = $('#cv_file')[0].files[0].name;
			$('label[for="cv_file"] .file_names').html(fileNamesCv);
			if(fileNamesCv.length > 0){
				$('.cv_file .file__upload__inner label').addClass('uploaded');
			}
		});
	}
	
	
	if ($('input[type="file"][name="job_applications"]')[0]) {
		
		var fileInputJa = document.querySelector('label[for="job_applications"]');
		let fileInputJaElement = document.querySelector('input[type="file"][name="job_applications"]');
		fileInputJa.ondragover = function() {
			this.className = "file_job_applications changed";
			return false;
		}
		fileInputJa.ondragleave = function() {
			this.className = "file_job_applications";
			return false;
		}
		
		fileInputJa.ondrop = function(e) {
			e.preventDefault();
			fileInputJaElement.files = e.dataTransfer.files;
			var fileNamesJa = e.dataTransfer.files;
			$('label[for="job_applications"] .file_names').html('');
			for (var x = 0; x < fileNamesJa.length; x++) {
				console.log('Dragndrop:'+fileNamesJa[x].name);
				$('label[for="job_applications"] .file_names').append(fileNamesJa[x].name+'<br />');
			}
			if(fileNamesJa.length > 0){
				$('.job_applications .file__upload__inner label').addClass('uploaded');
			}
		}
		
		$('#job_applications').change(function() {
			$('label[for="job_applications"] .file_names').html('');
			var fileNamesJa = $('#job_applications')[0].files;
			for (var x = 0; x < fileNamesJa.length; x++) {
				//console.log('Dragndrop:'+fileNames[x].name);
				$('label[for="job_applications"] .file_names').append(fileNamesJa[x].name+'<br />');
			}
			if(fileNamesJa.length > 0){
				$('.job_applications .file__upload__inner label').addClass('uploaded');
			}
		});
	}

	
	
	//$("#job_application_form").dropzone();
	
	/*
	if($('#job_application_form').length){
		const inputfield = document.querySelector('#job_application_form');
		
		
		inputfield.addEventListener('input', () => {
		inputfield.setCustomValidity('');
		inputfield.checkValidity();
		  console.log(inputfield.checkValidity());
		
		});
		inputfield.addEventListener('invalid', () => {
			console.log('dsf');
			$('input[type="file"]').each(function(){
				var originalTitle = $(this).parent().find('.file_names').attr('data-originaltitle');
				$(this).parent().find('.file_names').html(originalTitle);
			});
			$('input[type="file"]').parent().find('label').removeClass('uploaded');
			$('input[type="file"]').parent().find('label').removeClass('changed');
		});
	}
	*/

	/*
	if($('#job_application_form').length){
		const inputfields = document.querySelectorAll('#job_application_form input');
		

		
		inputfields.forEach((inputfield) => {
		  inputfield.addEventListener('input', () => {
		    inputfield.setCustomValidity('');
		    inputfield.checkValidity();
		      console.log(inputfield.checkValidity());
		
		  });
		  inputfield.addEventListener('invalid', () => {
			$('input[type="file"]').each(function(){
				var originalTitle = $(this).parent().find('.file_names').attr('data-originaltitle');
				$(this).parent().find('.file_names').html(originalTitle);
			});
		  	$('input[type="file"]').parent().find('label').removeClass('uploaded');
			$('input[type="file"]').parent().find('label').removeClass('changed');
		  });
		});
	}
	*/
	/*
	$("#job_application_form").change(function () {
		console.log('changes');
		$('input[type="file"]').each(function(){
				var originalTitle = $(this).parent().find('.file_names').attr('data-originaltitle');
				$(this).parent().find('.file_names').html(originalTitle);
				$(this).parent().find('label').removeClass('uploaded');
				$(this).parent().find('label').removeClass('changed');
		});
	});
	*/

	// Handle Formular Data Transfer
	$("#job_application_form").submit(function (event) {
		event.preventDefault();
		$('.wpcf7-response-output').remove();
		var originalButtonText = $('#job_application_form button').text();
		$('#job_application_form button').text('Bewerbung abschicken');
		$('#job_application_form button').addClass('sending');
		
		var redirectURL = $("#redirect_url_after_form_submit").val();
		var redirectSeconds = parseInt($("#redirect_after_seconds").val());
		if(redirectURL != '' && redirectSeconds > 0){
			var redirectText = '<br /><br />Du wirst in '+redirectSeconds+' Sekunden weitergeleitet';
		} else {
			var redirectText = '';
		}
		
		var data = new FormData();
			data.append('action','send_application_form');
		// Iterate through each Formular Input/Select Field
		var customFieldCount = 0;
		$('[name]').each(function(){
			var identifier = $(this).attr('name');
			var type = $(this).attr('type');
			var dataType = $(this).attr('data-type');
			
			// Handle the Custom Fields
			if($(this).attr('data-field') === 'custom_field'){

				var fieldData = [];
				var fieldDataValues = [];
				
				if(type === 'number' || type === 'date' || type === 'text'){
					if($("[name='"+identifier+"']").val() != '' && $("[name='"+identifier+"']").val() != null){
						fieldDataValues.push({'id': '','value': $("[name='"+identifier+"']").val()});
						fieldData.push({'custom_field_id': identifier,'values': fieldDataValues});
						data.append('custom_field_'+customFieldCount+'',JSON.stringify(fieldData));
						customFieldCount++;
					}
				} else if(type === 'radio' || type === 'checkbox' || dataType === 'select'){
					if(type === 'radio' || type === 'checkbox'){
						if($(this).is(':checked')){
							fieldDataValues.push({'id': $(this).val(),'value': ''});
							fieldData.push({'custom_field_id': identifier,'values': fieldDataValues});
							data.append('custom_field_'+customFieldCount+'',JSON.stringify(fieldData));							
							customFieldCount++;
						}
					} else {
						if($(this).val() != '' && $(this).val() != null){
							fieldDataValues.push({'id': $(this).val(),'value': ''});
							fieldData.push({'custom_field_id': identifier,'values': fieldDataValues});
							data.append('custom_field_'+customFieldCount+'',JSON.stringify(fieldData));
							customFieldCount++;
						}
					}
				}
			// Handle the "fixed" Fields
			} else {
				if(type === 'file'){
	
					if(typeof $(this).attr('multiple') !== 'undefined' && $(this).attr('multiple') !== false){
						var files = {};
						var len = $(this)[0].files.length
						var i;
						for (i = 0; i < $(this)[0].files.length; i++) {
							data.append(identifier+'_'+[i],$(this)[0].files[i]);
						}
					} else {
						var file = $(this)[0].files[0];
						data.append(identifier,file);
					}
	
				} else if(type === 'checkbox' || type === 'radio'){
					if($("[name='"+identifier+"']").prop("checked") === true){
						data.append(identifier,true);
					} else {
						data.append(identifier,false);
					}
				} else if(identifier === 'candidate_tag_ids'){
					data.append(identifier,$("[name='"+identifier+"']").val());
				} else {
					if($("[name='"+identifier+"']").val() != '' && $("[name='"+identifier+"']").val() != null){
						data.append(identifier,$("[name='"+identifier+"']").val());
					}
				}
			}
		});
		

		for (var pair of data.entries()) {
		    //console.log(pair[0]+ ', ' + pair[1]); 
		}
		


		
		jQuery.ajax({
			type: 'POST',
			url: my_ajax_object.ajax_url,
			data: data,
			contentType: false,
       	 	processData: false,
			success: function(response) {
				//console.log(JSON.parse(response).message);
				console.log(response);
				
				if (isJson(response) === true) {
					if(JSON.parse(response).status === 'success'){
						$('#job_application_form').append('<div class="wpcf7-response-output wpcf7-mail-sent-ok">'+JSON.parse(response).message+redirectText+'</div>');
						if(redirectURL != '' && redirectSeconds > 0){
						setTimeout(function(){
							window.location.href = redirectURL;
						}, redirectSeconds*1000);	
						}
					} else {
						$('#job_application_form').append('<div class="wpcf7-response-output wpcf7-validation-errors">'+JSON.parse(response).message+'</div>');
					}
				} else {
					$('#job_application_form').append('<div class="wpcf7-response-output wpcf7-mail-sent-ok">'+response+'</div>');
				}
				
				//$('#job_application_form button').removeClass('loading');
				$('#job_application_form button').text(originalButtonText);
				$('#job_application_form button').removeClass('sending');
				
			},
		    error: function (xhRequest, ErrorText, thrownError) {
		        alert("Failed to process promotion correctly, please try again");
		        console.log('xhRequest: ' + xhRequest + "\n");
		        console.log('ErrorText: ' + ErrorText + "\n");
		        console.log('thrownError: ' + thrownError + "\n");
				$('#job_application_form button').removeClass('loading');
		    }
		});
		
		
		
	});
	

});