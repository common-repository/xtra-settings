<?php
/*
Plugin Name: XTRA Settings
Plugin URI: https://wordpress.org/plugins/xtra-settings/
Description: All useful hidden settings of Wordpress
Version: 2.1.8
Author: fures
Author URI: http://www.fures.hu/xtra-settings/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: xtra-settings
Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$xtra_do_memlog = 0;
if ($xtra_do_memlog) xtra_memlog("---> start xtra.php");

//. Defines
define( 'XTRA_VERSION', '2.1.8' );
define( 'XTRA_PLUGIN', __FILE__ );
define( 'XTRA_PLUGIN_BASENAME', plugin_basename( XTRA_PLUGIN ) );
define( 'XTRA_PLUGIN_SLUG', dirname( plugin_basename( XTRA_PLUGIN ) ) );
define( 'XTRA_PLUGIN_DIR', str_replace( XTRA_PLUGIN_BASENAME, "", XTRA_PLUGIN ) . trailingslashit(XTRA_PLUGIN_SLUG) );
define( 'XTRA_WPCONTENT_BASENAME', str_ireplace(ABSPATH,"",WP_CONTENT_DIR) );
define( 'XTRA_UPLOAD_DIR', wp_upload_dir()['basedir'] );
define( 'XTRA_UPLOAD_URL', wp_upload_dir()['baseurl'] );
$exploded_uri = explode('/', site_url());
$xtra_host = $exploded_uri[0]."//".$exploded_uri[2];
if (strpos(site_url(),"://")===false)
	$xtra_host = "http://".$exploded_uri[0];
define( 'XTRA_HOST', $xtra_host );

//date_default_timezone_set( get_option('timezone_string', 'UTC') ); //it interferes with WP Site Health check

// set missing array indexes in $_SERVER
$_SERVER["REMOTE_ADDR"]     	 = array_key_exists( 'REMOTE_ADDR',      	$_SERVER) ? $_SERVER['REMOTE_ADDR']     	 : '';
$_SERVER["REMOTE_HOST"]     	 = array_key_exists( 'REMOTE_HOST',      	$_SERVER) ? $_SERVER['REMOTE_HOST']     	 : '';
$_SERVER["SERVER_PROTOCOL"] 	 = array_key_exists( 'SERVER_PROTOCOL',  	$_SERVER) ? $_SERVER['SERVER_PROTOCOL'] 	 : "HTTP/1.1";
$_SERVER["REQUEST_METHOD"]  	 = array_key_exists( 'REQUEST_METHOD',   	$_SERVER) ? $_SERVER['REQUEST_METHOD']  	 : "GET";
$_SERVER["SERVER_PORT"]     	 = array_key_exists( 'SERVER_PORT',      	$_SERVER) ? $_SERVER['SERVER_PORT']     	 : "80";
$_SERVER["SERVER_SOFTWARE"] 	 = array_key_exists( 'SERVER_SOFTWARE',  	$_SERVER) ? $_SERVER['SERVER_SOFTWARE'] 	 : '';
$_SERVER["HTTP_ACCEPT"]     	 = array_key_exists( 'HTTP_ACCEPT',      	$_SERVER) ? $_SERVER['HTTP_ACCEPT']     	 : "text/html,application/xhtml+xml,application/xml,application/json";
$_SERVER["HTTP_ACCEPT_LANGUAGE"] = array_key_exists( 'HTTP_ACCEPT_LANGUAGE',$_SERVER) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "en";
$_SERVER["HTTP_HOST"]       	 = array_key_exists( 'HTTP_HOST',        	$_SERVER) ? $_SERVER['HTTP_HOST']       	 : '';
$_SERVER["HTTP_USER_AGENT"] 	 = array_key_exists( 'HTTP_USER_AGENT',  	$_SERVER) ? $_SERVER['HTTP_USER_AGENT'] 	 : '';
$_SERVER["HTTP_REFERER"] 		 = array_key_exists( 'HTTP_REFERER',     	$_SERVER) ? $_SERVER['HTTP_REFERER']    	 : wp_get_referer();

add_action( 'init', 'xtra_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function xtra_load_textdomain() { load_plugin_textdomain( 'xtra-settings', false, basename( dirname( __FILE__ ) ) . '/languages/' ); }


//.. just for xtra_options_compat
if (false) update_optionXTRA('xtra_options_convert_once', 0); //manual delete of old options - if one load was not enough
get_optionXTRA( 'xtra_browserbugs' ); //not called - deprecated
get_optionXTRA( 'xtra_redir_bots_text' ); //not called - deprecated
get_optionXTRA( 'xtra_deactivate_names', array() ); //not called - deprecated
get_optionXTRA( 'xtra_hits_exclude_string_text' ); //not called - deprecated
get_optionXTRA( 'xtra_hits_last_time' ); //not called - too late: shutdown/wp_footer

//.. Remove deprecated functions
update_optionXTRA( 'xtra_browserbugs', 0 );
if ( get_optionXTRA('xtra_protect_xss') || get_optionXTRA('xtra_protect_pageframing') || get_optionXTRA('xtra_protect_cotentsniffing') ) {
	xtra_protect_xss(0);
	xtra_protect_pageframing(0);
	xtra_protect_cotentsniffing(0);
	xtra_response_header_hardening(1);
}
delete_optionXTRA('xtra_protect_xss');
delete_optionXTRA('xtra_protect_pageframing');
delete_optionXTRA('xtra_protect_cotentsniffing');
if ( get_optionXTRA('xtra_redir_bots') ) {
	xtra_redir_bots(0);
	update_optionXTRA( 'xtra_redir_bots2', 1 );
}
delete_optionXTRA('xtra_redir_bots');
if ( get_optionXTRA('xtra_block_external_post') ) {
	xtra_block_external_post(0);
	update_optionXTRA( 'xtra_block_external_post2', 1 );
}
delete_optionXTRA('xtra_block_external_post');
if ( get_optionXTRA('xtra_WPcache') ) {
	xtra_WPcache(0);
	delete_optionXTRA('xtra_WPcache');
	xtra_options_save();
}

if ( !get_optionXTRA('xtra_correct_htacc') ) {
	xtra_correct_htacc();
	update_optionXTRA( 'xtra_correct_htacc', 1 );
	xtra_options_save();
}

//. Run before output
if(isset($_POST['xtra_allopts_backup'])) {
	//xtra_check_nonce();
	xtra_allopts_EXPORT();
	exit;
}
if(isset($_POST['xtra_database_backup'])) {
	//xtra_check_nonce();
	xtra_EXPORT_TABLES(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	exit;
}


//. Register the activation hooks
register_activation_hook( __FILE__, 'xtra_activate' );
register_deactivation_hook( __FILE__, 'xtra_deactivate' );

//. Admin menu
add_action('admin_menu', 'xtra_menu');
function xtra_menu() {
	add_menu_page( 'XTRA Settings', 'XTRA Settings', 'manage_options', 'xtra', 'xtra_html_page', 'dashicons-lightbulb', 81 );
}
/*----- moved to the end ----------
function xtra_html_page() {
	global $wpdb;
	if ( !current_user_can('manage_options') ) {
		echo 'This page is only for administrators.';
		exit;
	}
	//.. Include admin-setting.php
	include_once('admin-setting.php');
	update_optionXTRA('xtra_options_convert_once', 1);
}
*/

//. SET DEFAULTS
$xtra_sets = xtra_make_dataset();
foreach ($xtra_sets as $setname => $set) get_optionXTRA($setname,$set['default']);
$xtra_sets = xtra_make_ownset();
foreach ($xtra_sets as $setname => $set) if (empty($set['title'])) get_optionXTRA($setname,$set['default']);
$xtra_sets = "";

//. Include ajax.php - if called
if (xtra_instr($_SERVER['REQUEST_URI'], 'admin-ajax.php'))
	include_once('ajax.php');

//. Activate - deactivate
if( !function_exists( 'xtra_activate' ) ) {
	function xtra_activate($networkwide) {
		global $wpdb;
		if (function_exists('is_multisite') && is_multisite()) {
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					_xtra_activate();
				}
				switch_to_blog($old_blog);
				return;
			}
		}
		_xtra_activate();
	}
	function _xtra_activate() {
		xtra_dir_index_disable(1);
		update_optionXTRA( 'xtra_remove_version_number', 1 );
		update_optionXTRA( 'xtra_suppress_login_errors', 1 );
		xtra_deflates(1);
		xtra_image_expiry(1);
		xtra_remove_etags(1);
		return true;
	}
	function xtra_deactivate($networkwide) {
		global $wpdb;
		if (function_exists('is_multisite') && is_multisite()) {
			if ($networkwide) {
				$old_blog = $wpdb->blogid;
				$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					_xtra_remove_all_file_insertions();
				}
				switch_to_blog($old_blog);
				return;
			}
		}
		_xtra_remove_all_file_insertions();
	}
	function _xtra_remove_all_file_insertions() {
		$names = get_optionXTRA( 'xtra_deactivate_names' );
		if (!is_array($names) || count((array)$names)<10 ) {
			$names = array(
				'xtra_deflates',
				'xtra_browserbugs',
				'xtra_image_expiry',
				'xtra_remove_etags',
				//'xtra_redir_bots',
				'xtra_dir_index_disable',
				'xtra_response_header_hardening',
				//'xtra_protect_xss',
				//'xtra_protect_pageframing',
				//'xtra_protect_cotentsniffing',
				//'xtra_block_external_post',
				//'xtra_WPcache',
				'xtra_autosave_interval',
				'xtra_empty_trash',
				'xtra_debug',
				'xtra_disable_debug_display',
				'xtra_debug_log'
			);
			update_optionXTRA( 'xtra_deactivate_names', $names );
		}
		foreach ($names as $name) {
			if (!empty($name) && function_exists($name)) $name(0); //call functions dynamically with FALSE
		}
		return true;
	}
}

//. Monitor new blog create if multisite
function xtra_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	global $wpdb;
	if ( ! function_exists( 'is_plugin_active_for_network' ) && is_multisite() ) {
		// need to include the plugin library for the is_plugin_active function
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	if (is_plugin_active_for_network( XTRA_PLUGIN_BASENAME )) {
		$old_blog = $wpdb->blogid;
		switch_to_blog($blog_id);
		_shiba_activate();
		switch_to_blog($old_blog);
	}
}
add_action( 'wpmu_new_blog', 'xtra_new_blog', 10, 6);

//. Add settings link on plugin page
function xtra_settings_link ($links) {
	$settings_link = '<a href="admin.php?page=xtra">'.esc_html__('Settings', 'xtra-settings').'</a>';
	array_unshift($links, $settings_link);
	return $links;
}
$xtra_plugin = XTRA_PLUGIN_BASENAME;
add_filter("plugin_action_links_$xtra_plugin", 'xtra_settings_link' );

function xtra_version_id() {
	if ( WP_DEBUG )
		return time();
	return XTRA_VERSION;
}

function xtra_load_admin_scripts($hook) {

	// for Dashboard Hits Counter Chart
	if($hook == 'index.php') {
		if ( get_optionXTRA('xtra_hits_dashboard_widget') ) {
			wp_enqueue_style( 'xtra_wp_admin_css', plugins_url('/assets/css/admin.css', __FILE__), array(), xtra_version_id() );
			if ( get_optionXTRA('xtra_show_hit_chart') ) {
				wp_enqueue_script( 'xtra_google_charts', 'https://www.gstatic.com/charts/loader.js' );
			}
		}
	}

	// only for xtra page
	if($hook != 'toplevel_page_xtra') return;

	// page css
	wp_enqueue_style( 'xtra_wp_admin_css', plugins_url('/assets/css/admin.css', __FILE__), array(), xtra_version_id() );
	if(isset($_POST['xtra_submit_options'])) {
		xtra_check_nonce();
		update_optionXTRA( 'xtra_opt_light', $_POST['xtra_opt_light'] );
		update_optionXTRA( 'xtra_opt_vertical', $_POST['xtra_opt_vertical'] );
	}
	if ( get_optionXTRA('xtra_opt_light') )
		wp_enqueue_style( 'xtra_wp_admin_tabs_css', plugins_url('/assets/css/admin-tabs.css', __FILE__), array(), xtra_version_id() );
	if ( get_optionXTRA('xtra_opt_vertical') )
		wp_enqueue_style( 'xtra_wp_admin_tabs_vertical_css', plugins_url('/assets/css/admin-tabs-vertical.css', __FILE__), array(), xtra_version_id() );

	// page js
	wp_enqueue_script( 'xtra_admin_tabs_js', plugins_url('/assets/js/admin-tabs.js', __FILE__ ), array( 'jquery' ), xtra_version_id() );
	wp_enqueue_script( 'xtra_admin_search_js', plugins_url('/assets/js/admin-search.js', __FILE__ ), array( 'jquery' ), xtra_version_id() );
	wp_enqueue_script( 'xtra_compress_script', plugins_url('/assets/js/compress.js', __FILE__ ), array( 'jquery' ), xtra_version_id() );
	wp_localize_script( 'xtra_compress_script', 'xtra_vars', array(
			'_wpnonce' => wp_create_nonce( 'xtra_ajax_nonce' ),
			'invalid_response' => esc_html__( 'Invalid ajax response. Check the console for errors.', 'xtra-settings' ),
			'started' => esc_html__( 'Started...', 'xtra-settings' ),
			'simulation_mode' => esc_html__( 'SIMULATION MODE', 'xtra-settings' ),
			'checksure' => esc_html__( 'check "I am sure" to enable REAL MODE.', 'xtra-settings' ),
			'nothing_selected' => esc_html__( 'NOTHING was selected.', 'xtra-settings' ),
			'stopped' => esc_html__( 'Stopped', 'xtra-settings' ),
			'refresh' => esc_html__( 'Refresh Image List', 'xtra-settings' ),
			'stop' => esc_html__( 'Stop', 'xtra-settings' ),
			'sec' => esc_html__( 'sec', 'xtra-settings' ),
			'compression' => esc_html__( 'Compression', 'xtra-settings' ),
			'restore' => esc_html__( 'Restore', 'xtra-settings' ),
			'xdelete' => esc_html__( 'Delete', 'xtra-settings' ),
			'ximgdelete' => esc_html__( 'Image-Delete', 'xtra-settings' ),
			'regenerate' => esc_html__( 'Regenerate', 'xtra-settings' ),
		)
	);

	// google chart
	if ( get_optionXTRA('xtra_show_hit_chart') )
		wp_enqueue_script( 'xtra_google_charts', 'https://www.gstatic.com/charts/loader.js' );


	// others
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'xtra_load_admin_scripts' );



function xtra_admin_message() {
	$xtra_msg = get_optionXTRA( 'xtra_msg', 0 );
	if($xtra_msg!=1){
		echo '<div id="xtra_admin_message" class="notice-warning settings-error notice is-dismissible">
		<p>XTRA Settings '. sprintf(esc_html__('is installed. We enabled some Security and Speed settings. Check %s page!', 'xtra-settings'),'<a href="admin.php?page=xtra&first=1">XTRA Settings</a>') .'!</p>
		</div>';
	}
}
add_action( 'admin_notices', 'xtra_admin_message' );



if ($xtra_do_memlog) xtra_memlog("Admin settings");

//---optionXTRA----
function get_optionXTRA($name,$def="") {
global $xtra_options;
	if(strpos($name, 'xtra_')!==0) return get_option($name,$def);

	xtra_options_load();
	xtra_options_compat($name,$def);
	//if ( !isset($xtra_options[$name]) ) return $def;
	if ( !@array_key_exists($name,$xtra_options) ) return $def;
	return $xtra_options[$name];
}
function update_optionXTRA($name,$val="") {
global $xtra_options;
	if(strpos($name, 'xtra_')!==0) return update_option($name,$val);

	xtra_options_load();
	xtra_options_compat($name);
	$xtra_options[$name] = $val;
	//xtra_options_save(); // added action at shutdown
}
function delete_optionXTRA($name) {
global $xtra_options;
	if(strpos($name, 'xtra_')!==0) return delete_option($name);

	xtra_options_load();
	xtra_options_compat($name);
	//if (isset($xtra_options[$name])) unset($xtra_options[$name]);
	if (@array_key_exists($name,$xtra_options)) unset($xtra_options[$name]);
	//xtra_options_save(); // added action at shutdown
}

function xtra_options_load($force=0) {
global $xtra_options, $xtra_options_name;
	$xtra_options_name = "xtra_settings_data";
	if ($force) xtra_options_save();
	//if ( !isset($xtra_options_name) ) $xtra_options_name = get_option('xtra_settings_name', 'xtra_settings_data');
	if ( !isset($xtra_options) || $force ) $xtra_options = get_option($xtra_options_name,array());
}
function xtra_options_save() {
global $xtra_options, $xtra_options_name;
	$xtra_options_name = "xtra_settings_data";
	update_option($xtra_options_name,$xtra_options);
}
function xtra_options_compat($name,$def="") {
global $xtra_options;
	//if ( !isset($xtra_options[$name]) ) $xtra_options[$name] = get_option($name,$def); //convert old option into new array-format
	if ( !@array_key_exists($name,$xtra_options) ) $xtra_options[$name] = get_option($name,$def); //convert old option into new array-format
	if ( !$xtra_options['xtra_options_convert_once'] && get_option($name, null) !== null ) delete_option($name); //delete old option - only once
}

//---DATASET----
function xtra_make_ownset() {
	$set = array(
		'xtra_opt_show_own' 				=> array( 'default' => 0 ,	'label' => "".esc_html__('Show in Tab', 'xtra-settings') ."<br/><span>(".esc_html__('Show this box in a tab on the top', 'xtra-settings') .")</span>"),
		'xtra_opt_admin_bar_menu' 			=> array( 'default' => 1 ,	'label' => "".esc_html__('Admin Bar', 'xtra-settings') ."<br/><span>".esc_html__('Add XTRA-Menu to Admin Bar', 'xtra-settings') ."</span>"),
		'b1' 			=> array( 'title' => 2 ,	'label' => "".esc_html__('Show All WP-Options', 'xtra-settings') ."<br/><span> ".esc_html__('and indicate possible abandoned orphans left by badly unistalled plugins.', 'xtra-settings') ." <br/>".esc_html__('Separate page for a long list of options. Might not load on some servers due to low memory or exec-time. Try it again.', 'xtra-settings') ."</span>"),
		't1' 			=> array( 'title' => 1 ,	'label' => "".esc_html__('Show Optional Tabs', 'xtra-settings') ."<br/>"),
		'xtra_opt_show_database' 			=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Database', 'xtra-settings') ."<br/>"),
		'xtra_opt_show_crons' 				=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Crons', 'xtra-settings') ."<br/>"),
		'xtra_opt_show_plugins' 			=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Plugins', 'xtra-settings') ."<br/>"),
		'xtra_opt_show_images' 				=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Images', 'xtra-settings') ."<br/><span class='ind1'>".esc_html__('This takes a bit more time to load the page, depending on the number of images', 'xtra-settings') .".</span>"),
		'xtra_opt_show_all' 				=> array( 'default' => 0 ,	'label' => "&nbsp; - '".esc_html__('All', 'xtra-settings') ."' tab<br/><span class='ind1'>".esc_html__('This tab contains all options in one.', 'xtra-settings') ."</span>"),
		't2' 			=> array( 'title' => 1 ,	'label' => "".esc_html__('Show Boxes on the Right Side', 'xtra-settings') ."<br/>"),
		'xtra_opt_show_hitcntr' 			=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Hit Counter', 'xtra-settings') ."<br/>"),
		'xtra_opt_show_siteinf' 			=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Site Info', 'xtra-settings') ."<br/>"),
		'xtra_opt_show_curr_ins' 			=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Current Insertions', 'xtra-settings') ."<br/>"),
		't3' 			=> array( 'title' => 1 ,	'label' => "".esc_html__('Design Options', 'xtra-settings') ."<br/>"),
		'xtra_opt_light' 					=> array( 'default' => 0 ,	'label' => "&nbsp; - ".esc_html__('Light view', 'xtra-settings') ."<br/><span class='ind1'>(".esc_html__('Lighter page background and a darker tab-wrapper', 'xtra-settings') .")</span>"),
		'xtra_opt_vertical' 				=> array( 'default' => 0 ,	'label' => "&nbsp; - ".esc_html__('Vertical Tabs', 'xtra-settings') ."<br/><span class='ind1'>(".esc_html__('Put the tabs on the left side, under each other', 'xtra-settings') .")</span>"),
		'xtra_opt_sticky_tab' 				=> array( 'default' => 0 ,	'label' => "&nbsp; - ".esc_html__('Sticky Tabs', 'xtra-settings') ."<br/><span class='ind1'>(".esc_html__('Keep tabs freezed on the top of the screen', 'xtra-settings') .")</span>"),
		'xtra_opt_show_colors' 				=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Show colors', 'xtra-settings') ."<br/><span class='ind1'>".esc_html__('for XTRA settings options', 'xtra-settings') ."</span>"),
		'xtra_opt_show_opsnum' 				=> array( 'default' => 1 ,	'label' => "&nbsp; - ".esc_html__('Show numbers', 'xtra-settings') ."<br/><span class='ind1'>".esc_html__('for XTRA settings options', 'xtra-settings') ."</span>"),
		'xtra_opt_disable_boxresize_js' 	=> array( 'default' => 0 ,	'label' => "&nbsp; - ".esc_html__('Disable box-resizing JS', 'xtra-settings') ."<br/><span class='ind1'>(".esc_html__('JavaScript that changes the size of boxes in the right side-bar when clicked', 'xtra-settings') .")</span>"),
	);
	return $set;
}

function xtra_get_tab_colors($key="", $what="") {
	$x = array(
		esc_html__('Security'		,"xtra-settings")	=> array('color'=>'#86CC8C'),
		esc_html__('Speed'			,"xtra-settings")	=> array('color'=>'#E1847C'),
		esc_html__('SEO'			,"xtra-settings")	=> array('color'=>'#C485FF'),
		//esc_html__('Social'			,"xtra-settings")	=> array('color'=>'#FFC488'),
		esc_html__('Social'			,"xtra-settings")	=> array('color'=>'#FAAA3C'),
		esc_html__('WP Settings'	,"xtra-settings")	=> array('color'=>'#00a0d2'),
		esc_html__('Update'			,"xtra-settings")	=> array('color'=>'#E282E2'),
		esc_html__('Hits'			,"xtra-settings")	=> array('color'=>'#85E0E0'),
		esc_html__('Posts'			,"xtra-settings")	=> array('color'=>'#8292FF'),
		//esc_html__('Features'		,"xtra-settings")	=> array('color'=>'#E763A9'),
	);
	if ( !get_optionXTRA('xtra_opt_show_colors') ) {
	//$tc = '#CCCCCC';
	$tc = '#7BB3CF';
	$x = array(
		esc_html__('Security'		,"xtra-settings")	=> array('color'=>$tc),
		esc_html__('Speed'			,"xtra-settings")	=> array('color'=>$tc),
		esc_html__('SEO'			,"xtra-settings")	=> array('color'=>$tc),
		esc_html__('Social'			,"xtra-settings")	=> array('color'=>$tc),
		esc_html__('WP Settings'	,"xtra-settings")	=> array('color'=>$tc),
		esc_html__('Update'			,"xtra-settings")	=> array('color'=>$tc),
		esc_html__('Hits'			,"xtra-settings")	=> array('color'=>$tc),
		esc_html__('Posts'			,"xtra-settings")	=> array('color'=>$tc),
		//esc_html__('Features'		,"xtra-settings")	=> array('color'=>$tc),
	);
	}
	if ($what=='names') {
		foreach((array)$x as $k => $v) $res[]=$k;
		return $res;
	}
	if ($key) return $x[$key];
	return $x;
}

function xtra_get_section_icons($key="", $what="") {
	$x = array(
		esc_html__('Apache Server Hardening'			,"xtra-settings")	=> 'shield',
		esc_html__('Access Blocking'					,"xtra-settings")	=> 'lock',
		esc_html__('WP Security Settings'				,"xtra-settings")	=> 'sos',
		esc_html__('Online Security Tools'				,"xtra-settings")	=> 'plus-alt',
		esc_html__('Apache Compression and Caching'		,"xtra-settings")	=> 'dashboard',
		esc_html__('WP Cache Settings'					,"xtra-settings")	=> 'tickets',
		esc_html__('Memory and PHP Execution'			,"xtra-settings")	=> 'desktop',
		esc_html__('Minifier'							,"xtra-settings")	=> 'nametag',
		esc_html__('Online Speed Tools'					,"xtra-settings")	=> 'plus-alt',
		esc_html__('SEO Settings'						,"xtra-settings")	=> 'admin-site',
		esc_html__('JavaScript Settings'				,"xtra-settings")	=> 'welcome-write-blog',
		esc_html__('SEO Online Tools'					,"xtra-settings")	=> 'plus-alt',
		esc_html__('Social Media Settings'				,"xtra-settings")	=> 'share',
//		esc_html__('Share buttons settings'				,"xtra-settings")	=> 'share-alt',
//		esc_html__('Share buttons'						,"xtra-settings")	=> 'thumbs-up',
		esc_html__('Share buttons - 1st block'			,"xtra-settings")	=> 'thumbs-up',
		esc_html__('Share buttons - 2nd block'			,"xtra-settings")	=> 'thumbs-up',
		esc_html__('Admin'								,"xtra-settings")	=> 'admin-generic',
		esc_html__('Wordpress Mods'						,"xtra-settings")	=> 'wordpress',
		esc_html__('Wordpress Cron'						,"xtra-settings")	=> 'clock',
		esc_html__('Wordpress Maintenance mode'			,"xtra-settings")	=> 'admin-tools',
		esc_html__('Wordpress Debug mode'				,"xtra-settings")	=> 'admin-settings',
		esc_html__('Wordpress Auto-Update'				,"xtra-settings")	=> 'backup',
		esc_html__('Hit Counter'						,"xtra-settings")	=> 'chart-bar',
		esc_html__('Hit Counter Options'				,"xtra-settings")	=> 'chart-bar',
		esc_html__('Related Posts'						,"xtra-settings")	=> 'images-alt',
		esc_html__('Editing'							,"xtra-settings")	=> 'edit',
		esc_html__('Publishing'							,"xtra-settings")	=> 'share-alt2',
		esc_html__('HTML Content Changes'				,"xtra-settings")	=> 'media-code',
	);
	if ($what=='names') {
		foreach((array)$x as $k => $v) $res[]=$k;
		return $res;
	}
	if ($key) return $x[$key];
	return $x;
}

function xtra_make_dataset() {
global $xtra_pageload_time;
$xtb = xtra_get_tab_colors('','names');
$xsecs = xtra_get_section_icons('','names');
$xtra_sets = array(
	'xtra_dir_index_disable' 				=> array( 'tab' => $xtb[0]	,'section' => $xsecs[0]		,'default' => 0			,'label' => ''. esc_html__('Disable Apache directory views', 'xtra-settings') .' <br/>'. esc_html__('Disables file listing in directories.', 'xtra-settings') .' <br/>('. esc_html__('Writes in .htaccess file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_response_header_hardening'		=> array( 'tab' => $xtb[0]	,'section' => $xsecs[0]		,'default' => 0			,'label' => ''. esc_html__('Harden your HTTP response header', 'xtra-settings') .'<br/>'. esc_html__('Protect against XSS attacks, Page-Framing, Click-Jacking and Content-Sniffing', 'xtra-settings') .'<br/>('. esc_html__('Writes in .htaccess file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_block_external_post2' 			=> array( 'tab' => $xtb[0]	,'section' => $xsecs[1]		,'default' => 0			,'label' => ''. esc_html__('Block external POST requests', 'xtra-settings') .' <br/>'. esc_html__('Be careful - it might disable some external apps access.', 'xtra-settings') .''	),
	'xtra_redir_bots2' 						=> array( 'tab' => $xtb[0]	,'section' => $xsecs[1]		,'default' => 0			,'label' => ''. esc_html__('Block access - by Remote Info', 'xtra-settings') .' <br/>'. esc_html__('Redirect back to referring URL.', 'xtra-settings') .'<br/>'. esc_html__('Regex substrings to match in User-Agent, IP or Remote Host name.', 'xtra-settings') .' ('. esc_html__('Don\'t forget to escape special characters', 'xtra-settings') .' $^{[()*+.?| '. esc_html__('with back-slash.', 'xtra-settings') .')'	),
	'xtra_redir_bots2_texta'				=> array( 'tab' => $xtb[0]	,'section' => $xsecs[1]		,'default' => "rv:40\.0.*Firefox\/40\.1\npopeofslope.net"			,'label' => ''. esc_html__('Blocklist (remote)', 'xtra-settings') .''	),
	'xtra_redir_lookup_hostnames'	 		=> array( 'tab' => $xtb[0]	,'section' => $xsecs[1]		,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Lookup Host Names for each request', 'xtra-settings') .'<br/>'. esc_html__('Be careful, it can be', 'xtra-settings') .' <span class="dark-red">'. esc_html__('very slow', 'xtra-settings') .'</span> '. esc_html__('on some servers and slow down your site!', 'xtra-settings') .'', '_current' => xtra_time_measure('xtra_hostlookup_avg')	),
	'xtra_redir_bots3' 						=> array( 'tab' => $xtb[0]	,'section' => $xsecs[1]		,'default' => 0			,'label' => ''. esc_html__('Block access -  by Targeted Page', 'xtra-settings') .'<br/>'. esc_html__('Redirect back to referring URL.', 'xtra-settings') .'<br/>'. esc_html__('Regex substrings to match in Request-URI.', 'xtra-settings') .' ('. esc_html__('Don\'t forget to escape special characters', 'xtra-settings') .' $^{[()*+.?| '. esc_html__('with back-slash.', 'xtra-settings') .')'	),
	'xtra_redir_bots3_texta'				=> array( 'tab' => $xtb[0]	,'section' => $xsecs[1]		,'default' => "//(wp-login|xmlrpc|wp-comments-post)\.php\n/\?author=\d+"			,'label' => ''. esc_html__('Blocklist (page)', 'xtra-settings') .''	),
	'xtra_remove_version_number' 			=> array( 'tab' => $xtb[0]	,'section' => $xsecs[2]		,'default' => 0			,'label' => ''. esc_html__('Remove WordPress Version Number from HTML Head', 'xtra-settings') .''	),
	'xtra_suppress_login_errors' 			=> array( 'tab' => $xtb[0]	,'section' => $xsecs[2]		,'default' => 0			,'label' => ''. esc_html__('Dont display WP login errors', 'xtra-settings') .''	),
	'xtra_fb_disable_feed' 					=> array( 'tab' => $xtb[0]	,'section' => $xsecs[2]		,'default' => 0			,'label' => ''. esc_html__('Disable all type of RSS Feeds', 'xtra-settings') .'<br/>'. esc_html__('Be careful - it is not SEO friendly.', 'xtra-settings') .''	),
	'xtra_disable_xmlrpc' 					=> array( 'tab' => $xtb[0]	,'section' => $xsecs[2]		,'default' => 0			,'label' => ''. esc_html__('Disable XML-RPC access', 'xtra-settings') .'<br/>'. esc_html__('Be careful - it might disable some external apps access like e.g. Jetpack.', 'xtra-settings') .''	),
	'xtra_rest_must_loggedin' 				=> array( 'tab' => $xtb[0]	,'section' => $xsecs[2]		,'default' => 0			,'label' => ''. esc_html__('Require authentication for all REST API requests', 'xtra-settings') .'<br/>'. esc_html__('Effectively prevents anonymous external access by JSON REST API. Admin requests still work fine.', 'xtra-settings') .''	),
	'xtra_protect_from_bad_requests' 		=> array( 'tab' => $xtb[0]	,'section' => $xsecs[2]		,'default' => 0			,'label' => ''. esc_html__('Protect from some malicious URL requests', 'xtra-settings') .'<br/>('. esc_html__('URLs including', 'xtra-settings') .' eval(), exec(), CONCAT, UNION+SELECT, base64_ or UserAgent with libwww, Wget, EmailSiphon, EmailWolf)'	),
	'xtra_online_security_tools' 			=> array( 'tab' => $xtb[0]	,'section' => $xsecs[3]		,'default' => 0			,'label' => ''. esc_html__('Check your site with online security tools', 'xtra-settings') .''	),
	'xtra_deflates' 						=> array( 'tab' => $xtb[1]	,'section' => $xsecs[4]		,'default' => 0			,'label' => ''. esc_html__('Enable GZIP compression: deflate', 'xtra-settings') .' <br/>('. esc_html__('Writes in .htaccess file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_image_expiry' 					=> array( 'tab' => $xtb[1]	,'section' => $xsecs[4]		,'default' => 0			,'label' => ''. esc_html__('Set file cache expirations', 'xtra-settings') .'<br/>(css, js, images, fonts) <br/>('. esc_html__('Writes in .htaccess file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_image_expiry_num'					=> array( 'tab' => $xtb[1]	,'section' => $xsecs[4]		,'default' => 30		,'label' => ''. esc_html__('Cache expiration', 'xtra-settings') .':-'. esc_html__('days', 'xtra-settings') .''	),
	'xtra_remove_etags' 					=> array( 'tab' => $xtb[1]	,'section' => $xsecs[4]		,'default' => 0			,'label' => ''. esc_html__('Remove ETags', 'xtra-settings') .' <br/>'. esc_html__('Reduces the size of the HTTP headers.', 'xtra-settings') .' <br/>('. esc_html__('Writes in .htaccess file', 'xtra-settings') .')'	,'submitX' => 1	),
//	'xtra_WPcache'			 				=> array( 'tab' => $xtb[1]	,'section' => $xsecs[5]		,'default' => 0			,'label' => ''. esc_html__('Enable WordPress Cache', 'xtra-settings') .'<br/>'. esc_html__('Includes wp-cache.php in settings.', 'xtra-settings') .' <br/>('. esc_html__('Writes in wp-config file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_remove_query_strings' 			=> array( 'tab' => $xtb[1]	,'section' => $xsecs[5]		,'default' => 0			,'label' => ''. esc_html__('Remove query strings from script and css filenames', 'xtra-settings') .'<br/>('. esc_html__('those files with .css?ver=2.11 endings', 'xtra-settings') .')'	),
	'xtra_remove_query_strings_plus'		=> array( 'tab' => $xtb[1]	,'section' => $xsecs[5]		,'default' => 0			,'label' => ''. esc_html__('Add time() as query string if Debug Mode is ON', 'xtra-settings') .'<br/>('. esc_html__('this ensures instant refresh in development stage', 'xtra-settings') .')'	),
	'xtra_memory_limit' 					=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 0			,'label' => ''. esc_html__('PHP Memory limit', 'xtra-settings') .'<br/>('. esc_html__('Only if your hosting/server allows it', 'xtra-settings') .')'	),
//	'xtra_memory_limit_num'					=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 128		,'label' => '-MB'	,'current' => ini_get('memory_limit').'B'.((get_optionXTRA('xtra_memory_limit_num').'M'!=ini_get('memory_limit'))?' - '. esc_html__('obviously, does not work', 'xtra-settings') .'':'')	),
	'xtra_memory_limit_num'					=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 128		,'label' => '-MB'	,'current' => ini_get('memory_limit').'B'	),
	'xtra_upload_max_filesize' 				=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 0			,'label' => ''. esc_html__('PHP Upload max file-size', 'xtra-settings') .'<br/>('. esc_html__('Only if your hosting/server allows it', 'xtra-settings') .')'	),
//	'xtra_upload_max_filesize_num'			=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 32		,'label' => '-MB'	,'current' => ini_get('upload_max_filesize').'B'.((get_optionXTRA('xtra_upload_max_filesize_num').'M'!=ini_get('upload_max_filesize'))?' - '. esc_html__('obviously, does not work', 'xtra-settings') .'':'')	),
	'xtra_upload_max_filesize_num'			=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 32		,'label' => '-MB'	,'current' => ini_get('upload_max_filesize').'B'	),
	'xtra_max_execution_time' 				=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 0			,'label' => ''. esc_html__('PHP Max execution time', 'xtra-settings') .'<br/>('. esc_html__('Only if your hosting/server allows it', 'xtra-settings') .')'	),
//	'xtra_max_execution_time_num'			=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 60		,'label' => '-sec'	,'current' => ini_get('max_execution_time').((get_optionXTRA('xtra_max_execution_time_num')!=ini_get('max_execution_time'))?' - '. esc_html__('obviously, does not work', 'xtra-settings') .'':'')	),
	'xtra_max_execution_time_num'			=> array( 'tab' => $xtb[1]	,'section' => $xsecs[6]		,'default' => 60		,'label' => '-sec'	,'current' => ini_get('max_execution_time')	),
	'xtra_minify_html'						=> array( 'tab' => $xtb[1]	,'section' => $xsecs[7]		,'default' => 0			,'label' => ''. esc_html__('HTML Minifier', 'xtra-settings') .' <br/>'. esc_html__('Remove extra spaces from between HTML tags. Also remove comments from inline js and css.', 'xtra-settings') .'<br/>('. esc_html__('Works 99% of the cases...', 'xtra-settings') .')'	),
	'xtra_online_speed_tools' 				=> array( 'tab' => $xtb[1]	,'section' => $xsecs[8]		,'default' => 0			,'label' => ''. esc_html__('Check your site speed with online tools', 'xtra-settings') .''	),
	'xtra_meta_description' 				=> array( 'tab' => $xtb[2]	,'section' => $xsecs[9]		,'default' => 0			,'label' => ''. esc_html__('Add Meta Description', 'xtra-settings') .'<br/>'. esc_html__('to all posts and pages HTML head using post excerpt and tags', 'xtra-settings') .''	),
	'xtra_meta_keywords' 					=> array( 'tab' => $xtb[2]	,'section' => $xsecs[9]		,'default' => 0			,'label' => ''. esc_html__('Add Meta Keywords', 'xtra-settings') .'<br/>'. esc_html__('to all posts and pages HTML head using post excerpt and tags', 'xtra-settings') .''	),
	'xtra_meta_robots'		 				=> array( 'tab' => $xtb[2]	,'section' => $xsecs[9]		,'default' => 0			,'label' => ''. esc_html__('Add Robots meta tag', 'xtra-settings') .'<br/>'. esc_html__('to all posts and pages HTML head depending on page type', 'xtra-settings') .''	),
	'xtra_WPTime_redirect_404_to_homepage' 	=> array( 'tab' => $xtb[2]	,'section' => $xsecs[9]		,'default' => 0			,'label' => ''. esc_html__('Redirect page-not-found (404) to Frontpage', 'xtra-settings') .''	),
	'xtra_attachment_redirect_to_post' 		=> array( 'tab' => $xtb[2]	,'section' => $xsecs[9]		,'default' => 0			,'label' => ''. esc_html__('Redirect Attachment pages to their Parent Post', 'xtra-settings') .''	),
	'xtra_rel_external' 					=> array( 'tab' => $xtb[2]	,'section' => $xsecs[9]		,'default' => 0			,'label' => ''. esc_html__('Add rel="external"', 'xtra-settings') .'<br/>'. esc_html__('to any URL with target="_blank" attribute in post contents', 'xtra-settings') .''	),
	'xtra_img_alt'		 					=> array( 'tab' => $xtb[2]	,'section' => $xsecs[9]		,'default' => 0			,'label' => ''. esc_html__('Add missing alt="..." attribute to images', 'xtra-settings') .'<br/>'. esc_html__('in pages and post contents', 'xtra-settings') .''	),
	'xtra_remove_double_title_meta'			=> array( 'tab' => $xtb[2]	,'section' => $xsecs[9]		,'default' => 0			,'label' => ''. esc_html__('Remove double title', 'xtra-settings') .'<br/>'. esc_html__('meta tag in HTML headers (caused by some SEO plugins)', 'xtra-settings') .''	),
	'xtra_defer_parsing_of_js'				=> array( 'tab' => $xtb[2]	,'section' => $xsecs[10]	,'default' => 0			,'label' => ''. esc_html__('Defer parsing of all JS', 'xtra-settings') .'<br/>'. esc_html__('Except for jquery.js', 'xtra-settings') .'<br/>'. esc_html__('Test your site carefully to check correct functioning!', 'xtra-settings') .''	),
	'xtra_move_all_js_to_footer'			=> array( 'tab' => $xtb[2]	,'section' => $xsecs[10]	,'default' => 0			,'label' => ''. esc_html__('Move all JS to the footer', 'xtra-settings') .'<br/>'. esc_html__('Test your site carefully to check correct functioning!', 'xtra-settings') .''	),
	'xtra_online_seo_tools'					=> array( 'tab' => $xtb[2]	,'section' => $xsecs[11]	,'default' => 0			,'label' => ''. esc_html__('Use online SEO tools to further optimize your site', 'xtra-settings') .''	),
	'xtra_facebook_og_metas' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[12]	,'default' => 0			,'label' => ''. esc_html__('Add Facebook Open Graph (OG)', 'xtra-settings') .'<br/>'. esc_html__('meta-tags to all posts and pages HTML head using post title, excerpt and thumbnail image', 'xtra-settings') .''	),
	'xtra_twitter_metas'	 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[12]	,'default' => 0			,'label' => ''. esc_html__('Add Twitter Cards', 'xtra-settings') .'<br/>'. esc_html__('meta-tags to posts and pages HTML head using post data and thumbnails', 'xtra-settings') .''	),
	'xtra_twitter_metas_text' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[12]	,'default' => ''		,'label' => ''. esc_html__('Your Twitter username', 'xtra-settings') .': '	),
	'xtra_facebook_sdk' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[12]	,'default' => 0			,'label' => ''. esc_html__('Add Facebook JS-SDK', 'xtra-settings') .'<br/>'. esc_html__('to be able to use native Like/Share buttons and show Facebook Page iFrame', 'xtra-settings') .''	),
	'xtra_share_buttons' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => ''. esc_html__('Enable Share Buttons - 1st block', 'xtra-settings') .''	),
	'xtra_share_buttons_text' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 'Share on:'			,'label' => '<br/>'. esc_html__('Block Title', 'xtra-settings') .':'	),
	'xtra_share_buttons_cbx' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '<br/>'. esc_html__('Inline Tilte', 'xtra-settings') .':'	),
	'xtra_share_buttons_pnum' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 'h3'		,'label' => ''. esc_html__('Tilte Tag', 'xtra-settings') .':'	),
	'xtra_share_buttons_num' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 100		,'label' => '<br/>'. esc_html__('Icon Zoom', 'xtra-settings') .':-%'	),
	'xtra_share_buttons_num2' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 4			,'label' => ''. esc_html__('Icon spacing', 'xtra-settings') .':-px'	),
	'xtra_share_buttons_posts' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 1			,'label' => '&nbsp; - '. esc_html__('show on Posts?', 'xtra-settings') .''	),
	'xtra_share_buttons_pages' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show on Pages?', 'xtra-settings') .''	),
	'xtra_share_buttons_homepage' 			=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show on Frontpage?', 'xtra-settings') .''	),
	'xtra_share_buttons_shape' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => ''. esc_html__('Shape', 'xtra-settings') .''	),
	'xtra_share_buttons_place' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 1			,'label' => ''. esc_html__('Position', 'xtra-settings') .''	),
	'xtra_share_facebook' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Facebook share button', 'xtra-settings') .''	),
	'xtra_share_twitter' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Twitter share button', 'xtra-settings') .''	),
	'xtra_share_linkedin' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('LinkedIn share button', 'xtra-settings') .''	),
	'xtra_share_pinterest' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Pinterest share button', 'xtra-settings') .''	),
	'xtra_share_tumblr' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('tumblr share button', 'xtra-settings') .''	),
	'xtra_share_gplus' 						=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Google+ share button', 'xtra-settings') .''	),
	'xtra_share_reddit' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Reddit share button', 'xtra-settings') .''	),
	'xtra_share_buffer' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Buffer share button', 'xtra-settings') .''	),
	'xtra_share_facelike' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => ''. esc_html__('Facebook native LIKE button', 'xtra-settings') .' <span>'. esc_html__('only if JS-SDK (13.3) enabled', 'xtra-settings') .'</span>'	),
	'xtra_share_faceshare' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[13]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('also show native SHARE button', 'xtra-settings') .' <span>'. esc_html__('as part of the LIKE button frame', 'xtra-settings') .'</span>'	),
	'xtra_share2_buttons' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => ''. esc_html__('Enable Share Buttons - 2nd block', 'xtra-settings') .''	),
	'xtra_share2_buttons_text' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 'Share on:'			,'label' => '<br/>'. esc_html__('Block Title', 'xtra-settings') .':'	),
	'xtra_share2_buttons_cbx' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '<br/>'. esc_html__('Inline Tilte', 'xtra-settings') .':'	),
	'xtra_share2_buttons_pnum' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 'h3'		,'label' => ''. esc_html__('Tilte Tag', 'xtra-settings') .':'	),
	'xtra_share2_buttons_num' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 100		,'label' => '<br/>'. esc_html__('Icon Zoom', 'xtra-settings') .':-%'	),
	'xtra_share2_buttons_num2' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 4			,'label' => ''. esc_html__('Icon spacing', 'xtra-settings') .':-px'	),
	'xtra_share2_buttons_posts' 			=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show on Posts?', 'xtra-settings') .''	),
	'xtra_share2_buttons_pages' 			=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show on Pages?', 'xtra-settings') .''	),
	'xtra_share2_buttons_homepage' 			=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show on Frontpage?', 'xtra-settings') .''	),
	'xtra_share2_buttons_shape' 			=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => ''. esc_html__('Shape', 'xtra-settings') .''	),
	'xtra_share2_buttons_place' 			=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => ''. esc_html__('Position', 'xtra-settings') .''	),
	'xtra_share2_facebook' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Facebook share button', 'xtra-settings') .''	),
	'xtra_share2_twitter' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Twitter share button', 'xtra-settings') .''	),
	'xtra_share2_linkedin' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('LinkedIn share button', 'xtra-settings') .''	),
	'xtra_share2_pinterest' 				=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Pinterest share button', 'xtra-settings') .''	),
	'xtra_share2_tumblr' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('tumblr share button', 'xtra-settings') .''	),
	'xtra_share2_gplus' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Google+ share button', 'xtra-settings') .''	),
	'xtra_share2_reddit' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Reddit share button', 'xtra-settings') .''	),
	'xtra_share2_buffer' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Buffer share button', 'xtra-settings') .''	),
	'xtra_share2_facelike' 					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => ''. esc_html__('Facebook native LIKE button', 'xtra-settings') .' <span>'. esc_html__('only if JS-SDK (13.3) enabled', 'xtra-settings') .'</span>'	),
	'xtra_share2_faceshare'					=> array( 'tab' => $xtb[3]	,'section' => $xsecs[14]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('also show native SHARE button', 'xtra-settings') .' <span>'. esc_html__('as part of the LIKE button frame', 'xtra-settings') .'</span>'	),
	'xtra_remove_admin_bar' 				=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => ''. esc_html__('Remove Admin Bar', 'xtra-settings') .' <br/>'. esc_html__('on the front-end for everybody. But...', 'xtra-settings') .''	),
	'xtra_remove_admin_bar_excl_adm'		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show for Admins', 'xtra-settings') .''	),
	'xtra_remove_admin_bar_excl_edt'		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show for Editors', 'xtra-settings') .''	),
	'xtra_remove_admin_bar_excl_aut'		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show for Authors', 'xtra-settings') .''	),
	'xtra_remove_admin_bar_excl_cnt'		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show for Contributors', 'xtra-settings') .''	),
	'xtra_remove_admin_bar_excl_sub'		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show for Subscribers', 'xtra-settings') .''	),
	'xtra_remove_admin_bar_excl_ano'		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show for Anonymous', 'xtra-settings') .'<span> ('. esc_html__('not logged in', 'xtra-settings') .')</span>'	),
	'xtra_login_checked_remember_me' 		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => ''. esc_html__('Auto-Check Remember-Me', 'xtra-settings') .'<br/>'. esc_html__('checkbox automatically at login', 'xtra-settings') .''	),
	'xtra_keep_me_logged_in_for' 			=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => ''. esc_html__('Set Login Expiration', 'xtra-settings') .'<br/>'. esc_html__('days for Remember-Me auth cookie at login', 'xtra-settings') .''	),
	'xtra_keep_me_logged_in_for_text' 		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 60		,'label' => '-'. esc_html__('days', 'xtra-settings') .''	),
	'xtra_doEmailNameFilter' 				=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => ''. esc_html__('Change default WP email Sender Name', 'xtra-settings') .''	),
	'xtra_doEmailNameFilter_text' 			=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => ''		,'label' => ''. esc_html__('Name', 'xtra-settings') .':'	),
	'xtra_doEmailFilter' 					=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => ''. esc_html__('Change default WP email Sender Address', 'xtra-settings') .''	),
	'xtra_doEmailFilter_text' 				=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => ''		,'label' => ''. esc_html__('Email', 'xtra-settings') .':'	),
	'xtra_disable_heartbeat'			 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => ''. esc_html__('Disable WP Heartbeat', 'xtra-settings') .'<br/>('. esc_html__('Stop WordPress admin-ajax requests regularly while any admin page is open.', 'xtra-settings') .')'	),
	'xtra_disable_heartbeat_exedit'		 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('except for post/page editor pages', 'xtra-settings') .'<br/>('. esc_html__('Recommended to keep the auto-save feature working.', 'xtra-settings') .')'	),
	'xtra_remove_admin_notices' 			=> array( 'tab' => $xtb[4]	,'section' => $xsecs[15]	,'default' => 0			,'label' => ''. esc_html__('Remove Admin Notices', 'xtra-settings') .' <br/>'. esc_html__('All of it.', 'xtra-settings') .''	),
	'xtra_php_in_textwidgets' 				=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Allow PHP code in text widgets', 'xtra-settings') .''	),
	'xtra_shortcode_in_textwidgets' 		=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Enable Shortcodes in text widgets', 'xtra-settings') .''	),
	'xtra_remove_WPemoji' 					=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Remove support for WP emoji', 'xtra-settings') .'<br/>('. esc_html__('As of WordPress 4.2, by default WordPress includes support for Emojis. Great if that is your cup of tea, but if not, you might want to remove the additional resources Emoji support adds to your webpage.', 'xtra-settings') .')'	),
	'xtra_custom_jpeg_quality' 				=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Custom JPEG quality', 'xtra-settings') .'<br/>('. esc_html__('By default, in order to save space, Wordpress compresses uploaded JPG images with 82% quality ratio.', 'xtra-settings') .')'	),
	'xtra_custom_jpeg_quality_num' 			=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 82		,'label' => '-%'	),
	'xtra_auto_resize_upload'			 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Auto-Resize Image Uploads', 'xtra-settings') .'<br/>'. esc_html__('to maximum width/height px', 'xtra-settings') .''	),
	'xtra_auto_resize_upload_num'		 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 1600		,'label' => ''. esc_html__('Max width', 'xtra-settings') .':-px'	),
	'xtra_auto_resize_upload_pnum'		 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 1600		,'label' => ''. esc_html__('Max height', 'xtra-settings') .':-px'	),
	'xtra_disable_wpautop'				 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Disable wpautop globally', 'xtra-settings') .'<br/>('. esc_html__('Stop WordPress auto-formatting that replaces double line breaks with &lt;p&gt; tags, and single line breaks with &lt;br /&gt; tags, called wpautop.', 'xtra-settings') .')'	),
	'xtra_disable_comments_switch'			=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Disable Comments globally', 'xtra-settings') .'<br/>('. esc_html__('Hide comments and reply-box from the front-end.', 'xtra-settings') .')'	),
	'xtra_disable_self_pingback'			=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Disable Self Pingback', 'xtra-settings') .'<br/>('. esc_html__('Pingbacks also work within your site. So if one of your posts link to another post, then WP will send a self-ping. This can get really annoying.', 'xtra-settings') .')'	),
	'xtra_extend_search'				 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Extend WordPress Search', 'xtra-settings') .'<br/>'. esc_html__('Search not only in title and post content, but also in tags, categories and comments. Several key words find posts which contain all of them.', 'xtra-settings') .''	),
	'xtra_highlight_search'				 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Highlight Search Results', 'xtra-settings') .''	),
	'xtra_title_shorten'				 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 0			,'label' => ''. esc_html__('Shorten the Title in post-lists', 'xtra-settings') .'<br/>'. esc_html__('i.e. all non-singular views.', 'xtra-settings') .'<br/>'. esc_html__('Avoids cutting words.', 'xtra-settings') .''	),
	'xtra_title_shorten_num'			 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[16]	,'default' => 50		,'label' => ''. esc_html__('Max', 'xtra-settings') .':-'. esc_html__('chars', 'xtra-settings') .''	),
	'xtra_uptime_robot_buttons' 			=> array( 'tab' => $xtb[4]	,'section' => $xsecs[17]	,'default' => 0			,'label' => ''. esc_html__('You can set up an Uptime Robot auto trigger', 'xtra-settings') .' <br/>'. esc_html__('pointing to', 'xtra-settings') .' '.site_url().'/wp-cron.php<br/>'. esc_html__('and then you can disable WP Cron below.', 'xtra-settings') .''	),
	'xtra_disable_WPcron'					=> array( 'tab' => $xtb[4]	,'section' => $xsecs[17]	,'default' => 0			,'label' => ''. esc_html__('Disable WordPress Cron', 'xtra-settings') .'<br/>('. esc_html__('Scheduled code-run service', 'xtra-settings') .')<br/>'. esc_html__('Please be careful!', 'xtra-settings') .'<br/>'. esc_html__('If you disable Cron, some background services will not run automatically.', 'xtra-settings') .'<br/>('. esc_html__('Writes in wp-config file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_maintenance'						=> array( 'tab' => $xtb[4]	,'section' => $xsecs[18]	,'default' => 0			,'label' => ''. esc_html__('Enable Maintenance mode', 'xtra-settings') .'<br/>('. esc_html__('Locks down public access to the website - except for admins with the right to', 'xtra-settings') .' "edit_themes")'	),
	'xtra_maintenance_title'				=> array( 'tab' => $xtb[4]	,'section' => $xsecs[18]	,'default' => ''. esc_html__('Website under Maintenance', 'xtra-settings') .''			,'label' => '<br/>'. esc_html__('Title', 'xtra-settings') .':'	),
	'xtra_maintenance_text'					=> array( 'tab' => $xtb[4]	,'section' => $xsecs[18]	,'default' => ''. esc_html__('We are performing scheduled maintenance. We will be back online shortly!', 'xtra-settings') .''			,'label' => '<br/>'. esc_html__('Text', 'xtra-settings') .':'	),
	'xtra_debug'				 			=> array( 'tab' => $xtb[4]	,'section' => $xsecs[19]	,'default' => 0			,'label' => ''. esc_html__('Enable Debug mode', 'xtra-settings') .'<br/>('. esc_html__('Writes in wp-config file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_debug_text'		 				=> array( 'tab' => $xtb[4]	,'section' => $xsecs[19]	,'default' => ''		,'label' => ''. esc_html__('Error Reporting Level', 'xtra-settings') .':'	),
	'xtra_disable_debug_display'		 	=> array( 'tab' => $xtb[4]	,'section' => $xsecs[19]	,'default' => 0			,'label' => ''. esc_html__('Disable Debug display on screen', 'xtra-settings') .'<br/>('. esc_html__('Writes in wp-config file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_debug_log'		 				=> array( 'tab' => $xtb[4]	,'section' => $xsecs[19]	,'default' => 0			,'label' => ''. esc_html__('Set Debug logging to', 'xtra-settings') .' '.( (file_exists(XTRA_WPCONTENT_BASENAME.'/debug.log')) ? ('<a target="_blank" href="'.site_url()."/".XTRA_WPCONTENT_BASENAME.'/debug-log">/'.XTRA_WPCONTENT_BASENAME.'/debug.log</a>') : (XTRA_WPCONTENT_BASENAME.'/debug.log') ).'<br/>('. esc_html__('Writes in wp-config file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_all_autoupdate' 					=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => ''. esc_html__('Overall Auto-update Feature', 'xtra-settings') .''			,'level' => 0 ,'def' => 'ON' 	),
	'xtra_core_autoupdate_major'			=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => '&bull; '. esc_html__('Core: Major version', 'xtra-settings') .''				,'level' => 1 ,'def' => 'OFF' 	),
	'xtra_core_autoupdate_minor'			=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => '&bull; '. esc_html__('Core: Minor version', 'xtra-settings') .''				,'level' => 1 ,'def' => 'ON' 	),
	'xtra_core_autoupdate_dev'				=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => '&bull; '. esc_html__('Core: Development version', 'xtra-settings') .''		,'level' => 1 ,'def' => 'OFF' 	),
	'xtra_translation_autoupdate' 			=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => '&bull; '. esc_html__('Translations', 'xtra-settings') .''					,'level' => 1 ,'def' => 'ON' 	),
	'xtra_plugin_autoupdate' 				=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => '&bull; '. esc_html__('Plugins', 'xtra-settings') .''							,'level' => 1 ,'def' => 'OFF' 	),
	'xtra_plugin_excl_hide_notif'			=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Hide Notification for Excluded Plugins', 'xtra-settings') .'<br/>'. esc_html__('Works if Plugins Auto-Update (above) is Enabled', 'xtra-settings') .''	,'level' => 2 				 	),
	'xtra_theme_autoupdate' 				=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => '&bull; '. esc_html__('Themes', 'xtra-settings') .''							,'level' => 1 ,'def' => 'OFF' 	),
	'xtra_theme_excl_hide_notif'			=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('Hide Notification for Excluded themes', 'xtra-settings') .'<br/>'. esc_html__('Works if themes Auto-Update (above) is Enabled', 'xtra-settings') .''	,'level' => 2 				 	),
	'xtra_autoupdate_cron_buttons' 			=> array( 'tab' => $xtb[5]	,'section' => $xsecs[20]	,'default' => 0			,'label' => ''. esc_html__('Trigger Auto-Update Now', 'xtra-settings') .''				,'level' => 1 					),
	'xtra_hits_enable'			 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => ''. esc_html__('Enable Hit Counter feature', 'xtra-settings') .'<br/>'. esc_html__('It counts hits and unique hits for the given number of days.', 'xtra-settings') .'<br/>'. esc_html__('No external connection (except for GeoIP).', 'xtra-settings') .''	),
	'xtra_hits_enable_num'			 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 30		,'label' => '<br/><br/>'. esc_html__('Count hits for', 'xtra-settings') .'-'. esc_html__('days', 'xtra-settings') .''	),
	'xtra_hits_enable_pnum'			 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 1000		,'label' => '<br/>'. esc_html__('Log hits for max', 'xtra-settings') .'-'. esc_html__('rows', 'xtra-settings') .''	),
	'xtra_hits_exclude_admin'	 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('exclude admin-page hits', 'xtra-settings') .''	),
	'xtra_hits_exclude_cron'	 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('exclude wp-cron hits', 'xtra-settings') .''	),
	'xtra_hits_exclude_ajax'	 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('exclude admin-ajax hits', 'xtra-settings') .''	),
	'xtra_hits_exclude_string'	 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('exclude list', 'xtra-settings') .'<br/>'. esc_html__('Regex substrings to match in Request-URI, User-Agent, IP or Remote-Server name.', 'xtra-settings') .' ('. esc_html__('Don\'t forget to escape special characters', 'xtra-settings') .' $^{[()*+.?| '. esc_html__('with back-slash.', 'xtra-settings') .')'	),
	'xtra_hits_exclude_string_texta'	 	=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => "robot\ncrawler\nspider\nslurp\ngooglebot\nfacebookexternalhit\nfacebot\npinterestbot"			,'label' => '<br/>'. esc_html__('Exclude list', 'xtra-settings') .''	),
	'xtra_hits_lookup_hostnames'	 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => ''. esc_html__('Lookup Host Names for each hit', 'xtra-settings') .'<br/>'. esc_html__('Be careful, it can be', 'xtra-settings') .' <span class="dark-red">'. esc_html__('very slow', 'xtra-settings') .'</span> '. esc_html__('on some servers!', 'xtra-settings') .'', '_current' => xtra_time_measure('xtra_hostlookup_avg')	),
	'xtra_hits_geoip'	 					=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => ''. esc_html__('Lookup Geo IP for each hit', 'xtra-settings') .'<br/>'. esc_html__('Be careful, it can be', 'xtra-settings') .' <span class="dark-red">'. esc_html__('quite slow', 'xtra-settings') .'</span>.', '_current' => xtra_time_measure('xtra_geoiplookup_avg')	),
	'xtra_hits_geoip_force'	 				=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => ''. esc_html__('Force refresh Geo IP info for Hit Counter', 'xtra-settings') .'<br/>'. esc_html__('Loads in about 10s for 100 records. Use it only sometimes.', 'xtra-settings') .''	),
	'xtra_hits_send_mail'			 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => ''. esc_html__('Send daily stats email', 'xtra-settings') .''	),
	'xtra_hits_send_mail_cbx' 				=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => 0			,'label' => '<br/><br/>'. esc_html__('All detailed info', 'xtra-settings') .': '	),
	'xtra_hits_send_mail_text'		 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => get_bloginfo('admin_email')			,'label' => '<br/>'. esc_html__('To email', 'xtra-settings') .':'	),
//	'xtra_hits_send_mail_num'		 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[21]	,'default' => '23:55'			,'label' => '<br/>'. esc_html__('Around', 'xtra-settings') .':- ('. esc_html__('empty = sending at day-change', 'xtra-settings') .')'	),
	'xtra_show_hit_chart'			 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 1			,'label' => ''. esc_html__('Show Hits Chart', 'xtra-settings') .' <span>'. esc_html__('in the Hit Counter (with Google Charts API)', 'xtra-settings') .''	),
	'xtra_hits_show_ips'		 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 1			,'label' => ''. esc_html__('Show Unique (the last X Unique IPs)', 'xtra-settings') .' <span>'. esc_html__('in the Hit Counter', 'xtra-settings') .'</span>'	),
	'xtra_hits_show_ips_num'	 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 10		,'label' => ''. esc_html__('last', 'xtra-settings') .'-'. esc_html__('IPs', 'xtra-settings') .''	),
	'xtra_hits_show_hitsdata'	 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 1			,'label' => ''. esc_html__('Show Log (the last X Hits)', 'xtra-settings') .' <span>'. esc_html__('in the Hit Counter', 'xtra-settings') .'</span><br/>('. esc_html__('max', 'xtra-settings') .'. '.get_optionXTRA('xtra_hits_enable_pnum', 1000).' '. esc_html__('hits are saved', 'xtra-settings') .')'	),
	'xtra_hits_show_hitsdata_num'			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 50		,'label' => ''. esc_html__('last', 'xtra-settings') .'-'. esc_html__('hits', 'xtra-settings') .''	),
	'xtra_hits_show_skipped_hits'			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show also Excluded Hits', 'xtra-settings') .' <span>'. esc_html__('in the Log', 'xtra-settings') .'.</span><br/>('. esc_html__('admin, cron, ajax or the exclude-list hits as set above', 'xtra-settings') .')'	),
	'xtra_hits_show_geo'		 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 1			,'label' => ''. esc_html__('Show the Top Countries', 'xtra-settings') .' <span>'. esc_html__('in the Hit Counter', 'xtra-settings') .'</span>'	),
	'xtra_hits_show_ipc'		 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 1			,'label' => ''. esc_html__('Show the Top IPs', 'xtra-settings') .' <span>'. esc_html__('in the Hit Counter', 'xtra-settings') .'</span>'	),
	'xtra_hits_show_pages'		 			=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 1			,'label' => ''. esc_html__('Show the Top Pages', 'xtra-settings') .' <span>'. esc_html__('in the Hit Counter', 'xtra-settings') .'</span>'	),
	'xtra_hits_dashboard_widget'	 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 0			,'label' => ''. esc_html__('Add Dashboard Widget', 'xtra-settings') .'<br/>'. esc_html__('Show Hit Counter also on the Admin Dashboard.', 'xtra-settings') .''	),
	'xtra_hits_show_side'			 		=> array( 'tab' => $xtb[6]	,'section' => $xsecs[22]	,'default' => 0			,'label' => ''. esc_html__('Right Side-Bar', 'xtra-settings') .'<br/>'. esc_html__('Show Hit Counter on the right side-bar, not here.', 'xtra-settings') .''	),
	'xtra_related_posts' 					=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 0			,'label' => ''. esc_html__('Enable Related Posts feature', 'xtra-settings') .''	),
	'xtra_related_posts_text' 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 'Related posts'			,'label' => '<br/>'. esc_html__('Block Title', 'xtra-settings') .':'	),
	'xtra_related_posts_num' 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 4			,'label' => '<br/>'. esc_html__('Show', 'xtra-settings') .':-'. esc_html__('posts', 'xtra-settings') .''	),
	'xtra_related_posts_size_num'			=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 150		,'label' => '<br/>'. esc_html__('Thumbnail size', 'xtra-settings') .':-px'	),
	'xtra_related_posts_posts' 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 1			,'label' => '&nbsp; - '. esc_html__('show on Posts?', 'xtra-settings') .''	),
	'xtra_related_posts_pages' 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show on Pages?', 'xtra-settings') .''	),
	'xtra_related_posts_homepage' 			=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 0			,'label' => '&nbsp; - '. esc_html__('show on Frontpage?', 'xtra-settings') .''	),
	'xtra_related_posts_place' 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 4			,'label' => ''. esc_html__('Position', 'xtra-settings') .''	),
	'xtra_related_posts_nocatmix' 			=> array( 'tab' => $xtb[7]	,'section' => $xsecs[23]	,'default' => 0			,'label' => ''. esc_html__('Don\'t mix categories', 'xtra-settings') .' <br/>'. esc_html__('Select only from the post\'s own category', 'xtra-settings') .'</span>'	),
	'xtra_revisions_to_keep' 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 0			,'label' => ''. esc_html__('Limit revisions', 'xtra-settings') .' <br/>'. esc_html__('of posts or pages to keep', 'xtra-settings') .''	),
	'xtra_revisions_to_keep_num' 			=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 99		,'label' => '-'. esc_html__('for pages', 'xtra-settings') .' &nbsp;&nbsp;&nbsp;'	),
	'xtra_revisions_to_keep_pnum' 			=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 99		,'label' => '-'. esc_html__('for others', 'xtra-settings') .''	),
	'xtra_autosave_interval' 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 0			,'label' => ''. esc_html__('Auto-Save interval', 'xtra-settings') .' <br/>'. esc_html__('when editing', 'xtra-settings') .'<br/>('. esc_html__('Writes in wp-config file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_autosave_interval_num' 			=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 160		,'label' => '-'. esc_html__('sec', 'xtra-settings') .''	),
	'xtra_empty_trash'		 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 0			,'label' => ''. esc_html__('Empty trash days', 'xtra-settings') .' <br/>'. esc_html__('for deleted posts', 'xtra-settings') .'<br/>('. esc_html__('Writes in wp-config file', 'xtra-settings') .')'	,'submitX' => 1	),
	'xtra_empty_trash_num'		 			=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 30		,'label' => '-'. esc_html__('days', 'xtra-settings') .''	),
	'xtra_posts_status_color'			 	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 0			,'label' => ''. esc_html__('Highlight Post Color by Status', 'xtra-settings') .'<br/>'. esc_html__('in Posts Admin screen', 'xtra-settings') .''	),
	'xtra_add_thumb_column'				 	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 0			,'label' => ''. esc_html__('Add Thumbnails to Post List', 'xtra-settings') .'<br/>'. esc_html__('in Posts Admin screen', 'xtra-settings') .''	),
	'xtra_add_thumb_column_num'			 	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 70		,'label' => ''. esc_html__('Size', 'xtra-settings') .':-px'	),
	'xtra_enable_column_shortcodes'		 	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 0			,'label' => ''. esc_html__('Enable column shortcodes', 'xtra-settings') .'<br/>'. esc_html__('Use', 'xtra-settings') .' [one_third]...[/one_third] [one_third]...[/one_third] [one_third_last]...[/one_third_last] '. esc_html__('etc. shortcodes for html columns.', 'xtra-settings') .' <a target="_blank" href="'.plugins_url('/assets/css/column-style.css', __FILE__).'">'. esc_html__('See the css', 'xtra-settings') .'</a> '. esc_html__('for hints.', 'xtra-settings') .''	),
	'xtra_auto_shortcodes_select'		 	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[24]	,'default' => 0			,'label' => ''. esc_html__('Shortcodes selector in editor', 'xtra-settings') .'<br/>'. esc_html__('Add a select menu with an automatically generated list of your shortcodes on top of the post editor.', 'xtra-settings') .''	),
	'xtra_require_featured_image' 			=> array( 'tab' => $xtb[7]	,'section' => $xsecs[25]	,'default' => 0			,'label' => ''. esc_html__('Require a Featured Image', 'xtra-settings') .' <br/>'. esc_html__('when publishing posts', 'xtra-settings') .''	),
	'xtra_auto_featured_image' 				=> array( 'tab' => $xtb[7]	,'section' => $xsecs[25]	,'default' => 0			,'label' => ''. esc_html__('Auto-add Featured Image', 'xtra-settings') .' <br/>'. esc_html__('if missing from the 1st image in the post', 'xtra-settings') .''	),
	'xtra_disallow_duplicate_posttitles' 	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[25]	,'default' => 0			,'label' => ''. esc_html__('Disallow Duplicate Post Titles', 'xtra-settings') .' <br/>'. esc_html__('when publishing posts', 'xtra-settings') .''	),
	'xtra_notify_author_on_publish'		 	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[25]	,'default' => 0			,'label' => ''. esc_html__('Email notify author when his post has been published', 'xtra-settings') .''	),
	'xtra_notify_author_on_publish_s_text'	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[25]	,'default' => ''. esc_html__('Your post is published.', 'xtra-settings') .''			,'label' => '<br/>'. esc_html__('Subject', 'xtra-settings') .':'	),
	'xtra_notify_author_on_publish_text'	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[25]	,'default' => ''. esc_html__('Thank you for your submission!', 'xtra-settings') .''			,'label' => '<br/>'. esc_html__('Text', 'xtra-settings') .':'	),
	'xtra_link_new_tab' 					=> array( 'tab' => $xtb[7]	,'section' => $xsecs[26]	,'default' => 0			,'label' => ''. esc_html__('Open all external links in new tab', 'xtra-settings') .'<br/>('. esc_html__('Internal links', 'xtra-settings') .': '.home_url().' - '. esc_html__('will not change', 'xtra-settings') .')'	),
	'xtra_attachment_image_link_filter' 	=> array( 'tab' => $xtb[7]	,'section' => $xsecs[26]	,'default' => 0			,'label' => ''. esc_html__('Add self-link to all uploaded images in posts', 'xtra-settings') .'<br/>('. esc_html__('Link to full-size image', 'xtra-settings') .')<br/>('. esc_html__('Also adds', 'xtra-settings') .' data-rel="lightbox-gallery-postimages" '. esc_html__('as an attribute', 'xtra-settings') .')'	),
);
//	If an option needs to call its function -  add 'submitX'
//	If an option needs write access to a file - add 'writes in .htaccess file' or 'writes in wp-config file'
//	Options that write in files should be added to deactivate() in xtra.php
return $xtra_sets;
}


//---HELPER functions----
function _xtra_add_all_file_insertions() {
	$names = array(
		'xtra_deflates',
		'xtra_browserbugs',
		'xtra_image_expiry',
		'xtra_remove_etags',
		//'xtra_redir_bots',
		'xtra_dir_index_disable',
		'xtra_response_header_hardening',
		//'xtra_protect_xss',
		//'xtra_protect_pageframing',
		//'xtra_protect_cotentsniffing',
		//'xtra_block_external_post',
		//'xtra_WPcache',
		'xtra_autosave_interval',
		'xtra_empty_trash',
		'xtra_debug',
		'xtra_disable_debug_display',
		'xtra_debug_log'
	);
	foreach ($names as $name) {
		if (!empty($name) && function_exists($name)) {
			//call functions dynamically
			if (xtra_instr($name,"_autosave_interval")) 		$name( get_optionXTRA($name,0), get_optionXTRA($name."_num",160) ); 
			elseif (xtra_instr($name,"_empty_trash")) 			$name( get_optionXTRA($name,0), get_optionXTRA($name."_num",30) );
			else	 											$name( get_optionXTRA($name,0) );
		}
	}
	return true;
}

// Delete All Options starting with 'xtra_'
function xtra_delete_all_options() {
global $xtra_options;
	_xtra_remove_all_file_insertions();
	$all_options = wp_load_alloptions();
	foreach( $all_options as $name => $value ) {
		if(stripos($name, 'xtra_')===0) delete_option( $name );
	}
	$xtra_options = array();
}

// Verify nonce
function xtra_check_nonce()
{
	$xtra_nonce = $_REQUEST['_wpnonce'];
	if ( ! wp_verify_nonce( $xtra_nonce, 'mynonce' ) ) {
		echo ''. esc_html__('This request could not be verified.', 'xtra-settings') .'';
		exit;
	}
	if ( !current_user_can('manage_options') ) {
		echo ''. esc_html__('This page is only for administrators.', 'xtra-settings') .'';
		exit;
	}
}

function xtra_find_img_src() {
global $post;
    if (!$img = xtra_find_image_id($post->ID)) {
        if ($img = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches))
            $img = $matches[1][0];
	}
    //if (is_int($img)) {
    if (ctype_digit($img)) {
        $img = wp_get_attachment_image_src($img);
        $img = $img[0];
    }
	$img = preg_replace('{-\d+x\d+\.}','.',$img);
    return $img;
}
function xtra_find_image_id($post_id) {
    if (!$img_id = get_post_thumbnail_id ($post_id)) {
        $attachments = get_children(array(
            'post_parent' => $post_id,
            'post_type' => 'attachment',
            'numberposts' => 1,
            'post_mime_type' => 'image'
        ));
        if (is_array($attachments)) foreach ($attachments as $a)
            $img_id = $a->ID;
    }
    if ($img_id)
        return $img_id;
    return false;
}

function xtra_get_excerpt() {
	global $post;
	$excerpt = strip_tags($post->post_content);
//echo "<pre>".$excerpt."</pre>";
	$excerpt = strip_shortcodes($excerpt);
	$excerpt = htmlentities($excerpt, null, 'utf-8');
	$excerpt = str_replace(array("\n","\r","\t","&nbsp;")," ",$excerpt);
	$excerpt = preg_replace('/\s+/', ' ',$excerpt);
	$excerpt = html_entity_decode($excerpt, null, 'utf-8');
	$excerpt = mb_substr($excerpt, 0, 125, 'UTF-8');
	return $excerpt;
}

function xtra_get_taglist() {
	global $post;
	$tag_list = get_the_terms( $post->ID, 'post_tag' );
	if( $tag_list ) {
		foreach( $tag_list as $tag )
			$tag_array[] = $tag->name;
		return implode(', ', $tag_array);
	}
}

function xtra_is_apache(){
	if ( stripos($_SERVER["SERVER_SOFTWARE"],"apache")!==FALSE ) return true;
	return false;
}

function xtra_get_image_id($file_url) {
	if (strpos($fule_url,"://")!==FALSE)
		$file_path = ltrim(str_replace(wp_upload_dir()['baseurl'], '', $file_url), '/');
	else
		$file_path = ltrim(str_replace(wp_upload_dir()['basedir'], '', $file_url), '/');

	global $wpdb;
	$statement = $wpdb->prepare("SELECT `ID`
		FROM {$wpdb->posts} AS posts
		JOIN {$wpdb->postmeta} AS meta on meta.`post_id`=posts.`ID`
		WHERE
			posts.`guid` LIKE '%%%s'
		OR (
			meta.`meta_key`='_wp_attached_file'
			AND meta.`meta_value` LIKE '%%%s'
		)
		;",
		$file_path,
		$file_path);

	$attachment = $wpdb->get_col($statement);

	if (count((array)$attachment) < 1) {
		return false;
	}

	return implode(", ",array_unique($attachment));
}

function xtra_get_image_ids() {

	//$file_path = wp_upload_dir()['baseurl']."/";
	$file_path = "\.(jpe?g|gif|png)$";

	global $wpdb;
	$statement = $wpdb->prepare("SELECT ID,guid,meta_value
		FROM {$wpdb->posts} AS posts
		JOIN {$wpdb->postmeta} AS meta on meta.`post_id`=posts.`ID`
		WHERE
			posts.`guid` REGEXP '%s'
		OR (
			meta.`meta_key`='_wp_attached_file'
			AND meta.`meta_value` REGEXP '%s'
		)
		;",
		$file_path,
		$file_path);

	$attachment = $wpdb->get_results($statement);

//echo "<pre>".count((array)$attachment)." img_postids: ".print_r($attachment,1)."</pre>";

	if (count((array)$attachment) < 1) {
		return false;
	}

	$arr = array();

	foreach ($attachment as $row) {
		preg_match("#[^\"']*?\.(jpe?g|gif|png)#",$row->guid,$matches);
		$tkey = "";
		if (isset($matches[0]))
			$tkey = xtra_clean_tkey($matches[0]);
		if (!isset($arr[$tkey])) $arr[$tkey] = "";
		if ($tkey && !xtra_instr($arr[$tkey],$row->ID.",") )
			$arr[$tkey] .= $row->ID.",";

		preg_match("#[^\"']*?\.(jpe?g|gif|png)#",$row->meta_value,$matches);
		$tkey = "";
		if (isset($matches[0]))
			$tkey = xtra_clean_tkey($matches[0]);
		if (!isset($arr[$tkey])) $arr[$tkey] = "";
		if ($tkey && !xtra_instr($arr[$tkey],$row->ID.",") )
			$arr[$tkey] .= $row->ID.",";
	}

	return $arr;
}

function xtra_clean_tkey($tkey) {
	$upldirname = basename(wp_upload_dir()['basedir']);
	$tkey = str_ireplace(wp_upload_dir()['basedir']."/","",$tkey);
	$tkey = str_ireplace(wp_upload_dir()['baseurl']."/","",$tkey);
	$tkey = preg_replace("#.*$upldirname/#i","",$tkey);
	return $tkey;
}

function xtra_instr($haystack,$needle,$casesensitive=false) {
	if ($casesensitive) {
		if (str_replace($needle,"",$haystack)==$haystack) $ret=false;
		else $ret=true;
	}
	else {
		if (!is_array($needle)) $needle = mb_convert_case($needle,MB_CASE_LOWER,"UTF-8");
		if (!is_array($haystack)) $haystack = mb_convert_case($haystack,MB_CASE_LOWER,"UTF-8");

		if (str_ireplace($needle,"",$haystack)==$haystack) $ret=false;
		else $ret=true;
	}
	return $ret;
}

function xtra_toolong($str,$part='',$max=100,$expand='[...]') {
	if ($part==1) return esc_url(urldecode(substr($str,0,$max)));
	if ($part==2) {
		if (strlen($str)<=$max) return "";
		$toolong = '<a href="#" onclick="jQuery(this).prev().html(\''.esc_url(urldecode($str)).'\');jQuery(this).html(\'\');jQuery(this).blur();return false;" >'.$expand.'</a>';
	}
	return $toolong;
}

function xtra_remove_empty_subfolders($path)
{
	$empty=true;
	foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file) {
		if (is_dir($file)) {
			if (!xtra_remove_empty_subfolders($file)) $empty=false;
		}
		else {
			$empty=false;
		}
	}
	if ($empty) rmdir($path);
	return $empty;
}

/**
 * Get size information for all currently-registered image sizes.
 *
 * @global $_wp_additional_image_sizes
 * @uses   get_intermediate_image_sizes()
 * @return array $sizes Data for all currently-registered image sizes.
 */
function xtra_get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	return $sizes;
}

function xtra_mylog($txt,$tit="") {
	$html = "";
	$html .= "<div style='margin:10px;margin-left:180px;padding:10px;overflow:auto;background:#FFF;'>";
	$html .= "<h3>$tit</h3>";
	$html .= "<hr>";
	$html .= "<pre>".print_r($txt,1)."</pre>";
	$html .= "</div>";
	echo $html;
}

function xtra_memlog($txt) {
global $memoryTableTRs, $status, $last_memory, $fromStart, $memory_limit, $memory_usage;

	if (!$memory_limit) {
		$memory_limit = ini_get('memory_limit');
		if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
			if ($matches[2] == 'M') $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
			elseif ($matches[2] == 'K') $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
		}
	}
	if (!$memory_limit) $memory_limit = 128*1024*1024; //???
	$memory = memory_get_peak_usage(true);
	$memory_usage = $memory/$memory_limit*100;

	if ($txt=="memoryTable") { //return memoryTable
		$thisStyle = "style='font-size:90%;border-collapse:collapse;'";
		$memTRx = "<tr><td colspan='6' align='center'><h3>Memory: ".
			round($memory/1024/1024,1)." MB".
			" / ".round($memory_limit/1024/1024,1)." MB".
			//" = ".round(($memory_limit-$memory)/1024/1024,0)." MB".
			" (".round($memory_usage,0)."%)
			</h3></td></tr>";
		$memoryTable = "
		<div style='width:50%; margin:30px auto; padding:20px; background:#FFDDBB;'>
		<table width='100%' $thisStyle border='1' cellpadding='3' cellspacing='0'>$memoryTableTRs $memTRx</table></div>
		";
		$memoryTable = "
		<div style='float:left; margin-right:30px; margin-top:50px; padding:20px; background:#FFDDBB;'><h2>Memory Table</h2>
		<table style='width:auto;' class='wp-list-table widefat striped xtra slimrow'>$memoryTableTRs $memTRx</table></div>
		";

		return $memoryTable;
	}
	else { //count elapsed time & memory increase
		if (!$status)
		{
			$elapsed = "Start";
			$fromStart = 0;
		}
		else {
			$elapsed = "+".round(microtime(true)-$status,2);
			$fromStart += $elapsed;
		}
		$mem_inc = $memory-($last_memory?$last_memory:0);
		$mem_prc = $memory/$memory_limit*100;
		$memoryTableTRs .= "<tr><th align='right'>";
		if ($elapsed != '+0') $memoryTableTRs .= "$elapsed s";
		$memoryTableTRs .= "</td><td align='right'>$fromStart s</td>
			<td>$txt</td><td align='right'>";
		if ($mem_inc) $memoryTableTRs .= "+".round($mem_inc/1024/1024,1)." MB";
		$memoryTableTRs .= "</td><td align='right'>".round($memory/1024/1024,1)." MB</td>
			<td align='right'>".round($mem_prc,0)."%</td>
			</tr>";
		$status = microtime(true);
		$last_memory = $memory;
	}
}

function xtra_request_anal($what,$row) {
	if ($what=="country") {
		$x = 1;
		$filter = strtoupper(substr($row["server"],strrpos($row["server"],".")+1));
		if (!$filter || is_numeric($filter)) {
			preg_match("/:\/\/[^\/\?&]*\.([a-z]+)/i",$row["href"],$match);
			$filter = strtoupper($match[1]);
		}
		if ( $row["lang"] && ( !$filter || is_numeric($filter) || xtra_instr($filter,array('com','net')) ) ) {
			$filter = strtoupper(substr($row["lang"],0,2));
		}
		if (!$filter || is_numeric($filter)) {
			$filter = "N.A.";
		}
	}
	elseif ($what=="platform") {
		$filter = 'PC ';
		if (stripos($row["ua"], 'ipad'))
			$filter = 'TABLET iPad ';
		elseif (stripos($row["ua"], 'iphone'))
			$filter = 'MOBILE iPhone ';
		elseif (stripos($row["ua"], 'Tablet'))
			$filter = 'TABLET ';
		elseif (stripos($row["ua"], 'Mobi'))
			$filter = 'MOBILE ';
		elseif (stripos($row["ua"], 'Android'))
			$filter = 'MOBILE ';

		if (stripos($row["ua"], 'Android'))
			$filter .= 'Android';
		elseif (stripos($row["ua"], 'linux'))
			$filter .= 'Linux';
		elseif (stripos($row["ua"], 'mac'))
			$filter .= 'Apple';
		elseif (stripos($row["ua"], 'win')) {
			$filter .= 'Windows';
			$winvers = array(
				"Windows NT 10"	=>	" 10",
				"Windows NT 6.3"	=>	" 8.1",
				"Windows NT 6.2"	=>	" 8",
				"Windows NT 6.1"	=>	" 7",
				"Windows NT 6.0"	=>	" Vista",
				"Windows NT 5.2"	=>	" XP x64",
				"Windows NT 5.1"	=>	" XP",
				"Windows NT 5.01"	=>	" 2000, SP1",
				"Windows NT 5.0"	=>	" 2000",
			);
			foreach($winvers as $key => $val) {
				if (strpos(" ".$row["ua"], $key)) {
					$filter .= $val;
					break;
				}
			}
		}
		elseif (stripos(" ".$row["ua"], 'facebook.com'))
			$filter = '- Facebook Bot';
		elseif (stripos(" ".$row["ua"], 'pinterest.com'))
			$filter = '- Pinterest Bot';
		else
			$filter .= 'N.A.';
	}
	elseif ($what=="browser") {
		if (stripos(" ".$row["ua"], 'Firefox/') && !stripos(" ".$row["ua"], 'Seamonkey/'))
			$filter = 'Firefox';
		elseif (stripos(" ".$row["ua"], 'Seamonkey/'))
			$filter = 'Seamonkey';
		elseif (stripos(" ".$row["ua"], 'facebook.com'))
			$filter = '- Facebook Bot';
		elseif (stripos(" ".$row["ua"], 'pinterest.com'))
			$filter = '- Pinterest Bot';
		elseif (stripos(" ".$row["ua"], 'Rockmelt/'))
			$filter = 'Rockmelt';
		elseif (stripos(" ".$row["ua"], 'Chrome/') && !stripos(" ".$row["ua"], 'Chromium/'))
			$filter = 'Chrome';
		elseif (stripos(" ".$row["ua"], 'Chromium/'))
			$filter = 'Chromium';
		elseif (stripos(" ".$row["ua"], 'Safari/') && !(stripos(" ".$row["ua"], 'Chrome/') || stripos(" ".$row["ua"], 'Chromium/')))
			$filter = 'Safari';
		elseif (stripos(" ".$row["ua"], '(iphone;') || stripos(" ".$row["ua"], '(ipad;') || stripos(" ".$row["ua"], '(Macintosh;'))
			$filter = 'Safari';
		elseif (stripos(" ".$row["ua"], 'Opera/'))
			$filter = 'Opera';
		elseif (stripos(" ".$row["ua"], 'MSIE '))
			$filter = 'Internet Explorer';
		elseif (stripos(" ".$row["ua"], ' MSIE'))
			$filter = 'Internet Explorer';
		elseif (stripos(" ".$row["ua"], 'Android'))
			$filter = 'Android';
		elseif (stripos(" ".$row["ua"], 'Symbian'))
			$filter = 'Symbian';
		elseif (stripos(" ".$row["ua"], 'baidu.com'))
			$filter = '- Baidu Bot';
		elseif (stripos(" ".$row["ua"], 'Java'))
			$filter = 'Java';
		elseif (stripos(" ".$row["ua"], 'curl'))
			$filter = 'curl';
		elseif (stripos(" ".$row["ua"], 'Mozilla'))
			$filter = 'other Mozilla';
		else {
			$filter = 'N.A.';
			//$filter .= ' '.$row["ua"];
		}
	}
	return $filter;
}

function xtra_time_measure($what,$time_start=0) {
	$arr = get_optionXTRA($what,array());
	if (!is_array($arr)) $arr = array();
	$exec_time = microtime(true)-$time_start;

	if ($time_start) array_unshift($arr, number_format($exec_time,3));
	elseif (count((array)$arr)) return sprintf(esc_html__('last %s calls: avg %s sec - max %s sec', 'xtra-settings'),count((array)$arr),round(array_sum($arr)/count((array)$arr),3),max($arr));
	else return "n.a.";

	if (count((array)$arr) > 10) array_splice($arr,10);
	update_optionXTRA($what, $arr);
}

function xtra_check_opt($p1) {
	if (true) { //$known_ok array
	$known_ok = array(
			'current_theme',					//xtra
			'db_upgraded',						//xtra
			'default_post_format',				//xtra
			'finished_splitting_shared_terms',	//xtra
			'fresh_site',						//xtra
			'link_manager_enabled',				//xtra
			'medium_large_size_h',				//xtra
			'medium_large_size_w',				//xtra
			'nav_menu_options',					//xtra
			'theme_switched',					//xtra
			'active_plugins',					//3.0
			'admin_email',						//3.0
			'advanced_edit',					//3.0
			'blog_charset',						//3.0
			'blogdescription',					//3.0
			'blogname',							//3.0
			'category_base',					//3.0
			'comment_max_links',				//3.0
			'comment_moderation',				//3.0
			'comments_notify',					//3.0
			'cron',								//3.0@
			'date_format',						//3.0
			'default_category',					//3.0
			'default_comment_status',			//3.0
			'default_ping_status',				//3.0
			'default_pingback_flag',			//3.0
			'default_post_edit_rows',			//3.0
			'dismissed_update_core',			//3.0&
			'gmt_offset',						//3.0
			'gzipcompression',					//3.0
			'hack_file',						//3.0
			'home',								//3.0
			'links_recently_updated_append',	//3.0
			'links_recently_updated_prepend',	//3.0
			'links_recently_updated_time'	,	//3.0
			'links_updated_date_format',		//3.0
			'mailserver_login',					//3.0
			'mailserver_pass',					//3.0
			'mailserver_port',					//3.0
			'mailserver_url',					//3.0
			'moderation_keys',					//3.0
			'moderation_notify',				//3.0
			'page_for_posts',					//3.0
			'page_on_front',					//3.0
			'permalink_structure',				//3.0
			'ping_sites',						//3.0
			'posts_per_page',					//3.0
			'posts_per_rss',					//3.0
			'require_name_email',				//3.0
			'rewrite_rules',					//3.0!
			'rss_use_excerpt',					//3.0
			'rss_excerpt_length',				//3.0+
			'show_on_front',					//3.0*
			'siteurl',							//3.0
			'start_of_week',					//3.0
			'time_format',						//3.0
			'use_balanceTags',					//3.0
			'use_smilies',						//3.0
			'use_ssl',							//3.0% get_user_option
			'users_can_register',				//3.0

// multisite
			'template',				//3.0
			'stylesheet',			//3.0
			'upload_path',			//3.0
			'fileupload_url',		//3.0

// akismet ver 2.3.0 with WordPress ver 3.0
			'wordpress_api_key',				//3.0
			'akismet_discard_month',			//3.0
			'akismet_connectivity_time',		//3.0
			'akismet_available_servers',		//3.0
			'akismet_spam_count',				//3.0
			'widget_akismet',					//3.0

// old kubrick theme
			'kubrick_header_color',				//2.9
			'kubrick_header_display',			//2.9
			'kubrick_header_image',				//2.9

// get_transient()s
			'_site_transient_theme_roots',				//2.9.1
			'_site_transient_timeout_theme_roots',		//2.9.1
			'_transient_doing_cron',					//2.9.1
			'_transient_mailserver_last_checked',		//2.9.1
			'_transient_plugin_slugs',					//2.9.1
			'_transient_timeout_plugin_slugs',			//2.9.1
			'_transient_timeout_dirsize_cache',			//3.0
			'_transient_random_seed',					//2.9.1
			'_transient_rewrite_rules',					//2.9.1
			'_transient_update_core',					//2.9.1
			'_transient_update_plugins',				//2.9.1
			'_transient_update_themes',					//2.9.1
			'_transient_wporg_theme_feature_list',		//2.9.1

// Core default widgets
			'widget_archives',				//2.9.1
			'widget_calendar',				//2.9.1
			'widget_categories',			//2.9.1
			'widget_links',					//2.9.1
			'widget_meta',					//2.9.1
			'widget_nav_menu',				//3.0
			'widget_pages',					//2.9.1
			'widget_recent_comments',		//2.9.1
			'widget_recent_posts',			//2.9.1
			'widget_rss',					//2.9.1
			'widget_search',				//2.9.1
			'widget_tag_cloud',				//2.9.1
			'widget_text',					//2.9.1

// from earlier versions
			'embed_autourls',				//2.9
			'embed_size_h',					//2.9
			'embed_size_w',					//2.9

			'timezone_string',				//2.8 and 3.0+
			'widget_recent-comments',		//2.8
			'widget_recent-posts',			//2.8
			'widget_recent_entries',		//2.8

			'close_comments_days_old',		//2.7
			'close_comments_for_old_posts',	//2.7
			'comment_order',				//2.7
			'comments_per_page',			//2.7
			'default_comments_page',		//2.7
			'image_default_align',			//2.7
			'image_default_link_type',		//2.7
			'image_default_size',			//2.7
			'large_size_h',					//2.7
			'large_size_w',					//2.7
			'page_comments',				//2.7
			'sticky_posts',					//2.7
			'thread_comments',				//2.7
			'thread_comments_depth',		//2.7
			'widget_categories',			//2.7
			'widget_rss',					//2.7
			'widget_text',					//2.7

			'avatar_default',			//2.6
			'enable_app',				//2.6
			'enable_xmlrpc',			//2.6
			'page_attachment_uris',		//2.6!

			'avatar_rating',			//2.5
			'medium_size_w',			//2.5
			'medium_size_h',			//2.5
			'show_avatars',				//2.5
			'thumbnail_crop',			//2.5
			'thumbnail_size_h',			//2.5
			'thumbnail_size_w',			//2.5
			'upload_url_path',			//2.5

			'xvalid_options',			//2.3 during backup

			'tag_base',					//2.2 and 3.0!

			'import-blogger',			//2.1.3
			'blog_public',				//2.1
			'default_link_category',	//2.1
			'show_on_front',			//2.1

			'secret',					//2.0.3
			'upload_path',				//2.0.1
			'uploads_use_yearmonth_folders',	//2.0.1
			'db_version',				//2.0
			'default_role',				//2.0

			'use_trackback',			//1.5.1
			'blacklist_keys',			//1.5
			'comment_registration',		//1.5
			'comment_whitelist',		//1.5
			'default_email_category',	//1.5
			'html_type',				//1.5
			'page_uris',				//1.5
			'recently_edited',			//1.5
			'rss_language',				//1.5
			'stylesheet',				//1.5
			'template',					//1.5
			'use_linksupdate',			//1.5
		);
	}
	if (false) {
		if (file_exists(WP_CONTENT_DIR)) 					$dir_iterator = new RecursiveDirectoryIterator(WP_CONTENT_DIR);
		if (file_exists(WP_CONTENT_DIR)) 					$xtra_iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
	}
	if (true) {
		if (file_exists(WP_PLUGIN_DIR)) 					$dir_iterator1 = new RecursiveDirectoryIterator(WP_PLUGIN_DIR);
		if (file_exists(get_theme_root())) 					$dir_iterator2 = new RecursiveDirectoryIterator(get_theme_root());
		if (file_exists(WP_CONTENT_DIR ."/mu-plugins/")) 	$dir_iterator3 = new RecursiveDirectoryIterator(WP_CONTENT_DIR ."/mu-plugins/");
		$xtra_iterator = new AppendIterator();
		if (file_exists(WP_PLUGIN_DIR)) 					$xtra_iterator->append(new RecursiveIteratorIterator($dir_iterator1, RecursiveIteratorIterator::SELF_FIRST));
		if (file_exists(get_theme_root())) 					$xtra_iterator->append(new RecursiveIteratorIterator($dir_iterator2, RecursiveIteratorIterator::SELF_FIRST));
		if (file_exists(WP_CONTENT_DIR ."/mu-plugins/")) 	$xtra_iterator->append(new RecursiveIteratorIterator($dir_iterator3, RecursiveIteratorIterator::SELF_FIRST));
	}

	$ret = array();
	if (!is_array($p1)) $p1 = array($p1);
	$p0 = $p1;
	$p1 = array_diff($p1,$known_ok);
	foreach ($xtra_iterator as $file) {
		if ($file->isFile()) {
			$filename = $file->getFilename();
			if (!preg_match('{\.(php|phps)$}i',$filename)) continue;
			$fullpath = $file->getPathname();
			//$size = $file->getSize();
			//$mtime = $file->getMTime();
			$relapath = str_ireplace(ABSPATH,"/",$fullpath);

			$fcstr = file_get_contents($fullpath);

			foreach ($p1 as $op1) {
				if (!empty($ret[$op1])) continue; //already found

				$found = false;
				if (strpos($fcstr,$op1)!==FALSE) $found = 1; //7.28s	i:14.03s
				if (!$found && strpos($op1,"_transient")!==FALSE) {
					$op2 = str_replace(array("_transient_","_site_transient_"),"",$op1);
					if (strpos($fcstr,$op2)!==FALSE) $found = 2; //+0.41s
				}

				if ($found) {
					$user = $fullpath;
					$user = str_replace(WP_PLUGIN_DIR,"",$user);
					$user = str_replace(get_theme_root(),"",$user);
					$user = str_replace(WP_CONTENT_DIR,"",$user);
					$user = preg_replace("#/?([^/]*)/?.*#","$1",$user);
					$msg = "<a href='#' title='$filename\n$relapath'><b>$user</b></a>";
					$ret[$op1] = $msg;
				}
				elseif (!isset($ret[$op1])) $ret[$op1] = '';
			}
		}
	}
	$newret = array();
	foreach ($p0 as $key => $val) {
		if ( in_array($val, $known_ok)
			|| strpos($val, 'user_roles') !== FALSE
			|| strpos($val, 'category_children') !== FALSE
			|| strpos($val, '_transient_feed_') !== FALSE
			) {
				$newret[$val] = '<b>'. esc_html__('known OK', 'xtra-settings') .'</b>';
			}
			else {
				$newret[$val] = $ret[$val];
			}
	}
	$ret = $newret;
	return $ret;
}

function xtra_2_wpeditor_image_resize( $file, $jpeg_quality = 82, $max_w=null, $max_h=null, $crop = false, $suffix = null, $dest_path = null ) {
	global $xtra_ajax_sure;
	global $xtra_ajax;
	if ( function_exists( 'wp_get_image_editor' ) ) {
		// WP 3.5 and up use the image editor

		$fsiz1 = filesize($file);
		if ($xtra_ajax) $backup = xtra_backup_image($file);
		else $backup['backupOK'] = 1;

		if ( $backup['backupOK']!="ERROR" ) {
			$editor = wp_get_image_editor( $file );
			if (!$xtra_ajax && is_wp_error( $editor ) ) return $editor;
			$editor->set_quality( $jpeg_quality );

			$ftype = pathinfo( $file, PATHINFO_EXTENSION );

			// try to correct for auto-rotation if the info is available
			if (function_exists('exif_read_data') && ($ftype == 'jpg' || $ftype == 'jpeg') ) {
				$exif = @exif_read_data($file);
				$orientation = is_array($exif) && array_key_exists('Orientation', $exif) ? $exif['Orientation'] : 0;
				switch($orientation) {
					case 3:
						$editor->rotate(180);
						break;
					case 6:
						$editor->rotate(-90);
						$sav = $max_w;
						$max_w = $max_h;
						$max_h = $sav;
						break;
					case 8:
						$editor->rotate(90);
						$sav = $max_w;
						$max_w = $max_h;
						$max_h = $sav;
						break;
				}
			}

			$resiz = 0;
			if ($max_w || $max_h) {
				$resized = $editor->resize( $max_w, $max_h, $crop );
				$resiz = 1;
			}
			if (!$xtra_ajax && is_wp_error( $resized ) ) return $resized;

			if ($xtra_ajax) {
				$dest_file = $file;
				if ($xtra_ajax_sure) $saved = $editor->save( $dest_file );
			}
			else {
				$dest_file = $editor->generate_filename( $suffix, $dest_path );
				// FIX: make sure that the destination file does not exist.  this fixes
				// an issue during bulk resize where one of the optimized media filenames may get
				// used as the temporary file, which causes it to be deleted.
				while (file_exists($dest_file)) {
					$dest_file = $editor->generate_filename('TMP', $dest_path );
				}
				$saved = $editor->save( $dest_file );
			}
			//$editor->__destruct(); // does not help to lower memory usage.

			if (!$xtra_ajax && is_wp_error( $saved ) ) return $saved;

			if (!$xtra_ajax) return $dest_file;
		}
		if ($xtra_ajax) {
			$fsiz2 = filesize($dest_file);
			$savingPRC = 100-round($fsiz2/$fsiz1*100)."%";
			$savingKB = round(($fsiz1-$fsiz2)/1024)." KB";
			return array_merge($backup,array(
				'path'			=>	$dest_file,
				'name'			=>	$dest_file==$file ? basename($dest_file) : basename($file)." ==> ".basename($dest_file),
				'imageOK'		=>	is_wp_error( $saved ) ? "ERROR" : "OK".($resiz ? " RESIZED" : ""),
				'compressPRC'	=>	$jpeg_quality,
				'savingPRC'		=>	$savingPRC,
				'savingKB'		=>	$savingKB,
				'wh'			=>	$max_w."x".$max_h,
			));
		}
	}
	return false;
}

function getElementsByClass(&$parentNode, $tagName, $className) {
    $nodes=array();

    $childNodeList = $parentNode->getElementsByTagName($tagName);
    for ($i = 0; $i < $childNodeList->length; $i++) {
        $temp = $childNodeList->item($i);
        if (stripos($temp->getAttribute('class'), $className) !== false) {
            $nodes[]=$temp;
        }
    }

    return $nodes;
}

function XXX_xtra_html_places($buffer,$html,$plac) {
	$htm = new DOMDocument('1.0', 'utf-8');
	$htm->loadHTML($html);

	$dom = new DOMDocument('1.0', 'utf-8');
	$dom->loadHTML($buffer);

	$body = $dom->getElementsByTagName('body')->item(0);
	$page_item = getElementsByClass($body, "div", "page-item");
	$post_item = getElementsByClass($body, "div", "post-item");
	if ($page_item->length > 0) {
		$box = $page_item[0];
		$titt = $box->getElementsByTagName('h2')->item(0);
		$txtt = $box;
	}
	elseif ($post_item->length > 0) {
		$box = $post_item[0];
		$titt = $box->getElementsByTagName('h1')->item(0);
		$txtt = $box;
	}
	else {
		$box = $dom->getElementsByTagName('article')->item(0);
		$titt = getElementsByClass($box, "div", "entry-header")[0];
		$txtt = getElementsByClass($box, "div", "entry-content")[0];
	}

	//$node = $dom->importNode($htm, true);
	//$box->appendChild($node);


	return $dom->saveHTML();
}
function xtra_html_places($buffer,$html,$plac,$shortcode) {
/*
		if ($plac == 1) { //after text
			$buffer = preg_replace( array(
				'#</div>\s*</article>(?!.*</div>\s*</article>)#umis',
			), array( $html . '</div></article>' ), $buffer, 1 ); //only the last occurence: negative lookahead (?!...)
		}
		else if ($plac == 2) { //before text
			$buffer = preg_replace( array(
				'#(\<div[^>]*?entry-content)#umi',
			), array( $html . '$1' ), $buffer, 1 ); //only the 1st occurence
		}
		else if ($plac == 3) { //before title
			$buffer = preg_replace( array(
				'#(\<header[^>]*?entry-header)#umi',
			), array( $html . '$1' ), $buffer, 1 ); //only the 1st occurence
		}
		else if ($plac == 6) { //after title
			$buffer = preg_replace( array(
				'#(\<h1[^>]*?entry-title.*?</h1>)#umi',
			), array( '$1' . $html ), $buffer, 1 ); //only the 1st occurence
		}
		else if ($plac == 4) { //after atricle
			if (preg_match('#\<nav[^>]*?nav-below.*?\</nav>#umis',$buffer)) {
				$buffer = preg_replace( array(
					'#(\<nav[^>]*?nav-below.*?\</nav>)#umis',
				), array( '$1' . $html ), $buffer, 1 ); //only the 1st occurence
			}
			else {
				$buffer = preg_replace( array(
					'#</div>\s*</article>(?!.*</div>\s*</article>)#umis',
				), array( '</div></article>' . $html ), $buffer, 1 ); //only the last occurence: negative lookahead (?!...)
			}
		}
		else if ($plac == 5) { //shortcode: [xtra_share_buttons]
			$buffer = preg_replace( array(
				'#\[xtra_share_buttons\]#umi',
			), array( $html ), $buffer, 1 ); //only the 1st occurence
		}
		*/
/*		1	after text
		2	before text
		3	before title
		4	after article
		5	shortcode
		6	after title
*/
	$htmlplaces = array(
		1	=>	array(
			1	=>	array(	'from' => '</div>\s*</article>(?!.*</div>\s*</article>)'	, 'to' => $html.'</div></article>' ),
			2	=>	array(	'from' => '(\<div[^>]*?entry-content)'						, 'to' => $html.'$1' ),
			3	=>	array(	'from' => '(\<header[^>]*?entry-header)'					, 'to' => $html.'$1' ),
			4	=>	array(	'from' => '</div>\s*</article>(?!.*</div>\s*</article>)'	, 'to' => '</div></article>'.$html ),
			5	=>	array(	'from' => '\['.$shortcode.'\]'								, 'to' => $html ),
			6	=>	array(	'from' => '(\<h1[^>]*?entry-title.*?</h1>)'					, 'to' => '$1'.$html ),
		),
		2	=>	array(
			//1	=>	array(	'from' => '(<div class="\w+?-item">.*?)(</div>\s*</div>(\s*<nav)?)'				, 'to' => '$1'.$html.'$2' ), //done by content filter
			//2	=>	array(	'from' => '(<div class="\w+?-item">.*?)(<p)'									, 'to' => '$1'.$html.'$2' ), //done by content filter
			3	=>	array(	'from' => '(<div class="\w+?-item">.*?)(<h\d)'									, 'to' => '$1'.$html.'$2' ),
			4	=>	array(	'from' => '(</div>\s*<div class="sidebar)'		, 'to' => $html.'$1' ), // works only on post page
			5	=>	array(	'from' => '\['.$shortcode.'\]'													, 'to' => $html ),
			6	=>	array(	'from' => '(<div class="\w+?-item">.*?<h\d.*?</h\d>)'							, 'to' => '$1'.$html ),
		),
	);
	$plc = $htmlplaces[1];
	if (xtra_instr(xtra_get_theme_name(),'mesmerize')) $plc = $htmlplaces[2];
	$buffer = preg_replace( '#'.$plc[$plac]['from'].'#umis', $plc[$plac]['to'], $buffer, 1 );

	return $buffer;
}

function xtra_get_theme_name() {
	$theme = wp_get_theme();
	$themename = $theme->get( 'Name' );
	return $themename;
}

//---Functions--------------

function xtra_getSizes($size,$dec=2){
	$size_r = '';
	if($size==0){
		$size_r = "0";
	}
	else if($size<1024){
		$size_r = $size. ' B';
	}
	else if($size>=1024 && $size<(1024*1024)){
		$size_r = round($size/(1024),$dec). ' KB';
	}
	else{
		$size_r = round($size/(1024*1024),$dec). ' MB';
	}
	return $size_r;
}

function xtra_table_optimize($tables){
	global $wpdb;
	$status = true;
	$sql_opt = "OPTIMIZE TABLE ".$tables;
	$wpdb->query($sql_opt);
	return $status;
}

function xtra_table_remove($type){
	global $wpdb;
	$status = true;
	if($type==1){
		$sql = "DELETE FROM `$wpdb->posts` WHERE post_type = 'revision'";
		$wpdb->query( $sql );
	}
	else if($type==2){
		$sql = "DELETE FROM `$wpdb->posts` WHERE post_status = 'auto-draft'";
		$wpdb->query( $sql );
	}
	else if($type==3){
		$sql = "DELETE FROM `$wpdb->posts` WHERE post_status = 'trash'";
		$wpdb->query( $sql );
		//$sql = "DELETE asi FROM  `$wpdb->postmeta`  asi LEFT JOIN  `$wpdb->posts`  wp ON wp.ID = asi.post_id WHERE wp.ID IS NULL";
		//$wpdb->query( $sql );
	}
	else if($type==4){
		$sql = "DELETE FROM `$wpdb->comments` WHERE comment_approved = 'spam'";
		$wpdb->query( $sql );
	}
	else if($type==5){
		$sql = "DELETE FROM `$wpdb->comments` WHERE comment_approved = 'trash'";
		$wpdb->query( $sql );
	}
	else if($type==6){
		$sql = "DELETE FROM `$wpdb->options` WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'";
		$wpdb->query( $sql );
		$sql = "DELETE FROM `$wpdb->options` WHERE option_name rlike 'wpseo_sitemap_[0-9]*_cache_validator'";
		$wpdb->query( $sql );
	}
	else if($type==7){
		xtra_purge_transients('7 days',true);
		return true;
	}

	return $status;
}
function xtra_table_remove_count($type){
	global $wpdb;
	if($type==1){
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$wpdb->posts` WHERE post_type = 'revision'" );
	}
	else if($type==2){
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$wpdb->posts` WHERE post_status = 'auto-draft'" );
	}
	else if($type==3){
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$wpdb->posts` WHERE post_status = 'trash'" );
		//$count += $wpdb->get_var( "SELECT COUNT(asi) FROM  `$wpdb->postmeta`  asi LEFT JOIN  `$wpdb->posts`  wp ON wp.ID = asi.post_id WHERE wp.ID IS NULL" );
	}
	else if($type==4){
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$wpdb->comments` WHERE comment_approved = 'spam'" );
	}
	else if($type==5){
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$wpdb->comments` WHERE comment_approved = 'trash'" );
	}
	else if($type==6){
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM `$wpdb->options` WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'" );
		$count += $wpdb->get_var( "SELECT COUNT(*) FROM `$wpdb->options` WHERE option_name rlike 'wpseo_sitemap_[0-9]*_cache_validator'" );
	}
	else if($type==7){
		return xtra_purge_transients('7 days',false,'count');
	}

	return $count;
}
function xtra_table_remove_show($type){
	global $wpdb;
	if($type==1){
		$resarr = $wpdb->get_results( "SELECT * FROM `$wpdb->posts` WHERE post_type = 'revision'" );
	}
	else if($type==2){
		$resarr = $wpdb->get_results( "SELECT * FROM `$wpdb->posts` WHERE post_status = 'auto-draft'" );
	}
	else if($type==3){
		$resarr = $wpdb->get_results( "SELECT * FROM `$wpdb->posts` WHERE post_status = 'trash'" );
		//$resarr2 = $wpdb->get_results( "SELECT asi FROM  `$wpdb->postmeta`  asi LEFT JOIN  `$wpdb->posts`  wp ON wp.ID = asi.post_id WHERE wp.ID IS NULL" );
	}
	else if($type==4){
		$resarr = $wpdb->get_results( "SELECT * FROM `$wpdb->comments` WHERE comment_approved = 'spam'" );
	}
	else if($type==5){
		$resarr = $wpdb->get_results( "SELECT * FROM `$wpdb->comments` WHERE comment_approved = 'trash'" );
	}
	else if($type==6){
		$resarr = $wpdb->get_results( "SELECT * FROM `$wpdb->options` WHERE option_name LIKE '_site_transient_browser_%' OR option_name LIKE '_site_transient_timeout_browser_%' OR option_name LIKE '_transient_feed_%' OR option_name LIKE '_transient_timeout_feed_%'" );
		$resarr2 = $wpdb->get_results( "SELECT * FROM `$wpdb->options` WHERE option_name rlike 'wpseo_sitemap_[0-9]*_cache_validator'" );
		$resarr = array_merge($resarr,$resarr2);
	}
	else if($type==7){
		return xtra_purge_transients('7 days',false,'show');
	}

	$txt = "";
	foreach ($resarr as $row) {
		foreach ($row as $key=>$fld) {
			if (str_ireplace(array("name","title"),"",$key) != $key || preg_match("#id$#i",$key))
				$txt .= $fld.", ";
			//$txt .= "[$key]=".$fld.", ";
		}
		$txt .= "\n------------------\n";
	}

	return $txt;
}

function xtra_purge_transients($older_than = '7 days', $safemode = true, $just_count_or_show="") {
	global $wpdb;

	$older_than_time = strtotime('-' . $older_than);
	if ($older_than_time > time() || $older_than_time < 1) {
		return false;
	}

	$transients = $wpdb->get_col( $wpdb->prepare( "SELECT REPLACE(option_name, '_transient_timeout_', '') AS transient_name FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\__%%' AND option_value < %s", $older_than_time) );
	if ($safemode) {
		foreach($transients as $transient) {
			get_transient($transient);
		}
	} else {
		$options_names = array();
		foreach($transients as $transient) {
			$options_names[] = '_transient_' . $transient;
			$options_names[] = '_transient_timeout_' . $transient;
		}
		if ($options_names) {
			$options_names = array_map(array($wpdb, 'escape'), $options_names);
			$options_names = "'". implode("','", $options_names) ."'";

			if ($just_count_or_show=='count') return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name IN ({$options_names})" );
			else if ($just_count_or_show=='show') {
				$resarr = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name IN ({$options_names})" );
				$txt = "";
				foreach ($resarr as $row) {
					foreach ($row as $key=>$fld) {
						if (str_ireplace(array("name","title"),"",$key) != $key || preg_match("#id$#i",$key))
							$txt .= $fld.", ";
					}
					$txt .= "\n------------------\n";
				}
				return $txt;
			}
			else $result = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN ({$options_names})" );
			if (!$result) {
				return false;
			}
		}
	}

	return 0;
}
if ($xtra_do_memlog) xtra_memlog("Functions");


//---ob_starts---------------------
if ( !is_admin() && !xtra_is_customize_preview() ) {
	function xtra_buffer_start() { ob_start("ob_start_callback"); }
	function xtra_buffer_end() { ob_end_flush(); }
	add_action('after_setup_theme', 'xtra_buffer_start');
	add_action('shutdown', 'xtra_buffer_end');
	function ob_start_callback($buffer) {
		if (is_admin()) return $buffer;
		if (xtra_is_customize_preview()) return $buffer;
		//spec order !!!
		if (get_optionXTRA( 'xtra_remove_double_title_meta', 0 )) 		$buffer = xtra_remove_double_title_meta($buffer);
		if (get_optionXTRA( 'xtra_img_alt', 0 )) 						$buffer = xtra_img_alt($buffer);
		if (get_optionXTRA( 'xtra_share_buttons', 0 )) 					$buffer = xtra_share_buttons($buffer);
		if (get_optionXTRA( 'xtra_share2_buttons', 0 )) 				$buffer = xtra_share2_buttons($buffer);
		if (get_optionXTRA( 'xtra_related_posts', 0 )) 					$buffer = xtra_related_posts($buffer);
		if (get_optionXTRA( 'xtra_minify_html', 0 )) 					$buffer = xtra_minify_html($buffer);

		// hide shortcodes if not used
		$plac1 = get_optionXTRA('xtra_share_buttons_place', 0);
		$plac2 = get_optionXTRA('xtra_share2_buttons_place', 0);
		$plac3 = get_optionXTRA('xtra_related_posts_place', 0);
		if ($plac1 != 5) $buffer = preg_replace('#\[xtra_share_buttons\]#umis', '', $buffer, 1);
		if ($plac2 != 5) $buffer = preg_replace('#\[xtra_share_buttons2\]#umis', '', $buffer, 1);
		if ($plac3 != 5) $buffer = preg_replace('#\[xtra_related_posts\]#umis', '', $buffer, 1);

		// wonderplugin-slider jsfolder correction
/*		if (stripos($buffer,'/engine/"')!==FALSE) $buffer = preg_replace(
			'# data-jsfolder=".*?/plugins/wonderplugin-slider(-lite)?/engine/" style="display:none#i',
			' style="display:none',
		$buffer);
*/
		return $buffer;
	}
}

if (get_optionXTRA( 'xtra_minify_html', 0 )) {
	function xtra_minify_html($buffer) {
		//$initial = strlen($buffer);

		$single = '{(\s|^)//(.*)$}';
		$buffer = preg_replace($single, '/* \\2 */', $buffer);

		//$block = '{/\*[\s\S]*?\*/}';
		$block = '{/\*\s*[^<\]]*?\*/}'; //except for /* <[CDATA and /* ]
		$buffer = preg_replace($block, '', $buffer);

		$search = array(
			'/\>[^\S ]+/s',  // strip whitespaces after tags, except space
			'/[^\S ]+\</s',  // strip whitespaces before tags, except space
			'/(\s)+/s'       // shorten multiple whitespace sequences
		);
		$replace = array(
			'>',
			'<',
			'\\1'
		);
		$buffer = preg_replace($search, $replace, $buffer);
		//$final = strlen($buffer);
		//$savings = round(($initial-$final)/$initial*100, 0);
		return $buffer;
	}
}



// IMG alt
if( get_optionXTRA('xtra_img_alt') ) {
	function xtra_img_alt( $content ) {
		preg_match('#<title>(.*?)</title>#i',$content,$tit);
		if (is_array($tit) && array_key_exists(1, $tit)) $title = $tit[1];
		if ( strpos($title, get_bloginfo('name'))===FALSE ) $title .= " | " . get_bloginfo('name');
		preg_match_all('/<img (.*?)\/>/i', $content, $images);
		if(!is_null($images)) {
			foreach($images[1] as $index => $value) {
				preg_match('/ alt=["\'](.*?)["\']/i', $value, $imgalt);
				if(!isset($imgalt[1]) || $imgalt[1] == '') {
					preg_match('/ title=["\'](.*?)["\']/i', $value, $imgtit);
					if(isset($imgtit[1]) && $imgtit[1] != '')
						$new_img = str_ireplace('<img', '<img alt="'.$imgtit[1].'"', $images[0][$index]);
					else
						$new_img = str_ireplace('<img', '<img alt="'.$title.'"', $images[0][$index]);
					//$new_img = str_ireplace('<img', '<img title="'.$title.'"', $new_img);
					$new_img = str_ireplace(array(' alt=""'," alt=''"), "", $new_img);
					$content = str_ireplace($images[0][$index], $new_img, $content);
				}
			}
		}
		return $content;
	}
	//add_filter( 'the_content', 'xtra_img_alt', 99 );
}


// Remove double title

if( get_optionXTRA('xtra_remove_double_title_meta') ) {
	function xtra_remove_double_title_meta($buffer) {

		$hed = substr($buffer,0,stripos($buffer,"</head>")+7);
		$buffer = str_replace($hed,'',$buffer);

			$hed = preg_replace( '#(\<title>.*?\</title>)(.*?)(\<title>.*?\</title>)#umis', '$1$2', $hed );

		return $hed.$buffer;
	}
}





if ($xtra_do_memlog) xtra_memlog("ob_start");

//---FILE: wp-config--------------

function xtra_wpconfig( $bool, $optName, $insertion, $remove="" ){
	$file_path = ABSPATH . 'wp-config.php';
	$insertion = xtra_mytrim($insertion);
	$regex_safe_insertion = str_replace(array(".","?","*","+","(",")"),array("\.","\?","\*","\+","\(","\)"),$insertion);
	$remove = '/' . substr($regex_safe_insertion,0,strpos($regex_safe_insertion,",")) . ".*?;" . '/s';
	$curr = xtra_extract_from_markers( $file_path, "XTRA Settings" );
	$currstr = implode("\n",$curr);
	if ($remove) $currstr = preg_replace($remove,"",$currstr); //remove remove
	$currstr = xtra_mytrim($currstr);
	$currarr = array();

	if ($bool) {
		if ($currstr) $currstr .= "\n";
		$currstr .= $insertion; //add insertion
	}
	if ($currstr) $currarr = explode("\n",$currstr);
	$status = xtra_insert_with_markers( $file_path, "XTRA Settings", $currarr );
	if ($status) update_optionXTRA( $optName, $bool);
	xtra_deactivate_names_add( $optName );
	return $status;
}

function xtra_insert_with_markers( $filename, $marker, $insertion ) {
    if ( ! file_exists( $filename ) ) {
        return false;
		/*
        if ( ! is_writable( dirname( $filename ) ) ) {
            return false;
        }
        if ( ! touch( $filename ) ) {
            return false;
        }
		*/
    } elseif ( ! is_writable( $filename ) ) {
        return false;
    }

    if ( ! is_array( $insertion ) ) {
        $insertion = explode( "\n", $insertion );
    }

    $start_marker = "// BEGIN {$marker}";
    $end_marker   = "// END {$marker}";

    $fp = fopen( $filename, 'r+' );
    if ( ! $fp ) {
        return false;
    }

    // Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
    flock( $fp, LOCK_EX );

    $lines = array();
    while ( ! feof( $fp ) ) {
        $lines[] = rtrim( fgets( $fp ), "\r\n" );
    }

	if ( !in_array($start_marker,$lines) ) { //ne elõre tegyük, hanem hátra!
		$ltxt = implode("\n",$lines);
		$xpos = strpos($ltxt,"<?php");
		if ($xpos!==false) {
			$xpos += 5;
			$ltxt2 = substr($ltxt,0,$xpos) . "\n\n" . $start_marker . "\n" . $end_marker . "\n\n" . substr($ltxt,$xpos);
			$lines = explode("\n",$ltxt2);
		}
	}

    // Split out the existing file into the preceding lines, and those that appear after the marker
    $pre_lines = $post_lines = $existing_lines = array();
    $found_marker = $found_end_marker = false;
    foreach ( $lines as $line ) {
        if ( ! $found_marker && false !== strpos( $line, $start_marker ) ) {
            $found_marker = true;
            continue;
        } elseif ( ! $found_end_marker && false !== strpos( $line, $end_marker ) ) {
            $found_end_marker = true;
            continue;
        }
        if ( ! $found_marker ) {
            $pre_lines[] = $line;
        } elseif ( $found_marker && $found_end_marker ) {
            $post_lines[] = $line;
        } else {
            $existing_lines[] = $line;
        }
    }

    // Check to see if there was a change
    if ( $existing_lines === $insertion ) {
        flock( $fp, LOCK_UN );
        fclose( $fp );

        return true;
    }

    // Generate the new file data
    $new_file_data = implode( "\n", array_merge(
        $pre_lines,
        array( $start_marker ),
        $insertion,
        array( $end_marker ),
        $post_lines
    ) );

    // Write to the start of the file, and truncate it to that length
    fseek( $fp, 0 );
    $bytes = fwrite( $fp, $new_file_data );
    if ( $bytes ) {
        ftruncate( $fp, ftell( $fp ) );
    }
    fflush( $fp );
    flock( $fp, LOCK_UN );
    fclose( $fp );

    return (bool) $bytes;
}

function xtra_extract_from_markers( $filename, $marker ) {
    $result = array ();

    if (!file_exists( $filename ) ) {
        return $result;
    }

    if ( $markerdata = explode( "\n", implode( '', file( $filename ) ) ));
    {
        $state = false;
        foreach ( $markerdata as $markerline ) {
            if (strpos($markerline, '// END ' . $marker) !== false)
                $state = false;
            if ( $state )
                $result[] = $markerline;
            if (strpos($markerline, '// BEGIN ' . $marker) !== false)
                $state = true;
        }
    }

    return $result;
}




//---FILE: htaccess--------------

function xtra_my_extract_from_markers( $filename, $marker ) {
	$result = array ();

	if (!file_exists( $filename ) ) {
		return $result;
	}

	if ( $markerdata = explode( "\n", implode( '', file( $filename ) ) ));
	{
		$state = false;
		foreach ( $markerdata as $markerline ) {
			if (strpos($markerline, '# END ' . $marker) !== false)
				$state = false;
			if ( $state )
				$result[] = $markerline;
			if (strpos($markerline, '# BEGIN ' . $marker) !== false)
				$state = true;
		}
	}

	return $result;
}

function xtra_insert_with_markers2( $filename, $marker, $insertion ) {
    if ( ! file_exists( $filename ) ) {
        if ( ! is_writable( dirname( $filename ) ) ) {
            return false;
        }
        if ( ! touch( $filename ) ) {
            return false;
        }
    } elseif ( ! is_writable( $filename ) ) {
        return false;
    }

    if ( ! is_array( $insertion ) ) {
        $insertion = explode( "\n", $insertion );
    }

    $start_marker = "# BEGIN {$marker}";
    $end_marker   = "# END {$marker}";

    $fp = fopen( $filename, 'r+' );
    if ( ! $fp ) {
        return false;
    }

    // Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
    flock( $fp, LOCK_EX );

    $lines = array();
    while ( ! feof( $fp ) ) {
        $lines[] = rtrim( fgets( $fp ), "\r\n" );
    }

	if ( !in_array($start_marker,$lines) ) { //ne elõre tegyük, hanem hátra!
		$ltxt = implode("\n",$lines);
		$xpos = strpos($ltxt,"<?php");
		if ($xpos!==false) {
			$xpos += 5;
			$ltxt2 = substr($ltxt,0,$xpos) . "\n\n" . $start_marker . "\n" . $end_marker . "\n\n" . substr($ltxt,$xpos);
			$lines = explode("\n",$ltxt2);
		}
	}

    // Split out the existing file into the preceding lines, and those that appear after the marker
    $pre_lines = $post_lines = $existing_lines = array();
    $found_marker = $found_end_marker = false;
    foreach ( $lines as $line ) {
        if ( ! $found_marker && false !== strpos( $line, $start_marker ) ) {
            $found_marker = true;
            continue;
        } elseif ( ! $found_end_marker && false !== strpos( $line, $end_marker ) ) {
            $found_end_marker = true;
            continue;
        }
        if ( ! $found_marker ) {
            $pre_lines[] = $line;
        } elseif ( $found_marker && $found_end_marker ) {
            $post_lines[] = $line;
        } else {
            $existing_lines[] = $line;
        }
    }

    // Check to see if there was a change
    if ( $existing_lines === $insertion ) {
        flock( $fp, LOCK_UN );
        fclose( $fp );

        return true;
    }

    // Generate the new file data
    $new_file_data = implode( "\n", array_merge(
        $pre_lines,
        array( $start_marker ),
        $insertion,
        array( $end_marker ),
        $post_lines
    ) );

    // Write to the start of the file, and truncate it to that length
    fseek( $fp, 0 );
    $bytes = fwrite( $fp, $new_file_data );
    if ( $bytes ) {
        ftruncate( $fp, ftell( $fp ) );
    }
    fflush( $fp );
    flock( $fp, LOCK_UN );
    fclose( $fp );

    return (bool) $bytes;
}

function xtra_htacc( $bool, $optName, $insertion ){
	if (!xtra_is_apache()) return true;
	$file_path = ABSPATH . '.htaccess';
	$insertion = xtra_mytrim($insertion);
	$regex_safe_insertion = str_replace(array(".","?","*","+","(",")"),array("\.","\?","\*","\+","\(","\)"),$insertion);
	$remove = "/" . substr($regex_safe_insertion,0,strpos($regex_safe_insertion,"\n")) . ".*?" . '#' . "/s";
//echo "<pre>".$remove."</pre>";
	//if ( !function_exists('extract_from_markers') ) include_once('includes/misc.php');
	$curr = xtra_my_extract_from_markers( $file_path, "XTRA Settings" );
	$currstr = implode("\n",$curr);
	if ($remove) $currstr = preg_replace($remove,"",$currstr); //remove remove
//echo "<pre>".$currstr."</pre><hr>";
	$currstr = xtra_mytrim($currstr);
	$currarr = array();

	if ($bool) {
		if ($currstr) $currstr .= "\n";
		$currstr .= $insertion; //add insertion
	}
	if ($currstr) $currarr = explode("\n",$currstr);
	//$status = insert_with_markers( $file_path, "XTRA Settings", $currarr );
	$status = xtra_insert_with_markers2( $file_path, "XTRA Settings", $currarr );
	if ($status) update_optionXTRA( $optName, $bool);
	xtra_deactivate_names_add( $optName );
	return $status;
}
function xtra_mytrim($str) {
	$str = str_replace("\r","",$str); //remove \r
	$str = str_replace("\t","",$str); //remove \t
	for($i=1;$i<=3;$i++) $str = str_replace("\n\n","\n",$str); //remove empty lines
	$str = trim($str);
	return $str;
}
function xtra_deactivate_names_add($name) {
	$names = get_optionXTRA( 'xtra_deactivate_names', array() );
	if (!is_array($names)) $names = array();
	if (!in_array($name,$names)) update_optionXTRA( 'xtra_deactivate_names', array_merge($names,array($name)) );
}

function xtra_correct_htacc() {
	if (!xtra_is_apache()) return true;
	$file_path = ABSPATH . '.htaccess';
	$curr = xtra_my_extract_from_markers( $file_path, "XTRA Settings" );
	$currstr = implode("\n",$curr);
	$currstr = str_replace(array(
"# The directives (lines) between `BEGIN XTRA Settings` and `END XTRA Settings` are",
"# dynamically generated, and should only be modified via WordPress filters.",
"# Any changes to the directives between these markers will be overwritten."
	),"",$currstr);
	$currstr = xtra_mytrim($currstr);
	$currarr = array();
	if ($currstr) $currarr = explode("\n",$currstr);
	$status = xtra_insert_with_markers2( $file_path, "XTRA Settings", $currarr );
}



if ($xtra_do_memlog) xtra_memlog("FILE functions");

//---1. Security-------


//---Apache Server Hardening

function xtra_dir_index_disable( $bool ){
	$opt = "xtra_dir_index_disable";
	$ins = '#---Disable Apache Dir-Index pages
Options -Indexes
#';
	return xtra_htacc($bool,$opt,$ins);
}

function xtra_response_header_hardening( $bool ){
	$opt = "xtra_response_header_hardening";
	$ins = '#---Response Header Hardening
<IfModule mod_headers.c>
Header set X-XSS-Protection "1; mode=block"
Header always append X-Frame-Options SAMEORIGIN
Header set X-Content-Type-Options nosniff
</IfModule>
#';
	return xtra_htacc($bool,$opt,$ins);
}

// deprecated as of 1.4.7 - merged into xtra_response_header_hardening
function xtra_protect_xss( $bool ){
//Protect against XSS attacks
	$opt = "xtra_protect_xss";
	$ins = '#---Protect against XSS
<IfModule mod_headers.c>
Header set X-XSS-Protection "1; mode=block"
</IfModule>
#';
	return xtra_htacc($bool,$opt,$ins);
}

// deprecated as of 1.4.7 - merged into xtra_response_header_hardening
function xtra_protect_pageframing( $bool ){
//Protect against page-framing and click-jacking
	$opt = "xtra_protect_pageframing";
	$ins = '#---Protect against page-framing and click-jacking
<IfModule mod_headers.c>
Header always append X-Frame-Options SAMEORIGIN
</IfModule>
#';
	return xtra_htacc($bool,$opt,$ins);
}

// deprecated as of 1.4.7 - merged into xtra_response_header_hardening
function xtra_protect_cotentsniffing( $bool ){
//Protect against content-sniffing
	$opt = "xtra_protect_cotentsniffing";
	$ins = '#---Protect against content-sniffing
<IfModule mod_headers.c>
Header set X-Content-Type-Options nosniff
</IfModule>
#';
	return xtra_htacc($bool,$opt,$ins);
}

// deprecated as of 1.5.3 - changed into xtra_block_external_post2
function xtra_block_external_post( $bool ){
//Block external POST
	$opt = "xtra_block_external_post";
	$ins = '#---Block external POST
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_METHOD} POST
RewriteCond %{REQUEST_URI} (wp-comments-post|wp-login)\.php [NC]
RewriteCond %{HTTP_REFERER} !(.*)'.str_ireplace(array("http://","https://"),"",site_url()).' [NC,OR]
RewriteCond %{HTTP_USER_AGENT} ^$
RewriteRule .* - [L]
</IfModule>
#';
	return xtra_htacc($bool,$opt,$ins);
}

// deprecated as of 1.5.3 - changed into xtra_redir_bots2
function xtra_redir_bots( $bool ){
	$opt = "xtra_redir_bots";
	//if ($bool) $txt = stripslashes_deep(sanitize_text_field($_POST[$opt.'_text']));
	if ($bool) $txt = "%{HTTP_USER_AGENT} rv:40\.0.*Firefox\/40\.1 [NC]";
	$ins = '#---Redirect bots to referring URL
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{REQUEST_URI} wp-login\.php [NC,OR]
RewriteCond %{REQUEST_URI} xmlrpc\.php [NC]
RewriteCond '.$txt.'
RewriteRule (.*) http://%{REMOTE_ADDR}/ [R=301,L]
</IfModule>
#';
	return xtra_htacc($bool,$opt,$ins);
}

//. xtra_block_external_post2
if (get_optionXTRA( 'xtra_block_external_post2', 0 )) {
	$href = $_SERVER['HTTP_REFERER'];
	$myhost1 = str_ireplace( array("http://","https://"), "", site_url() );
	$myhost2 = str_ireplace( array("http://","https://"), "", home_url() );
	$usag = $_SERVER['HTTP_USER_AGENT'];
	$tpage = basename( $_SERVER['REQUEST_URI'] );
	if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
		if ( !xtra_instr($href,array($myhost1,$myhost2)) || $usag == "" ) {
			if ( preg_match( "#(wp-login|wp-comments-post)\.php#i", $tpage ) ) {
				@header("Location: http://".$_SERVER['REMOTE_ADDR']."/", TRUE, 301);
				@exit();
			}
		}
	}
}

//. xtra_redir_bots2 - REMOTE_
if (get_optionXTRA( 'xtra_redir_bots2', 0 )) {
	global $rem_serv;
	$usag = $_SERVER['HTTP_USER_AGENT'];
	$chstr = $_SERVER['HTTP_USER_AGENT']." ".$_SERVER['REMOTE_ADDR'];
	if ( isset($_SERVER['REMOTE_HOST']) ) $chstr .= " ".$_SERVER['REMOTE_HOST'];
	$blocklist = get_optionXTRA('xtra_redir_bots2_texta',"rv:40\.0.*Firefox\/40\.1\npopeofslope.net");
	$blockarr = explode("\n",$blocklist);
//xtra_mylog($_SERVER);
	foreach((array)$blockarr as $block) {
		if (!$block) continue;
		if (!$chstr) continue;
		if ( xtra_instr( $_SERVER['REQUEST_URI'], 'admin.php' ) ) continue; //to avoid suicide
		if ( preg_match( "#".$block."#i", $chstr ) ) {
			if ( !file_exists( XTRA_PLUGIN_DIR . 'block_visitors.php' ) ) break;
				@header("Location: http://".$_SERVER['REMOTE_ADDR']."/", TRUE, 301);
				@exit();
		}
		if (get_optionXTRA('xtra_redir_lookup_hostnames',0)) {
			if (!isset($rem_serv)) {
				$thisIP = $_SERVER['REMOTE_ADDR'];
				if (!isset($IPdata)) $IPdata = get_optionXTRA('xtra_hits_IPdata',array());
				if (@$IPdata[$thisIP]['host']) $rem_serv = $IPdata[$thisIP]['host'];
			}
			if (!isset($rem_serv)) {
				$time_start = microtime(true);
				$rem_serv = @gethostbyaddr($_SERVER['REMOTE_ADDR']); //maybe slow
				xtra_time_measure('xtra_hostlookup_avg',$time_start);
			}
		}
		if (!isset($rem_serv)) continue;
		if ( preg_match( "#".$block."#i", $rem_serv ) ) {
			if ( !file_exists( XTRA_PLUGIN_DIR . 'block_visitors.php' ) ) break;
				@header("Location: http://".$_SERVER['REMOTE_ADDR']."/", TRUE, 301);
				@exit();
		}
	}
}

//. xtra_redir_bots3 - REQUEST_URI
if (get_optionXTRA( 'xtra_redir_bots3', 0 )) {
	$chstr = $_SERVER['REQUEST_URI'];
	$blocklist = get_optionXTRA('xtra_redir_bots3_texta',"\/\/(wp-login|xmlrpc|wp-comments-post)\.php)");
	$blockarr = explode("\n",$blocklist);
	foreach((array)$blockarr as $block) {
		if (!$block) continue;
		if (!$chstr) continue;
		if ( xtra_instr( $_SERVER['REQUEST_URI'], 'admin.php' ) ) continue; //to avoid suicide
		if ( preg_match( "#".$block."#i", $chstr ) ) {
			if ( !file_exists( XTRA_PLUGIN_DIR . 'block_visitors.php' ) ) break;
				@header("Location: http://".$_SERVER['REMOTE_ADDR']."/", TRUE, 301);
				@exit();
		}
	}
}

//---WP Security settings

//Remove WordPress Version Number
if (get_optionXTRA( 'xtra_remove_version_number', 0 )) {
	function xtra_remove_version() {
	return '';
	}
	add_filter('the_generator', 'xtra_remove_version');
	remove_action('wp_head', 'wp_generator');
}

//. Don’t display login errors
if (get_optionXTRA( 'xtra_suppress_login_errors', 0 )) {
	//add_filter('login_errors',create_function('$a', "return null;"));
	add_filter('login_errors',function ( $a ) {return null;});
}

//Disable RSS Feeds
if (get_optionXTRA( 'xtra_fb_disable_feed', 0 )) {
	function xtra_fb_disable_feed() {
		wp_die( 'No feed available, please visit our <a href="'. get_bloginfo('url') .'">homepage</a>!' );
	}
	add_action('do_feed', 'xtra_fb_disable_feed', 1);
	add_action('do_feed_rdf', 'xtra_fb_disable_feed', 1);
	add_action('do_feed_rss', 'xtra_fb_disable_feed', 1);
	add_action('do_feed_rss2', 'xtra_fb_disable_feed', 1);
	add_action('do_feed_atom', 'xtra_fb_disable_feed', 1);
}

//. Disable XML-RPC
$xtra_xmldis_enable = get_optionXTRA( 'xtra_disable_xmlrpc', 0 );
if ($xtra_xmldis_enable) add_filter('xmlrpc_enabled', '__return_false');

//. require authentication for REST API requests
if (get_optionXTRA( 'xtra_rest_must_loggedin', 0 )) {
	add_filter( 'rest_authentication_errors', function( $result ) {
		if ( ! empty( $result ) ) {
			return $result;
		}
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array( 'status' => 401 ) );
		}
		return $result;
	});
}

//. Protect From Malicious URL Requests
if (get_optionXTRA( 'xtra_protect_from_bad_requests', 0 )) {
	//if (strlen($_SERVER['REQUEST_URI']) > 255 ||
	if (stripos($_SERVER['REQUEST_URI'], "eval(") ||
	stripos($_SERVER['REQUEST_URI'], "exec(") ||
	stripos($_SERVER['REQUEST_URI'], "CONCAT") ||
	stripos($_SERVER['REQUEST_URI'], "UNION+SELECT") ||
	stripos($_SERVER['HTTP_USER_AGENT'], "libwww")!==FALSE ||
	stripos($_SERVER['HTTP_USER_AGENT'], "Wget")!==FALSE ||
	stripos($_SERVER['HTTP_USER_AGENT'], "EmailSiphon")!==FALSE ||
	stripos($_SERVER['HTTP_USER_AGENT'], "EmailWolf")!==FALSE ||
	stripos($_SERVER['REQUEST_URI'], "base64_")) {
		@header("HTTP/1.1 414 Request-URI Too Long");
		@header("Status: 414 Request-URI Too Long");
		@header("Connection: Close");
		@exit;
	}
}





if ($xtra_do_memlog) xtra_memlog("Security");

//---2. Speed-------

//---Apache Compression

function xtra_deflates( $bool ){
	$opt = "xtra_deflates";
	$ins = '#---Enable deflates
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/plain
AddOutputFilterByType DEFLATE text/html
AddOutputFilterByType DEFLATE text/xml
AddOutputFilterByType DEFLATE text/x-component
AddOutputFilterByType DEFLATE text/css
AddOutputFilterByType DEFLATE text/javascript
AddOutputFilterByType DEFLATE application/javascript
AddOutputFilterByType DEFLATE application/x-javascript
AddOutputFilterByType DEFLATE application/xml
AddOutputFilterByType DEFLATE application/xhtml+xml
AddOutputFilterByType DEFLATE application/rss+xml
AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
AddOutputFilterByType DEFLATE application/font-opentype
AddOutputFilterByType DEFLATE application/font-truetype
AddOutputFilterByType DEFLATE application/font-otf
AddOutputFilterByType DEFLATE application/font-ttf
AddOutputFilterByType DEFLATE application/x-font-opentype
AddOutputFilterByType DEFLATE application/x-font-truetype
AddOutputFilterByType DEFLATE application/x-font-otf
AddOutputFilterByType DEFLATE application/x-font-ttf
AddOutputFilterByType DEFLATE font/opentype
AddOutputFilterByType DEFLATE font/truetype
AddOutputFilterByType DEFLATE font/otf
AddOutputFilterByType DEFLATE font/ttf
AddOutputFilterByType DEFLATE image/svg+xml
AddOutputFilterByType DEFLATE image/x-icon
AddOutputFilter       DEFLATE woff woff2
BrowserMatch ^Mozilla/4 gzip-only-text/html
BrowserMatch ^Mozilla/4\.0[678] no-gzip
BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
Header append Vary User-Agent
</IfModule>
#';
	xtra_browserbugs(0);
	return xtra_htacc($bool,$opt,$ins);
}

// deprecated as of 1.4.4 - merged into xtra_deflates
function xtra_browserbugs( $bool ){
$bool = false;
	$opt = "xtra_browserbugs";
	$ins = '#---Remove browser bugs (only needed for really old browsers)
<IfModule mod_deflate.c>
BrowserMatch ^Mozilla/4 gzip-only-text/html
BrowserMatch ^Mozilla/4\.0[678] no-gzip
BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
Header append Vary User-Agent
</IfModule>
#';
	return xtra_htacc($bool,$opt,$ins);
}

function xtra_image_expiry( $bool ){
	$opt = "xtra_image_expiry";
	$days = get_optionXTRA('xtra_image_expiry_num',30);
	$ins = '#---Image Expiration
<IfModule mod_expires.c>
ExpiresActive On
ExpiresDefault A0
ExpiresByType image/gif "access plus '.$days.' days"
ExpiresByType image/png "access plus '.$days.' days"
ExpiresByType image/jpg "access plus '.$days.' days"
ExpiresByType image/jpeg "access plus '.$days.' days"
ExpiresByType image/ico "access plus '.$days.' days"
ExpiresByType image/x-icon "access plus '.$days.' days"
ExpiresByType image/svg+xml "access plus '.$days.' days"
ExpiresByType text/css "access plus '.$days.' days"
ExpiresByType text/javascript "access plus '.$days.' days"
ExpiresByType application/javascript "access plus '.$days.' days"
ExpiresByType application/x-javascript "access plus '.$days.' days"
ExpiresByType font/truetype "access plus '.$days.' days"
ExpiresByType font/opentype "access plus '.$days.' days"
ExpiresByType application/x-font-woff "access plus '.$days.' days"
ExpiresByType application/vnd.ms-fontobject "access plus '.$days.' days"
<filesMatch "\.(jpg|jpeg|png|gif|js|css|swf|ico|ttf|otf|eot|svg|woff|woff2)$">
    ExpiresActive on
    ExpiresDefault "access plus '.$days.' days"
</filesMatch>
</IfModule>
<IfModule mod_headers.c>
<filesMatch "\.(ico|jpe?g|png|gif|swf)$">
Header set Cache-Control "public"
</filesMatch>
<filesMatch "\.(css)$">
Header set Cache-Control "public"
</filesMatch>
<filesMatch "\.(woff|woff2)$">
Header set Cache-Control "public"
</filesMatch>
<filesMatch "\.(js)$">
Header set Cache-Control "private"
</filesMatch>
<filesMatch "\.(x?html?|php)$">
Header set Cache-Control "private, must-revalidate"
</filesMatch>
</IfModule>
#';
	return xtra_htacc($bool,$opt,$ins);
}

function xtra_remove_etags( $bool ){
	$opt = "xtra_remove_etags";
	$ins = '#---Remove ETags
FileETag none
#';
	return xtra_htacc($bool,$opt,$ins);
}


// deprecated as of 1.7.5
function xtra_WPcache( $bool ){
	$opt = "xtra_WPcache";
	$ins = "define('WP_CACHE', ".(($bool)?"true":"false").");";
	return xtra_wpconfig($bool,$opt,$ins);
}

if (get_optionXTRA( 'xtra_remove_query_strings', 0 )) {
	if(!is_admin() && !xtra_is_customize_preview()) {
		function xtra_remove_script_version( $src ){
			if (!xtra_instr($src,array('.css','.js'))) return $src;
			$parts = explode( '?', $src );
			if (get_optionXTRA( 'xtra_remove_query_strings_plus', 0 ) && get_optionXTRA( 'xtra_debug', 0 ))
				return $parts[0]."?ver=".time();
			return $parts[0];
		}
		add_filter( 'script_loader_src', 'xtra_remove_script_version', 99, 1 );
		add_filter( 'style_loader_src', 'xtra_remove_script_version', 99, 1 );
	}
}


//---Memory and PHP Exec

//. xtra_memory_limit
if (get_optionXTRA( 'xtra_memory_limit', 0 )) {
	$num = get_optionXTRA( 'xtra_memory_limit_num', 128 );
	@ini_set( 'memory_limit' , $num.'M' );
	if (!defined('WP_MEMORY_LIMIT')) define('WP_MEMORY_LIMIT', $num.'M');
	if (!defined('WP_MAX_MEMORY_LIMIT')) define('WP_MAX_MEMORY_LIMIT', $num.'M');
}

//. xtra_upload_max_filesize
if (get_optionXTRA( 'xtra_upload_max_filesize', 0 )) {
	$num = get_optionXTRA( 'xtra_upload_max_filesize_num', 32 );
	@ini_set( 'upload_max_filesize' , $num.'M' );
	@ini_set( 'upload_max_size' , $num.'M' );
	@ini_set( 'post_max_size', $num.'M');
}

//. xtra_max_execution_time
if (get_optionXTRA( 'xtra_max_execution_time', 0 )) {
	$num = get_optionXTRA( 'xtra_max_execution_time_num', 60 );
	@ini_set( 'max_execution_time', $num );
}



if ($xtra_do_memlog) xtra_memlog("Speed");

//---3. SEO---
function basic_wp_seo($desc_add=1,$keyword_add=1,$robots_add=1,$title_add=1) {
	global $page, $paged, $post;
	//$default_keywords = 'wordpress, plugins, themes, design, dev, development, security, htaccess, apache, php, sql, html, css, jquery, javascript, tutorials'; // customize
	$default_keywords = get_optionXTRA('xtra_seo_default_keywords','');
	$output = '';

	// description
	$seo_desc = get_post_meta($post->ID, 'mm_seo_desc', true);
	$description = get_bloginfo('description', 'display');
	$pagedata = get_post($post->ID);
	if (is_singular()) {
		if (!empty($seo_desc)) {
			$content = $seo_desc;
		} else if (!empty($pagedata)) {
			$content = apply_filters('the_excerpt_rss', $pagedata->post_content);
			$content = substr(trim(strip_tags($content)), 0, 155);
			$content = preg_replace('#\n#', ' ', $content);
			$content = preg_replace('#\s{2,}#', ' ', $content);
			$content = trim($content);
		}
	} else {
		$content = $description;
	}
	if ($desc_add) $output .= '<meta name="description" content="' . esc_attr($content) . '">' . "\n";

	// keywords
	$keys = get_post_meta($post->ID, 'mm_seo_keywords', true);
	$cats = get_the_category();
	$tags = get_the_tags();
	if (empty($keys)) {
		if (!empty($cats)) foreach($cats as $cat) $keys .= $cat->name . ', ';
		if (!empty($tags)) foreach($tags as $tag) $keys .= $tag->name . ', ';
		$keys .= $default_keywords;
	}
	if ($keyword_add) $output .= "\t\t" . '<meta name="keywords" content="' . esc_attr($keys) . '">' . "\n";

	// robots
	if ($robots_add) {
		if (is_category() || is_tag()) {
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			if ($paged > 1) {
				$output .=  "\t\t" . '<meta name="robots" content="noindex,follow">' . "\n";
			} else {
				$output .=  "\t\t" . '<meta name="robots" content="index,follow">' . "\n";
			}
		} else if (is_home() || is_singular()) {
			$output .=  "\t\t" . '<meta name="robots" content="index,follow">' . "\n";
		} else {
			$output .= "\t\t" . '<meta name="robots" content="noindex,follow">' . "\n";
		}
	}

	// title
	$title_custom = get_post_meta($post->ID, 'mm_seo_title', true);
	$url = ltrim(esc_url($_SERVER['REQUEST_URI']), '/');
	$name = get_bloginfo('name', 'display');
	$title = trim(wp_title('', false));
	$cat = single_cat_title('', false);
	$tag = single_tag_title('', false);
	$search = get_search_query();

	if (!empty($title_custom)) $title = $title_custom;
	if ($paged >= 2 || $page >= 2) $page_number = ' | ' . sprintf('Page %s', max($paged, $page));
	else $page_number = '';

	if (is_home() || is_front_page()) $seo_title = $name . ' | ' . $description;
	elseif (is_singular())            $seo_title = $title . ' | ' . $name;
	elseif (is_tag())                 $seo_title = 'Tag Archive: ' . $tag . ' | ' . $name;
	elseif (is_category())            $seo_title = 'Category Archive: ' . $cat . ' | ' . $name;
	elseif (is_archive())             $seo_title = 'Archive: ' . $title . ' | ' . $name;
	elseif (is_search())              $seo_title = 'Search: ' . $search . ' | ' . $name;
	elseif (is_404())                 $seo_title = '404 - Not Found: ' . $url . ' | ' . $name;
	else                              $seo_title = $name . ' | ' . $description;

	if ($title_add) $output .= "\t\t" . '<title>' . esc_attr($seo_title . $page_number) . '</title>' . "\n";

	return $output;
}

// Add description meta tag in posts/pages
if( get_optionXTRA('xtra_meta_description') ) {
	function xtra_meta_description() {
		if (is_admin()) return;
		if (xtra_is_customize_preview()) return;
		global $post;

		//$exc = xtra_get_excerpt();
		//if (!$exc) $exc = get_bloginfo('name')." - ".get_bloginfo('description');
		$exc = get_bloginfo('name')." ".xtra_get_excerpt()." ".get_bloginfo('description');
		$exc = htmlspecialchars_decode($exc);
		$exc = mb_substr($exc, 0, 125, 'UTF-8');

		echo '<meta name="description" content="'.$exc.'" />'."\r\n";
	}
	add_action( 'wp_head', 'xtra_meta_description' );
}

// Add keyword meta tag in posts/pages
if( get_optionXTRA('xtra_meta_keywords') ) {
	function xtra_meta_keywords() {
		if (is_admin()) return;
		if (xtra_is_customize_preview()) return;
		global $post;

		$tls = xtra_get_taglist(); // post tags
		$tlsa = explode(", ",$tls);

		$cats = get_the_category(); // post categories
		$tlsa2 = array();
		if (!empty($cats)) foreach($cats as $cat) $tlsa2[] = $cat->name;
		$tlsa = array_merge((array)$tlsa2,(array)$tlsa);

		$tls = implode(", ", (array)$tlsa);
		if (count((array)$tlsa) < 10) {
			//$tlsa = array();
			$exc = get_bloginfo('name')." ".xtra_get_excerpt()." ".get_bloginfo('description');
			$exc = htmlspecialchars_decode($exc);
			foreach( explode(' ',$exc ) as $word) {
				if ( preg_match('/([&,\.-]|\&\w+;)/',$word) ) continue;
				if ( !in_array($word,(array)$tlsa) ) $tlsa[] = $word;
				if (count((array)$tlsa) >= 15) break;
			}
			$tls = implode(", ", $tlsa);
		}
		$tls = htmlspecialchars_decode($tls);
		$tls = str_replace(", ",",",$tls);

		echo '<meta name="keywords" content="'.$tls.'" />'."\r\n";
	}
	add_action( 'wp_head', 'xtra_meta_keywords' );
}

// Add robots meta tag in posts/pages
if( get_optionXTRA('xtra_meta_robots') ) {
	function xtra_meta_robots() {
		if (is_admin()) return;
		if (xtra_is_customize_preview()) return;
		echo basic_wp_seo(0,0,1,0);
	}
	add_action( 'wp_head', 'xtra_meta_robots' );
}

//Redirect 404 to Home
if (get_optionXTRA( 'xtra_WPTime_redirect_404_to_homepage', 0 )) {
	function xtra_WPTime_redirect_404_to_homepage(){
		if( is_404() ){
			wp_redirect( get_bloginfo('url'), 301 );
			exit();
		}
	}
	add_action('template_redirect', 'xtra_WPTime_redirect_404_to_homepage');
}

//Redirect Attachments to Post
if (get_optionXTRA( 'xtra_attachment_redirect_to_post', 0 )) {
	function xtra_attachment_redirect_to_post(){
		global $post;
		if ( is_attachment() && isset($post->post_parent) && is_numeric($post->post_parent) && ($post->post_parent != 0) ) {
			wp_redirect( get_permalink( $post->post_parent ), 301 );
			exit();
			wp_reset_postdata();
		}
	}
	add_action('template_redirect', 'xtra_attachment_redirect_to_post');
}

// REL External
if( get_optionXTRA('xtra_rel_external') ) {
	function xtra_rel_external( $content ) {
		$content = str_replace('target="_self"', '', $content);
		$content = str_replace('target="_blank"', 'rel="external" target="_blank"', $content);
		return $content;
	}
	add_filter( 'the_content', 'xtra_rel_external' );
}

function xtra_is_customize_preview() {
	return xtra_instr($_SERVER['REQUEST_URI'], array('?customize_changeset_uuid','/wp-admin/customize.php'));
}

// Defer js parsing
if (get_optionXTRA( 'xtra_defer_parsing_of_js', 0 )) {
	if (!is_admin() && !xtra_is_customize_preview()) {
		function xtra_defer_parsing_of_js ( $url ) {
			if ( !xtra_instr( $url, '.js' ) ) return $url;
			// Exceptions
				if ( xtra_instr( $url, 'jquery.js' ) ) return $url;
				if ( preg_match( '#mapsvg(\.min)?\.js#i', $url ) ) return $url;
			return "$url' defer onload='";
		}
		add_filter( 'clean_url', 'xtra_defer_parsing_of_js', 11, 1 );
	}
}

// Move all JS from header to footer
if (get_optionXTRA( 'xtra_move_all_js_to_footer', 0 )) {
	if(!is_admin() && !xtra_is_customize_preview()) {
		function xtra_move_all_js_to_footer() {
			remove_action('wp_head', 'wp_print_scripts');
			remove_action('wp_head', 'wp_print_head_scripts', 9);
			remove_action('wp_head', 'wp_enqueue_scripts', 1);
			add_action('wp_footer', 'wp_print_scripts', 5);
			add_action('wp_footer', 'wp_enqueue_scripts', 5);
			add_action('wp_footer', 'wp_print_head_scripts', 5);
		}
		add_action('wp_enqueue_scripts', 'xtra_move_all_js_to_footer');
	}
}



if ($xtra_do_memlog) xtra_memlog("SEO");

//---4. Social---

if( !is_admin() && !xtra_is_customize_preview() && get_optionXTRA('xtra_facebook_og_metas') ) {
	function xtra_facebook_og_metas() {
		global $post;
		if( ( is_single() || is_page() ) && !is_front_page() ) {
			?>
<meta property="og:type" content="article">
<meta property="og:site_name" content="<?php echo get_bloginfo('name'); ?>">
<link property="og:url" href="<?php echo get_permalink(); ?>">
<meta property="og:title" content="<?php single_post_title(''); ?>">
<meta property="og:description" content="<?php echo xtra_get_excerpt(); ?>">
<meta property="og:image" content="<?php echo xtra_find_img_src(); ?>">
			<?php
		}
		else {
			?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?php echo get_bloginfo('name'); ?>">
<meta property="og:url" content="<?php echo home_url(); ?>">
<meta property="og:title" content="<?php get_bloginfo('name'); ?>">
<meta property="og:description" content="<?php echo mb_substr(get_bloginfo('description'),0,125,'UTF-8'); ?>">
<meta property="og:image" content="<?php echo xtra_find_img_src(); ?>">
			<?php
		}
	}
	add_action('wp_head', 'xtra_facebook_og_metas');
}

if( !is_admin() && !xtra_is_customize_preview() && get_optionXTRA('xtra_twitter_metas') ) {
	function xtra_twitter_metas() {
		global $post;
		if( ( is_single() || is_page() ) && !is_front_page() ) {
			?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php single_post_title(''); ?>">
<meta name="twitter:description" content="<?php echo xtra_get_excerpt(); ?>">
<meta name="twitter:site" content="@<?php echo get_optionXTRA('xtra_twitter_metas_text'); ?>">
<meta name="twitter:image" content="<?php echo xtra_find_img_src(); ?>">
			<?php
		}
		else {
			?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php get_bloginfo('name'); ?>">
<meta name="twitter:description" content="<?php echo mb_substr(get_bloginfo('description'),0,125,'UTF-8'); ?>">
<meta name="twitter:site" content="@<?php echo get_optionXTRA('xtra_twitter_metas_text'); ?>">
<meta name="twitter:image" content="<?php echo xtra_find_img_src(); ?>">
			<?php
		}
	}
	add_action('wp_head', 'xtra_twitter_metas');
}

if( !is_admin() && !xtra_is_customize_preview() && get_optionXTRA('xtra_facebook_sdk') ) {
	function xtra_facebook_sdk() {
		$lang = get_locale();
		if (!$lang) $lang = "en_US";
		if( ( is_single() || is_page() ) && !is_front_page() ) {
			?>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php echo $lang; ?>/sdk.js#xfbml=1&version=v2.6";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
			<?php
		}
	}
	//add_action('wp_head', 'xtra_facebook_sdk');
	add_action('wp_footer', 'xtra_facebook_sdk');
	function xtra_facebook_sdk2() {
		if( ( is_single() || is_page() ) && !is_front_page() ) {
		//if( is_singular() && !is_front_page() ) {
			?>
			<div id="fb-root"></div>
			<?php
		}
	}
	//add_action('the_post', 'xtra_facebook_sdk2');
	add_action('wp_footer', 'xtra_facebook_sdk2');
}




//---Share buttons common---
function xtra_share_buttons_scale($zoom) {
	return 'transform: scale('.round($zoom/100,1).');-ms-transform: scale('.round($zoom/100,1).');-webkit-transform: scale('.round($zoom/100,1).');-o-transform: scale('.round($zoom/100,1).');-moz-transform: scale('.round($zoom/100,1).');';
}
function xtra_share_buttons_transform_origin($top_left) {
	return 'transform-origin: '.$top_left.';-ms-transform-origin: '.$top_left.';-webkit-transform-origin: '.$top_left.';-moz-transform-origin: '.$top_left.';-o-transform-origin: '.$top_left.';';
}


//---Share buttons---
if( get_optionXTRA('xtra_share_buttons') ) {
	function xtra_add_social_share_icons_css() {
		wp_enqueue_style( 'xtra_social_share_buttons_css', plugin_dir_url( __FILE__ ) . 'assets/css/social-share-buttons.css' );
	}
	add_action( 'wp_enqueue_scripts', 'xtra_add_social_share_icons_css' );
	function xtra_stylesheet_installed($array_css)
	{
		global $wp_styles;
		foreach( $wp_styles->queue as $style )
		{
			foreach ($array_css as $css)
			{
				if (false !== strpos( $wp_styles->registered[$style]->src, $css ))
					return 1;
			}
		}
		return 0;
	}
	function xtra_add_fa_css(){
		global $xtra_fa;
		$font_awesome = array('font-awesome', 'fontawesome');
		$xtra_fa = false;
		if (xtra_stylesheet_installed($font_awesome) === 0) {
			 wp_enqueue_style('font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		}
		if (xtra_stylesheet_installed($font_awesome) !== 0)
			$xtra_fa = true;
	}
	add_action( 'wp_enqueue_scripts', 'xtra_add_fa_css', 99 );

	function xtra_get_social_share_icons() {
		global $xtra_fa;
		global $post;
		$postcat = get_the_category( $post->ID ); 	// Geting the current post's category lists if exists.
		$category_ids = $postcat[0]->term_id;	// getting cat ids
		$dontshow_cats = get_optionXTRA('xtra_share_cat_exclude', array());	// dont show on these categories
		foreach( (array)$category_ids as $tcatid ) if ( in_array($tcatid,$dontshow_cats) ) return;	// filter

		$tit = get_optionXTRA('xtra_share_buttons_text', 'Share on:');
		if ( function_exists('pll__') ) $tit = utf8_decode(pll__($tit));
		$inl = get_optionXTRA('xtra_share_buttons_cbx', 0);
		$ttag = get_optionXTRA('xtra_share_buttons_pnum', 0);
		$tsty = "";
		if ($inl) $tsty = ' style="display:inline-block;margin-right: 0.3em;"';
		$zoom = get_optionXTRA('xtra_share_buttons_num', 100);
		$spac = get_optionXTRA('xtra_share_buttons_num2', 4);
		$shape = get_optionXTRA('xtra_share_buttons_shape', 0);
		if ($shape==0) $sty = "border-radius: 0px;";
		if ($shape==1) $sty = "border-radius: ".(5/(16+2) *$zoom/100)."em;";
		if ($shape==2) $sty = "border-radius: 50%;";
		if ($shape==3) $sty = "border-radius: 0px; width: ".(3.3 *$zoom/100)."em;";
		if ($shape==4) $sty = "border-radius: ".(4/(16+2) *$zoom/100)."em; width: ".(3.3 *$zoom/100)."em;";

		$html = "<div class='xtra-social-share-wrap'>";
		if ($tit) $html .= "<$ttag$tsty>$tit</$ttag>";
		global $post;
		$url = get_permalink($post->ID);
		$url = esc_url($url);
		$shares = array(
			'facebook'		=>	array('Facebook',	'fa-facebook',		'http://www.facebook.com/sharer.php?u='.$url),
			'twitter'		=>	array('Twitter',	'fa-twitter',		'https://twitter.com/share?url='.$url),
			'linkedin'		=>	array('LinkedIn',	'fa-linkedin',		'http://www.linkedin.com/shareArticle?url='.$url),
			'pinterest'		=>	array('Pinterest',	'fa-pinterest',		"javascript:void((function()%7Bvar%20e=document.createElement(\"script\");e.setAttribute(\"type\",\"text/javascript\");e.setAttribute(\"charset\",\"UTF-8\");e.setAttribute(\"src\",\"//assets.pinterest.com/js/pinmarklet.js?r=\"+Math.random()*99999999);document.body.appendChild(e)%7D)());"),
			'tumblr'		=>	array('tumblr',		'fa-tumblr',		'http://www.tumblr.com/share/link?url='.$url),
			'gplus'			=>	array('Google+',	'fa-google-plus',	'https://plus.google.com/share?url='.$url),
			'reddit'		=>	array('Reddit',		'fa-reddit',		'http://reddit.com/submit?url='.$url),
			'buffer'		=>	array('Buffer',		'fa-share-alt',		'https://bufferapp.com/add?url='.$url),
		);
		foreach ($shares as $slg=>$set) {
			if (get_optionXTRA('xtra_share_'.$slg)) {
				/*
				$html .= "
					<a target='_blank' title='".$set[0]."' href='".$set[2]."'>
						<div class='".$slg."' style='zoom:".$zoom."% !important;".$sty."'>
							<span>".($xtra_fa?"<i style='zoom:101% !important;' class='fa ".$set[1]."'></i>":$set[0])."</span>
						</div>
					</a>";
					*/
				$html .= "
					<a target='_blank' title='".$set[0]."' href='".$set[2]."'>
					<div class='".$slg."' style='
						width:".(1.188 *$zoom/100)."em; 
						height:".(1.188 *$zoom/100)."em; 
						padding:".(5/(16+2) *$zoom/100)."em; 
						margin-right:".($spac/16 *$zoom/100)."em; 
						margin-bottom:".($spac/16 *$zoom/100)."em;
						line-height:".(1.15 *$zoom/100)."em;
						vertical-align: middle;
						".$sty."'>
					<span>".($xtra_fa?"<i style='
							font-size:".(1.0 *$zoom/100)."em !important;
							line-height:1px;
							vertical-align: middle;
							' class='fa ".$set[1]."'></i>":$set[0])."
					</span>
					</div>
					</a>
					";
			}
		}
		$fbst = 'margin-right:0px;vertical-align:top;text-align: left;';
		$fbst .= xtra_share_buttons_scale($zoom);
		$fbst .= xtra_share_buttons_transform_origin("top left");
		if (get_optionXTRA('xtra_share_facelike')) $html .= '<div style="'.$fbst.'" class="fb-like" data-href="'.$url.'" data-width="200" data-layout="button_count" data-action="like" data-size="large" data-show-faces="false" data-share="'.(get_optionXTRA('xtra_share_faceshare') ? "true" : "false").'"></div>';
		$html .= "<div class='clear'></div></div>";
		return $html;
	}
	
	function xtra_share_buttons($buffer) {
		$plac = get_optionXTRA('xtra_share_buttons_place', 0);
		if (!xtra_check_pagetype("share_buttons",$plac)) return $buffer;

		$html = xtra_get_social_share_icons();
		if (!$html) return $buffer;

		$hed = substr($buffer,0,stripos($buffer,"</head>")+7);
		$buffer = str_replace($hed,'',$buffer);

		$html = utf8_encode($html); // $html comes from ansi text (this file)

		$buffer = xtra_html_places($buffer,$html,$plac,"xtra_share_buttons");

		return $hed.$buffer;
	}

	//alternative for before text and after text positions
	function xtra_content_addi( $content ) {
		$plac = get_optionXTRA('xtra_share_buttons_place', 0);
		if (!xtra_check_pagetype("share_buttons",$plac,1)) return $content;

		$html = xtra_get_social_share_icons();
		
		$html = utf8_encode($html); // $html comes from ansi text (this file)

		if ($plac == 2) $content = $html . $content;
		elseif ($plac == 1) $content = $content . $html;

		return $content;
	}
	add_filter( 'the_content', 'xtra_content_addi', 99 );

}

function xtra_check_pagetype($str="",$plac=0,$alt=0) {
	if (!is_singular()) return 0;
	if (is_front_page() && 	!get_optionXTRA('xtra_'.$str.'_homepage')) 	return 0;
	if (is_page() 		&& 	!get_optionXTRA('xtra_'.$str.'_pages')) 	return 0;
	if (is_single() 	&& 	!get_optionXTRA('xtra_'.$str.'_posts')) 	return 0;

	if (!$plac) return 0;

	//if ( !xtra_instr(xtra_get_theme_name(),array('vantage','mesmerize')) && xtra_instr($plac, array(1,2)) )
	if ( !xtra_instr(xtra_get_theme_name(),array('vantage')) && xtra_instr($plac, array(1,2)) )
		return ($alt ? 1 : 0);
	return ($alt ? 0 : 1);
}

//---Share 2 buttons---

if( get_optionXTRA('xtra_share2_buttons') ) {
	function xtra_add_social_share2_icons_css() {
		wp_enqueue_style( 'xtra_social_share_buttons_css', plugin_dir_url( __FILE__ ) . 'assets/css/social-share-buttons.css' );
	}
	add_action( 'wp_enqueue_scripts', 'xtra_add_social_share2_icons_css' );
	function xtra_stylesheet_installed2($array_css)
	{
		global $wp_styles;
		foreach( $wp_styles->queue as $style )
		{
			foreach ($array_css as $css)
			{
				if (false !== strpos( $wp_styles->registered[$style]->src, $css ))
					return 1;
			}
		}
		return 0;
	}
	function xtra_add_fa_css2(){
		global $xtra_fa;
		$font_awesome = array('font-awesome', 'fontawesome');
		$xtra_fa = false;
		if (xtra_stylesheet_installed2($font_awesome) === 0) {
			 wp_enqueue_style('font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
		}
		if (xtra_stylesheet_installed2($font_awesome) !== 0)
			$xtra_fa = true;
	}
	add_action( 'wp_enqueue_scripts', 'xtra_add_fa_css2', 99 );

	function xtra_get_social_share2_icons() {
		global $xtra_fa;
		global $post;
		$postcat = get_the_category( $post->ID ); 	// Geting the current post's category lists if exists.
		$category_ids = $postcat[0]->term_id;	// getting cat ids
		$dontshow_cats = get_optionXTRA('xtra_share2_cat_exclude', array());	// dont show on these categories
		foreach( (array)$category_ids as $tcatid ) if ( in_array($tcatid,$dontshow_cats) ) return;	// filter

		$tit = get_optionXTRA('xtra_share2_buttons_text', 'Share on:');
		if ( function_exists('pll__') ) $tit = utf8_decode(pll__($tit));
		$inl = get_optionXTRA('xtra_share2_buttons_cbx', 0);
		$ttag = get_optionXTRA('xtra_share2_buttons_pnum', 0);
		$tsty = "";
		if ($inl) $tsty = ' style="display:inline-block;margin-right: 0.3em;"';
		$zoom = get_optionXTRA('xtra_share2_buttons_num', 100);
		$spac = get_optionXTRA('xtra_share2_buttons_num2', 4);
		$shape = get_optionXTRA('xtra_share2_buttons_shape', 0);
		if ($shape==0) $sty = "border-radius: 0px;";
		if ($shape==1) $sty = "border-radius: 5px;";
		if ($shape==2) $sty = "border-radius: 50%;";
		if ($shape==3) $sty = "border-radius: 0px; width: 3.3em;";
		if ($shape==4) $sty = "border-radius: 4px; width: 3.3em;";

		$html = "<div class='xtra-social-share-wrap'>";
		if ($tit) $html .= "<$ttag$tsty>$tit</$ttag>";
		global $post;
		$url = get_permalink($post->ID);
		$url = esc_url($url);
		$shares = array(
			'facebook'		=>	array('Facebook',	'fa-facebook',		'http://www.facebook.com/sharer.php?u='.$url),
			'twitter'		=>	array('Twitter',	'fa-twitter',		'https://twitter.com/share?url='.$url),
			'linkedin'		=>	array('LinkedIn',	'fa-linkedin',		'http://www.linkedin.com/shareArticle?url='.$url),
			'pinterest'		=>	array('Pinterest',	'fa-pinterest',		"javascript:void((function()%7Bvar%20e=document.createElement(\"script\");e.setAttribute(\"type\",\"text/javascript\");e.setAttribute(\"charset\",\"UTF-8\");e.setAttribute(\"src\",\"//assets.pinterest.com/js/pinmarklet.js?r=\"+Math.random()*99999999);document.body.appendChild(e)%7D)());"),
			'tumblr'		=>	array('tumblr',		'fa-tumblr',		'http://www.tumblr.com/share/link?url='.$url),
			'gplus'			=>	array('Google+',	'fa-google-plus',	'https://plus.google.com/share?url='.$url),
			'reddit'		=>	array('Reddit',		'fa-reddit',		'http://reddit.com/submit?url='.$url),
			'buffer'		=>	array('Buffer',		'fa-share-alt',		'https://bufferapp.com/add?url='.$url),
		);
		foreach ($shares as $slg=>$set) {
			if (get_optionXTRA('xtra_share2_'.$slg))
				/*
				$html .= "
					<a target='_blank' title='".$set[0]."' href='".$set[2]."'>
						<div class='".$slg."' style='zoom:".$zoom."% !important;".$sty."'>
							<span>".($xtra_fa?"<i style='zoom:101% !important;' class='fa ".$set[1]."'></i>":$set[0])."</span>
						</div>
					</a>";
					*/
				$html .= "
					<a target='_blank' title='".$set[0]."' href='".$set[2]."'>
					<div class='".$slg."' style='
						width:".(1.188 *$zoom/100)."em; 
						height:".(1.188 *$zoom/100)."em; 
						padding:".(5/(16+2) *$zoom/100)."em; 
						margin-right:".($spac/16 *$zoom/100)."em; 
						margin-bottom:".($spac/16 *$zoom/100)."em;
						line-height:".(1.15 *$zoom/100)."em;
						vertical-align: middle;
						".$sty."'>
					<span>".($xtra_fa?"<i style='
							font-size:".(1.0 *$zoom/100)."em !important;
							line-height:1px;
							vertical-align: middle;
							' class='fa ".$set[1]."'></i>":$set[0])."
					</span>
					</div>
					</a>
					";
		}
		$fbst = 'margin-right:0px;vertical-align:top;text-align: left;';
		$fbst .= xtra_share_buttons_scale($zoom);
		$fbst .= xtra_share_buttons_transform_origin("top left");
		if (get_optionXTRA('xtra_share2_facelike')) $html .= '<div style="'.$fbst.'" class="fb-like" data-href="'.$url.'" data-width="200" data-layout="button_count" data-action="like" data-size="large" data-show-faces="false" data-share="'.(get_optionXTRA('xtra_share2_faceshare') ? "true" : "false").'"></div>';
		$html .= "<div class='clear'></div></div>";
		return $html;
	}

	function xtra_share2_buttons($buffer) {
		$plac = get_optionXTRA('xtra_share2_buttons_place', 0);
		if (!xtra_check_pagetype("share2_buttons",$plac)) return $buffer;

		$html = xtra_get_social_share2_icons();
		if (!$html) return $buffer;

		$hed = substr($buffer,0,stripos($buffer,"</head>")+7);
		$buffer = str_replace($hed,'',$buffer);

		$html = utf8_encode($html); // $html comes from ansi text (this file)

		$buffer = xtra_html_places($buffer,$html,$plac,"xtra_share_buttons2");

		return $hed.$buffer;
	}

	//alternative for before text and after text positions
	function xtra_content2_addi( $content ) {
		$plac = get_optionXTRA('xtra_share2_buttons_place', 0);
		if (!xtra_check_pagetype("share2_buttons",$plac,1)) return $content;

		$html = xtra_get_social_share_icons();
		
		$html = utf8_encode($html); // $html comes from ansi text (this file)

		if ($plac == 2) $content = $html . $content;
		elseif ($plac == 1) $content = $content . $html;

		return $content;
	}
	add_filter( 'the_content', 'xtra_content2_addi', 99 );

}



if ($xtra_do_memlog) xtra_memlog("Social");

//---5. WP Settings---

//Remove admin bar on front-end
if (get_optionXTRA( 'xtra_remove_admin_bar', 0 )) {
	add_action('after_setup_theme', 'xtra_remove_admin_bar');
	function xtra_remove_admin_bar() {
		if ( !is_admin() ) {
			show_admin_bar(false);
			if ( get_optionXTRA( 'xtra_remove_admin_bar_excl_adm', 0 ) && current_user_can('manage_options') ) //administrator
				show_admin_bar(true);
			elseif ( get_optionXTRA( 'xtra_remove_admin_bar_excl_edt', 0 ) && current_user_can('edit_others_posts') && !current_user_can('manage_options') ) //editor
				show_admin_bar(true);
			elseif ( get_optionXTRA( 'xtra_remove_admin_bar_excl_aut', 0 ) && current_user_can('edit_published_posts') && !current_user_can('edit_others_posts') ) //author
				show_admin_bar(true);
			elseif ( get_optionXTRA( 'xtra_remove_admin_bar_excl_cnt', 0 ) && current_user_can('edit_posts') && !current_user_can('edit_published_posts') ) //contributor
				show_admin_bar(true);
			elseif ( get_optionXTRA( 'xtra_remove_admin_bar_excl_sub', 0 ) && current_user_can('read') && !current_user_can('edit_posts') ) //subscriber
				show_admin_bar(true);
			elseif ( get_optionXTRA( 'xtra_remove_admin_bar_excl_ano', 0 ) && !current_user_can('read') ) //anonymous - not logged in
				show_admin_bar(true);
		}
	}
}

//Remove admin notices
if (get_optionXTRA( 'xtra_remove_admin_notices', 0 )) {
	
	add_action('in_admin_header', function () {
		if ($admin_page_exception) return;
		remove_all_actions('admin_notices');
		remove_all_actions('all_admin_notices');
		//add_action('admin_notices', function () {	echo 'My notice';	});
	}, 1000);

	add_action('admin_enqueue_scripts', 'xtra_admin_theme_style');
	add_action('login_enqueue_scripts', 'xtra_admin_theme_style');
	function xtra_admin_theme_style() {
		if ($admin_page_exception) return;
		//if (!current_user_can( 'manage_options' )) {
			echo '<style>.update-nag, .updated, .error, .is-dismissible { display: none; }</style>';
		//}
	}

}

//Check the Remember Me checkbox automatically:
if (get_optionXTRA( 'xtra_login_checked_remember_me', 0 )) {
	function xtra_login_checked_remember_me() {
		add_filter( 'login_footer', 'xtra_rememberme_checked' );
	}
	add_action( 'init', 'xtra_login_checked_remember_me' );
	function xtra_rememberme_checked() {
		echo "<script>document.getElementById('rememberme').checked = true;</script>";
	}
}

//Set auth cookie expiration for Remember Me
if (get_optionXTRA( 'xtra_keep_me_logged_in_for', 0 )) {
	add_filter( 'auth_cookie_expiration', 'xtra_keep_me_logged_in_for' );
	function xtra_keep_me_logged_in_for( $expirein ) {
		$days = get_optionXTRA( 'xtra_keep_me_logged_in_for_text', '60' );
		return ($days*24*60*60); // 60 days in seconds
	}
}

// Change default WP email sender name and address
if (get_optionXTRA( 'xtra_doEmailNameFilter', 0 )) {
	add_filter('wp_mail_from_name', 'xtra_doEmailNameFilter');
	function xtra_doEmailNameFilter($email_from){
		$text = get_optionXTRA( 'xtra_doEmailNameFilter_text', '' );
		if($text && $email_from === "WordPress")
			return $text;
		else
			return $email_from;
	}
}
if (get_optionXTRA( 'xtra_doEmailFilter', 0 )) {
	add_filter('wp_mail_from', 'xtra_doEmailFilter');
	function xtra_doEmailFilter($email_address){
		$myhost1 = str_ireplace( array("http://","https://"), "", site_url() );
		$myhost2 = str_ireplace( array("http://","https://"), "", home_url() );
		$text = get_optionXTRA( 'xtra_doEmailFilter_text', '' );
		if($text && (xtra_instr($email_address,"wordpress@".$myhost1) || xtra_instr($email_address,"wordpress@".$myhost2) ) )
			return $text;
		else
			return $email_address;
	}
}

//. xtra_remove_WPemoji
//As of WordPress 4.2, by default WordPress supports Emojis. Great if that is your cup of tea, but if not, you might want to remove the additional resources Emoji support adds to your webpages.
if (get_optionXTRA( 'xtra_remove_WPemoji', 0 )) {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7);
	remove_action( 'wp_print_styles', 'print_emoji_styles');
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}

//custom jpeg quality
if (get_optionXTRA( 'xtra_custom_jpeg_quality', 0 )) {
	function xtra_custom_jpeg_quality( $quality, $context ) {
		$num = get_optionXTRA( 'xtra_custom_jpeg_quality_num', 85 );
		return $num;
	}
	add_filter( 'jpeg_quality', 'xtra_custom_jpeg_quality', 10, 2 );
}

function xtra_disable_WPcron( $bool ){
	$opt = "xtra_disable_WPcron";
	$ins = "define('DISABLE_WP_CRON', ".(($bool)?"true":"false").");";
	return xtra_wpconfig($bool,$opt,$ins);
}

if( get_optionXTRA('xtra_disable_heartbeat') ) {
	function xtra_stop_heartbeat() {
		global $pagenow;
		if( get_optionXTRA('xtra_disable_heartbeat_exedit', 0) ) {
			if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) return;
		}
		wp_deregister_script('heartbeat');
	}
	add_action( 'init', 'xtra_stop_heartbeat', 1 );
}

if( get_optionXTRA('xtra_disable_self_pingback') ) {
	function xtra_no_self_ping( &$links ) {
		$home = get_option( 'home' );
		foreach ( $links as $l => $link )
			if ( 0 === strpos( $link, $home ) )
				unset($links[$l]);
	}
	add_action( 'pre_ping', 'xtra_no_self_ping' );
}

if( get_optionXTRA('xtra_extend_search') ) {
	function xtra_extend_search( $search, $wp_query ) {
		global $wpdb;

		if ( empty( $search ))
		return $search;

		global $xtra_sterms;
		$xtra_sterms = $wp_query->query_vars[ 's' ];
		$exploded = explode( ' ', $xtra_sterms );
		if( $exploded === FALSE || count((array) $exploded ) == 0 )
		$exploded = array( 0 => $xtra_sterms );
		$xtra_sterms = $exploded;

		$search = '';
		foreach( $exploded as $tag ) {
			$search .= " AND (
				(post_title LIKE '%$tag%')
				OR (post_content LIKE '%$tag%')
				OR EXISTS
				(
					SELECT * FROM $wpdb->comments
					WHERE comment_post_ID = ID
						AND comment_content LIKE '%$tag%'
				)
				OR EXISTS
				(
					SELECT * FROM $wpdb->terms AS trm
					INNER JOIN $wpdb->term_taxonomy AS ttx ON ttx.term_id = trm.term_id
					INNER JOIN $wpdb->term_relationships AS tre ON tre.term_taxonomy_id = ttx.term_taxonomy_id
					WHERE ( taxonomy = 'post_tag' OR taxonomy = 'category' OR taxonomy = 'country' OR taxonomy = 'material' )
						AND object_id = ID
						AND (trm.name LIKE '%$tag%' OR ttx.description LIKE '%$tag%')
				)
				OR EXISTS
				(
					SELECT * FROM $wpdb->users
					WHERE post_author = ID
						AND display_name LIKE '%$tag%'
				)
			)";
		}
		return $search;
	}
	add_filter( 'posts_search', 'xtra_extend_search', 500, 2 );
}

if( get_optionXTRA('xtra_highlight_search') ) {
	function xtra_highlight_search_result($postcontent) {
		global $xtra_sterms;
		// highlighting
		if ( !is_admin() && is_search() && count((array)$xtra_sterms) ) {
			$highlight_color = array("#FFDDAA","#FFAADD","#DDAAFF","#DDFFAA","#AAFFDD","#AADDFF");
			$highlight_style = "";
			$search_terms = $xtra_sterms;

			$i = 0;
			foreach ( $search_terms as $term ) {
				if ( preg_match( '/\>/', $term ) )
					continue; //don't try to highlight this one
				$term = preg_quote( $term );

				if ( $highlight_color != '' )
					$postcontent = preg_replace(
						'"(?<!\<)(?<!\w)(\pL*'.$term.'\pL*)(?!\w|[^<>]*>)"iu'
						, '<span class="xtra-search-highlight-color" style="background-color:'.$highlight_color[$i].'">$1</span>'
						, $postcontent
					);
				else
					$postcontent = preg_replace(
						'"(?<!\<)(?<!\w)(\pL*'.$term.'\pL*)(?!\w|[^<>]*>)"iu'
						, '<span class="xtra-search-highlight" style="'.$highlight_style.'">$1</span>'
						, $postcontent
					);
				$i++;
			}
		}
		return $postcontent;
	}
	add_filter( 'the_content',	'xtra_highlight_search_result' , 11 );
	add_filter( 'the_title',	'xtra_highlight_search_result' , 11 );
	add_filter( 'the_excerpt',	'xtra_highlight_search_result' , 11 );
	add_filter( 'comment_text',	'xtra_highlight_search_result' );
}

if( get_optionXTRA('xtra_title_shorten') ) {
	function xtra_title_shorten($title) {
		if ( is_singular() ) return $title;
		if ( xtra_instr($title,"<") ) return $title;
		$max = get_optionXTRA('xtra_title_shorten_num',50);
		$title = utf8_truncate($title,$max,"&nbsp;&hellip;");
		//$title = mb_substr($title,0,$max)."&nbsp;&hellip;";
		return $title;
	}
	add_filter( 'the_title', 'xtra_title_shorten');
	function utf8_truncate( $string, $max_chars = 200, $append = "\xC2\xA0…" ) {
		$string = strip_tags( $string );
		$string = html_entity_decode( $string, ENT_QUOTES, 'utf-8' );
		// \xC2\xA0 is the no-break space
		$string = trim( $string, "\n\r\t .-;–,—\xC2\xA0" );
		$length = strlen( utf8_decode( $string ) );

		// Nothing to do.
		if ( $length < $max_chars ) {
			return $string;
		}

		// mb_substr() is in /wp-includes/compat.php as a fallback if
		// your the current PHP installation doesn’t have it.
		$string = mb_substr( $string, 0, $max_chars, 'utf-8' );

		// No white space. One long word or chinese/korean/japanese text.
		if ( FALSE === strpos( $string, ' ' ) ) {
			return $string . $append;
		}

		// Avoid breaks within words. Find the last white space.
		if ( extension_loaded( 'mbstring' ) ) {
			$pos   = mb_strrpos( $string, ' ', 0, 'utf-8' );
			$short = mb_substr( $string, 0, $pos, 'utf-8' );
		}
		else {
			// Workaround. May be slow on long strings.
			$words = explode( ' ', $string );
			// Drop the last word.
			array_pop( $words );
			$short = implode( ' ', $words );
		}

		return $short . $append;
	}
}


//---Auto-Resize Image Uploads---
if( get_optionXTRA('xtra_auto_resize_upload') ) {
	function xtra_handle_upload($params) {
		if (!current_user_can('upload_files')) // Verify the current user can upload files
			wp_die(esc_html__('You do not have permission to upload files.', 'xtra-settings'));
		$maxW = get_optionXTRA('xtra_auto_resize_upload_num',0);
		$maxH = get_optionXTRA('xtra_auto_resize_upload_pnum',0);
		$xtra_crop_image = false;
		$force_keep_new_file_even_if_bigger = true;

		if ( $params['type'] == 'image/bmp' && $xtra_bmp_to_jpg ) {
			$params = xtra_convert_to_jpg( 'bmp', $params );
		}
		if ( $params['type'] == 'image/png' && $xtra_png_to_jpg ) {
			$params = xtra_convert_to_jpg( 'png', $params );
		}
		$oldPath = $params['file'];
		if ( ( ! is_wp_error( $params ) ) && is_writable( $oldPath ) && in_array( $params['type'], array( 'image/png', 'image/gif', 'image/jpeg' ) ) ) {
			list( $oldW, $oldH ) = getimagesize( $oldPath );
			if ( ( $oldW > $maxW && $maxW > 0 ) || ( $oldH > $maxH && $maxH > 0 ) ) {
				$quality = get_optionXTRA('xtra_custom_jpeg_quality_num',82);
				if ( $oldW > $maxW && $maxW > 0 && $oldH > $maxH && $maxH > 0 && $xtra_crop_image ) {
					$newW = $maxW;
					$newH = $maxH;
				} else {
					list( $newW, $newH ) = wp_constrain_dimensions( $oldW, $oldH, $maxW, $maxH );
				}
				remove_filter( 'wp_image_editors', 'ewww_image_optimizer_load_editor', 60 );
				$resizeResult = xtra_2_wpeditor_image_resize( $oldPath, $quality, $newW, $newH, $xtra_crop_image, null, null );
				if ( function_exists( 'ewww_image_optimizer_load_editor' ) ) {
					add_filter( 'wp_image_editors', 'ewww_image_optimizer_load_editor', 60 );
				}
				if ( $resizeResult && ! is_wp_error( $resizeResult ) ) {
					$newPath = $resizeResult;
					if ( is_file( $newPath ) ) {
						if ( $force_keep_new_file_even_if_bigger || filesize( $newPath ) <  filesize( $oldPath ) ) {
							// we saved some file space. remove original and replace with resized image
							unlink( $oldPath );
							rename( $newPath, $oldPath );
						} else {
							// the resized image is actually bigger in filesize (most likely due to jpg quality).
							// keep the old one and just get rid of the resized image
							unlink( $newPath );
						}
					}
					else {
						// file not found, resize didn't work
						// remove the old image so we don't leave orphan files hanging around
						unlink( $oldPath );

						$params = wp_handle_upload_error( $oldPath ,
							sprintf( esc_html__( "XTRA was unable to resize this image for an unknown reason. If you think you have discovered a bug, please report it on the XTRA support forum: %s", 'xtra-settings' ), 'https://wordpress.org/support/plugin/xtra-settings' ) );

					}
				} else if ( $resizeResult === false ) {
					return $params;
				} else {
					// resize didn't work, likely because the image processing libraries are missing
					// remove the old image so we don't leave orphan files hanging around
					unlink( $oldPath );

					$params = wp_handle_upload_error( $oldPath ,
						sprintf( esc_html__( "XTRA was unable to resize this image for the following reason: %s. If you continue to see this error message, you may need to install missing server library components. If you think you have discovered a bug, please report it on the XTRA support forum: %s", 'xtra-settings' ), $resizeResult->get_error_message(), 'https://wordpress.org/support/plugin/xtra-settings' ) );

				}
			}
		}
		return $params;
	}
	add_filter( 'wp_handle_upload', 'xtra_handle_upload' );
	function xtra_convert_to_jpg( $type, $params )
	{
		$img = null;
		if ( $type == 'bmp' ) {
			$img = imagecreatefrombmp( $params['file'] );
		} elseif ( $type == 'png' ) {
			if( ! function_exists( 'imagecreatefrompng' ) ) {
				return wp_handle_upload_error( $params['file'], esc_html__( 'XTRA requires the GD library to convert PNG images to JPG', 'xtra-settings' ) );
			}
			$img = imagecreatefrompng( $params['file'] );
			// convert png transparency to white
			$bg = imagecreatetruecolor( imagesx( $img ), imagesy( $img ) );
			imagefill( $bg, 0, 0, imagecolorallocate( $bg, 255, 255, 255 ) );
			imagealphablending( $bg, TRUE );
			imagecopy($bg, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
		}
		else {
			return wp_handle_upload_error( $params['file'], esc_html__( 'Unknown image type specified in xtra_convert_to_jpg function', 'xtra-settings' ) );
		}
		// we need to change the extension from the original to .jpg so we have to ensure it will be a unique filename
		$uploads = wp_upload_dir();
		$oldFileName = basename($params['file']);
		$newFileName = basename(str_ireplace(".".$type, ".jpg", $oldFileName));
		$newFileName = wp_unique_filename( $uploads['path'], $newFileName );

		$quality = get_optionXTRA('xtra_custom_jpeg_quality_num',82);

		if ( imagejpeg( $img, $uploads['path'] . '/' . $newFileName, $quality ) ) {
			// conversion succeeded.  remove the original bmp/png & remap the params
			unlink($params['file']);
			$params['file'] = $uploads['path'] . '/' . $newFileName;
			$params['url'] = $uploads['url'] . '/' . $newFileName;
			$params['type'] = 'image/jpeg';
		}
		else {
			unlink($params['file']);
			return wp_handle_upload_error( $oldPath,
					sprintf( esc_html__( "XTRA was unable to process the %s file. If you continue to see this error you may need to disable the conversion option in the XTRA settings.", 'xtra-settings' ), $type ) );
		}
		return $params;
	}
}






//---Maintenance mode---

//Maintenance mode
if (get_optionXTRA( 'xtra_maintenance', 0 )) {
	// Activate WordPress Maintenance Mode
	function xtra_maintenance(){
		if(!current_user_can('edit_themes') || !is_user_logged_in()){
			$title = get_optionXTRA( 'xtra_maintenance_title', 'Website under Maintenance' );
			$text = get_optionXTRA( 'xtra_maintenance_text', 'We are performing scheduled maintenance. We will be back online shortly!' );
			wp_die('<h1 style="color:red">'.$title.'</h1><br />'.$text);
		}
	}
	add_action('get_header', 'xtra_maintenance');
}





//---Debug mode-------

function xtra_debug( $bool ){
	$opt = "xtra_debug";
	$ins = "define('WP_DEBUG', ".(($bool)?"true":"false").");";
	return xtra_wpconfig($bool,$opt,$ins);
}
if (get_optionXTRA( 'xtra_debug', 0 )) {
	//error_reporting(E_ERROR | E_WARNING | E_PARSE);
	error_reporting(E_ERROR | E_PARSE);
}

function xtra_disable_debug_display( $bool ){
	$opt = "xtra_disable_debug_display";
	$ins = "define('WP_DEBUG_DISPLAY', ".((!$bool)?"true":"false").");";
	return xtra_wpconfig($bool,$opt,$ins);
}
if (get_optionXTRA( 'xtra_disable_debug_display', 0 )) {
	@ini_set( 'display_errors', false );
}

function xtra_debug_log( $bool ){
	$opt = "xtra_debug_log";
	$ins = "define('WP_DEBUG_LOG', ".(($bool)?"true":"false").");";
	return xtra_wpconfig($bool,$opt,$ins);
}

if ( !defined( 'WP_DEBUG' ) || !WP_DEBUG ) {
	if ( file_exists(WP_CONTENT_DIR ."/debug.log") ) unlink( WP_CONTENT_DIR ."/debug.log" );
}



if ($xtra_do_memlog) xtra_memlog("WP Settings");



//---6. Auto-update-------

//. All Auto-update
$xtra_all_autoupdate = get_optionXTRA( 'xtra_all_autoupdate', 0 );
if ($xtra_all_autoupdate==1) {
	add_filter( 'automatic_updater_disabled', '__return_false' );
}
if ($xtra_all_autoupdate==-1) {
	add_filter( 'automatic_updater_disabled', '__return_true' );
	add_filter('pre_site_transient_update_core','xtra_remove_core_updates');
	add_filter('pre_site_transient_update_plugins','xtra_remove_core_updates');
	add_filter('pre_site_transient_update_themes','xtra_remove_core_updates');
}

//. core-major Auto-update
$xtra_core_autoupdate_any = 0;
if (get_optionXTRA( 'xtra_core_autoupdate_major', 0 ) == 1) {
	add_filter( 'allow_major_auto_core_updates', '__return_true' );
	$xtra_core_autoupdate_any++;
}
if (get_optionXTRA( 'xtra_core_autoupdate_major', 0 ) == -1) {
	add_filter( 'allow_major_auto_core_updates', '__return_false' );
}
//. core-minor Auto-update
if (get_optionXTRA( 'xtra_core_autoupdate_minor', 0 ) == 1) {
	add_filter( 'allow_minor_auto_core_updates', '__return_true' );
	$xtra_core_autoupdate_any++;
}
if (get_optionXTRA( 'xtra_core_autoupdate_minor', 0 ) == -1) {
	add_filter( 'allow_minor_auto_core_updates', '__return_false' );
}
//. core-dev Auto-update
if (get_optionXTRA( 'xtra_core_autoupdate_dev', 0 ) == 1) {
	add_filter( 'allow_dev_auto_core_updates', '__return_true' );
	$xtra_core_autoupdate_any++;
}
if (get_optionXTRA( 'xtra_core_autoupdate_dev', 0 ) == -1) {
	add_filter( 'allow_dev_auto_core_updates', '__return_false' );
}

if (!$xtra_core_autoupdate_any) {
	add_filter('pre_site_transient_update_core','xtra_remove_core_updates');
}

//. translation Auto-update
$xtra_translation_autoupdate = get_optionXTRA( 'xtra_translation_autoupdate', 0 );
if ($xtra_translation_autoupdate == 1) {
	add_filter( 'auto_update_translation', '__return_true' );
}
if ($xtra_translation_autoupdate == -1) {
	add_filter( 'auto_update_translation', '__return_false' );
}

//. plugin Auto-update
$xtra_plugin_autoupdate = get_optionXTRA( 'xtra_plugin_autoupdate', 0 );
if ($xtra_plugin_autoupdate == 1) {
	function xtra_auto_update_specific_plugins ( $update, $item ) {
		// Array of plugin slugs to exclude from auto-update
		$plugins = get_optionXTRA( 'xtra_plugins_exclude', array() );
		if ( in_array( $item->slug, $plugins ) ) {
			return false; // Never update plugins in this array
		} else {
			//return $update; // Else, use the normal API response to decide whether to update or not
			return true; // Always update plugins in this array
		}
	}
	add_filter( 'auto_update_plugin', 'xtra_auto_update_specific_plugins', 10, 2 );

	// remove update notice for excluded plugins
	if (get_optionXTRA('xtra_plugin_excl_hide_notif', 0) && !xtra_instr($_SERVER['REQUEST_URI'],"update-core.php") ) {
		//echo "<div style='margin:0px 200px;'><hr>DUMP2:<hr><pre>".print_r($value,1)."</pre></div>";
		function xtra_remove_plugin_update_notifications( $value ) {
		global $plugins_by_slug;
		if (!is_array($plugins_by_slug)) $plugins_by_slug = xtra_make_plugins_by_slug();
			$plugins = get_optionXTRA( 'xtra_plugins_exclude', array() );
			if ( isset( $value ) && is_object( $value ) ) {
				foreach ($plugins as $slug) {
					if ( isset( $value->response[ $slug ] ) ) unset( $value->response[ $slug ] );
					$file = $plugins_by_slug[$slug];
					if ( isset( $value->response[ $file ] ) ) unset( $value->response[ $file ] );
				}
			}
			return $value;
		}
		add_filter( 'site_transient_update_plugins', 'xtra_remove_plugin_update_notifications' );

		function xtra_make_plugins_by_slug(){
			global $plugins_by_slug;
			$plugins_by_slug = array();
			if ( ! function_exists( 'get_plugins' ) ) { require_once ABSPATH . 'wp-admin/includes/plugin.php'; }
			foreach( get_plugins() as $file => $pl ) {
				$slug = dirname( plugin_basename( $file ) );
				$plugins_by_slug[$slug] = $file;
			}
			return $plugins_by_slug;
		}
	}
}
if ($xtra_plugin_autoupdate == -1) {
	add_filter( 'auto_update_plugin', '__return_false' );
	add_filter('pre_site_transient_update_plugins','xtra_remove_core_updates');
}

//. theme Auto-update
$xtra_theme_autoupdate = get_optionXTRA( 'xtra_theme_autoupdate', 0 );
if ($xtra_theme_autoupdate == 1) {
	function xtra_auto_update_specific_themes ( $update, $item ) {
		// Array of theme slugs to exclude from auto-update
		$theme = get_optionXTRA( 'xtra_theme_exclude', array() );
		if ( in_array( $item->slug, $theme ) ) {
			return false; // Never update theme in this array
		} else {
			//return $update; // Else, use the normal API response to decide whether to update or not
			return true; // Always update theme in this array
		}
	}
	add_filter( 'auto_update_theme', 'xtra_auto_update_specific_themes', 10, 2 );

	// remove update notice for excluded themes
	if (get_optionXTRA('xtra_theme_excl_hide_notif', 0) && !xtra_instr($_SERVER['REQUEST_URI'],"update-core.php") ) {
		//echo "<div style='margin:0px 200px;'><hr>DUMP2:<hr><pre>".print_r($value,1)."</pre></div>";
		function xtra_remove_theme_update_notifications( $value ) {
		global $themes_by_slug;
		if (!is_array($themes_by_slug)) $themes_by_slug = xtra_make_themes_by_slug();
			$themes = get_optionXTRA( 'xtra_themes_exclude', array() );
			if ( isset( $value ) && is_object( $value ) ) {
				foreach ($themes as $slug) {
					if ( isset( $value->response[ $slug ] ) ) unset( $value->response[ $slug ] );
					$file = $themes_by_slug[$slug];
					if ( isset( $value->response[ $file ] ) ) unset( $value->response[ $file ] );
				}
			}
			return $value;
		}
		add_filter( 'site_transient_update_themes', 'xtra_remove_theme_update_notifications' );

		function xtra_make_themes_by_slug(){
			global $themes_by_slug;
			$themes_by_slug = array();
			if ( ! function_exists( 'get_themes' ) ) { require_once ABSPATH . 'wp-admin/includes/theme.php'; }
			foreach( wp_get_themes() as $file => $pl ) {
				$slug = dirname( wp_basename( $file ) );
				$themes_by_slug[$slug] = $file;
			}
			return $themes_by_slug;
		}
	}
	//add_filter( 'auto_update_theme', '__return_true' );	
}
if ($xtra_theme_autoupdate == -1) {
	add_filter( 'auto_update_theme', '__return_false' );
	add_filter('pre_site_transient_update_themes','xtra_remove_core_updates');
}

function xtra_remove_core_updates(){
	global $wp_version;
	return(object) array('last_checked'=> time(),'version_checked'=> $wp_version,'updates' => array());
}


//---7. Hits---
if( get_optionXTRA('xtra_hits_enable') ) {
	//if (is_admin()) add_action('shutdown','xtra_hits_add');
	//else add_action('wp_footer','xtra_hits_add');
	add_action('shutdown','xtra_hits_add');
	//add_action('shutdown','xtra_hits_send_mail');
}

function xtra_hits_add() {

	$harr = get_optionXTRA('xtra_hits', array(array('all'=>0,'uni'=>0)));
	$hits = $harr[0]['all'];
	$hitu = $harr[0]['uni'];

	$last_hit_time = get_optionXTRA('xtra_hits_last_time', time());
	if (!$last_hit_time) $last_hit_time = time();
	$daydiff = ceil( (time()-$last_hit_time)/(24*60*60) );

	$last_hit_day = date( "j", $last_hit_time );
	if ($last_hit_day != date("j", time()) ) {
		//check mail send - daychange
		//if (!get_optionXTRA('xtra_hits_send_mail_num', 0)) xtra_hits_send_mail("daychange");
		xtra_hits_send_mail("daychange");
		//start a new day
		while ( $daydiff > 0 ) {
			array_unshift( $harr, array('all'=>0,'uni'=>0) ); //prepend missing days
			$daydiff = $daydiff-1;
		}
		$maxdays = get_optionXTRA('xtra_hits_enable_num', 30);
		if (count((array)$harr) > $maxdays) array_splice($harr,$maxdays);
		update_optionXTRA( 'xtra_hits', $harr );
		delete_optionXTRA('xtra_hits_IPs'); //reset unique IPs
		delete_optionXTRA('xtra_hits_IPdata'); //reset IPdata
		//update_optionXTRA( 'xtra_hits_last_time', time() ); //this was missing and made a bug in 1.5.3
	}
	else {
		//check mail send - around ...
		//if (get_optionXTRA('xtra_hits_send_mail_num', 0)) xtra_hits_send_mail();
	}

	// get IPs
	$thisIP = $_SERVER['REMOTE_ADDR'];
	if (!$thisIP) $thisIP = "n.a.";
	$IPs = get_optionXTRA('xtra_hits_IPs',array());
	if (!is_array($IPs)) $IPs = array($IPs);
	$IPdata = get_optionXTRA('xtra_hits_IPdata',array());
	$HITdata = get_optionXTRA('xtra_hits_HITdata',array());

	$skip_hit = "";
	$save_hit = 0;
	//check too frequent hits
	$secdiff = time()-$last_hit_time;
	if ($secdiff < 1) $skip_hit .= "Less than 1sec, ";

	//check page URI undefined
	if ( $_SERVER['REQUEST_URI'] == "/undefined" ) $skip_hit .= "URI undefined, ";

	//check exclude admin and cron
	if ( get_optionXTRA('xtra_hits_exclude_ajax',0) && xtra_instr($_SERVER['REQUEST_URI'],"admin-ajax") ) {
		$skip_hit .= "Ajax-hit, ";
		if ( !get_optionXTRA('xtra_hits_exclude_ajax_abandon') ) $save_hit = 1;
	}
	elseif ( get_optionXTRA('xtra_hits_exclude_cron',0) && xtra_instr($_SERVER['REQUEST_URI'],"wp-cron") ) {
		$skip_hit .= "Cron-hit, ";
		if ( !get_optionXTRA('xtra_hits_exclude_cron_abandon') ) $save_hit = 1;
	}
	elseif ( get_optionXTRA('xtra_hits_exclude_admin',0) && xtra_instr($_SERVER['REQUEST_URI'],"wp-admin") ) {
		$skip_hit .= "Admin-hit, ";
		if ( !get_optionXTRA('xtra_hits_exclude_admin_abandon') ) $save_hit = 1;
	}
	elseif ( get_optionXTRA('xtra_hits_exclude_admin',0) && xtra_instr($_SERVER['REQUEST_URI'],"admin-notice") ) {
		$skip_hit .= "Admin-hit, ";
		if ( !get_optionXTRA('xtra_hits_exclude_admin_abandon') ) $save_hit = 1;
	}
	elseif ( get_optionXTRA('xtra_hits_exclude_string',0) ) {
		$tdef = get_optionXTRA('xtra_hits_exclude_string_text','');
		if (!$tdef) $tdef = "robot\ncrawler\nspider\nslurp\ngooglebot\nfacebookexternalhit\nfacebot\npinterestbot";
		$excl = get_optionXTRA('xtra_hits_exclude_string_texta',$tdef);
		$excl = explode("\n",$excl);

		global $rem_serv;

		if (@$IPdata[$thisIP]['host'])
			$rem_serv = $IPdata[$thisIP]['host'];

		if (!isset($rem_serv) && get_optionXTRA('xtra_hits_lookup_hostnames',0)) {
			$time_start = microtime(true);
			$rem_serv = @gethostbyaddr($_SERVER['REMOTE_ADDR']); //maybe slow
			xtra_time_measure('xtra_hostlookup_avg',$time_start);
		}
		$chstr = $_SERVER['HTTP_USER_AGENT']." ".$_SERVER['REMOTE_ADDR']." ".$_SERVER['REQUEST_URI'];
		if ( isset($_SERVER['REMOTE_HOST']) ) $chstr .= " ".$_SERVER['REMOTE_HOST'];
		if ( isset($rem_serv) && $rem_serv ) $chstr .= " ".$rem_serv;
		foreach((array)$excl as $block) {
			if (!$block) continue;
			if ( preg_match( "#".$block."#i", $chstr ) ) {
				$skip_hit .= "Block-word: '".$block."', ";
				if ( !get_optionXTRA('xtra_hits_exclude_string_abandon') ) $save_hit = 1;
				break;
			}
		}
	}

	//check too frequent hits from same IP+page
	if ($secdiff < 2) {
		reset($HITdata);
		$fkey = key($HITdata);
		if ( $thisIP == $HITdata[$fkey]['IP'] && untrailingslashit($_SERVER['REQUEST_URI']) == untrailingslashit($HITdata[$fkey]['page']) ) {
			$skip_hit .= "Same IP+page in 2sec, ";
		}
	}
//xtra_mylog($HITdata[$fkey],'data for '.$HITdata[$fkey]['IP']);
//xtra_mylog($HITdata,'HITdata');

	// add a hit
	if (!$skip_hit)
		$harr[0]['all']++;

	// check unique IPs - add a unique hit
	if (!$skip_hit) {
		if ( !xtra_instr($IPs,$thisIP) ) {
			$harr[0]['uni']++;
			array_unshift($IPs,$thisIP); //add to the top position
		}
		else {
			$key = array_search($thisIP,$IPs);
			if ($key !== FALSE) {
				array_splice($IPs,$key,1); //delete item[$key] + reindex array
				array_unshift($IPs,$thisIP); //add to the top position
			}
		}
	}

	//geoIP
	$tcity = '';
	$tcountry = '';
	if ( get_optionXTRA('xtra_hits_geoip',0) ) {
		if (@$IPdata[$thisIP]['country']) {
			$tcountry = $IPdata[$thisIP]['country'];
			$tcity = $IPdata[$thisIP]['city'];
		}
		else {
			$time_start = microtime(true);
			$geo = @file_get_contents("https://freegeoip.app/json/".$thisIP."");
			xtra_time_measure('xtra_geoiplookup_avg',$time_start);
			$geo = json_decode($geo,true);
			//{"ip":"192.30.253.113","country_code":"US","country_name":"United States","region_code":"CA","region_name":"California","city":"San Francisco","zip_code":"94107","time_zone":"America/Los_Angeles","latitude":37.7697,"longitude":-122.3933,"metro_code":807}
			if (isset($geo['country_code'])) {
				$tcountry = $geo['country_code'];
				$tcity = $geo['city'];
			}
		}
	}

	//update options
	$userid = "";
	$username = "";
	$current_user = wp_get_current_user();
	if ( $current_user->ID ) $userid = $current_user->ID;
	if ( $current_user->display_name ) $username = $current_user->display_name;

	if (!$skip_hit) {
		$rdata = array(
			'ua'		=>	$_SERVER['HTTP_USER_AGENT'],
			'href'		=>	$_SERVER['HTTP_REFERER'],
			'server'	=>	$rem_serv,
			'lang'		=>	$_SERVER['HTTP_ACCEPT_LANGUAGE'],
		);
		$IPdata[$thisIP] = array(
			'time'		=>	date("Y-m-d H:i:s", time()),
			'page'		=>	$_SERVER['REQUEST_URI'],
			'host'		=>	$rem_serv,
			'country'	=>	$tcountry ? $tcountry : xtra_request_anal( "country", $rdata ),
			'city'		=>	$tcity,
			'platform'	=>	xtra_request_anal( "platform", $rdata ),
			'browser'	=>	xtra_request_anal( "browser", $rdata ),
			'user'		=>	$_SERVER['HTTP_USER_AGENT'],
			'ref'		=>	str_ireplace( array( trailingslashit(home_url()), trailingslashit(site_url()) ), "/", $_SERVER['HTTP_REFERER'] ),
			'userid'	=>	$userid,
			'username'	=>	$username,
		);
		update_optionXTRA( 'xtra_hits_IPdata', $IPdata );
	}

	if ( !$skip_hit || $save_hit > 0 ) {
		$rdata = array(
			'ua'		=>	$_SERVER['HTTP_USER_AGENT'],
			'href'		=>	$_SERVER['HTTP_REFERER'],
			'server'	=>	$rem_serv,
			'lang'		=>	$_SERVER['HTTP_ACCEPT_LANGUAGE'],
		);
		$HITdata[date("Y-m-d H:i:s", time())] = array(
			'IP'		=>	$thisIP,
			'page'		=>	$_SERVER['REQUEST_URI'],
			'host'		=>	$rem_serv,
			'country'	=>	$tcountry ? $tcountry : xtra_request_anal( "country", $rdata ),
			'city'		=>	$tcity,
			'platform'	=>	xtra_request_anal( "platform", $rdata ),
			'browser'	=>	xtra_request_anal( "browser", $rdata ),
			'user'		=>	$_SERVER['HTTP_USER_AGENT'],
			'ref'		=>	str_ireplace( array( trailingslashit(home_url()), trailingslashit(site_url()) ), "/", $_SERVER['HTTP_REFERER'] ),
			'userid'	=>	$userid,
			'username'	=>	$username,
			'skip'		=>	$skip_hit,
		);
		krsort($HITdata); // sort reverse by date
		$maxd = get_optionXTRA('xtra_hits_enable_pnum', 1000);
		if (count((array)$HITdata) > $maxd) array_splice($HITdata,$maxd);
		update_optionXTRA( 'xtra_hits_HITdata', $HITdata );
	}
	update_optionXTRA( 'xtra_hits_IPs', $IPs );
	update_optionXTRA( 'xtra_hits', $harr );
	update_optionXTRA( 'xtra_hits_last_time', time() );
}

function xtra_hits_send_mail($force="") {
	//if ( xtra_instr($_SERVER['REQUEST_URI'],"wp-cron") ) return; //prevent sending multiple emails
	if ($force == 'sendnow') {
		$debug = "
			<hr>sent with 'daychange'
			<br>REQUEST_URI=".$_SERVER['REQUEST_URI']."
			<br>time=".date("Y-m-d H:i:s",time())."
			<br>last=".date("Y-m-d H:i:s",get_optionXTRA('xtra_hits_send_mail_last', 0))."
			<hr>
		";
		//xtra_hits_do_send_mail($debug);
		xtra_hits_do_send_mail();
		return;
	}
	if (!get_optionXTRA('xtra_hits_send_mail', 0)) return;
	if (get_transient('xtra_hits_sending_mail')) return;
	set_transient('xtra_hits_sending_mail',1,2*60);
	if ($force == 'daychange') {
					$last_hit_time = get_optionXTRA('xtra_hits_last_time', time());
					if (!$last_hit_time) $last_hit_time = time();
					$last_hit_day = date( "j", $last_hit_time );
		$debug = "
			<hr>sent with 'daychange'
			<br>REQUEST_URI=".$_SERVER['REQUEST_URI']."
			<br>time=".date("Y-m-d H:i:s",time())."
			<br>last mail=".date("Y-m-d H:i:s",get_optionXTRA('xtra_hits_send_mail_last', 0))."
			<hr>last_hit_time=".date("Y-m-d H:i:s",$last_hit_time)."
			<br>last_hit_day=".$last_hit_day."
			<br>this_day=".date("j", time())."
			<hr>
		";
		update_optionXTRA('xtra_hits_send_mail_last', time() );
		//xtra_hits_do_send_mail($debug);
		xtra_hits_do_send_mail();
		return;
	}
	$last = get_optionXTRA('xtra_hits_send_mail_last', 0);
	$around = get_optionXTRA('xtra_hits_send_mail_num', 0);
	if (!$around) return;
	$around_ts = strtotime("today ".$around);
	if ($around_ts === FALSE) $around_ts = time()-1;
	$last_day = date( "j", $last );
	$today = date( "j", time() );
	if ( $last_day != $today && time()-$around_ts > 0 ) {
		$debug = "
			<hr>sent with 'around'
			<br>REQUEST_URI=".$_SERVER['REQUEST_URI']."
			<br>time=".date("Y-m-d H:i:s",time())."
			<br>last=".date("Y-m-d H:i:s",get_optionXTRA('xtra_hits_send_mail_last', 0))."
			<br>last_day=$last_day
			<br>today=$today
			<br>around_ts=".date("Y-m-d H:i:s",$around_ts)."
			<hr>
		";
		//xtra_hits_do_send_mail($debug);
		xtra_hits_do_send_mail();
		update_optionXTRA('xtra_hits_send_mail_last', time() );
	}
}

function xtra_hits_do_send_mail($add_txt1="", $add_txt2="") {
	$addr = get_optionXTRA('xtra_hits_send_mail_text', '');
	if (!$addr) return;
	$subject = "". esc_html__('Today hits', 'xtra-settings') .": ".str_ireplace( array("http://","https://"), "", home_url() )."";
	$body = "";
	$body .= $add_txt1;
	$body .= xtra_hits_show();
	if (get_optionXTRA('xtra_hits_send_mail_cbx', 0)) {
		if (get_optionXTRA('xtra_hits_show_ips',0)) $body .= xtra_hits_show("IPs",get_optionXTRA('xtra_hits_show_ips_num',10),"mail");
		if (get_optionXTRA('xtra_hits_show_geo',0)) $body .= xtra_hits_show("Countries",10,"mail");
		if (get_optionXTRA('xtra_hits_show_pages',0)) $body .= xtra_hits_show("Pages",10,"mail");
		if (get_optionXTRA('xtra_hits_show_pages',0)) $body .= xtra_hits_show("IPc",10,"mail");
		if (get_optionXTRA('xtra_hits_show_hitsdata',0)) $body .= xtra_hits_show("HITs",get_optionXTRA('xtra_hits_show_hitsdata_num',50),"mail");
	}
	$body .= $add_txt2;
	/*
	$body = preg_replace('#<a.*?>(.*?)</a>#is', '\1', $body); //remove links
	*/
	$headers = array('Content-Type: text/html; charset=UTF-8');
	wp_mail($addr, $subject, $body, $headers);	
}

function xtra_hits_reset() {
		delete_optionXTRA('xtra_hits');
		delete_optionXTRA('xtra_hits_IPs');
		delete_optionXTRA('xtra_hits_IPdata');
		delete_optionXTRA('xtra_hits_HITdata');
}
function xtra_hits_today_reset() {
		$harr = get_optionXTRA('xtra_hits', array(array('all'=>0,'uni'=>0)));
		$harr[0] = array('all'=>0,'uni'=>0);
		update_optionXTRA( 'xtra_hits', $harr );
		delete_optionXTRA( 'xtra_hits_IPs' );
		delete_optionXTRA( 'xtra_hits_IPdata' );
}

function xtra_hits_show($what="",$maxitems="",$isformail="") {
global $xtra_do_memlog;

	$hits_y = 0;
	$hitu_y = 0;
	$hits_x = 0;
	$hitu_x = 0;
	$hits_7 = 0;
	$hitu_7 = 0;
	$hits_30 = 0;
	$hitu_30 = 0;
	$html = "";

	$harr = get_optionXTRA('xtra_hits', array(array('all'=>0,'uni'=>0)));
	if (isset($harr[0]['all'])) 
		$hits = $harr[0]['all'];
	else
		$hits = 0;
	if (isset($harr[0]['uni'])) 
		$hitu = $harr[0]['uni'];
	else
		$hitu = 0;
	if (isset($harr[1]['all'])) 
		$hits_y += $harr[1]['all'];
	if (isset($harr[1]['uni'])) 
		$hitu_y += $harr[1]['uni'];

	$maxdays = get_optionXTRA('xtra_hits_enable_num', 30);
	for ($i=0;$i<7;$i++) {
		if (isset($harr[$i]['all'])) 
			$hits_7 +=  $harr[$i]['all'];
		if (isset($harr[$i]['uni'])) 
			$hitu_7 +=  $harr[$i]['uni'];
	}
	for ($i=0;$i<30;$i++) {
		if (isset($harr[$i]['all'])) 
			$hits_30 +=  $harr[$i]['all'];
		if (isset($harr[$i]['uni'])) 
			$hitu_30 +=  $harr[$i]['uni'];
	}
	for ($i=0;$i<$maxdays;$i++) {
		if (isset($harr[$i]['all'])) 
			$hits_x +=  $harr[$i]['all'];
		if (isset($harr[$i]['uni'])) 
			$hitu_x +=  $harr[$i]['uni'];
	}

	if ( !$what ) {
		$html .= "<h4>".esc_html__('Hits', 'xtra-settings') .": </h4><ul>";
		$html .= "<li>".esc_html__('today', 'xtra-settings') .": $hits, ".esc_html__('unique', 'xtra-settings') .": $hitu"."</li>";
		$html .= "<li>".esc_html__('yesterday', 'xtra-settings') .": $hits_y, ".esc_html__('unique', 'xtra-settings') .": $hitu_y"."</li>";
		if ($maxdays > 7) $html .= "<li>".esc_html__('last 7 days', 'xtra-settings') .": $hits_7, ".esc_html__('unique', 'xtra-settings') .": $hitu_7"."</li>";
		if ($maxdays > 30) $html .= "<li>".esc_html__('last 30 days', 'xtra-settings') .": $hits_30, ".esc_html__('unique', 'xtra-settings') .": $hitu_30"."</li>";
		$html .= "<li>".sprintf(esc_html__('last %s days', 'xtra-settings'),$maxdays).": $hits_x, ".esc_html__('unique', 'xtra-settings') .": $hitu_x"."</li>";
		$html .= "</ul>";
	}
	else if ( $what=="text" ) {
		$html .= "".esc_html__('today', 'xtra-settings') .": $hits, ".esc_html__('unique', 'xtra-settings') .": $hitu";
		$html .= "\n".esc_html__('yesterday', 'xtra-settings') .": $hits_y, ".esc_html__('unique', 'xtra-settings') .": $hitu_y";
		if ($maxdays > 7) $html .= "\n".esc_html__('last 7 days', 'xtra-settings') .": $hits_7, ".esc_html__('unique', 'xtra-settings') .": $hitu_7";
		if ($maxdays > 30) $html .= "\n".esc_html__('last 30 days', 'xtra-settings') .": $hits_30, ".esc_html__('unique', 'xtra-settings') .": $hitu_30";
		$html .= "\n".sprintf(esc_html__('last %s days', 'xtra-settings'),$maxdays).": $hits_x, ".esc_html__('unique', 'xtra-settings') .": $hitu_x";
	}
	else if ( $what=="tr" ) {
		$mxw = "style='width:100px;'";
		$html .= "					  <tr><th>						<td $mxw>".esc_html__('Unique', 'xtra-settings') ."	<td>".esc_html__('All', 'xtra-settings') ."			";
		$html .= "					  <tr><th>".esc_html__('today', 'xtra-settings') ."					<td>$hitu		<td>$hits		";
		$html .= "					  <tr><th>".esc_html__('yesterday', 'xtra-settings') ."				<td>$hitu_y		<td>$hits_y		";
		if ($maxdays > 7) 	$html .= "<tr><th>".esc_html__('last 7 days', 'xtra-settings') ."			<td>$hitu_7		<td>$hits_7		";
		if ($maxdays > 30) 	$html .= "<tr><th>".esc_html__('last 30 days', 'xtra-settings') ."			<td>$hitu_30	<td>$hits_30	";
		$html .= "					  <tr><th>".sprintf(esc_html__('last %s days', 'xtra-settings'),$maxdays)."	<td>$hitu_x		<td>$hits_x		";
	}
	else if ( $what=="IPs" ) {
		$IPs = get_optionXTRA('xtra_hits_IPs',array());
		$IPdata = get_optionXTRA('xtra_hits_IPdata',array());
		$max = 100;
		$stbr = "class='wordbreak'";
		if ($maxitems) $max = $maxitems;
		$k=1;
		for($i=1;$i<=$max;$i++) {
			if (!isset($IPs[$i-1]) || !$IPs[$i-1]) break;
			if ( isset($IPdata[$IPs[$i-1]]['skip']) && $IPdata[$IPs[$i-1]]['skip'] && !get_optionXTRA('xtra_hits_show_skipped_hits', 0) ) continue;
			if ($isformail == 'mail') {
				$html .= "<hr><h3>$k. <a target='_blank' title='whois data' href='https://www.whois.com/whois/".$IPs[$i-1]."'";
				$html .= ">".$IPs[$i-1]."</a></h3>";
				$html .= "<p>".xtra_hits_get_dataline( $IPdata[$IPs[$i-1]], $IPs[$i-1], "", true )."</p>"."<br/>";
			}
			else {
				$html .= "<tr><th>$k.<br><a target='_blank' title='whois data' href='https://www.whois.com/whois/".$IPs[$i-1]."'";
				//if (!$IPdata[$IPs[$i-1]]['host']) $html .= " onmouseover='xtra_do_ipdata(this.innerHTML,\"ipdata_$i\");'";
				$html .= ">".$IPs[$i-1]."</a>";
				$html .= "<td $stbr colspan='2' id='ipdata_$i'>".xtra_hits_get_dataline( $IPdata[$IPs[$i-1]], $IPs[$i-1] )."</td>"."</tr>";
			}
			$k++;
		}
		if ($isformail == 'mail') {
			$html = "<hr><h2>".sprintf(esc_html__('The last %s unique IPs today', 'xtra-settings'),($k-1))."</h2>".$html;
		}
		else {
			$html = "<tr><th colspan=3><h2>".sprintf(esc_html__('The last %s unique IPs today', 'xtra-settings'),($k-1))."</h2></tr>".$html;
		}
	}
	else if ( $what=="HITs" ) {
		$HITdata = get_optionXTRA('xtra_hits_HITdata',array());
		$max = 100;
		$stbr = "class='wordbreak'";
		if ($maxitems) $max = $maxitems;
		$i = 0;
		$k = 1;
		$hskp = "
				<span class='float-right'>
					<input class='button button-small' type='button' id='xtra_skipped_hide' value='".esc_html__('Hide skipped', 'xtra-settings') ."' />
					<input class='button button-small button-primary xtra-hd1' type='button' id='xtra_skipped_show' value='".esc_html__('Show skipped', 'xtra-settings') ."' />
				</span>
		";
		$srch = "
				<span class='m-left-30 small float-right'>
					<span id='xtra_filter_results'></span>".esc_html__('Filter', 'xtra-settings') .": <input id='xtra_filter_input' type='text' style='width:120px;margin: -5px 0px;' value='' />
					<input class='button button-small' style='position: relative;top: -2px;left: -27px;height: 26px;' type='button' id='xtra_filter_clear' value='X' />
				</span>
		";
		foreach($HITdata as $hkey => $hit) {
			if ( @$hit['skip'] && !get_optionXTRA('xtra_hits_show_skipped_hits', 0) ) continue;
			if ($i++ >= $max) break;
			if ($isformail == 'mail') {
				$html .= "<hr><h3>$k. $hkey</h3>";
				$html .= "<p>".xtra_hits_get_dataline( $hit, $hkey, "", true )."</p>"."<br/>";
			}
			else {
				if ($k == 1 && get_optionXTRA('xtra_hits_show_skipped_hits', 0) && $isformail != "dashboard") $html .= $hskp."";
				if ($k == 1 && $isformail != "dashboard") $html .= $srch."</h2></tr>";
				$html .= "<tr><th>$k.<br>$hkey";
				$html .= "<td $stbr colspan='2' id='HITdata_$i'>".xtra_hits_get_dataline( $hit, $hkey )."</td>"."</tr>";
			}
			$k++;
		}
		if ($isformail == 'mail') {
			$html = "<hr><h2>".sprintf(esc_html__('The last %s visits', 'xtra-settings'),($k-1))."</h2>".$html;
		}
		else {
			$html = "<tr><th colspan=3><h2>".sprintf(esc_html__('The last %s visits', 'xtra-settings'),($k-1))."".$html;
		}
	}
	else if ( $what=="Countries" ) {
		$HITdata = get_optionXTRA('xtra_hits_HITdata',array());
		$max = 50;
		$stbr = "";
		//$stbr = "class='wordbreak'";
		if ($maxitems) $max = $maxitems;
		$cnt = array();
		foreach($HITdata as $hkey => $hit) {
			if ( @$hit['skip'] && !get_optionXTRA('xtra_hits_show_skipped_hits', 0) ) continue;
			if ($hit['country']=="EN") $hit['country'] = "US";
			if(!isset($cnt[$hit['country']])) $cnt[$hit['country']] = 0;
				$cnt[$hit['country']]++;
		}
		arsort($cnt);
		$i = 0;
		$k = 1;
		$hitsum = 0;
		foreach($cnt as $hkey => $hit) {
			if ($i++ >= $max) break;
			if (file_exists(WP_CONTENT_DIR ."/plugins/wordfence/images/flags/".strtolower($hkey).".png"))
				$hkey = "<img src='".str_ireplace(ABSPATH,site_url()."/",WP_CONTENT_DIR)."/plugins/wordfence/images/flags/".strtolower($hkey).".png' />"." ".$hkey;
			if ($isformail == 'mail') {
				$html .= "<p>$k. $hkey = $hit</p>"."<br/>";
			}
			else {
				$html .= "<tr><th>$k. $hkey";
				$html .= "<td $stbr id='HITdata_$i'>".$hit."</td>"."</tr>";
			}
			$k++;
			$hitsum += $hit;
		}
		if ($isformail == 'mail') {
			$html = "<hr><h2>".sprintf(esc_html__('Top %s countries of %s hits', 'xtra-settings'),($k-1),$hitsum)."</h2>".$html;
		}
		else {
			$html = "<tr><th colspan=3><h2>".sprintf(esc_html__('Top %s countries of %s hits', 'xtra-settings'),($k-1),$hitsum)."".$html;
		}
	}
	else if ( $what=="IPc" ) {
		$HITdata = get_optionXTRA('xtra_hits_HITdata',array());
		$max = 50;
		$stbr = "class='wordbreak'";
		if ($maxitems) $max = $maxitems;
		$cnt = array();
		$xhit = array();
		foreach($HITdata as $hkey => $hit) {
			if ( @$hit['skip'] && !get_optionXTRA('xtra_hits_show_skipped_hits', 0) ) continue;
			if (!isset($cnt[$hit['IP']])) $cnt[$hit['IP']] = 0;
				$cnt[$hit['IP']]++;
//			if (isset($xhit[$hit['IP']]) && $hkey > $xhit[$hit['IP']]['time'] && !xtra_instr($hit['page'],"wordfence")) {
			if (!isset($xhit[$hit['IP']])) $xhit[$hit['IP']] = array();
			if (!isset($xhit[$hit['IP']]['time'])) $xhit[$hit['IP']]['time'] = "";
			
			if ($hkey > $xhit[$hit['IP']]['time'] && !xtra_instr($hit['page'],"wordfence")) {
				$xhit[$hit['IP']] = $hit;
				$xhit[$hit['IP']]['skip'] = "";
				$xhit[$hit['IP']]['time'] = $hkey;
			}
		}
		arsort($cnt);
		$i = 0;
		$k = 1;
		$hitsum = 0;
		$hskp = "
				<span class='float-right'>
					<input class='button button-small' type='button' id='xtra_skipped_hide2' value='".esc_html__('Hide skipped', 'xtra-settings') ."' />
					<input class='button button-small button-primary xtra-hd1' type='button' id='xtra_skipped_show2' value='".esc_html__('Show skipped', 'xtra-settings') ."' />
				</span>
		";
		$srch = "
				<span class='m-left-30 small float-right'>
					<span id='xtra_filter_results2'></span>".esc_html__('Filter', 'xtra-settings') .": <input id='xtra_filter_input2' type='text' style='width:120px;margin: -5px 0px;' value='' />
					<input class='button button-small' style='position: relative;top: -2px;left: -27px;height: 26px;' type='button' id='xtra_filter_clear2' value='X' />
				</span>
		";
		foreach($cnt as $hkey => $hit) {
			if ($i++ >= $max) break;
			if ($isformail == 'mail') {
				$html .= "<p>$k. ".xtra_hits_get_dataline( $xhit[$hkey], "", "IP", true )." = $hit</p>"."<br/>";
			}
			else {
				if ($k == 1 && get_optionXTRA('xtra_hits_show_skipped_hits', 0) && $isformail != "dashboard") $html .= $hskp."";
				if ($k == 1 && $isformail != "dashboard") $html .= $srch."</h2></tr>";
				$html .= "<tr><th>$k.<br>$hkey";
				$html .= "<td $stbr id='HITdata_$i'>".xtra_hits_get_dataline( $xhit[$hkey], "", "IP" )."</td>";
				$html .= "<td id='HITcdata_$i'>".$hit."</td>"."</tr>";
			}
			$k++;
			$hitsum += $hit;
		}
		if ($isformail == 'mail') {
			$html = "<hr><h2>".sprintf(esc_html__('Top %s IPs of %s hits', 'xtra-settings'),($k-1),$hitsum)."</h2>".$html;
		}
		else {
			$html = "<tr><th colspan=3><h2>".sprintf(esc_html__('Top %s IPs of %s hits', 'xtra-settings'),($k-1),$hitsum)."".$html;
		}
	}
	else if ( $what=="Pages" ) {
		$HITdata = get_optionXTRA('xtra_hits_HITdata',array());
		$max = 50;
		$stbr = "";
		//$stbr = "class='wordbreak'";
		if ($maxitems) $max = $maxitems;
		$cnt = array();
		foreach($HITdata as $hkey => $hit) {
			if ( @$hit['skip'] && !get_optionXTRA('xtra_hits_show_skipped_hits', 0) ) continue;
			$hit['page'] = preg_replace('#\?.*#ui','',$hit['page']);
			if (!isset($cnt[$hit['page']])) $cnt[$hit['page']] = 0;
				$cnt[$hit['page']]++;
		}
		arsort($cnt);
		$i = 0;
		$k = 1;
		$hitsum = 0;
		foreach($cnt as $hkey => $hit) {
			if ($i++ >= $max) break;
			$pagea1 = "<a target='_blank' title='". esc_html__('Click: open page', 'xtra-settings') ."' href='".XTRA_HOST.$hkey."'>";
			$a2 = "</a>";
			//$hkey = $pagea1.$hkey.$a2;
			if ($isformail == 'mail') {
				$html .= "<p>$k. ".$pagea1.$hkey.$a2." = $hit</p>"."<br/>";
			}
			else {
				$html .= "<tr><th>$k. ".$pagea1.xtra_toolong($hkey,1,150).$a2.xtra_toolong($hkey,2,150);
				$html .= "<td $stbr id='HITdata_$i'>".$hit."</td>"."</tr>";
			}
			$k++;
			$hitsum += $hit;
		}
		if ($isformail == 'mail') {
			$html = "<hr><h2>".sprintf(esc_html__('Top %s pages of %s hits', 'xtra-settings'),($k-1),$hitsum)."</h2>".$html;
		}
		else {
			$html = "<tr><th colspan=3><h2>".sprintf(esc_html__('Top %s pages of %s hits', 'xtra-settings'),($k-1),$hitsum)."".$html;
		}
	}
	else if ( $what=="chart" ) {
		$html = "
	<script type='text/javascript'>
		// Load the Visualization API and the corechart package.
		google.charts.load('current', {'packages':['corechart']});
		// Set a callback to run when the Google Visualization API is loaded.
		google.charts.setOnLoadCallback(xtra_drawChart);

		function xtra_drawChart() {
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Date');
			data.addColumn('number', 'All');
			data.addColumn('number', 'Unique');
			data.addRows([
		";
				$harr = get_optionXTRA('xtra_hits', array(array('all'=>0,'uni'=>0)));
				for ($i=count((array)$harr);$i>=1;$i--) {
					$hitday = date( "j-M", strtotime("-".($i-1)." days") );
					$hitdayarr = $harr[$i-1];
					if (!$hitdayarr['all']) $hitdayarr['all'] = 0;
					if (!$hitdayarr['uni']) $hitdayarr['uni'] = 0;
					$html .= "['".$hitday."',".$hitdayarr['all'].",".$hitdayarr['uni']."],";
				}
		$html .= "
			]);
			var options = {title:'".esc_html__('Hits', 'xtra-settings') ." - ".sprintf(esc_html__('Last %s days', 'xtra-settings'),get_optionXTRA('xtra_hits_enable_num'))."',
							legend:'none',
							pointSize: 4,
							chartArea:{left:'13%',width:'83%'},
							width:'100%',
							height:200};
			var chart = new google.visualization.AreaChart(document.getElementById('xtra_hits_chart_div'));
			chart.draw(data, options);

			function xtra_resizeChart() {
				chart.draw(data, options);
			}
			if (document.addEventListener) {
				window.addEventListener('resize', xtra_resizeChart);
			}
			else if (document.attachEvent) {
				window.attachEvent('onresize', xtra_resizeChart);
			}
			else {
				window.resize = xtra_resizeChart;
			}
		}
	</script>
	";
	$html .= '<div id="xtra_hits_chart_div" onclick="window.dispatchEvent(new Event(\'resize\'));"></div>
		';
	}
	else if ( $what=="hits" )
		$html = $hits;
	else if ( $what=="hitu" )
		$html = $hitu;

	if ($xtra_do_memlog) xtra_memlog("   - xtra_hits_show: ".$what);

	return $html;
}
function xtra_hits_get_dataline($darr, $prev="", $force_dtype="", $formail=false) {
	if ( !is_array($darr) ) return;
	if ($prev) {
		if ( !isset($darr['IP']) || !$darr['IP'] ) {
			$darr['IP'] = $prev;
			$dtype = "IP";
		}
		if ( !isset($darr['time']) || !$darr['time'] ) {
			$darr['time'] = $prev;
			$dtype = "date";
		}
	}
	if ($force_dtype) $dtype = $force_dtype;
	if ($dtype == "IP") $darr['skip'] = xtra_hits_check_skipped($darr);

	$darr['page'] = esc_html($darr['page']);

	if ($darr['country']=="EN") $darr['country'] = "US";
	$city = $darr['city'] ? " ".$darr['city'] : "";
	$whoisa1 = "<a target='_blank' title='". esc_html__('Click: Whois IP data', 'xtra-settings') ."' href='https://www.whois.com/whois/".$darr['IP']."'>";
	//$hosta1 = "<a target='_blank' title='".$darr['country']."\n". esc_html__('Click: GeoIP data', 'xtra-settings') ."' href='https://freegeoip.app/?q=".$darr['IP']."'>";
	$hosta1 = "<a target='_blank' title='".$darr['country']."\n". esc_html__('Click: GeoIP data', 'xtra-settings') ."' href='https://ipinfo.io/".$darr['IP']."'>";
	//$pagea1 = "<a target='_blank' title='". esc_html__('Click: open page', 'xtra-settings') ."' href='".home_url().$darr['page']."'>";
	$pagea1 = "<a target='_blank' title='". esc_html__('Click: open page', 'xtra-settings') ."' href='".XTRA_HOST.$darr['page']."'>";
	$refa1 = "<a target='_blank' title='". esc_html__('Click: open page', 'xtra-settings') ."' href='".(xtra_instr($darr['ref'],'://') ? '' : home_url()).$darr['ref']."'>";
	$usra1 = "<a target='_blank' title='". esc_html__('Click: edit user', 'xtra-settings') ."' href='".site_url()."/wp-admin/user-edit.php?user_id=".$darr['userid']."'>";

	$a2 = "</a>";

	$usr = "";
	if ($darr['userid']) $usr = $usra1.$darr['username'].$a2." ";

	$page = $pagea1.xtra_toolong($darr['page'],1,100).$a2.xtra_toolong($darr['page'],2,100);
	$time = $darr['time'];
	$time = str_replace(date("Y-m-d"),"".esc_html__('Today', 'xtra-settings') .",",$time);
	$time = str_replace(date("Y-m-d",strtotime("-1 days")),"".esc_html__('Yesterday', 'xtra-settings') .",",$time);
	$IP = $whoisa1.$darr['IP'].$a2;
	if ($darr['host'] != $darr['IP']) $host = $darr['host'];
	if (file_exists(WP_CONTENT_DIR ."/plugins/wordfence/images/flags/".strtolower($darr['country']).".png")) {
		$darr['country'] = "<img src='".str_ireplace(ABSPATH,site_url()."/",WP_CONTENT_DIR)."/plugins/wordfence/images/flags/".strtolower($darr['country']).".png' />"." ".$darr['country'];
		$ctry = $hosta1.$darr['country'].$city.$a2;
	}
	else {
		$ctry = $hosta1."[".$darr['country'].$city."]".$a2;
	}
	$server = "$ctry | $IP";
	if (isset($host) && $host) $server .= " | $host";
	if ( substr($darr['platform'],-7) == "Windows") $darr['platform'] = xtra_request_anal( "platform", array('ua'=>$darr['user']) );
	$browser = $darr['platform'].", ".$darr['browser'];
	if ($darr['user'] && !$formail) $browser .= " <span title='".$darr['user']."' onclick='this.innerHTML=\""."<small>(".$darr['user'].")</small>"."\"'><div class='small dashicons dashicons-star-empty'></div></span>";

	$disa = "";
	$disatxt = "";
	$disatxt2 = "";
	$disaicon = "";
	if ($darr['skip']) $disa = "text-disabled";
	if ($darr['skip']) $disatxt = "<b class='sbold $disa'>"."".esc_html__('Skipped for', 'xtra-settings') .": </b><b>".substr($darr['skip'],0,-2)."</b><br/>";
	if ($darr['skip']) $disatxt2 = "<span style='font-size:0px;'>xtra_skipped_hit</span>"; // for jquery textsearch in admin-search.js
	else $disatxt2 = "<span style='font-size:0px;'>unskipped human hit</span>"; // for jquery textsearch
	$dsty = "style='font-size:3em; width:1.2em; height:80px;'";
	if ($darr['skip']) {
		if (xtra_instr($darr['skip'],'Admin')) $disaicon = 				"<div title='admin hit' 	$dsty class='disaicon gray dashicons dashicons-admin-generic'></div>";
		elseif (xtra_instr($darr['skip'],'Cron')) $disaicon = 			"<div title='cron hit' 		$dsty class='disaicon gray dashicons dashicons-clock'></div>";
		elseif (xtra_instr($darr['skip'],'Ajax')) $disaicon = 			"<div title='ajax hit' 		$dsty class='disaicon gray dashicons dashicons-controls-repeat'></div>";
		elseif (xtra_instr($darr['skip'],'Less than 1sec')) $disaicon = "<div title='less 1sec' 	$dsty class='disaicon gray dashicons dashicons-backup'></div>";
		elseif (xtra_instr($darr['skip'],'Same')) $disaicon = 			"<div title='same Page' 	$dsty class='disaicon gray dashicons dashicons-backup'></div>";
		elseif (xtra_instr($darr['skip'],'URI undefined')) $disaicon = 	"<div title='uri undef.' 	$dsty class='disaicon red dashicons dashicons-editor-strikethrough'></div>";
		elseif (xtra_instr($darr['skip'],'Block')) {
			$strs = $darr['host'].$darr['user'];
			if (xtra_instr($strs,array("google","feedburner"))) 	$disaicon = 	"<div title='block word' 	$dsty class='disaicon dark-red dashicons dashicons-googleplus'></div>";
			elseif (xtra_instr($strs,"faceb")) 	$disaicon = 	"<div title='block word' 	$dsty class='disaicon dark-red dashicons dashicons-facebook-alt'></div>";
			elseif (xtra_instr($strs,"twit")) 	$disaicon = 	"<div title='block word' 	$dsty class='disaicon dark-red dashicons dashicons-twitter'></div>";
			elseif (xtra_instr($strs,"pinterest")) 	$disaicon = "<div title='block word' 	$dsty class='disaicon dark-red dashicons dashicons-admin-post'></div>";
			elseif (xtra_instr($strs,"bing") && !xtra_instr($strs,"bubing")) 	$disaicon = 	"<div title='block word' 	$dsty class='disaicon dark-red dashicons dashicons-screenoptions'></div>";
			else 								$disaicon = 	"<div title='block word' 	$dsty class='disaicon dark-red dashicons dashicons-visibility'></div>";
		}
	}
	else {
		//$disaicon = "<div title='hit' $dsty class='disaicon green dashicons dashicons-thumbs-up'></div>";
		if ($darr['userid']) 
			$disaicon = "<div title='hit' $dsty class='disaicon orange dashicons dashicons-admin-users'></div>";
		else
			$disaicon = "<div title='hit' $dsty class='disaicon green dashicons dashicons-admin-users'></div>";
	}

	$ref = "";

	if ($darr['ref'] && $darr['ref'] != $page) $ref = "<br>".esc_html__('from', 'xtra-settings') ." <b class='sbold $disa'>".$refa1.xtra_toolong($darr['ref'],1,100).$a2.xtra_toolong($darr['ref'],2,100)."</b>";

	if ($dtype == "IP")
		$html = $disaicon."$disatxt<b class='sbold $disa'>".$usr."$server</b><br/>".esc_html__('Last hit', 'xtra-settings') .": <b class='xbold $disa'>$time</b> ".esc_html__('on', 'xtra-settings') ." <b class='xbold $disa'>$page</b>$ref<br/>$browser" .$disatxt2;
	if ($dtype == "date")
		$html = $disaicon."$disatxt<b class='xbold $disa'>$time</b> ".esc_html__('on', 'xtra-settings') ." <b class='xbold $disa'>$page</b><br/><b class='sbold $disa'>".$usr."$server</b>$ref<br/>$browser" .$disatxt2;
	return $html;
}

function xtra_hits_check_skipped($darr) {
	$skip_hit = "";
	if (empty($darr)) return;
	if ( get_optionXTRA('xtra_hits_exclude_ajax',0) && xtra_instr($darr['page'],"admin-ajax") ) {
		$skip_hit .= "Ajax-hit, ";
	}
	elseif ( get_optionXTRA('xtra_hits_exclude_cron',0) && xtra_instr($darr['page'],"wp-cron") ) {
		$skip_hit .= "Cron-hit, ";
	}
	elseif ( get_optionXTRA('xtra_hits_exclude_admin',0) && xtra_instr($darr['page'],"wp-admin") ) {
		$skip_hit .= "Admin-hit, ";
	}
	elseif ( get_optionXTRA('xtra_hits_exclude_string',0) ) {
		$tdef = get_optionXTRA('xtra_hits_exclude_string_text','');
		if (!$tdef) $tdef = "robot\ncrawler\nspider\nslurp\ngooglebot\nfacebookexternalhit\nfacebot\npinterestbot";
		$excl = get_optionXTRA('xtra_hits_exclude_string_texta',$tdef);
		$excl = explode("\n",$excl);

		$rem_serv = $darr['host'];

		if (!isset($rem_serv) && get_optionXTRA('xtra_hits_lookup_hostnames',0)) {
			$rem_serv = @gethostbyaddr($darr['IP']); //maybe slow
		}
		$chstr = $darr['user']." ".$darr['IP']." ".$darr['page'];
		if ( isset($darr['host']) ) $chstr .= " ".$darr['host'];
		if ( isset($rem_serv) && $rem_serv ) $chstr .= " ".$rem_serv;
		foreach((array)$excl as $block) {
			if (!$block) continue;
			if ( preg_match( "#".$block."#i", $chstr ) ) {
				$skip_hit .= "Block-word: '".$block."', ";
				break;
			}
		}
	}
	return $skip_hit;
}

function xtra_hits_geoip_force() {
	$IPdata = get_optionXTRA('xtra_hits_IPdata',array());
	foreach((array)$IPdata as $key => $val) {
		if (!$key) continue;
		$time_start = microtime(true);
		$geo = @file_get_contents("https://freegeoip.app/json/".$key."");
		xtra_time_measure('xtra_geoiplookup_avg',$time_start);
		if (!$geo) continue;
		$geo = json_decode($geo,true);
		//{"ip":"192.30.253.113","country_code":"US","country_name":"United States","region_code":"CA","region_name":"California","city":"San Francisco","zip_code":"94107","time_zone":"America/Los_Angeles","latitude":37.7697,"longitude":-122.3933,"metro_code":807}
		if (isset($geo['country_code'])) {
			$IPdata[$key]['country'] = $geo['country_code'];
			$IPdata[$key]['city'] = $geo['city'];
		}
	}
	update_optionXTRA( 'xtra_hits_IPdata', $IPdata );

/*
	$HITdata = get_optionXTRA('xtra_hits_HITdata',array());
	foreach((array)$HITdata as $key => $val) {
		if (!$key) continue;
		$time_start = microtime(true);
		$geo = @file_get_contents("https://freegeoip.app/json/".$val['IP']."");
		xtra_time_measure('xtra_geoiplookup_avg',$time_start);
		if (!$geo) continue;
		$geo = json_decode($geo,true);
		//{"ip":"192.30.253.113","country_code":"US","country_name":"United States","region_code":"CA","region_name":"California","city":"San Francisco","zip_code":"94107","time_zone":"America/Los_Angeles","latitude":37.7697,"longitude":-122.3933,"metro_code":807}
		if (isset($geo['country_code'])) {
			$HITdata[$key]['country'] = $geo['country_code'];
			$HITdata[$key]['city'] = $geo['city'];
		}
	}
	update_optionXTRA( 'xtra_hits_HITdata', $HITdata );
	*/
}

if( get_optionXTRA('xtra_hits_dashboard_widget') ) {
	function xtra_hits_dashboard_widget_function() {
		echo '
			'. (get_optionXTRA('xtra_show_hit_chart',0) ? xtra_hits_show("chart") : '') .'
			<div style="text-align: center;">
				<a class="button button-xsmall button-primary" onclick="event.stopPropagation();return true;" href="'.wp_nonce_url('admin.php?page=xtra&hitlist=1','mynonce').'">'. esc_html__('Show Hits Data Analysis', 'xtra-settings') .'</a>
				<a class="button button-xsmall button-primary" onclick="event.stopPropagation();return true;" href="'.wp_nonce_url('admin.php?page=xtra&xtra_seltab=6','mynonce').'">'. esc_html__('XTRA Hits Options', 'xtra-settings') .'</a>
			</div>
			<div class="_xtra widget_box" onclick="this.style.maxHeight=\'450px\';">
				<table class="wp-list-table widefat striped xtra">
					'. xtra_hits_show("tr") .'
					'. (get_optionXTRA('xtra_hits_show_ips',0) ? xtra_hits_show("IPs",get_optionXTRA('xtra_hits_show_ips_num',10)) : '') .'
					'. (get_optionXTRA('xtra_hits_show_hitsdata',0) ? xtra_hits_show("HITs",get_optionXTRA('xtra_hits_show_hitsdata_num',50),"dashboard") : '') .'
				</table>
			</div>
		';
	}
	function xtra_hits_add_dashboard_widgets() {
		wp_add_dashboard_widget( 'xtra_hits__dashboard_widget', ''. esc_html__('Hits Counter', 'xtra-settings') .' by XTRA', 'xtra_hits_dashboard_widget_function' );
	}
	add_action( 'wp_dashboard_setup', 'xtra_hits_add_dashboard_widgets' );
}


//---Related posts---

if( get_optionXTRA('xtra_related_posts') ) {
	function xtra_add_related_posts_css() {
		wp_enqueue_style( 'xtra_related_posts_css', plugin_dir_url( __FILE__ ) . 'assets/css/related-posts.css' );
	}
	add_action( 'wp_enqueue_scripts', 'xtra_add_related_posts_css' );

	function xtra_get_related_posts($num=4, $title="Related Posts", $size=150) {
	// Related Posts: Based on current post's category, tags. And it shouldnt show the current post.
		global $post;
		$postcat = get_the_category( $post->ID ); 	// Geting the current post's category lists if exists.
		$category_ids = $postcat[0]->term_id;	// getting cat ids
		$dontshow_cats = get_optionXTRA('xtra_relposts_cat_exclude', array());	// dont show on these categories
		foreach( (array)$category_ids as $tcatid ) if ( in_array($tcatid,$dontshow_cats) ) return;	// filter

		$excl_cats = get_optionXTRA('xtra_categories_exclude', array());
		$post_tags = wp_get_post_tags($post->ID, array( 'fields' => 'ids' ));	// Geting the current post's tags lists if exists.

		global $wp_query;
		$tmp_query = $wp_query;	// Put default query object in a temp variable
		$wp_query = null;	// Now wipe it out completely

		$related_post = array(
			'post_type' =>'post',
			'category__in' => $category_ids,
			'category__not_in' => $excl_cats,
			'tag__in' => $post_tags,
			'posts_per_page'=>$num,
			'orderby' => 'rand',
			'post__not_in' => array($post->ID)
		);
		$the_query_related_post = new WP_Query( $related_post );
		if ( $the_query_related_post->found_posts < $num ) {
			$related_post = array(
				'post_type' =>'post',
				'category__in' => $category_ids,
				'category__not_in' => $excl_cats,
				'posts_per_page'=>$num,
				'orderby' => 'rand',
				'post__not_in' => array($post->ID)
			);
			$the_query_related_post = new WP_Query( $related_post );
			if ( !get_optionXTRA('xtra_related_posts_nocatmix') && $the_query_related_post->found_posts < $num ) {
				$related_post = array(
					'post_type' =>'post',
					'category__not_in' => $excl_cats,
					'posts_per_page'=>$num,
					'orderby' => 'rand',
					'post__not_in' => array($post->ID)
				);
				$the_query_related_post = new WP_Query( $related_post );
			}
		}

		$wp_query = $the_query_related_post;	// Re-populate the global with our custom query

		if ( $the_query_related_post->have_posts() ) {
			if ( function_exists('pll__') ) $title = pll__($title);
			$html .= '
			<div class="xtra_rp_wrap">
				<div class="xtra_rp_content">
					<h3 class="related_post_title">'.$title.'</h3>
					<ul class="xtra_rp">
			';
			while ( $the_query_related_post->have_posts() ) {
				$the_query_related_post->the_post();
				$thumb = get_the_post_thumbnail($the_query_related_post->ID, array($size,$size));
				$altt = get_the_title();
				preg_match('/ title=["\'](.*?)["\']/i', $thumb, $img);
				if(!isset($img[1]) || $img[1] == '') $thumb = str_ireplace('<img','<img title="'.$altt.'"',$thumb);
				$thumb = str_ireplace(array(' title=""'," title=''"), "", $thumb);
				if ( strpos($altt, get_bloginfo('name'))===FALSE ) $altt .= " | " . get_bloginfo('name');
				preg_match('/ alt=["\'](.*?)["\']/i', $thumb, $img);
				if(!isset($img[1]) || $img[1] == '') $thumb = str_ireplace('<img','<img alt="'.$altt.'"',$thumb);
				$thumb = str_ireplace(array(' alt=""'," alt=''"), "", $thumb);
				$html .= '
				<li>
					<a href="'.get_the_permalink().'">'.$thumb.'</a>
					<a href="'.get_the_permalink().'" class="xtra_rp_title" style="max-width:'.$size.'px !important;">'.get_the_title().'</a>
				</li>
				';
			}
			$html .= '
					</ul>
				</div>
			</div>
			';
			wp_reset_postdata();
		}

		// Restore original query object
		$wp_query = null;
		$wp_query = $tmp_query;
		return $html;
	}

	function xtra_related_posts($buffer) {
		$plac = get_optionXTRA('xtra_related_posts_place', 0);
		if (!xtra_check_pagetype("related_posts",$plac)) return $buffer;

		$num = get_optionXTRA('xtra_related_posts_num', 0);
		$tit = get_optionXTRA('xtra_related_posts_text', '');
		$siz = get_optionXTRA('xtra_related_posts_size_num', 150);
		if (!$num) return $buffer;

		$html = xtra_get_related_posts($num,$tit,$siz);
		if (!$html) return $buffer;
		//$html = utf8_encode($html); // $html comes from UTF-8 text (database)

		$hed = substr($buffer,0,stripos($buffer,"</head>")+7);
		$buffer = str_replace($hed,'',$buffer);

		$buffer = xtra_html_places($buffer,$html,$plac,"xtra_related_posts");

		return $hed.$buffer;
	}

	//alternative for before text and after text positions
	function xtra_content3_addi( $content ) {
		$plac = get_optionXTRA('xtra_related_posts_place', 0);
		if (!xtra_check_pagetype("related_posts",$plac,1)) return $content;

		$num = get_optionXTRA('xtra_related_posts_num', 0);
		$tit = get_optionXTRA('xtra_related_posts_text', '');
		$siz = get_optionXTRA('xtra_related_posts_size_num', 150);
		if (!$num) return $buffer;

		$html = xtra_get_related_posts($num,$tit,$siz);
		
		//$html = utf8_encode($html); // $html comes from UTF-8 text (database)

		if ($plac == 2) $content = $html . $content;
		elseif ($plac == 1) $content = $content . $html;

		return $content;
	}
	add_filter( 'the_content', 'xtra_content3_addi', 99 );

}

if ($xtra_do_memlog) xtra_memlog("Features");



//---8. Posts---


//Revisions
if (get_optionXTRA( 'xtra_revisions_to_keep', 0 )) {
	function xtra_revisions_to_keep( $revisions, $post ) {
		$pnum = get_optionXTRA( 'xtra_revisions_to_keep_pnum', 99 );
		$num = get_optionXTRA( 'xtra_revisions_to_keep_num', 99 );

		if ( 'page' == $post->post_type )
			return $pnum;
		else
			return $num;
	}
	add_filter( 'wp_revisions_to_keep', 'xtra_revisions_to_keep', 10, 2 );
}

function xtra_autosave_interval( $bool, $txt="" ){
	$opt = "xtra_autosave_interval";
	if ($bool && !$txt) $txt = stripslashes_deep(sanitize_text_field($_POST[$opt.'_num']));
	$ins = "define('AUTOSAVE_INTERVAL', $txt);";
	return xtra_wpconfig($bool,$opt,$ins);
}

function xtra_empty_trash( $bool, $txt="" ){
	$opt = "xtra_empty_trash";
	if ($bool && !$txt) $txt = stripslashes_deep(sanitize_text_field($_POST[$opt.'_num']));
	$ins = "define('EMPTY_TRASH_DAYS', $txt);";
	return xtra_wpconfig($bool,$opt,$ins);
}

//. Require a Featured Image
if (get_optionXTRA( 'xtra_require_featured_image', 0 )) {
	add_action('publish_post', 'xtra_check_thumbnail', 99);
	//add_action('save_post', 'xtra_check_thumbnail');
	add_action('admin_notices', 'xtra_thumbnail_error');
	function xtra_check_thumbnail($post_id) {
		// change to any custom post type
		if(get_post_type($post_id) != 'post') return;
		if(get_post_status($post_id) == 'trash') return;
		if ( !has_post_thumbnail( $post_id ) ) {
			// set a transient to show the users an admin message
			set_transient( "has_post_thumbnail", "no" );
			// unhook this function so it doesn't loop infinitely
			remove_action('publish_post', 'xtra_check_thumbnail');
			//remove_action('save_post', 'xtra_check_thumbnail');
			// update the post set it to draft
			wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));
			add_action('publish_post', 'xtra_check_thumbnail', 99);
			//add_action('save_post', 'xtra_check_thumbnail');
		} else {
			delete_transient( "has_post_thumbnail" );
		}
	}
	function xtra_thumbnail_error()
	{
		// check if the transient is set, and display the error message
		if ( get_transient( "has_post_thumbnail" ) == "no" ) {
			echo "<div id='message' class='error'><p><strong>".esc_html__('You must select a Featured Image. Your Post is saved but it can not be published.', 'xtra-settings') ."</strong></p></div>";
			delete_transient( "has_post_thumbnail" );
		}
	}
}

if (get_optionXTRA( 'xtra_auto_featured_image', 0 )) {
	function xtra_auto_featured_image() {
		global $post;
		$already_has_thumb = has_post_thumbnail($post->ID);
		if (!$already_has_thumb) {
			$attachments = get_children(array(
				'post_parent' => $post->ID,
				'post_status' => 'inherit',
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'order' => 'ASC',
				'orderby' => 'menu_order'
			));
			if ($attachments) {
				foreach ($attachments as $attachment) {
					set_post_thumbnail($post->ID, $attachment->ID);
					break;
				}
			}
		}
	}
	add_action('the_post', 'xtra_auto_featured_image');
	add_action('save_post', 'xtra_auto_featured_image', 9);
	add_action('draft_to_publish', 'xtra_auto_featured_image');
	add_action('new_to_publish', 'xtra_auto_featured_image');
	add_action('pending_to_publish', 'xtra_auto_featured_image');
	add_action('future_to_publish', 'xtra_auto_featured_image');
}

//Allow PHP in Default Text Widgets
if (get_optionXTRA( 'xtra_php_in_textwidgets', 0 )) {
	add_filter('widget_text', 'xtra_php_in_textwidgets', 100);
	function xtra_php_in_textwidgets($text) {
		$ttext = html_entity_decode($text);
		if (strpos($ttext, "<"."?") !== false) {
			ob_start();
			//set_error_handler("xtra_warning_handler", E_WARNING);
			set_error_handler("xtra_warning_handler", E_NOTICE);
			@eval("?".">".$ttext);
			restore_error_handler();
			$text = ob_get_contents();
			ob_end_clean();
		}
		return $text;
	}
	function xtra_warning_handler($errno, $errstr) { 
	// do something
	}

}

//. Enable shortcodes in text widgets
if (get_optionXTRA( 'xtra_shortcode_in_textwidgets', 0 )) {
	add_filter('widget_text','do_shortcode');
}

//Disallow Duplicate Post Titles
if (get_optionXTRA( 'xtra_disallow_duplicate_posttitles', 0 )) {
	function xtra_disallow_duplicate_posttitles($messages) {
		global $post;
		global $wpdb ;
		$title = $post->post_title;
		$post_id = $post->ID ;
		$wtitlequery = "SELECT post_title FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' AND post_title = '{$title}' AND ID != {$post_id} " ;

		$wresults = $wpdb->get_results( $wtitlequery) ;

		if ( $wresults ) {
			$error_message = ''. esc_html__('This title is already used. Post cannot be published. Please choose another title.', 'xtra-settings') .'';
			add_settings_error('post_has_links', '', $error_message, 'error');
			settings_errors( 'post_has_links' );
			$post->post_status = 'draft';
			wp_update_post($post);
			return;
		}
		return $messages;

	}
	//add_action('post_updated_messages', 'xtra_disallow_duplicate_posttitles');
	add_action('publish_post', 'xtra_disallow_duplicate_posttitles');
}


//---HTML Content Changes

//Open ALL links in a new tab
if (get_optionXTRA( 'xtra_link_new_tab', 0 )) {
	function xtra_link_new_tab($text) {
		$surl = home_url();
		$return = $text;
		$return = str_ireplace('target="_blank"', 				'', 						$return);
		$return = str_ireplace('href=', 						'target="_blank" href=', 	$return);
		$return = str_ireplace('target="_blank" href="'.$surl, 	'href="'.$surl, 			$return);
		$return = str_ireplace('target="_blank" href=\''.$surl, 'href=\''.$surl, 			$return);
		$return = str_ireplace('target="_blank" href="javascript', 		'href="javascript', 					$return);
		$return = str_ireplace('target="_blank" href="#', 		'href="#', 					$return);
		$return = str_ireplace('target="_blank" href=\'#', 		'href=\'#', 				$return);
		$return = str_ireplace('target="_blank" href="/', 		'href="/', 					$return);
		$return = str_ireplace('target="_blank" href=\'/', 		'href=\'/', 				$return);
		//$return = str_ireplace(' target="_blank">', 			'>', 						$return);
		return $return;
	}
	add_filter('the_content', 'xtra_link_new_tab', 19);
	add_filter('comment_text', 'xtra_link_new_tab', 19);
}

//Add self-link to all images
if (get_optionXTRA( 'xtra_attachment_image_link_filter', 0 )) {
	add_filter( 'the_content', 'xtra_attachment_image_link_filter', 8 );
	add_filter( 'post_thumbnail_html', 'xtra_attachment_image_link_filter', 20, 1 );
	function xtra_attachment_image_link_filter( $content ) {
		if (is_admin() && !xtra_is_customize_preview()) return $content;
		if (is_front_page()) return $content;
		if (!is_singular()) return $content;
		if (get_the_title() == 'Posts') return $content;

	//	$upload_dir = XTRA_UPLOAD_DIR;
	//	$upld = $upload_dir.'/';
	//	$upld = str_ireplace(ABSPATH,site_url()."/",$upld);
		$upload_dir = XTRA_UPLOAD_URL;
		$upld = $upload_dir.'/';

		$upld = ""; //for ALL images - not just the ones in upload_dir

		$content = preg_replace(
				'#<a[^>]*?href=[^>]*("|\')[^>]*>\s*<img([^>]*)src="([^>]*)'.$upld.'(.*?)</a>#umis',
				'<img$2src="$3'.$upld.'$4',
		$content);
		$content = preg_replace(
			'#<img([^>]*)src="([^>]*)'.$upld.'([^>]*)("|\')([^>]*)>#umis',
			'<a href="$2'.$upld.'$3" data-rel="lightbox-gallery-postimages"><img$1src="$2'.$upld.'$3"$5></a>',
		$content);
		$content = preg_replace(
			'#<a([^>]*)(-\d+x\d+)\.(jpe?g|png|gif)([^>]*)>\s*<img#umis',
			'<a$1.$3$4><img',
		$content);
		return $content;
	}
}

// Highlight Post Color by Status
if (get_optionXTRA( 'xtra_posts_status_color', 0 )) {
	function xtra_posts_status_color() {
		$html = '
			<style>
			.status-draft{background:'.get_optionXTRA('xtra_posts_status_color1','#FCE3F2').' !important;}
			.status-pending{background:'.get_optionXTRA('xtra_posts_status_color2','#f2e0ab').' !important;}
			.status-future{background:'.get_optionXTRA('xtra_posts_status_color3','#C6EBF5').' !important;}
			.status-private{background:'.get_optionXTRA('xtra_posts_status_color4','#b49de0').' !important;}
			.status-publish{/* no background keep wp alternating colors */}
			</style>
		';
		echo $html;
		return;
	}
	add_action('admin_footer','xtra_posts_status_color');
}

//--- Add thumbnails to post list ---
if( get_optionXTRA('xtra_add_thumb_column') ) {
	if ( !function_exists('xtra_AddThumbColumn') && function_exists('add_theme_support') ) {
		// First, check if current theme support post thumbnails
		function xtra_check_post_thumbnails() {
			// If current theme does not support post thumbnails
			if(!current_theme_supports('post-thumbnails')) {
				// Add post thumbnail support
				add_theme_support('post-thumbnails', array( 'post', 'page' ) );
			}
		}
		add_action('after_theme_setup', 'xtra_check_post_thumbnails');
		function xtra_AddThumbColumn($cols) {
			//$cols['thumbnail'] = esc_html__('Thumbnail', 'xtra-settings');
			//return $cols;
			$new = array();
			foreach($cols as $key => $title) {
				if ($key=='author') // Put the Thumbnail column before the Author column
				//if ($key=='title') // Put the Thumbnail column before the Author column
					$new['thumbnail'] = 'Thumbnail';
				$new[$key] = $title;
			}
			return $new;
		}
		function xtra_AddThumbValue($column_name, $post_id) {
			if ( 'thumbnail' == $column_name ) {
				$size = get_optionXTRA('xtra_add_thumb_column_num',70);
				$thumb = get_the_post_thumbnail($post_id, array($size,$size));
				if ( isset($thumb) && $thumb ) { echo $thumb; }
				else { echo 'None'; }
			}
		}
		// for posts
		add_filter( 'manage_posts_columns', 'xtra_AddThumbColumn' );
		add_action( 'manage_posts_custom_column', 'xtra_AddThumbValue', 10, 2 );
		// for pages
		add_filter( 'manage_pages_columns', 'xtra_AddThumbColumn' );
		add_action( 'manage_pages_custom_column', 'xtra_AddThumbValue', 10, 2 );

		add_action('admin_head', 'xtra_mytheme_admin_head');
		function xtra_mytheme_admin_head() {
			global $post_type;
			if ( TRUE || 'my_custom_post_type' == $post_type ) {
				$size = get_optionXTRA('xtra_add_thumb_column_num',70);
				?><style type="text/css"> .column-thumbnail { width: <?php echo $size; ?>px; } </style><?php
			}
		}
	}
}

if( get_optionXTRA('xtra_auto_shortcodes_select') ) {
	add_action('media_buttons','add_sc_select',11);
	function add_sc_select() {
		global $shortcode_tags;
		$exclude = array("wp_caption", "embed");
		echo '&nbsp;<select id="sc_select"><option>Shortcode</option>';
		$shortcodes_list = "";
		foreach ((array)$shortcode_tags as $key => $val) {
			if(!in_array($key,$exclude)) {
				if ($key == "button")
					$shortcodes_list .= '<option value="['.$key.' title=\"\" link=\"http://www.\" icon=\"new_window_alt\" color=\"blue\"]text[/'.$key.']">'.$key.'</option>';
				else
					$shortcodes_list .= '<option value="['.$key.'][/'.$key.']">'.$key.'</option>';
			}
		}
		 echo $shortcodes_list;
		 echo '</select>';
	}
	add_action('admin_head', 'button_js');
	function button_js() {
		echo '<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#sc_select").change(function() {
				send_to_editor(jQuery("#sc_select :selected").val());
				return false;
			});
		});
		</script>';
	}
}

if( get_optionXTRA('xtra_notify_author_on_publish') ) {
	function xtra_notify_author_on_publish($post_id){
		$post_author_id = get_post_field( 'post_author', $post_id );
		$email_address = get_the_author_meta('user_email', $post_author_id);

		$subject = get_optionXTRA('xtra_notify_author_on_publish_s_text','Your post is published.');
		$body = get_optionXTRA('xtra_notify_author_on_publish_text','Thank you for your submission!');

		wp_mail($email_address, $subject, $body);
	}
	add_action('publish_post','xtra_notify_author_on_publish');
}

if( get_optionXTRA('xtra_disable_wpautop') ) {
	remove_filter( 'the_content', 'wpautop' );
	remove_filter( 'the_excerpt', 'wpautop' );
}

//--- Column shortcodes ---
if( get_optionXTRA('xtra_enable_column_shortcodes') ) {
	function xtra_one_third( $atts, $content = null ) { return '<div class="xtra_col_one_third">' . do_shortcode($content) . '</div>'; }
	add_shortcode('one_third', 'xtra_one_third');
	function xtra_one_third_last( $atts, $content = null ) { return '<div class="xtra_col_one_third xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('one_third_last', 'xtra_one_third_last');
	function xtra_two_third( $atts, $content = null ) { return '<div class="xtra_col_two_third">' . do_shortcode($content) . '</div>'; }
	add_shortcode('two_third', 'xtra_two_third');
	function xtra_two_third_last( $atts, $content = null ) { return '<div class="xtra_col_two_third xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('two_third_last', 'xtra_two_third_last');
	function xtra_one_half( $atts, $content = null ) { return '<div class="xtra_col_one_half">' . do_shortcode($content) . '</div>'; }
	add_shortcode('one_half', 'xtra_one_half');
	function xtra_one_half_last( $atts, $content = null ) { return '<div class="xtra_col_one_half xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('one_half_last', 'xtra_one_half_last');
	function xtra_one_fourth( $atts, $content = null ) { return '<div class="xtra_col_one_fourth">' . do_shortcode($content) . '</div>'; }
	add_shortcode('one_fourth', 'xtra_one_fourth');
	function xtra_one_fourth_last( $atts, $content = null ) { return '<div class="xtra_col_one_fourth xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('one_fourth_last', 'xtra_one_fourth_last');
	function xtra_three_fourth( $atts, $content = null ) { return '<div class="xtra_col_three_fourth">' . do_shortcode($content) . '</div>'; }
	add_shortcode('three_fourth', 'xtra_three_fourth');
	function xtra_three_fourth_last( $atts, $content = null ) { return '<div class="xtra_col_three_fourth xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('three_fourth_last', 'xtra_three_fourth_last');
	function xtra_one_fifth( $atts, $content = null ) { return '<div class="xtra_col_one_fifth">' . do_shortcode($content) . '</div>'; }
	add_shortcode('one_fifth', 'xtra_one_fifth');
	function xtra_one_fifth_last( $atts, $content = null ) { return '<div class="xtra_col_one_fifth xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('one_fifth_last', 'xtra_one_fifth_last');
	function xtra_two_fifth( $atts, $content = null ) { return '<div class="xtra_col_two_fifth">' . do_shortcode($content) . '</div>'; }
	add_shortcode('two_fifth', 'xtra_two_fifth');
	function xtra_two_fifth_last( $atts, $content = null ) { return '<div class="xtra_col_two_fifth xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('two_fifth_last', 'xtra_two_fifth_last');
	function xtra_three_fifth( $atts, $content = null ) { return '<div class="xtra_col_three_fifth">' . do_shortcode($content) . '</div>'; }
	add_shortcode('three_fifth', 'xtra_three_fifth');
	function xtra_three_fifth_last( $atts, $content = null ) { return '<div class="xtra_col_three_fifth xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('three_fifth_last', 'xtra_three_fifth_last');
	function xtra_four_fifth( $atts, $content = null ) { return '<div class="xtra_col_four_fifth">' . do_shortcode($content) . '</div>'; }
	add_shortcode('four_fifth', 'xtra_four_fifth');
	function xtra_four_fifth_last( $atts, $content = null ) { return '<div class="xtra_col_four_fifth xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('four_fifth_last', 'xtra_four_fifth_last');
	function xtra_one_sixth( $atts, $content = null ) { return '<div class="xtra_col_one_sixth">' . do_shortcode($content) . '</div>'; }
	add_shortcode('one_sixth', 'xtra_one_sixth');
	function xtra_one_sixth_last( $atts, $content = null ) { return '<div class="xtra_col_one_sixth xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('one_sixth_last', 'xtra_one_sixth_last');
	function xtra_five_sixth( $atts, $content = null ) { return '<div class="xtra_col_five_sixth">' . do_shortcode($content) . '</div>'; }
	add_shortcode('five_sixth', 'xtra_five_sixth');
	function xtra_five_sixth_last( $atts, $content = null ) { return '<div class="xtra_col_five_sixth xtra_col_last">' . do_shortcode($content) . '</div><div class=" xtra_col_clearboth"></div>'; }
	add_shortcode('five_sixth_last', 'xtra_five_sixth_last');

	function xtra_column_stylesheet() {

		wp_register_style( 'xtra_column-styles', plugins_url('/assets/css/column-style.css', __FILE__) );
		wp_enqueue_style( 'xtra_column-styles' );
	}
	add_action('wp_print_styles', 'xtra_column_stylesheet');
}




if ($xtra_do_memlog) xtra_memlog("Posts");

//---Comments---
if( get_optionXTRA('xtra_disable_comments_switch') ) {

	// Remove comment-box on the front-end
	function xtra_disable_comments_status() {
		return false;
	}
	add_filter('comments_open', 'xtra_disable_comments_status', 20, 2);
	add_filter('pings_open', 'xtra_disable_comments_status', 20, 2);

	// Hide existing comments
	function xtra_disable_comments_hide_existing_comments($comments) {
		$comments = array();
		return $comments;
	}
	add_filter('comments_array', 'xtra_disable_comments_hide_existing_comments', 10, 2);

	// Disable support for comments and trackbacks in all post types
	function xtra_disable_comments_post_types_support() {
		$post_types = get_post_types();
		foreach ($post_types as $post_type) {
			if(post_type_supports($post_type, 'comments')) {
				remove_post_type_support($post_type, 'comments');
				remove_post_type_support($post_type, 'trackbacks');
			}
		}
	}
	//add_action('admin_init', 'xtra_disable_comments_post_types_support');

	// ADMIN ---

	// Remove comments page in admin menu
	function xtra_disable_comments_admin_menu() {
		remove_menu_page('edit-comments.php');
	}
	//add_action('admin_menu', 'xtra_disable_comments_admin_menu');

	// Redirect any user trying to access edit-comments page
	function xtra_disable_comments_admin_menu_redirect() {
		global $pagenow;
		if ($pagenow === 'edit-comments.php') {
			wp_redirect(admin_url()); exit;
		}
	}
	//add_action('admin_init', 'xtra_disable_comments_admin_menu_redirect');

	// Remove comments metabox from admin dashboard
	function xtra_disable_comments_dashboard() {
		remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	}
	//add_action('admin_init', 'xtra_disable_comments_dashboard');

	// Remove comments links from admin bar
	function xtra_disable_comments_admin_bar() {
		if (is_admin_bar_showing()) {
			remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
		}
	}
	//add_action('init', 'xtra_disable_comments_admin_bar');
}



//---Database---

// EXAMPLE:   EXPORT_TABLES("localhost","user","pass","db_name" );
		//optional: 5th parameter - to backup specific tables only: array("mytable1","mytable2",...)
		//optional: 6th parameter - backup filename
		// IMPORTANT NOTE for people who try to change strings in SQL FILE before importing, MUST READ:  goo.gl/2fZDQL
// https://github.com/ttodua/useful-php-scripts

function xtra_EXPORT_TABLES($host,$user,$pass,$name,       $tables=false, $backup_name=false){
	set_time_limit(3000); $mysqli = new mysqli($host,$user,$pass,$name); $mysqli->select_db($name); $mysqli->query("SET NAMES 'utf8'");
	$queryTables = $mysqli->query('SHOW TABLES'); while($row = $queryTables->fetch_row()) { $target_tables[] = $row[0]; }	if($tables !== false) { $target_tables = array_intersect( $target_tables, $tables); }
	$content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `".$name."`\r\n--\r\n\r\n\r\n";
	foreach($target_tables as $table){
		if (empty($table)){ continue; }
		$result	= $mysqli->query('SELECT * FROM `'.$table.'`');  	$fields_amount=$result->field_count;  $rows_num=$mysqli->affected_rows; 	$res = $mysqli->query('SHOW CREATE TABLE '.$table);	$TableMLine=$res->fetch_row();
		$content .= "\n\n".$TableMLine[1].";\n\n";   $TableMLine[1]=str_ireplace('CREATE TABLE `','CREATE TABLE IF NOT EXISTS `',$TableMLine[1]);
		for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) {
			while($row = $result->fetch_row())	{
				if ($st_counter%100 == 0 || $st_counter == 0 )	{$content .= "\nINSERT INTO ".$table." VALUES";}
					$content .= "\n(";    for($j=0; $j<$fields_amount; $j++){ $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); if (isset($row[$j])){$content .= '"'.$row[$j].'"' ;}  else{$content .= '""';}	   if ($j<($fields_amount-1)){$content.= ',';}   }        $content .=")";
				if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) {$content .= ";";} else {$content .= ",";}	$st_counter=$st_counter+1;
			}
		} $content .="\n\n\n";
	}
	$content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
	$backup_name = $backup_name ? $backup_name : $name.'___('.date('Y-m-d').'_'.date('His').').sql';
	ob_get_clean(); header('Content-Type: application/octet-stream');  header("Content-Transfer-Encoding: Binary");  header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($content, '8bit'): strlen($content)) );    header("Content-disposition: attachment; filename=\"".$backup_name."\"");
	echo $content; exit;
}

// EXAMPLE:	IMPORT_TABLES("localhost","user","pass","db_name", "my_baseeee.sql"); //TABLES WILL BE OVERWRITTEN
	// P.S. IMPORTANT NOTE for people who try to change/replace some strings  in SQL FILE before importing, MUST READ:  https://goo.gl/2fZDQL

function xtra_IMPORT_TABLES($host,$user,$pass,$dbname, $sql_file_OR_content){
	set_time_limit(3000);
	$SQL_CONTENT = (strlen($sql_file_OR_content) > 300 ?  $sql_file_OR_content : file_get_contents($sql_file_OR_content)  );
	$allLines = explode("\n",$SQL_CONTENT);
	$mysqli = new mysqli($host, $user, $pass, $dbname); if (mysqli_connect_errno()){echo "".esc_html__('Failed to connect to MySQL', 'xtra-settings') .": " . mysqli_connect_error();}
		$zzzzzz = $mysqli->query('SET foreign_key_checks = 0');	        preg_match_all("/\nCREATE TABLE(.*?)\`(.*?)\`/si", "\n". $SQL_CONTENT, $target_tables); foreach ($target_tables[2] as $table){$mysqli->query('DROP TABLE IF EXISTS '.$table);}         $zzzzzz = $mysqli->query('SET foreign_key_checks = 1');    $mysqli->query("SET NAMES 'utf8'");
	$templine = '';	// Temporary variable, used to store current query
	foreach ($allLines as $line)	{											// Loop through each line
		if (substr($line, 0, 2) != '--' && $line != '') {$templine .= $line; 	// (if it is not a comment..) Add this line to the current segment
			if (substr(trim($line), -1, 1) == ';') {		// If it has a semicolon at the end, it's the end of the query
				if(!$mysqli->query($templine)){ print(''. esc_html__('Error performing query', 'xtra-settings') .' \'<strong>' . $templine . '\': ' . $mysqli->error . '<br /><br />');  }  $templine = ''; // set variable to empty, to start picking up the lines after ";"
			}
		}
	}	return ''. esc_html__('Importing finished. Now, delete the import file.', 'xtra-settings') .'';
}

if ($xtra_do_memlog) xtra_memlog("Database");



//---allopts---
function xtra_allopts_EXPORT(){
global $xtra_options;
	$content = json_encode( $xtra_options, 0, 512 );
	$exploded_uri = explode('/', site_url());
	$t = $exploded_uri[0];
	if (strpos(site_url(),"://")!==false) $t = $exploded_uri[2];
	
	$t = site_url();
	$t = str_replace(array("http://","https://","www."),array(""),$t);
	$t = str_replace(array(".","/"),array("_","_"),$t);
	
	
	$backup_name = 'XTRA_settings_'.$t.'___('.date('Y-m-d').'_'.date('His').').json';
	ob_get_clean(); 
	header('Content-Type: application/octet-stream');  
	header("Content-Transfer-Encoding: Binary");  
	header('Content-Length: '. ( function_exists('mb_strlen') ? mb_strlen($content, '8bit') : strlen($content) ) );    
	header("Content-disposition: attachment; filename=\"".$backup_name."\"");
	echo $content; 
	exit;
}
function xtra_allopts_IMPORT($filename,$xtra_allopts_restore_include_hits=0){
global $xtra_options;

	if (isset($xtra_options["xtra_hits"])) $s1 = $xtra_options["xtra_hits"];
	if (isset($xtra_options["xtra_hits_IPs"])) $s2 = $xtra_options["xtra_hits_IPs"];
	if (isset($xtra_options["xtra_hits_IPdata"])) $s3 = $xtra_options["xtra_hits_IPdata"];
	if (isset($xtra_options["xtra_hits_HITdata"])) $s4 = $xtra_options["xtra_hits_HITdata"];
	
	$data = file_get_contents($filename);
	$xtra_options = json_decode($data,true);
	
	if (!$xtra_allopts_restore_include_hits) {
		if (isset($s1)) $xtra_options["xtra_hits"] = $s1;
		if (isset($s2)) $xtra_options["xtra_hits_IPs"] = $s2;
		if (isset($s3)) $xtra_options["xtra_hits_IPdata"] = $s3;
		if (isset($s4)) $xtra_options["xtra_hits_HITdata"] = $s4;
	}
	
}

if ($xtra_do_memlog) xtra_memlog("allopts");








//---XTRA---
/* Add XTRA Menu to Admin Bar */
if ( get_optionXTRA('xtra_opt_admin_bar_menu', 0) ) {
	function xtra_opt_admin_bar_menu() {
		global $wp_admin_bar;
		if ( !is_super_admin() || !is_admin_bar_showing() )
			return;
		$iconurl = '/images/custom-icon.png';
		$icon = 'dashicons dashicons-lightbulb';
		$iconspan = '<div class="'.$icon.'" style="font-family: dashicons; font-size: 18px; margin-top: -5px;"></div> ';
		$i = 0;
		$mid = 0;
		$wp_admin_bar->add_menu( array( 'id' => 'xtra_menu', 		'href' => admin_url('admin.php?page=xtra'),	'title' => $iconspan . 'XTRA' ) );
		$wp_admin_bar->add_menu( array( 'id' => 'xtra_menu'.($mid++),'parent' => 'xtra_menu',	'href' => admin_url('admin.php?page=xtra'), 'title' => 'XTRA Settings ' . esc_html__( 'Page', 'xtra-settings' ) ) );
		$tabs = xtra_get_tab_colors('','names');
		foreach ($tabs as $key => $val) {
			if ($val) $wp_admin_bar->add_menu( array( 'id' => 'xtra_menu'.($mid++),'parent' => 'xtra_menu',	'href' => admin_url('admin.php?page=xtra&xtra_seltab='.$key), 'title' => '&nbsp;&nbsp;- '.$val 	) );
		}
		if (	(get_optionXTRA('xtra_opt_show_database')) 	
		    ||  (get_optionXTRA('xtra_opt_show_crons')) 		
		    ||  (get_optionXTRA('xtra_opt_show_plugins')) 	
		    ||  (get_optionXTRA('xtra_opt_show_images')) 	
		    ||  (get_optionXTRA('xtra_opt_show_own'))	 	
		) {
			$wp_admin_bar->add_menu( array( 'id' => 'xtra_menu'.($mid++),'parent' => 'xtra_menu',	'href' => FALSE, 							'title' => xtra_make_sepa($i++)	) );
		}
		$tabs = array();
		if (get_optionXTRA('xtra_opt_show_database')) 	$tabs["database"] 	= "".esc_html__('Database', 'xtra-settings') .""	;
		if (get_optionXTRA('xtra_opt_show_crons')) 		$tabs["crons"] 		= "".esc_html__('Crons', 'xtra-settings') .""		;
		if (get_optionXTRA('xtra_opt_show_plugins')) 	$tabs["plugins"] 	= "".esc_html__('Plugins', 'xtra-settings') .""		;
		if (get_optionXTRA('xtra_opt_show_images')) 	$tabs["images"]		= "".esc_html__('Images', 'xtra-settings') .""		;
		if (get_optionXTRA('xtra_opt_show_own'))	 	$tabs["own"]		= "XTRA"		;
		foreach ($tabs as $key => $val) {
			if ($val) $wp_admin_bar->add_menu( array( 'id' => 'xtra_menu'.($mid++),'parent' => 'xtra_menu',	'href' => admin_url('admin.php?page=xtra&xtra_seltab='.$key), 'title' => '&nbsp;&nbsp;- '.$val 	) );
		}
		$wp_admin_bar->add_menu( array( 'id' => 'xtra_menu'.($mid++),'parent' => 'xtra_menu',	'href' => FALSE, 							'title' => xtra_make_sepa($i++)	) );
		$wp_admin_bar->add_menu( array( 'id' => 'xtra_menu'.($mid++),'parent' => 'xtra_menu',	'href' => admin_url('admin.php?page=xtra'), 'title' => sprintf(esc_html__( "load time: %s sec", 'xtra-settings' ),timer_stop(0)) ) );
		$wp_admin_bar->add_menu( array( 'id' => 'xtra_menu'.($mid++),'parent' => 'xtra_menu',	'href' => admin_url('admin.php?page=xtra'), 'title' => sprintf(esc_html__( 'db-queries: %s', 'xtra-settings' ),get_num_queries()) ) );
		$wp_admin_bar->add_menu( array( 'id' => 'xtra_menu'.($mid++),'parent' => 'xtra_menu',	'href' => admin_url('admin.php?page=xtra'), 'title' => sprintf(esc_html__( 'memory: %s MB', 'xtra-settings' ),memory_get_usage(true)/1024/1024) ) );
	}
	add_action( 'admin_bar_menu', 'xtra_opt_admin_bar_menu', 1000 );
	function xtra_make_sepa($i) {
		$sty = "border-bottom: 1px solid #BBB;height: 13px;";
		return '<div style="'.$sty.'"><span style="display:none;">'.$i.'</span></div>';
	}
}






//---Others---
/*
*/


//---HTML admin-settings
function xtra_html_page() {
	global $wpdb, $xtra_page_reload;
	if ( !current_user_can('manage_options') ) {
		echo ''. esc_html__('This page is only for administrators.', 'xtra-settings') .'';
		exit;
	}
	//.. Include admin-setting.php
	include_once('admin-setting.php');
	update_optionXTRA('xtra_options_convert_once', 1);
}
/* 	save options to DB only once,
	at the very end (shutdown, priority 999),
	that is surely after xtra_hits_add (shutdown, normal priority: 10)
*/
add_action( 'shutdown', 'xtra_options_save', 999 );
?>