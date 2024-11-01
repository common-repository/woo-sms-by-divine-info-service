<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; //Exit if access directly
}

if( !class_exists( 'WC_Settings_WCDISMS' ) )
{
	return;
}
	
if( !function_exists( 'get_wcdi_sms_woo_setting_fields' ) )
{	
	function get_wcdi_sms_woo_setting_fields() {
		global $wc_settings_wcdisms, $wcdismsid, $wcdismslabel, $smsforwcdiform, $wcdi_sms_gateway_list;
		
		$sms_text_descritption = "<table class='useshortcodetable'>
			<tbody>
				<tr>
					<td colspan='2'><b>". __('Use Message Shortcodes', WCDI_SMS_TEXT_DOMAIN) ."</b></td>
				</tr>
				<tr>
					<td>". __('Your Site Name', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{SHOP_NAME}</code></td></tr>
				<tr>
					<td>". __('Order Number', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{ORDER_NUMBER}</code></td>
				</tr>
				<tr>
					<td>". __('Order Status', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{ORDER_STATUS}</code></td>
				</tr>
				<tr>
					<td>". __('Order Amount', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{ORDER_AMOUNT}</code></td>
				</tr>
				<tr>
					<td>". __('Order Date', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{ORDER_DATE}</code></td>
				</tr>
				<tr>
					<td>". __('Order Items', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{ORDER_ITEMS}</code></td>
				</tr>
				<tr>
					<td>". __('First Name', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{BILLING_FNAME}</code></td>
				</tr>
				<tr>
					<td>". __('Last Name', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{BILLING_LNAME}</code></td>
				</tr>
				<tr>
					<td>". __('Billing Email', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{BILLING_EMAIL}</code></td>
				</tr>
				<tr>
					<td>". __('Current Date', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{CURRENT_DATE}</code></td>
				</tr>
				<tr>
					<td>". __('Current Time', WCDI_SMS_TEXT_DOMAIN) ."&nbsp;</td><td><code>{CURRENT_TIME}</code></td>
				</tr>
			</tbody>
		</table>";
		$status_lists = $wc_settings_wcdisms->get_wcdi_sms_enable_statuses();
		$total_status_lists = @count($status_lists);
		$status_lists_arr = array();
		$settings_customer_data_arr = array();
		$status_cntr = 0;
		$charactor_script_var = '';
		if($total_status_lists>0)
		{
			foreach ( $status_lists as $slug => $statusname ) {
				if($status_cntr==0) {
					$checkboxgroup = "start";
					$statustitle = __( 'Select status to send notification', WCDI_SMS_TEXT_DOMAIN );
				}else if(($total_status_lists-1)==$status_cntr && ($total_status_lists>1)) {
					$checkboxgroup = "end";
					$statustitle = "";
				}else {
					$checkboxgroup = "";
					$statustitle = "";
				}

				$status_lists_arr[] = array(
					'title' => esc_html($statustitle),
					'name'  => $wcdismsid.'_enable_' . esc_attr( $slug ) . '_sms_notify_status',
					'type'  => 'checkbox',
					'desc'  => __( "Order", WCDI_SMS_TEXT_DOMAIN ). " ". $statusname,
					'id'    => $wcdismsid.'_enable_' . esc_attr( $slug ) . '_sms_notify_status',
					'checkboxgroup'		=> $checkboxgroup,
					'default' => 'yes',
				);
			
				$settings_customer_data_arr[] = array(
					'title' => __( 'SMS Text for', WCDI_SMS_TEXT_DOMAIN ) ." ". esc_html($statusname) ." ". __('order', WCDI_SMS_TEXT_DOMAIN ),
					'name'  => $wcdismsid.'_'. $slug .'_sms_text',
					'type'  => 'textarea',
					'desc_tip'  => __( 'Please enter SMS text to send notification for', WCDI_SMS_TEXT_DOMAIN ) ." ". $statusname ." ". __( 'order', WCDI_SMS_TEXT_DOMAIN ).'</script>',
					'id'    => $wcdismsid.'_'. $slug .'_sms_text',
					'default' => 'Hello {BILLING_FNAME}, your order #{ORDER_NUMBER} updated with status:{ORDER_STATUS} by {SHOP_NAME}',
					'css'	=> '',
					'class'	=> 'wcdismstextarea wcdismscustomstatustextarea',
                                                               'desc' => woocommerce_admin_field_my_button_func(array('name' => $slug)),
									
				);
			$status_cntr++;
			}
		}
		update_option( $wcdismsid .'_gateway_list', $wcdi_sms_gateway_list);
		$get_sms_gateway_list_value = get_option( $wcdismsid. '_gateway_list' );
		$divineinfo_gateway_class = "hidedivineinfosms";
		$current_sms_gateway_name = "";
		if( $get_sms_gateway_list_value== $wcdismsid .'_divineinfo' ) {
			$divineinfo_gateway_class = "";
			$current_sms_gateway_name = __( 'Divine Info', WCDI_SMS_TEXT_DOMAIN );
		}
		
		$current_sms_gateway_name_label = '';
		if($current_sms_gateway_name!='')
		{
			$current_sms_gateway_name_label = ' ( '. $current_sms_gateway_name .' )';
		}
			
		//POPUP for wordpress Default Funcation
		add_thickbox();
			
		$settings = array(
			array( 'name' => __( 'General Settings', WCDI_SMS_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'wc_'. $wcdismsid .'_main_general_setting_section_title', ),
			
			array(
				'title' => __( 'Opt-in checkbox label', WCDI_SMS_TEXT_DOMAIN ),
				'name'  => $wcdismsid.'_opt_in_checkbox_label',
				'type'  => 'text',
				'desc'  => __( 'Shows label on checkout page for buyer.', WCDI_SMS_TEXT_DOMAIN ),
				'id'    => $wcdismsid.'_opt_in_checkbox_label',
				'default' => __( 'I want to Receive Order Updates by SMS', WCDI_SMS_TEXT_DOMAIN ),
				'css'	=> '',
				'class' => 'wcdismsinput',
				'desc_tip' => true,
			),
			
			array(
				'title' => __( 'Opt-in checkbox default', WCDI_SMS_TEXT_DOMAIN ),
				'name'  => $wcdismsid.'_opt_in_checkbox_default_value',
				'type'  => 'select',
				'options' => array( "0" => __( 'Unchecked' , WCDI_SMS_TEXT_DOMAIN ), "1" => __( 'Checked', WCDI_SMS_TEXT_DOMAIN ), ),
				'desc'  => __( 'Opt-in checkbox set default Checked/Unchecked', WCDI_SMS_TEXT_DOMAIN ),
				'id'    => $wcdismsid.'_opt_in_checkbox_default_value',
				'default' => '1',
				'css'	=> '',
				'class' => 'chosen_select wcdismsselect',
				'desc_tip' => true,
			),
				
			array( 'type' => 'sectionend', 'id' => 'wc_'. $wcdismsid .'_main_general_setting_section_title'),

			array( 'name' => __( 'SMS Settings', WCDI_SMS_TEXT_DOMAIN ), 'type' => 'title',	'desc' => '', 'id' => 'wc_'. $wcdismsid .'_main_section_title', 'class' => 'testdelta' ),
			
			array( 'type' => 'sectionend', 'id' => 'wc_'. $wcdismsid .'_main_section_title'),
			
			//Divine Info Section Start Here
			array( 'name' => __( 'Divine Info SMS Settings', WCDI_SMS_TEXT_DOMAIN ), 'type' => 'title',	'desc' => __( 'Please configure your Divine Info account to send SMS. If you don\'t have details with you then get it from', WCDI_SMS_TEXT_DOMAIN) .' <a href="http://sms.divineinfo.net" target="_blank">'. __( 'Divine Info', WCDI_SMS_TEXT_DOMAIN ) .'</a>', 'id' => 'wc_'. $wcdismsid .'_main_divineinfo_section_title', 'class' => $wcdismsid .'_main_divineinfo_section_title' ),
				
			array(
				'title' => __( 'Divine Info Sender Id', WCDI_SMS_TEXT_DOMAIN ),
				'name'  => $wcdismsid.'_divineinfo_account_sid',
				'type'  => 'text',
				'desc'  => __( "Sender id should be 6 characters long.", WCDI_SMS_TEXT_DOMAIN ),
				'id'    => $wcdismsid.'_divineinfo_account_sid',
				'default' => '',
				'css'	=> '',
				'class' => 'wcdismsinput',
				'desc_tip' => true,
			),
				
			array(
				'title' => __( 'Divine Info API Key', WCDI_SMS_TEXT_DOMAIN ),
				'name'  => $wcdismsid.'_divineinfo_auth_token',
				'type'  => 'text',
				'desc'  => __( 'Login authentication key.', WCDI_SMS_TEXT_DOMAIN ),
				'id'    => $wcdismsid.'_divineinfo_auth_token',
				'default' => '',
				'css'	=> '',
				'class' => 'wcdismsinput',
				'desc_tip' => true,
			),
			
			array( 'type' => 'sectionend', 'id' => 'wc_'. $wcdismsid .'_main_divineinfo_section_title'),
			//Divine Info Section End Here
			
			array( 'name' => __( 'SMS Notification Settings for Administrator', WCDI_SMS_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'wc_'. $wcdismsid .'_main_admin_notification_setting_section_title', ),
				
			array(
				'title' => __( 'Enable / Disable Admin Notification', WCDI_SMS_TEXT_DOMAIN ),
				'name'  => $wcdismsid.'_enable_disable_admin_notification',
				'type'  => 'checkbox',
				'id'    => $wcdismsid.'_enable_disable_admin_notification',
				'default' => '',
				'css'	=> '',
				'desc_tip' => true,
			),
			
			array(
				'title' => __( 'Enter Admin Phone Number', WCDI_SMS_TEXT_DOMAIN ),
				'name'  => $wcdismsid.'_admin_number',
				'type'  => 'text',
				'desc'  => __( 'Enter admin phone number for receiving SMS on customer place orders. ( Including country code. eg.+91XXXXXXXXXX )', WCDI_SMS_TEXT_DOMAIN ),
				'id'    => $wcdismsid.'_admin_number',
				'default' => '',
				'css'	=> '',
				'class' => 'wcdismsinput',
				'desc_tip' => true,
			),
				
			array(
				'title' => __( 'Admin SMS Text', WCDI_SMS_TEXT_DOMAIN ),
				'name'  => $wcdismsid.'_admin_sms_text',
				'type'  => 'textarea',
				//'desc'	=> $sms_text_descritption,
				'id'    => $wcdismsid.'_admin_sms_text',
				'default' => '#{ORDER_NUMBER} is updated with Status {ORDER_STATUS} on {CURRENT_DATE} at {SHOP_NAME}',
				'css'	=> '',
				'class'	=> 'wcdismstextarea'
			),
			
			array( 'type' => 'sectionend', 'id' => 'wc_'. $wcdismsid .'_main_admin_notification_setting_section_title'),
				
				
			array( 'name' => __( 'SMS Notification Settings for Customers', WCDI_SMS_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'wc_'. $wcdismsid .'_main_customer_notification_setting_section_title', ),
			
			$wcdismsid.'_enable_disable_customer_notification' => array(
				'title' => __( 'Enable / Disable Customer Notification', WCDI_SMS_TEXT_DOMAIN ),
				'name'  => $wcdismsid.'_enable_disable_customer_notification',
				'type'  => 'checkbox',
				'default'=> 'yes',
				'id'    => $wcdismsid.'_enable_disable_customer_notification',
			),
			
		);
				
		$settings = @array_merge($settings,$status_lists_arr);
		
		$settings = @array_merge($settings,$settings_customer_data_arr);
		
		$settings_customer_data = array(
			array( 'type' => 'sectionend', 'id' => 'wc_'. $wcdismsid .'_main_customer_notification_setting_section_title'),
							
			array( 'name' => __( 'View SMS History', WCDI_SMS_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'wc_'. $wcdismsid .'_main_view_sent_messages_section_title', 'desc' => '<div id="view-sms-content-main-div">
				<p style="margin:0px;padding:0px;"><span id="view_sms_loader"></span></p><p class="pleasewaittxt">'. __( 'Please wait...', WCDI_SMS_TEXT_DOMAIN ) .'</p><div id="view-sms-content"></div></div>'. __( 'Please', WCDI_SMS_TEXT_DOMAIN ) .' <a id="'. $wcdismsid .'_view_sms_sent_messages" href="#TB_inline?&color=cccccc&height=580&width=860&inlineId=view-sms-content-main-div" class="thickbox" title="'. __( 'SMS History', WCDI_SMS_TEXT_DOMAIN ) .'">'. __( 'Click Here', WCDI_SMS_TEXT_DOMAIN ) .'</a>&nbsp;'. __( 'To view all sent messages. You will get list of all sent SMS along with its status.', WCDI_SMS_TEXT_DOMAIN ) .'.'
						),
						
			array( 'type' => 'sectionend', 'id' => 'wc_'. $wcdismsid .'_main_view_sent_messages_section_title')
		);
			
		$settings = @array_merge($settings,$settings_customer_data);
		
		return apply_filters( 'wc_wcdi_sms_settings', $settings );
	}
}
        
if( !function_exists( 'woocommerce_admin_field_my_button_func' ) )
{ 
	function woocommerce_admin_field_my_button_func($status_name)
	{       
        $output = "<button id='wcdi_sms_shortcode_add_btn_".$status_name['name']."' class='wcdi_sms_shortcode_add_btn' type='button' data-id='jhkjkhj'>
            ".__('Shortcodes',  WCDI_SMS_TEXT_DOMAIN)." <img src='".WCDI_SMS_IMAGES_URL."/down-arrow.png' align='absmiddle'>
            </button>";
        $output.= '<div id="wcdi_sms_shortcode_div_'.$status_name['name'].'" class="wcdi_sms_shortcode_div" style="display: none;">'.__('Your Site Name',WCDI_SMS_TEXT_DOMAIN) .'<code> <b>{SHOP_NAME}</b></code>
                                           <br/>
            '.__('Order Number',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{ORDER_NUMBER}</b></code><br/>
            '.__('Order Status',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{ORDER_STATUS}</b></code><br/>
            '.__('Order Amount',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{ORDER_AMOUNT}</b></code><br/>
            '.__('Order Date',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{ORDER_DATE}</b></code><br/>
            '.__('Order Items',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{ORDER_ITEMS}</b></code><br/>
            '.__('First Name',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{BILLING_FNAME}</b></code><br/>
            '.__('Last Name',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{BILLING_LNAME}</b></code><br/>
            '.__('Billing Email',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{BILLING_EMAIL}</b></code><br/>
            '.__('Current Date',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{CURRENT_DATE}</b></code><br/>
            '.__('Current Time',WCDI_SMS_TEXT_DOMAIN) .'<code><b>{CURRENT_TIME}</b></code><br/>
    	</div>';
        return $output;
	}
}
	
if( !function_exists( 'wcdi_sms_woo_replace_shortcode_variable' ) )
{
	function wcdi_sms_woo_replace_shortcode_variable( $content, $order )	{
		if( !$content || !is_object($order))
			return;
		global $wc_settings_wcdisms;
		$order_id = $order->id;
		
		$order_custom_fields = get_post_custom($order_id);
		$current_date_time = current_time( 'timestamp' );
		
		if( preg_match("/{SHOP_NAME}/i", $content) )
		{
			$SHOP_NAME = get_option( "blogname" );
			$content = @str_replace( "{SHOP_NAME}", $SHOP_NAME, $content );
		}
		
		if( preg_match("/{ORDER_NUMBER}/i", $content) )
		{
			$ORDER_NUMBER = isset( $order_id ) ? $order_id : "";
			$content = @str_replace( "{ORDER_NUMBER}", $ORDER_NUMBER, $content );
		}
			
		if( preg_match("/{ORDER_DATE}/i", $content) )
		{
			$order_date_format = get_option( "date_format" );
			$ORDER_DATE = date_i18n($order_date_format, strtotime( $order->order_date ) );
			$content = @str_replace( "{ORDER_DATE}", $ORDER_DATE, $content );
		}
		
		if( preg_match("/{ORDER_STATUS}/i", $content) )
		{
			$ORDER_STATUS = @ucfirst($order->status);
			$content = @str_replace( "{ORDER_STATUS}", $ORDER_STATUS, $content );
		}
			
		if( preg_match("/{ORDER_ITEMS}/i", $content) )
		{
			$order_items = $order->get_items( apply_filters( "woocommerce_admin_order_item_types", array( "line_item" ) ) );
			$ORDER_ITEMS = "";
			if( count($order_items) )
			{
				$item_cntr = 0;
				foreach ( $order_items as $order_item ) {
					if($order_item["type"]=="line_item")
					{
						if($item_cntr==0)
							$ORDER_ITEMS = $order_item["name"];
						else 
							$ORDER_ITEMS .= ", ". $order_item["name"];
						$item_cntr++;
					}
				}
			}
			$content = @str_replace( "{ORDER_ITEMS}", $ORDER_ITEMS, $content );
		}
			
		if( preg_match("/{BILLING_FNAME}/i", $content) )
		{
			$BILLING_FNAME = $order_custom_fields["_billing_first_name"][0];
			$content = @str_replace( "{BILLING_FNAME}", $BILLING_FNAME, $content );
		}
		
		if( preg_match("/{BILLING_LNAME}/i", $content) )
		{
			$BILLING_LNAME = $order_custom_fields["_billing_last_name"][0];
			$content = @str_replace( "{BILLING_LNAME}", $BILLING_LNAME, $content );
		}
		
		if( preg_match("/{BILLING_EMAIL}/i", $content) )
		{
			$BILLING_EMAIL = $order_custom_fields["_billing_email"][0];
			$content = @str_replace( "{BILLING_EMAIL}", $BILLING_EMAIL, $content );
		}
		
		if( preg_match("/{ORDER_AMOUNT}/i", $content) )
		{
			$ORDER_AMOUNT = $order_custom_fields["_order_total"][0];
			$content = @str_replace( "{ORDER_AMOUNT}", $ORDER_AMOUNT, $content );
		}
			
		if( preg_match("/{CURRENT_DATE}/i", $content) )
		{
			$wp_date_format = get_option( "date_format" );
			$CURRENT_DATE = date_i18n($wp_date_format, $current_date_time );
			$content = @str_replace( "{CURRENT_DATE}", $CURRENT_DATE, $content );
		}
		
		if( preg_match("/{CURRENT_TIME}/i", $content) )
		{
			$wp_time_format = get_option( "time_format" );
			$CURRENT_TIME = date_i18n($wp_time_format, $current_date_time );
			$content = @str_replace( "{CURRENT_TIME}", $CURRENT_TIME, $content );
		}
		return $content;
	}
}
	
if( !function_exists( 'wcdi_sms_woo_get_sms_history_list' ) )
{
	function wcdi_sms_woo_get_sms_history_list()
	{
		global $wpdb, $wc_settings_wcdisms, $wcdismsid, $wcdismslabel;
		
		$RECORDPERPAGE = "10";
		if(isset($_REQUEST['paged']) && $_REQUEST['paged']>0)
		{
			$page = $_REQUEST['paged'];
		}else {
			$page = 0;
		}
			
		$fullresult = $wpdb->get_row( "SELECT count(id) as totalrow FROM ".$wpdb->prefix."wcdi_sms_log_history" );
		$full_total_log = $fullresult->totalrow;
		
		$total_page = ceil($full_total_log/$RECORDPERPAGE);
		
		$get_sms_logs = $wpdb->get_results(($page*$RECORDPERPAGE),$RECORDPERPAGE);
		$startfrom = $page*$RECORDPERPAGE;
		
		$get_logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."wcdi_sms_log_history ORDER BY id DESC LIMIT %d,%d", $startfrom, $RECORDPERPAGE ) );
		
		$added_date_format = get_option( "date_format" );
		$added_time_format = get_option( "time_format" );
		
		$content = "";
		$cntr=$startfrom+1;
		$content = '';
			
		if($full_total_log>0)
		{
			$content .= '<table class="smsstautspaginglink">
				<tr>';
				if($page>0) {
					$content .= '<td>
						<a onclick="sms_gateway_show_list('. ($page-1) .');">'. __( 'Previous', WCDI_SMS_TEXT_DOMAIN ) .'</a>
					</td>';
				}
				if($total_page>$page+1) {
					$add_separator = '';
					if($page>0) {
						$add_separator = '<td>&nbsp;|&nbsp;</td>';
					}
					$content .= $add_separator. '
						<td>
							<a onclick="sms_gateway_show_list('. ($page+1) .');">'. __( 'Next', WCDI_SMS_TEXT_DOMAIN ) .'</a>
						</td>';
				}
			$content .= '</tr>';
			$content .= '</table>';
		}
			
		if( count( $get_logs )>0 )
		{
			$content .= '<table class="data_sms_content">
				<tr>
					<th width="8%">'. __( 'Sr No.', WCDI_SMS_TEXT_DOMAIN ) .'</th>
					<th width="20%">'. __( 'Date Sent', WCDI_SMS_TEXT_DOMAIN ) .'</th>
					<th width="10%">'. __( 'Sent to', WCDI_SMS_TEXT_DOMAIN ) .'</th>
					<th width="35%">'. __( 'Message', WCDI_SMS_TEXT_DOMAIN ) .'</th>
					<th width="14%">'. __( 'Gateway', WCDI_SMS_TEXT_DOMAIN ) .'</th>
				</tr>';
			foreach ($get_logs as $get_log) {
				$sms_histor_id = $get_log->id;
				$sms_gateway = $get_log->sms_gateway;
				if( $sms_gateway=="d" ) { $log_sms_gateway = __( 'Divine Info', WCDI_SMS_TEXT_DOMAIN ); }
				
				$status = $get_log->delivered_flag;
				$status_delivered = "";
				$added_date_time = date_i18n($added_date_format." ".$added_time_format, strtotime( $get_log->added_date_time ) );
				$messagetext = $get_log->messagetext;
				
				$content .= '<tr>
					<td>'. $cntr .'</td>
					<td>'. $added_date_time .'</td>
					<td><span>'. $get_log->to_number .'</span></td>
					<td class="smsmessagebody">'. $messagetext .'</td>
					<td>'. $log_sms_gateway .'</td>
				   ';
				$cntr++;
			}
			$content .= '</table>';
		}else {
			$stylefornotrecord = '';
			if( $full_total_log<1 ) { $stylefornotrecord = 'margin:30px 0;'; }
			$content .= '<table class="nomessagefound_content" style="'. $stylefornotrecord .'">
				<tr>
					<td>'. __( 'No SMS History Found.', WCDI_SMS_TEXT_DOMAIN ) .'</td>
				</tr>
			</table>';
		}
			
		if($full_total_log>0)
		{
			$content .= '<table class="smsstautspaginglink">
				<tr>';
				if($page>0) {
					$content .= '<td>
						<a onclick="sms_gateway_show_list('. ($page-1) .');">'. __( 'Previous', WCDI_SMS_TEXT_DOMAIN ) .'</a>
					 </td>';
				}
				if($total_page>$page+1) {
					$add_separator = '';
					if($page>0) {
						$add_separator = '<td>&nbsp;|&nbsp;</td>';
					}
					$content .= $add_separator. '
						<td>
							<a onclick="sms_gateway_show_list('. ($page+1) .');">'. __( 'Next', WCDI_SMS_TEXT_DOMAIN ) .'</a>
						</td>';
				}
			$content .= '</tr>';
			$content .= '</table>';
		}
		return $content;
	}
}
?>