jQuery(document).ready(function(){
	var intl_tel_input = jQuery("#billing_phone");
	jQuery( intl_tel_input ).wrap( jQuery( '<div class="intl-tel-input allow-dropdown"><div class="flag-container"></div></div>' ) );
	jQuery( '<div class="selected-flag" tabindex="0"><div class="iti-flag"></div><div class="iti-arrow"></div></div>' ).insertBefore( intl_tel_input );
	var countryCode = jQuery('#billing_country').val();
	jQuery('#wcdi_sms_intl_tel_country_iso2').val(countryCode);
	getCountryDataByCode(intl_tel_input, countryCode);

	jQuery('#billing_country').on('change', function(){
		var countryCode = jQuery(this).val();
		var intl_tel_input = jQuery("#billing_phone");
		getCountryDataByCode(intl_tel_input, countryCode);
	});
});

function getCountryDataByCode(intl_tel_input, countryCode){
	jQuery.getJSON("https://restcountries.eu/rest/v2/alpha/" +countryCode,
		function(data) {
		    console.log(data); 
		    jQuery.each(data, function(k, v){
		      if(k == 'name'){
		      	jQuery('#wcdi_sms_intl_tel_country_name').val(v);
		      }
		      if(k == 'callingCodes'){
		      	jQuery('#wcdi_sms_intl_tel_country_code').val(v);
		      	jQuery('#wcdi_sms_intl_tel_full').val(v+''+intl_tel_input.val());
		      	jQuery('.intl-tel-input .selected-flag .iti-arrow').text('+'+v);
		      }
		      if(k == 'flag'){
		       	jQuery(".intl-tel-input .selected-flag .iti-flag").css("background-image", "url("+v+")");
		      }
			});
		}); 
}