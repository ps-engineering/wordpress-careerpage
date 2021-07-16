// @codekit-prepend "jplist.min.js"
// @codekit-prepend "career-applicationform.js"

if (typeof Object.assign != 'function') {
  Object.assign = function(target) {
    'use strict';
    if (target == null) {
      throw new TypeError('Cannot convert undefined or null to object');
    }

    target = Object(target);
    for (var index = 1; index < arguments.length; index++) {
      var source = arguments[index];
      if (source != null) {
        for (var key in source) {
          if (Object.prototype.hasOwnProperty.call(source, key)) {
            target[key] = source[key];
          }
        }
      }
    }
    return target;
  };
}

if (!Object.entries) {
  Object.entries = function( obj ){
    var ownProps = Object.keys( obj ),
        i = ownProps.length,
        resArray = new Array(i); // preallocate the Array
    while (i--)
      resArray[i] = [ownProps[i], obj[ownProps[i]]];

    return resArray;
  };
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};

function calculateDistance(lat1,lon1,lat2,lon2) {
	var R = 6371; // km (change this constant to get miles)
	var dLat = (lat2-lat1) * Math.PI / 180;
	var dLon = (lon2-lon1) * Math.PI / 180;
	var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
		Math.cos(lat1 * Math.PI / 180 ) * Math.cos(lat2 * Math.PI / 180 ) *
		Math.sin(dLon/2) * Math.sin(dLon/2);
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
	var d = R * c;
	return Math.round(d)
}

var i = 0;
var cityArray = [];
let map;
let markers = [];
let infowindow;
var infoWindows = [];
var initialMapArray = [];
var cityHoverText;
var cityUrl;

function writeDistanceToElements(selectedCityLat,selectedCityLng){
	if( $('.job__list tbody tr td.distance').length){
    $('.job__list tbody tr td.distance').each(function(){
        i++;
        var cityLat = $(this).attr('data-lat');
        var cityLng = $(this).attr('data-lng');
        
        var distance = calculateDistance(selectedCityLat,selectedCityLng,cityLat,cityLng);
        $(this).text(distance);
    });
	}
}

// Close All Info Windows
function closeAllInfoWindows() {
	for (var i=0;i<infoWindows.length;i++) {
		infoWindows[i].close();
	}
}

// Open Info Window
function openInfo(map,marker,title,link,city){
	// Count All City Duplicates in Array
	var cityCounts = {};
	cityArray.forEach(function(x) { cityCounts[x] = (cityCounts[x] || 0)+1; });
	
	infowindow = new google.maps.InfoWindow({
		content: '<span>'+cityCounts[city]+' '+cityHoverText+'</span>',
	});
	infoWindows.push(infowindow); 
	infowindow.open(map, marker);
}
  
// Add Marker to Map
function addMarker(location,title,link,city) {
	const marker = new google.maps.Marker({
		position: location,
		map: map,
	});
	
	marker.addListener("click",function(){
		if($('#careermap').attr('data-cityurl') != ''){
			window.location = cityUrl+'?searchcity='+city+'';
		} else {
			var searchBox = document.querySelector('select[data-name="cities"]'); 
			var citySelect = parseInt($('select[data-name="cities"] option[data-cityname="'+city+'"]').val());
			searchBox.value = citySelect; 
			searchBox.focus();
			searchBox.dispatchEvent(new Event('change')); 
		}
	});
	
	marker.addListener("mouseover",function(){
		openInfo(map,marker,title,link,city);
	});	
	
	marker.addListener("mouseout",function(){
		closeAllInfoWindows();
	});		
	  
	markers.push(marker);
}

// Sets the map on all markers in the array.
function setMapOnAll(map) {
  for (let i = 0; i < markers.length; i++) {
    markers[i].setMap(map);
  }
}

// Removes the markers from the map, but keeps them in the array.
function clearMarkers() {
  setMapOnAll(null);
}

// Shows any markers currently in the array.
function showMarkers() {
  setMapOnAll(map);
}

// Deletes all markers in the array by removing references to them.
function deleteMarkers() {
  clearMarkers();
  markers = [];
}

function buildMapArray(){
	initialMapArray = [];
	var table = document.querySelector(".job__list tbody");
	for (var i = 0, row; row = table.rows[i]; i++) {
	   initialMapArray[i] = row;
	}
	//console.log('NEW MAP ARRAY');
	//console.log(initialMapArray);
}

function refreshMap(){
	deleteMarkers();
	cityArray = [];
	
	for (var key in initialMapArray) {
	    if (initialMapArray[key]) { // assuming true or false values
			var pinTitle = initialMapArray[key].querySelector('.update-url').textContent;
			var pinLink = initialMapArray[key].querySelector('.update-url').href;
			var pinCity = initialMapArray[key].querySelector('.cities').textContent;
			var pinLat = parseFloat(initialMapArray[key].querySelector('.distance').dataset.lat);
			var pinLng = parseFloat(initialMapArray[key].querySelector('.distance').dataset.lng);
			var pinLatLng = { lat: pinLat, lng: pinLng };
			cityArray.push(pinCity);
			addMarker(pinLatLng,pinTitle,pinLink,pinCity);
	    }
	}
	
	/*
	for (var [key, value] of Object.entries(initialMapArray)) {
	  var pinTitle = initialMapArray[key].querySelector('.update-url').textContent;
	  var pinLink = initialMapArray[key].querySelector('.update-url').href;
	  var pinCity = initialMapArray[key].querySelector('.cities').textContent;
	  var pinLat = parseFloat(initialMapArray[key].querySelector('.distance').dataset.lat);
	  var pinLng = parseFloat(initialMapArray[key].querySelector('.distance').dataset.lng);
	  var pinLatLng = { lat: pinLat, lng: pinLng };
	  cityArray.push(pinCity);
	  addMarker(pinLatLng,pinTitle,pinLink,pinCity);
	}
	*/
	
	//console.log(cityArray);	
}

function initRadiusFilter(radius){

    if(Math.round(radius / 10) * 10 <= 1500){
        $('.radius-'+Math.round(radius / 10) * 10+'').click();
    } else {
        $('.radius-1500').click();
    }    
    //console.log('.radius-'+Math.round(radius / 10) * 10+'');
	
	if($('#careermap').length){
		buildMapArray();
		refreshMap();
	}

}


jQuery(document).ready(function($) {
	
	easydropdown.all();
	
	if($('#careermap').length){
		buildMapArray();
	}
	
	if($('section table').length){
		$('section table').basictable({
		    breakpoint: 720
		});
	}
	

    jplist.init({
        storage: 'sessionStorage', //'localStorage', 'sessionStorage' or 'cookies'
        storageName: 'jplist'  
    });
	
	if($('[data-jplist-control]').length){

		const filterCallback = document.querySelector('[data-jplist-control]');
		
		filterCallback.addEventListener('jplist.state', function(e){
			if($('section table').length){
				$('section table').basictable({
				    breakpoint: 720
				});
				console.log('Init Basic Table');
			}
		}, false);
		
		console.log('DJC init');
	
	}
	
	
	// Init Google Map
	function initGoogleMapFunctions(){
		
		const element = document.querySelector('[data-jplist-control]');
		
		element.addEventListener('jplist.state', function(e){
			deleteMarkers();
			cityArray = [];
			//console.log(e.jplistState.filtered[0]);
			
			for (var key in e.jplistState.filtered) {
			    if (e.jplistState.filtered[key]) { // assuming true or false values
				  var pinTitle = e.jplistState.filtered[key].querySelector('.update-url').textContent;
				  var pinLink = e.jplistState.filtered[key].querySelector('.update-url').href;
				  var pinCity = e.jplistState.filtered[key].querySelector('.cities').textContent;
				  var pinLat = parseFloat(e.jplistState.filtered[key].querySelector('.distance').dataset.lat);
				  var pinLng = parseFloat(e.jplistState.filtered[key].querySelector('.distance').dataset.lng);
				  var pinLatLng = { lat: pinLat, lng: pinLng };
				  //console.log(title);
				  cityArray.push(pinCity);
				  addMarker(pinLatLng,pinTitle,pinLink,pinCity);
				}
			}
					
			/*
			for (var [key, value] of Object.entries(e.jplistState.filtered)) {
			  var pinTitle = e.jplistState.filtered[key].querySelector('.update-url').textContent;
			  var pinLink = e.jplistState.filtered[key].querySelector('.update-url').href;
			  var pinCity = e.jplistState.filtered[key].querySelector('.cities').textContent;
			  var pinLat = parseFloat(e.jplistState.filtered[key].querySelector('.distance').dataset.lat);
			  var pinLng = parseFloat(e.jplistState.filtered[key].querySelector('.distance').dataset.lng);
			  var pinLatLng = { lat: pinLat, lng: pinLng };
			  //console.log(title);
			  cityArray.push(pinCity);
			  addMarker(pinLatLng,pinTitle,pinLink,pinCity);
			}
			*/
			//console.log(cityArray);
		}, false);
	}
	
	if($('#careermap').length){
		cityHoverText = $('#careermap').attr('data-hovertext');
		cityUrl = $('#careermap').attr('data-cityurl');
		
		
		var centerLat = parseFloat($('#careermap').attr('data-center-lat'));
		var centerLng = parseFloat($('#careermap').attr('data-center-lng'));
		var zoom = parseInt($('#careermap').attr('data-zoom'));
		
		map = new google.maps.Map(document.getElementById("careermap"), {
			center: { lat: centerLat, lng: centerLng },
			zoom: zoom,
			zoomControl: true,
			mapTypeControl: false,
			scaleControl: true,
			streetViewControl: false,
			rotateControl: false,
			fullscreenControl: false

		});
		
		
		refreshMap();
		
		//console.log(cityArray);
		initGoogleMapFunctions();
	}
    
    
	if(getUrlParameter('searchquery')){
		//console.log('hey');
		var search = getUrlParameter('searchquery'); 
		var searchBox = document.getElementById('jplist-search'); 
		searchBox.value = search; 
		searchBox.focus();
		searchBox.dispatchEvent(new KeyboardEvent('keyup',{'key':'a'})); 
	}
	
	if(getUrlParameter('searchcity')){
		//console.log('hey');
		var search = getUrlParameter('searchcity'); 
		var searchBox = document.querySelector('select[data-name="cities"]'); 
		var citySelect = parseInt($('select[data-name="cities"] option[data-cityname="'+search+'"]').val());
		searchBox.value = citySelect; 
		searchBox.focus();
		searchBox.dispatchEvent(new Event('change')); 
	}


    
    $('#umkreisinput').on('keyup paste',function(){
        
        if($('#city_radius').get(0).checked === true) {
        
            var currentGroup = $('.job__list tbody').attr('data-jplist-group');
                currentGroup = 'radiussearch_'+currentGroup;
            var value = $(this).val();
            if(value === ''){
                value = 0;
                initRadiusFilter(value);
                
                var currentSessionItem = window.sessionStorage.getItem(currentGroup);
                if (currentSessionItem != null){
                    currentSessionItem = currentSessionItem.split('&');
                    for (var i=currentSessionItem.length-1; i>=0; i--) {
                        
                        if (currentSessionItem[i].indexOf('radius=') > -1) {
                            
                            currentSessionItem[i] = 'radius='+value+'';
                            
                        }
                        //console.log( currentSessionItem[i]);
                    }
                    currentSessionItem = currentSessionItem.join("&");
                    //console.log(currentSessionItem);
                    window.sessionStorage.setItem(currentGroup,currentSessionItem);
                }
                
                //window.sessionStorage.setItem(currentGroup,'lat='+selectedCityLat+'&lng='+selectedCityLng+'&radius='+radius);
            } else {
                value = parseInt(value);
                initRadiusFilter(value);
                
                var currentSessionItem = window.sessionStorage.getItem(currentGroup);
                if (currentSessionItem != null){
                    currentSessionItem = currentSessionItem.split('&');
                    for (var i=currentSessionItem.length-1; i>=0; i--) {
                        
                        if (currentSessionItem[i].indexOf('radius=') > -1) {
    
                            currentSessionItem[i] = 'radius='+value+'';
                            
                        }
                        //console.log( currentSessionItem[i]);
                    }
                    currentSessionItem = currentSessionItem.join("&");
                    //console.log(currentSessionItem);
                    window.sessionStorage.setItem(currentGroup,currentSessionItem);
                }
                
            }
        
        }
    });
    
    var radius = 0;
    
    var currentCity = '';
    $('#select_cities').on('change', function(){
        currentCity = this.value; 
    });
    
    
    
    
    var currentGroup = $('.job__list tbody').attr('data-jplist-group');
        currentGroup = 'radiussearch_'+currentGroup;
        
        
    // Check if Session Storage for Radius Search is set and init Radius Search
    if(window.sessionStorage.getItem(currentGroup) != null){

        var currentSessionStorage = window.sessionStorage.getItem(currentGroup);
            currentSessionStorage = currentSessionStorage.split('&');
        for (var i=currentSessionStorage.length-1; i>=0; i--) {
            if (currentSessionStorage[i].indexOf('radius=') > -1) {
                var currentRadius = currentSessionStorage[i].replace('radius=','');
                //alert(currentRadius);
                $('#umkreisinput').val(currentRadius);
            }
            if (currentSessionStorage[i].indexOf('lat=') > -1) {
                var currentLat = currentSessionStorage[i].replace('lat=','');
            }
            if (currentSessionStorage[i].indexOf('lng=') > -1) {
                var currentLng = currentSessionStorage[i].replace('lng=','');
            }
        }
        easydropdown.destroy();
        //console.log(currentLat+currentLng);
        var currentCityId = $('#select_cities option[data-lat="'+currentLat+'"][data-lng="'+currentLng+'"]').val();
        $('#select_cities').val(currentCityId);
        
        // Get jplist Storage
        var currentSessionStorage = window.sessionStorage.getItem('jplist');
            currentSessionStorage = currentSessionStorage.split('&');
        for (var i=currentSessionStorage.length-1; i>=0; i--) {
            if (currentSessionStorage[i].indexOf('cities=') > -1) {
                currentSessionStorage[i] = 'cities='+currentCityId+''
            }
        }
        currentSessionStorage = currentSessionStorage.join("&");
        window.sessionStorage.setItem('jplist',currentSessionStorage);
        
        setTimeout(function(){
            $('#city_radius').click();
        }, 10);
    
    // If Session Storage for Radius Search is not set
    } else {
        
        // Look if there is a Select Field with the Default Attribute and check if there is no selection
        if($('select[data-default]').length){
            var defaultData = $('select[data-default]').attr('data-default');
            var defaultDataValue = $('select[data-default] option:contains("'+defaultData+'")').val();
            var defaultId = $('select[data-default]').attr('data-id');
            var currentSelectValue = $('select[data-default]').get(0).value;
            
            //console.log('Default Data is '+defaultData);

            // If there is no Selection in the Default Select Menu -> set one
            if(currentSelectValue === '0'){
                
                //jplist.resetControls();
                
                // Get jplist Storage
                
                /*
                var currentSessionStorage = window.sessionStorage.getItem('jplist');
                    currentSessionStorage = currentSessionStorage.split('&');
                for (var i=currentSessionStorage.length-1; i>=0; i--) {
                    if (currentSessionStorage[i].indexOf(defaultId+'=') > -1) {
                        currentSessionStorage[i] = defaultId+'='+parseInt(defaultDataValue)+''
                    }
                }
                currentSessionStorage = currentSessionStorage.join("&");

                
                window.sessionStorage.setItem('jplist',currentSessionStorage);
                */
                
                var currentDefaultSelect = easydropdown($('select[data-default]').get(0));
                
                $('select[data-default]').val(defaultDataValue).change();
                
                
                // Delete not matching Table Elements
                
                $('.job__list tr td').each(function(){
                    
                    defaultData = defaultData.replace(/ü/g, '').replace(/ä/g, '').replace(/ö/g, '');
                    defaultData =  defaultData.replace(/\s/g,'');
                    defaultData =  defaultData.replace(/[^a-zA-Z ]/g, '');
                    //console.log(defaultData);
                    if($(this).hasClass(defaultData)){
                        $(this).parent().addClass('stay');
                    }
                });
                $('.job__list tr:not(.stay)').remove();
                

                    //jplist.refresh();
                    //jplist.refresh('vertrieb-sales', $('select[data-default]'));
                
                

                /*
                setTimeout(function(){
                    jplist.refresh();
                }, 10000);
                */
                
            }

            
        }
        
    }
    
    $('#city_radius').on('click', function(e){
        
        var selectedCity = '';
        
        // Checkbox for Radius Search is activated
        if(this.checked === true) {
            
            // Get jplist Storage
            var currentSessionStorage = window.sessionStorage.getItem('jplist');
                currentSessionStorage = currentSessionStorage.split('&');
            for (var i=currentSessionStorage.length-1; i>=0; i--) {
                if (currentSessionStorage[i].indexOf('cities=') > -1) {
                    selectedCity = currentSessionStorage[i].replace('cities=','');
					//console.log(selectedCity);
					
                    if(selectedCity === '0' ){
						
						if($('select[data-default]').length){
							//var defaultId = $('select[data-default]').attr('data-id');
							var currentSelectValue = $('select[data-default]').get(0).value;
							selectedCity = currentSelectValue;
						} else {

	                        e.preventDefault();
	                        var errorMSG = $('.cities_with_radius_search').attr('data-error-message');
	                        if(errorMSG === ''){
	                            errorMSG = 'Bitte wählen Sie zuerst einen Ort aus';
	                        }
	                        alert(errorMSG);
	                        break;
							
						}

                    }
                    currentSessionStorage.splice(i, 1);
                }
            }
            
            if(selectedCity != '0' ){
                easydropdown.destroy();
                currentSessionStorage = currentSessionStorage.join('&');
                window.sessionStorage.setItem('jplist',currentSessionStorage);
                jplist.resetControls();
                
                //var cacheOldSelect = $('#select_cities');
                
                $('#select_cities').removeAttr('data-jplist-control').clone().attr('id','select_cities_clone').prependTo('.cities_with_radius_search');
                var selectCitiesDropdown = easydropdown($('#select_cities').get(0));
				

                $('#select_cities').hide();
                $('#select_cities').parents('.edd-root').hide();
                
                $('#select_cities_clone option:selected').removeAttr('selected');
                $('#select_cities_clone option[value="'+selectedCity+'"]').attr('selected','selected');
                var selectedCityLat = $('#select_cities_clone option[value="'+selectedCity+'"]').attr('data-lat');
                var selectedCityLng = $('#select_cities_clone option[value="'+selectedCity+'"]').attr('data-lng');
                
                //.log(cacheOldSelect);
                
                jplist.refresh();
                $('.jplist-dd-item[data-value="0"]').click();
                writeDistanceToElements(selectedCityLat,selectedCityLng);
                $('.jplist-dd-item:first-child').click();
                
                //
                //easydropdown.all();
                $('select:not(#select_cities_clone)').each(function(){
                    //easydropdown($(this));
                    easydropdown(this);
                });
                
                easydropdown('#select_cities_clone', {
                    callbacks: {
                        onSelect: function(value) {

                            var selectedCityLat = $('#select_cities_clone option[value="'+value+'"]').attr('data-lat');
                            var selectedCityLng = $('#select_cities_clone option[value="'+value+'"]').attr('data-lng');
                            var value = $('#umkreisinput').val();
                            //console.log('SELECT '+value);
                            $('.radius-1500').click();
                            
                            $('.jplist-dd-item[data-value="0"]').click();
                            writeDistanceToElements(selectedCityLat,selectedCityLng);
                            
                            initRadiusFilter(value);
                            window.sessionStorage.setItem(currentGroup,'lat='+selectedCityLat+'&lng='+selectedCityLng+'&radius='+value);
                            
                            $('.jplist-dd-item:first-child').click();
                            
                            /*
                            writeDistanceToElements(selectedCityLat,selectedCityLng);
                            $('.jplist-dd-item:first-child').click();
                            */
                            
                        }
                    }
                });
                
                var radius = $('#umkreisinput').val();
                if(radius === ''){
                    radius = 0;
                }
                initRadiusFilter(radius);
                window.sessionStorage.setItem(currentGroup,'lat='+selectedCityLat+'&lng='+selectedCityLng+'&radius='+radius);
            }
            
            
        // Checkbox for Radius Search is deactivated
        } else {

            window.sessionStorage.removeItem(currentGroup);
            
            var selectedCity = $('#select_cities_clone option:selected').val();
            var selectedCityLat = $('#select_cities_clone option[value="'+selectedCity+'"]').attr('data-lat');
            var selectedCityLng = $('#select_cities_clone option[value="'+selectedCity+'"]').attr('data-lng');
            
            if(selectedCity != undefined && selectedCityLat != undefined && selectedCityLng != undefined){
            
                easydropdown.destroy();
                
                //$('#select_cities_clone').attr('data-jplist-control','select-filter').clone().attr('id','select_cities').prependTo('.cities_with_radius_search');
                $('#select_cities_clone').remove();
                
                //jplist.resetControls();
                
                //console.log('UH '+selectedCity+','+selectedCityLat+','+selectedCityLng);
    
                var currentSessionItem = window.sessionStorage.getItem('jplist');
                if (currentSessionItem != null){
                    currentSessionItem = currentSessionItem.split('&');
                    for (var i=currentSessionItem.length-1; i>=0; i--) {
                        if (currentSessionItem[i].indexOf('cities=') > -1) {        
                            currentSessionItem[i] = 'cities='+selectedCity+'';
                        }
                    }
                    currentSessionItem = currentSessionItem.join("&");
                    
                    window.sessionStorage.setItem('jplist',currentSessionItem);
                    //jplist.resetControls();
                    
                    //alert(currentSessionItem);
                }
                            
                jplist.resetControls();
                $('.radius-all').click();
                jplist.refresh();
                $('#umkreisinput').val('');
                
                easydropdown.all();
				
				if($('#careermap').length){
					initGoogleMapFunctions();
			
				}
				

            
            } else {
                setTimeout(function(){
                    $('#city_radius').click();
                }, 10);
                //alert('hey');
            }
			
			setTimeout(function(){
				if($('#careermap').length){
					buildMapArray();
					refreshMap();
				}
			}, 50);
        }


        
    });




});