<?php 
/*
Plugin Name: Woo SMS by Divine Info Service
Description: WooCommerce plugin extension to send Order SMS Notification for customers and admin using Divine Info SMS service.
Version: 1.0
Plugin URI: http://www.divyanshiinfotech.com/
Author: Anil Meena
Author URI: https://www.anilmeena.com/
Text Domain: WC-Divineinfo-SMS
*/

if ( ! defined( "ABSPATH" ) ) exit; // Exit if accessed directly

define("WCDI_SMS_TEXT_DOMAIN","WC-Divineinfo-SMS");

$plugin_dir_name = dirname(plugin_basename( __FILE__ ));

define("WCDI_SMS_GATEEWAY_DIR", WP_PLUGIN_DIR."/".$plugin_dir_name);
define("WCDI_SMS_GATEEWAY_URL", WP_PLUGIN_URL."/".$plugin_dir_name);
define('WCDI_SMS_IMAGES_URL',WCDI_SMS_GATEEWAY_URL . '/images');
define('WCDI_SMS_IMAGES_DIR', WCDI_SMS_GATEEWAY_DIR . '/images');

global $wcdi_sms_gateway_plugin_version, $wcdi_sms_gateway_db_version, $wc_settings_wcdisms, $wcdismsid, $wcdismslabel, $smsforwcdiform;

$wcdi_sms_gateway_plugin_version= "1.0";
$wcdi_sms_gateway_db_version = "1.0";
$wcdi_sms_gateway_list = "wcdi_sms_divineinfo";

if ( ! class_exists( "WC_Divineinfo_SMS_Installer" ) )
{
	class WC_Divineinfo_SMS_Installer {
		public static function init() {
			register_activation_hook( WCDI_SMS_GATEEWAY_DIR, array('WCDI_SMS', 'wcdi_sms_install') );
		}
	
		public static function wcdi_sms_payment_gateway_active_check() {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			return is_plugin_active( 'woocommerce/woocommerce.php' );
		}
	}
}

// WC Checking Available plugin active Detection
if ( ! function_exists( "is_wcdi_sms_woocommerce_active" ) ) {
	function is_wcdi_sms_woocommerce_active() {
		return WC_Divineinfo_SMS_Installer::wcdi_sms_payment_gateway_active_check();
	}
}

if(is_wcdi_sms_woocommerce_active()) {
	$wc_settings_wcdisms = new WC_Settings_WCDISMS();
	require_once( WCDI_SMS_GATEEWAY_DIR.'/core/sms_functions.php' );
}

function wcdi_check_woocommerce_install() {
	if ( ! function_exists( 'WC' ) ) {
		add_action( 'admin_notices', 'wcdi_install_fail_woocommerce_admin_notice' );
	}
	else {
		do_action( 'wcdi_plugin_init' );
	}
}
add_action( 'plugins_loaded', 'wcdi_check_woocommerce_install', 11 );

function wcdi_install_fail_woocommerce_admin_notice() {
	?>
	<div class="error">
		<p><?php _e( 'WooCommerce SMS by Divine Info is enabled but not effective. It requires WooCommerce in order to work.', WCDI_SMS_TEXT_DOMAIN ); ?></p>
	</div>
<?php
}

class WC_Settings_WCDISMS 
{
	// Constructor.
	var $wcdismsid;
	var $wcdismslabel;
	var $smsforwcdiform;
	
	public function __construct() {
		global $wcdismsid, $wcdismslabel,$smsforwcdiform;
		$this->wcdismsid = "wcdi_sms";
		$this->smsforwcdiform = "smsforwcdiform";
		$this->wcdismslabel = __( "SMS Notification", WCDI_SMS_TEXT_DOMAIN );
		$this->request_timeout = 30;
		
		$smsforwcdiform = $this->smsforwcdiform;
		$wcdismsid = $this->wcdismsid;
		$wcdismslabel = $this->wcdismslabel;
		
		if(is_wcdi_sms_woocommerce_active()) {
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_wcdi_sms_settings_heading' ), 50 );
			add_action( 'woocommerce_settings_tabs_'. $this->wcdismsid,  array( &$this, 'wcdi_sms_settings_tab_output' ) );
			add_action( 'woocommerce_update_options_'. $this->wcdismsid, array( &$this, 'update_settings' ) );
			add_action('admin_enqueue_scripts', array( &$this, 'wcdi_sms_set_js'), 11);
			add_action('wp_enqueue_scripts', array( &$this, 'wcdi_sms_set_checkout_js'), 11);
			add_action('admin_enqueue_scripts', array( &$this, 'wcdi_sms_set_css'), 11);
			add_action('wp_enqueue_scripts', array( &$this, 'wcdi_sms_set_checkout_css'), 11);

			// Add the field to the checkout
			add_action( 'woocommerce_after_order_notes', array( &$this, 'wcdi_sms_checkout_fields' ), 10, 1 );
			
			// Update the order meta with field value
			add_action( 'woocommerce_checkout_update_order_meta', array( &$this, 'wcdi_sms_checkout_fields_update' ) );
			add_action( 'init', array( &$this, 'load_wcdi_sms_status_actions' ) );
			
			// Display field value on the order edit page
			add_action( 'woocommerce_admin_order_data_after_billing_address', array( &$this, 'wcdi_sms_checkout_field_display_admin_order_meta' ), 10, 1 );
			
			//view sent message function 
			add_action( 'wp_ajax_view_sms_sent_messages', array( &$this, 'get_sms_gateway_sent_messages' ) );
		}
	}

	public function wcdi_sms_install()
	{
		global $wpdb, $wcdi_sms_gateway_db_version, $wcdi_sms_gateway_plugin_version, $wcdi_sms_gateway_list;
		
		$wcdismsgatewaydbversioncheck = get_option( $this->wcdismsid .'_gateway_db_version');
		
		if( ! empty( $wcdismsgatewaydbversioncheck ) )
			return;
			
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$charset_collate = '';

        if( $wpdb->has_cap( 'collation' ) ){
            if( !empty($wpdb->charset) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if( !empty($wpdb->collate) )
                $charset_collate .= " COLLATE $wpdb->collate";
        }
		
		$sql = "CREATE TABLE `". $wpdb->prefix . $this->wcdismsid ."_log_history` (
			`id` INT( 11 ) NOT NULL AUTO_INCREMENT,
			`sms_gateway` VARCHAR( 10 ) NOT NULL,
			`messageid` TEXT NOT NULL,
			`messagetext` TEXT NOT NULL,
			`to_number` VARCHAR( 50 ) NOT NULL,
			`delivered_flag` INT( 3 ) NOT NULL,
			`added_date_time` DATETIME NOT NULL,
			PRIMARY KEY ( `id` )
		) {$charset_collate};";
		dbDelta($sql);
		
		update_option( $this->wcdismsid .'_gateway_db_version', $wcdi_sms_gateway_db_version);
		update_option( $this->wcdismsid .'_gateway_plugin_version', $wcdi_sms_gateway_plugin_version);
		update_option( $this->wcdismsid .'_gateway_list', $wcdi_sms_gateway_list);
	}

	public function load_wcdi_sms_status_actions()
	{
		global $wc_settings_wcdisms;
		
		$wcdismsgatewaydbversion = get_option('wcdi_sms_gateway_db_version');
		
		if($wcdismsgatewaydbversion == "" || !isset($wcdismsgatewaydbversion))
			$wc_settings_wcdisms->wcdi_sms_install();
		
		$status_lists = $wc_settings_wcdisms->get_wcdi_sms_enable_statuses();
		$total_status_lists = @count($status_lists);
		if($total_status_lists>0)
		{
			global $woocommerce;
			if (version_compare($woocommerce->version, '2.2', '>=')) 
			{
				foreach ( $status_lists as $slug => $status ) {
					add_action( 'woocommerce_order_status_'. strtolower( esc_attr( str_replace( "wc-", "", $slug ) ) ), array( &$this, 'check_wcdi_sms_to_send_customer_payment_complete' ) );
				}
			} else {
				foreach ( $status_lists as $slug => $status ) {
					add_action( 'woocommerce_order_status_'. strtolower( esc_attr( $slug ) ), array( &$this, 'check_wcdi_sms_to_send_customer_payment_complete' ) );
				}
			}
		}
	}

	public function add_wcdi_sms_settings_heading( $pages ) {
		$pages[$this->wcdismsid] = $this->wcdismslabel;
		return $pages;
	}

	public function wcdi_sms_set_js() {
		if(isset($_REQUEST["page"]) && $_REQUEST["page"] != "" && ( $_REQUEST["page"] == "wc-settings" || $_REQUEST["page"] == "woocommerce_settings" ) && (isset($_REQUEST["tab"]) && $_REQUEST["tab"]==$this->wcdismsid )) 
		{
            global $wcdi_sms_gateway_plugin_version;
			wp_register_script( "smsforwcdiservices-js", WCDI_SMS_GATEEWAY_URL . "/js/smsforwcdiservices.js", array("jquery") ,$wcdi_sms_gateway_plugin_version);
			wp_enqueue_script( "smsforwcdiservices-js" );
		}
	}

	public function wcdi_sms_set_checkout_js(){
		if(is_checkout())
		{
            global $wcdi_sms_gateway_plugin_version;
            $extension='.min.js';
			if( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) {
				$extension='.js';
			}
			wp_enqueue_script( 'wcdi-intl-tel-js', WCDI_SMS_GATEEWAY_URL . '/js/script'.$extension, array('jquery'), '1.4.0', true);
		}
	}

	public function wcdi_sms_set_css() {
		if(isset($_REQUEST["page"]) && $_REQUEST["page"] != "" && ( $_REQUEST["page"] == "wc-settings" || $_REQUEST["page"] == "woocommerce_settings" ) && (isset($_REQUEST["tab"]) && $_REQUEST["tab"]==$this->wcdismsid )) 
		{
            global $wcdi_sms_gateway_plugin_version;
			wp_register_style("smsforwcdiservices-css", WCDI_SMS_GATEEWAY_URL . "/css/smsforwcdiservices.css",array(), $wcdi_sms_gateway_plugin_version );
			wp_enqueue_style("smsforwcdiservices-css");
		}
	}

	public function wcdi_sms_set_checkout_css() {
		if(is_checkout())
		{
            global $wcdi_sms_gateway_plugin_version;
			wp_register_style("smsforwcdicheckout-style-css", WCDI_SMS_GATEEWAY_URL . "/css/smsforwcdicheckout-style.css",array(), $wcdi_sms_gateway_plugin_version );
			wp_enqueue_style("smsforwcdicheckout-style-css");
		}
	}

	function wcdi_sms_settings_tab_output() {
           woocommerce_admin_fields( get_wcdi_sms_woo_setting_fields() );
	}
	
	function update_settings() {
		woocommerce_update_options( get_wcdi_sms_woo_setting_fields() );
	}

	function get_wcdi_sms_enable_statuses()
	{
		global $woocommerce;
		if (version_compare($woocommerce->version, '2.2', '>=')) 
		{
			$statuses = wc_get_order_statuses();
		}else {
			$statuses = array();
			$term_statuses = (array) get_terms( 'shop_order_status', array( 'hide_empty' => 0, 'orderby' => 'id' ) );
			if(count($term_statuses)>0)
			{
				foreach( $term_statuses as $term_status )
				{
					$statuses[$term_status->slug] = $term_status->name;
				}
			}
		}
		
		return $statuses;
	}

	//Code for show Custom Fields
	function wcdi_sms_checkout_fields( $checkout )
	{
		$check_sms_gateway_enabled = get_option( $this->wcdismsid .'_gateway_list' );
		$enable_disable_customer_notification = get_option( $this->wcdismsid ."_enable_disable_customer_notification" );

		if($check_sms_gateway_enabled!="" && $enable_disable_customer_notification=="yes")
		{
			$opt_in_checkbox_label = get_option( $this->wcdismsid.'_opt_in_checkbox_label' );
			if(!$opt_in_checkbox_label)
				$opt_in_checkbox_label = __('I want to Receive Order Updates by SMS', WCDI_SMS_TEXT_DOMAIN );
				
			$opt_in_checkbox_default_value = get_option( $this->wcdismsid.'_opt_in_checkbox_default_value' );
				
			echo '<div id="'. $this->wcdismsid .'_send_me_sms_order_status_updates_heading"><h3>' . __("Get SMS Updates?", WCDI_SMS_TEXT_DOMAIN ) . '</h3>';
				woocommerce_form_field( 
					$this->wcdismsid .'_send_me_sms_order_status_updates',
					array(
						'type' => 'checkbox',
						'class' => array('form-row-wide'),
						'label' => $opt_in_checkbox_label,
						'default' => $opt_in_checkbox_default_value,
						),
						$checkout->get_value( $this->wcdismsid .'_send_me_sms_order_status_updates' )
				);
				 
			echo '</div>';

			// Output the hidden field
		    echo '<div id="user_link_hidden_checkout_field">
	            <input type="hidden" class="input-hidden" name="wcdi_sms_intl_tel_full" id="wcdi_sms_intl_tel_full" value="">
	            <input type="hidden" class="input-hidden" name="wcdi_sms_intl_tel_country_name" id="wcdi_sms_intl_tel_country_name" value="">
	            <input type="hidden" class="input-hidden" name="wcdi_sms_intl_tel_country_code" id="wcdi_sms_intl_tel_country_code" value="">
	            <input type="hidden" class="input-hidden" name="wcdi_sms_intl_tel_country_iso2" id="wcdi_sms_intl_tel_country_iso2" value="">
		    </div>';
		}
	}
	
	//Code for update Custom Fields
	function wcdi_sms_checkout_fields_update( $order_id ) {
		if ( ! empty( $_POST[$this->wcdismsid .'_send_me_sms_order_status_updates'] ) ) {
			update_post_meta( $order_id, $this->wcdismsid .'_send_me_sms_order_status_updates', sanitize_text_field( $_POST[ $this->wcdismsid .'_send_me_sms_order_status_updates' ] ) );
		}
		if ( ! empty( $_POST[$this->wcdismsid .'_intl_tel_full'] ) ) {
			update_post_meta( $order_id, $this->wcdismsid .'_intl_tel_full', sanitize_text_field( $_POST[ $this->wcdismsid .'_intl_tel_full' ] ) );
		}
		if ( ! empty( $_POST[$this->wcdismsid .'_intl_tel_country_name'] ) ) {
			update_post_meta( $order_id, $this->wcdismsid .'_intl_tel_country_name', sanitize_text_field( $_POST[ $this->wcdismsid .'_intl_tel_country_name' ] ) );
		}
		if ( ! empty( $_POST[$this->wcdismsid .'_intl_tel_country_code'] ) ) {
			update_post_meta( $order_id, $this->wcdismsid .'_intl_tel_country_code', sanitize_text_field( $_POST[ $this->wcdismsid .'_intl_tel_country_code' ] ) );
		}
		if ( ! empty( $_POST[$this->wcdismsid .'_intl_tel_country_iso2'] ) ) {
			update_post_meta( $order_id, $this->wcdismsid .'_intl_tel_country_iso2', sanitize_text_field( $_POST[ $this->wcdismsid .'_intl_tel_country_iso2' ] ) );
		}
	}
	
	//Code for Shows that, is user have subscribe sms gateway or not
	function wcdi_sms_checkout_field_display_admin_order_meta( $order ) {
		$chk_user_subscribe_sms = get_post_meta( $order->id, $this->wcdismsid .'_send_me_sms_order_status_updates', true );
		$chk_user_intl_tel_full = get_post_meta( $order->id, $this->wcdismsid .'_intl_tel_full', true );
		$chk_user_intl_tel_country_name = get_post_meta( $order->id, $this->wcdismsid .'_intl_tel_country_name', true );
		$chk_user_intl_tel_country_code= get_post_meta( $order->id, $this->wcdismsid .'_intl_tel_country_code', true );
		$chk_user_intl_tel_country_iso2 = get_post_meta( $order->id, $this->wcdismsid .'_intl_tel_country_iso2', true );
		if( $chk_user_subscribe_sms ) {
			$chk_user_subscribe_sms_text = __( 'Yes', WCDI_SMS_TEXT_DOMAIN );
		}else {
			$chk_user_subscribe_sms_text = __( 'No', WCDI_SMS_TEXT_DOMAIN );
		}
		echo '<div><strong>'.__( 'Enable', WCDI_SMS_TEXT_DOMAIN ) .' '. $this->wcdismslabel .':</strong> ' . $chk_user_subscribe_sms_text . '</div>';
		echo '<div><strong>'.__( 'Phone with Country Code', WCDI_SMS_TEXT_DOMAIN ) .':</strong> ' . $chk_user_intl_tel_full . '</div>';
		echo '<div><strong>'.__( 'Country Name', WCDI_SMS_TEXT_DOMAIN ) .':</strong> ' . $chk_user_intl_tel_country_name . '</div>';
		echo '<div><strong>'.__( 'Country Code', WCDI_SMS_TEXT_DOMAIN ) .':</strong> ' . $chk_user_intl_tel_country_code . '</div>';
		echo '<div><strong>'.__( 'Country ISO2', WCDI_SMS_TEXT_DOMAIN ) .':</strong> ' . $chk_user_intl_tel_country_iso2 . '</div>';
	}

	//Code for call this function for payment is done
	function check_wcdi_sms_to_send_customer_payment_complete( $order_id )
	{
		global $wc_settings_wcdisms;
		$check_sms_gateway_enabled = get_option( $this->wcdismsid ."_gateway_list" );
		if($check_sms_gateway_enabled=="") {
			return;
		}
		
		$check_order_sms_gateway_enabled = get_post_meta( $order_id, $this->wcdismsid .'_send_me_sms_order_status_updates', true );
		
		if(!$check_order_sms_gateway_enabled) {
			return;
		}
		
		$order = new WC_Order( $order_id );
		$user_id = $order->user_id;
		
		$order_status = $wc_settings_wcdisms->wcdi_sms_new_order_status( $order );
		$chk_sms_setting_status_enable = get_option( $this->wcdismsid ."_enable_". $order_status ."_sms_notify_status" );
		
		//Checking for status is enabled from sms settings
		if($chk_sms_setting_status_enable=="yes")
		{
			//Start Condition For Divine Info SMS
			if( $check_sms_gateway_enabled==$this->wcdismsid .'_divineinfo' )
			{
				$wc_settings_wcdisms->SendSMSFromDivineInfo( $order );
			}
			//End Condition For Divine Info SMS
		}
	}

	function get_customer_sms_text( $order ) {
		global $wc_settings_wcdisms;
		$order_status = $wc_settings_wcdisms->wcdi_sms_new_order_status( $order );
		$customer_sms_text_default = stripslashes_deep(get_option( $this->wcdismsid ."_". $order_status ."_sms_text" ));
		$customer_sms_text = wcdi_sms_woo_replace_shortcode_variable( $customer_sms_text_default, $order);
		return $customer_sms_text;
	}
	
	function get_admin_sms_text( $order ) {
		global $wc_settings_wcdisms;
		$admin_sms_text_default = stripslashes_deep(get_option( $this->wcdismsid ."_admin_sms_text" ));
		$admin_sms_text = wcdi_sms_woo_replace_shortcode_variable( $admin_sms_text_default, $order);
		return $admin_sms_text;
	}

	function AddMessageHistory($sms_gateway, $messageid, $messagetext, $to_number, $deliveryStatus = 0)
	{
		global $wpdb, $wc_settings_wcdisms;
		$added_date_time = current_time( 'mysql' );
		$res = $wpdb->insert(
			$wpdb->prefix. $this->wcdismsid .'_log_history',
			array( 
				'sms_gateway' => $sms_gateway,
				'messageid' => maybe_serialize( $messageid ),
				'messagetext' => $messagetext,
				'to_number' => $to_number,
				'delivered_flag' => $deliveryStatus,
				'added_date_time' => $added_date_time,
			), array( '%s', '%s', '%s', '%s', '%s' )
		);
	}
	
	function SendSMSFromDivineInfo( $order )
	{
		global $wc_settings_wcdisms;
		$order_id = $order->get_id();
		$order_status = $wc_settings_wcdisms->wcdi_sms_new_order_status( $order );
		$chk_divineinfo_account_sid = get_option( $this->wcdismsid ."_divineinfo_account_sid" );
		$chk_divineinfo_auth_token = get_option( $this->wcdismsid ."_divineinfo_auth_token" );
		
		if($chk_divineinfo_account_sid!="" && $chk_divineinfo_auth_token!="")
		{		
			$customer_sms_text = $wc_settings_wcdisms->get_customer_sms_text( $order );
			$admin_sms_text = $wc_settings_wcdisms->get_admin_sms_text( $order );
					
			//Send SMS to ADMIN
			$chk_admin_enabled_notification = get_option( $this->wcdismsid ."_enable_disable_admin_notification" );
			$chk_wcdi_sms_admin_number = get_option( $this->wcdismsid ."_admin_number" );
			if($chk_admin_enabled_notification=="yes" && $chk_wcdi_sms_admin_number!="")
			{	
				try {
			        $adminData = array();
			        $adminData["body"]["authkey"] = $chk_divineinfo_auth_token;
					$adminData["body"]["mobiles"] = $chk_wcdi_sms_admin_number;
					$adminData["body"]["message"] = urlencode($admin_sms_text);
					$adminData["body"]["sender"] = $chk_divineinfo_account_sid;
					$adminData["body"]["route"] = 4;
								
			        //API URL
			        $url="http://sms.divineinfo.net/api/sendhttp.php";
			        $smsAdminResp = wp_remote_post( $url, $adminData );

			        // Check for error
					if ( is_wp_error( $smsAdminResp ) ) {
						return;
					}

					$smsId = $smsAdminResp['body'];
					$messageid = array( "msgid" => $smsId );
					$sms_gateway = 'd';
					$deliveryStatus = 1;
					$wc_settings_wcdisms->AddMessageHistory($sms_gateway, $messageid, $admin_sms_text, $chk_wcdi_sms_admin_number, $deliveryStatus);
				} catch (Exception $e) {
					$smsId = 400;
					$messageid = array( "msgid" => $smsId );
					$sms_gateway = 'd';
					$deliveryStatus = 0;
					$wc_settings_wcdisms->AddMessageHistory($sms_gateway, $messageid, $admin_sms_text, $chk_wcdi_sms_admin_number, $deliveryStatus);
				}
			}
			
			$chk_customer_enabled_notification = get_option( $this->wcdismsid ."_enable_disable_customer_notification" );
			$chk_wcdi_sms_customer_phone = $wc_settings_wcdisms->wcdi_sms_woo_get_customer_nubmer( $order_id ); //This value from WooCommerce Ordered.
			
			if($chk_customer_enabled_notification=="yes" && $chk_wcdi_sms_customer_phone!="")
			{
				try {
					$customerData = array();
			        $customerData["body"]["authkey"] = $chk_divineinfo_auth_token;
					$customerData["body"]["mobiles"] = $chk_wcdi_sms_customer_phone;
					$customerData["body"]["message"] = urlencode($customer_sms_text);
					$customerData["body"]["sender"] = $chk_divineinfo_account_sid;
					$customerData["body"]["route"] = 4;
								
			        //API URL
			        $url="http://sms.divineinfo.net/api/sendhttp.php";
			        $smsCustomerResp = wp_remote_post( $url, $customerData );

			        // Check for error
					if ( is_wp_error( $smsCustomerResp ) ) {
						return;
					}
					
					$smsId = $smsCustomerResp['body'];
					$messageid = array( "msgid" => $smsId );
					$sms_gateway = 'd';
					$deliveryStatus = 1;
					$wc_settings_wcdisms->AddMessageHistory($sms_gateway, $messageid, $customer_sms_text, $chk_wcdi_sms_customer_phone, $deliveryStatus);
					//}
				} catch (Exception $e) {
					$smsId = 500;
					$messageid = array( "msgid" => $smsId );
					$sms_gateway = 'd';
					$deliveryStatus = 0;
					$wc_settings_wcdisms->AddMessageHistory($sms_gateway, $messageid, $customer_sms_text, $chk_wcdi_sms_customer_phone, $deliveryStatus);
				}
			}
		}
	}

	function get_sms_gateway_sent_messages()
	{
		$list = wcdi_sms_woo_get_sms_history_list();
		echo $list;
		die();
	}

	function wcdi_sms_woo_get_customer_nubmer( $order_id )
	{
		return get_post_meta( $order_id, "_intl_tel_full", true );
	}

	function wcdi_sms_new_order_status( $order )
	{
		global $woocommerce, $wpdb, $wc_settings_wcdisms;
		if (version_compare($woocommerce->version, '2.2', '>=')) 
		{
			$order_status = $order->post_status;
		}else {
			$order_status = $order->status;
		}
		return $order_status;
	}
}

function WCDISMSGatewayUninstall() 
{
	global $wpdb, $wcdismsid;
	if($wcdismsid=="") { $wcdismsid = "wcdi_sms"; }
	
	if ( is_multisite() ) 
	{
		$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
		if ($blogs) {
			foreach($blogs as $blog) {
				switch_to_blog($blog['blog_id']);
				$wpdb->query('DROP TABLE IF EXISTS '. $wpdb->prefix.  $wcdismsid .'_log_history');
				
				//Delete SMSWooCommerce options
				$wpdb->query('DELETE FROM '. $wpdb->prefix .'options WHERE option_name LIKE "'. $wcdismsid .'_%"');
			}
			restore_current_blog();
		}	
	}
	else
	{
		$wpdb->query('DROP TABLE IF EXISTS '. $wpdb->prefix.  $wcdismsid .'_log_history');
		
		//Delete SMSWooCommerce options
		$wpdb->query('DELETE FROM '. $wpdb->prefix .'options WHERE option_name LIKE "'. $wcdismsid .'_%"');
	}
}
register_uninstall_hook( __FILE__, 'WCDISMSGatewayUninstall' );