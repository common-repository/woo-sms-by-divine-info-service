jQuery(document).ready(function() { 
	var wcdi_sms = "wcdi_sms";
	var wcdi_content_separator = '<div class="wcdi_content_seperator"></div>';
	jQuery(".form-table:first").after('<div class="wcdi_content_seperator"></div>');
	jQuery(".form-table").addClass('wcdi_sms_section');
	jQuery("#"+ wcdi_sms +"_view_sms_sent_messages").parent().prev().prev().before(wcdi_content_separator);
	jQuery(".wcdisms_woo_success_msg").prev().prev().before(wcdi_content_separator);
	
	var divineinfofromnumber_field_obj = jQuery("#"+ wcdi_sms +"_divineinfo_from_number");
	var divineinfo_parameter = get_parent_html_from_field(divineinfofromnumber_field_obj);
	
	var smsglobalname_field_obj = jQuery("#"+ wcdi_sms +"_sms_global_username");
	var smsglobal_parameter = get_parent_html_from_field(smsglobalname_field_obj);
	
	var fieldids = "";
	var fieldid = "";
	
	jQuery("#"+ wcdi_sms +"_view_sms_sent_messages").click(function() { 
		sms_gateway_show_list(0);
	});
	
    jQuery('.wcdi_sms_shortcode_add_btn').click(function(){
        var id = jQuery(this).attr('id');
          
      
         var new_id =  id.replace("add_btn", "div"); 
        
        jQuery('#'+new_id).toggle();
        jQuery('.wcdi_sms_shortcode_div').not('#'+new_id).hide();
       
    });
});

function get_parent_html_from_field(obj)
{
	return obj.parent().parent().parent().parent();
}

function show_sms_previous_heading(obj, flag)
{
	obj.prev().fadeIn();
	obj.prev().prev().fadeIn();
	obj.fadeIn();
}

function sms_gateway_show_list(paged)
{
	jQuery("#view-sms-content").empty();
	jQuery("#view_sms_loader").show();
	jQuery(".pleasewaittxt").show();
	jQuery.ajax({
		type:"POST",
		url:ajaxurl,
		data:"action=view_sms_sent_messages&paged="+paged,
		success:function(html){
			jQuery("#view_sms_loader").hide();
			jQuery(".pleasewaittxt").hide();
			jQuery("#view-sms-content").html(html);
		}
	});
}

function SMSWooTextLimit(limitField, limitCount)
{
	var limitNum = 160;
	if(limitField && document.getElementById(limitCount))
	{
		if (limitField.val().length > limitNum) 
		{
			limitField.val(limitField.val().substring(0, limitNum));
		}
		else
		{
			document.getElementById(limitCount).innerHTML = limitNum - limitField.val().length;
		}
	}
}