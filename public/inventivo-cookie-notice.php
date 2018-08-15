<?php /*
Contributors: inventivogermany
Plugin Name:  Cookie Notice | inventivo
Plugin URI:   https://www.inventivo.de/wordpress-agentur/wordpress-plugins
Description:  Display the EU Cookie Notice in a popup (EU Cookie Guideline)
Version:      0.1.6
Author:       Nils Harder
Author URI:   https://www.inventivo.de
Tags: cookie notice, cookie hinweis, eu cookie richtlinie, cookie popup, inventivo
Requires at least: 3.0
Tested up to: 4.9.5
Stable tag: 0.1.6
Text Domain: inventivo-cookie-notice
Domain Path: /languages
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Version of the plugin
define('INVENTIVO_COOKIE_NOTICE_CURRENT_VERSION', '1.0.0' );

add_action( 'wp_enqueue_scripts', array(InvCookieSettingsPage,'inv_cookie_notice_styles' ));

class InvCookieSettingsPage
{
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Start up
	 */
	public function __construct()
	{
		add_action('plugins_loaded',array($this,'inventivo_cookie_notice_load_textdomain'));
		//add_action('init',array($this,'my_i18n_debug'));
	    add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );

	}


	function inventivo_cookie_notice_load_textdomain() {
		load_plugin_textdomain( 'inventivo-cookie-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
	}

	function my_i18n_debug(){

		$loaded=load_plugin_textdomain( 'inventivo-cookie-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

		if ( ! $loaded ){
			echo "<hr/>";
			echo "Error: the mo file was not found! ";
			exit();
		}else{
			echo "<hr/><strong>Debug info</strong>:<br/>";
			//echo "WPLANG: ". WPLANG;
			echo "<br/>";
			echo "translate test: ". __('Cookie Notice | inventivo','inventivo-cookie-notice');
			exit();
		}
	}


	// Load Frontend Scripts + Styles
	public function inv_cookie_notice_styles() {

		$options = get_option( 'my_option_name' );

	    switch($options['alignment']) {
		    case 'left':
			    $cssFile = 'inv-cookie-notice-left.css';
			    break;
		    case 'fullwidth':
			    $cssFile = 'inv-cookie-notice-fullwidth.css';
			    break;
		    case 'right':
			    $cssFile = 'inv-cookie-notice-right.css';
			    break;
            default:
	            $cssFile = 'inv-cookie-notice-right.css';
	            break;
        }

		//$cssFile = 'inv-cookie-notice-left.css';

		wp_register_style( 'cookie-notice-front-styles', plugins_url('/public/css/inv-cookie-notice.css', __FILE__) );
		wp_enqueue_style( 'cookie-notice-front-styles' );

		wp_register_style( 'cookie-notice-front-styles-alignment', plugins_url('/public/css/'.$cssFile, __FILE__) );
		wp_enqueue_style( 'cookie-notice-front-styles-alignment' );


		wp_register_script( 'js-cookie', plugins_url('/public/js/js.cookie.js', __FILE__), array('jquery') );
		wp_enqueue_script( 'js-cookie' );


		// Register the script
		wp_register_script( 'cookienotice', plugins_url('/public/js/inv-cookie-notice.js', __FILE__), array('jquery') );
		// Localize the script with new data


		$invoptions = array(
			//'domain' => __( 'inventivo.de', 'plugin-domain' ),
			'domain' => esc_attr($options['domain']),
			'privacylink' => esc_attr($options['privacylink']),
			'privacylinktext' => esc_attr($options['privacylinktext']),
			'cookietext' => esc_attr($options['cookietext']),
			'cookietextcolor' => esc_attr($options['cookietextcolor']),
			'buttontext' => esc_attr($options['buttontext']),
			'buttontextcolor' => esc_attr($options['buttontextcolor']),
			'buttoncolor' => esc_attr($options['buttoncolor']),
			'backgroundcolor' => esc_attr($options['backgroundcolor']),
			'alignment' => esc_attr($options['alignment']),
		);
		wp_localize_script( 'cookienotice', 'invoptions', $invoptions );

		// Enqueued script with localized data.
		wp_enqueue_script( 'cookienotice' );
	}



	/**
	 * Add options page
	 */
	public function add_plugin_page()
	{
		// This page will be under "Settings"
		add_options_page(
			'Settings Admin',
			esc_html__( 'Cookie Notice | inventivo', 'inventivo-cookie-notice' ),
			'manage_options',
			'my-setting-admin',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		// Set class property
		$this->options = get_option( 'my_option_name' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Cookie Notice | inventivo', 'inventivo-cookie-notice' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( 'my_option_group' );
				do_settings_sections( 'my-setting-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function page_init()
	{
		register_setting(
			'my_option_group', // Option group
			'my_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			esc_html__('Setup', 'inventivo-cookie-notice'), // Title
			array( $this, 'print_section_info' ), // Callback
			'my-setting-admin' // Page
		);

		add_settings_field(
			'domain', // ID
			'Domain', // Title
			array( $this, 'domain_callback' ), // Callback
			'my-setting-admin', // Page
			'setting_section_id' // Section
		);

		add_settings_field(
			'privacylink',
			esc_html__( 'Link to Privacy Notice', 'inventivo-cookie-notice' ),
			array( $this, 'privacylink_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);
		add_settings_field(
			'privacylinktext',
			esc_html__( 'Privacy Link Text', 'inventivo-cookie-notice' ),
			array( $this, 'privacylinktext_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'cookietext',
			esc_html__( 'Cookie Notice Text', 'inventivo-cookie-notice' ),
			array( $this, 'cookietext_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'cookietextcolor',
			esc_html__( 'Cookie Notice Text Color', 'inventivo-cookie-notice' ),
			array( $this, 'cookietextcolor_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'buttontext',
			esc_html__( 'Button Text', 'inventivo-cookie-notice' ),
			array( $this, 'buttontext_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'buttontextcolor',
			esc_html__( 'Button Text Color', 'inventivo-cookie-notice' ),
			array( $this, 'buttontextcolor_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'buttoncolor',
			esc_html__( 'Button Color', 'inventivo-cookie-notice' ),
			array( $this, 'buttoncolor_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'backgroundcolor',
			esc_html__( 'Background Color', 'inventivo-cookie-notice' ),
			array( $this, 'backgroundcolor_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'alignment',
			esc_html__( 'Alignment', 'inventivo-cookie-notice' ),
			array( $this, 'alignment_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);
	}

	public function sanitize( $input )
	{
		$new_input = array();
		if( isset( $input['domain'] ) )
			$new_input['domain'] = sanitize_text_field( $input['domain'] );

		if( isset( $input['privacylink'] ) )
			$new_input['privacylink'] = sanitize_text_field( $input['privacylink'] );

		if( isset( $input['privacylinktext'] ) )
			$new_input['privacylinktext'] = sanitize_text_field( $input['privacylinktext'] );

		if( isset( $input['cookietext'] ) )
			$new_input['cookietext'] = sanitize_text_field( $input['cookietext'] );

		if( isset( $input['cookietextcolor'] ) )
			$new_input['cookietextcolor'] = sanitize_text_field( $input['cookietextcolor'] );

		if( isset( $input['buttontext'] ) )
			$new_input['buttontext'] = sanitize_text_field( $input['buttontext'] );

		if( isset( $input['buttontextcolor'] ) )
			$new_input['buttontextcolor'] = sanitize_text_field( $input['buttontextcolor'] );

		if( isset( $input['buttoncolor'] ) )
			$new_input['buttoncolor'] = sanitize_text_field( $input['buttoncolor'] );

		if( isset( $input['backgroundcolor'] ) )
			$new_input['backgroundcolor'] = sanitize_text_field( $input['backgroundcolor'] );

		if( isset( $input['alignment'] ) )
			$new_input['alignment'] = sanitize_text_field( $input['alignment'] );

		return $new_input;
	}


	public function print_section_info()
	{
		_e( 'Please adapt texts and colors to your needs:', 'inventivo-cookie-notice' );
	}


	public function domain_callback()
	{
		printf(
			'<input type="text" id="domain" name="my_option_name[domain]" value="%s" /> '.__( 'Example: my-website.de', 'inventivo-cookie-notice' ),
			isset( $this->options['domain'] ) ? esc_attr( $this->options['domain']) : __( 'my-website.de', 'inventivo-cookie-notice' )
		);
	}
	public function privacylink_callback()
	{
		printf(
			'<input type="text" id="privacylink" name="my_option_name[privacylink]" value="%s" /> '.__( 'Example: /privacy', 'inventivo-cookie-notice' ),
			isset( $this->options['privacylink'] ) ? esc_attr( $this->options['privacylink']) : __( '/privacy', 'inventivo-cookie-notice' )
		);
	}
	public function privacylinktext_callback()
	{
		printf(
			'<input type="text" id="privacylinktext" name="my_option_name[privacylinktext]" value="%s" /> '.__( 'Example: Privacy policy', 'inventivo-cookie-notice' ),
			isset( $this->options['privacylinktext'] ) ? esc_attr( $this->options['privacylinktext']) : __( 'Privacy policy', 'inventivo-cookie-notice' )
		);
	}
	public function cookietext_callback()
	{
		printf(
			'<input type="text" id="cookietext" name="my_option_name[cookietext]" value="%s" /> '.__( 'Example: uses cookies to enhance your user experience.', 'inventivo-cookie-notice' ),
			isset( $this->options['cookietext'] ) ? esc_attr( $this->options['cookietext']) : __( 'uses cookies to enhance your user experience.', 'inventivo-cookie-notice' )
		);
	}
	public function cookietextcolor_callback()
	{
		printf(
			'<input type="text" id="cookietextcolor" name="my_option_name[cookietextcolor]" value="%s" /> '.__('Example: #555555','inventivo-cookie-notice'),
			isset( $this->options['cookietextcolor'] ) ? esc_attr( $this->options['cookietextcolor']) : __('#555555','inventivo-cookie-notice')
		);
	}
	public function buttontext_callback()
	{
		printf(
			'<input type="text" id="buttontext" name="my_option_name[buttontext]" value="%s" /> '.__('Example: Accept.','inventivo-cookie-notice'),
			isset( $this->options['buttontext'] ) ? esc_attr( $this->options['buttontext']) : __('Accept','inventivo-cookie-notice')
		);
	}

    public function buttontextcolor_callback()
	{
		printf(
			'<input type="text" id="buttontextcolor" name="my_option_name[buttontextcolor]" value="%s" /> '.__('Example: #FFFFFF','inventivo-cookie-notice'),
			isset( $this->options['buttontextcolor'] ) ? esc_attr( $this->options['buttontextcolor']) : __('#FFFFFF','inventivo-cookie-notice')
		);
	}

	public function buttoncolor_callback()
	{
		printf(
			'<input type="text" id="buttoncolor" name="my_option_name[buttoncolor]" value="%s" /> '.__('Example: #646464','inventivo-cookie-notice'),
			isset( $this->options['buttoncolor'] ) ? esc_attr( $this->options['buttoncolor']) : __('#646464','inventivo-cookie-notice')
		);
	}

	public function backgroundcolor_callback()
	{
		printf(
			'<input type="text" id="backgroundcolor" name="my_option_name[backgroundcolor]" value="%s" /> '.__('Example: #EFEFEF','inventivo-cookie-notice'),
			isset( $this->options['backgroundcolor'] ) ? esc_attr( $this->options['backgroundcolor']) : __('#EFEFEF','inventivo-cookie-notice')
		);
	}

	public function alignment_callback()
	{
		if($this->options['alignment'] == 'left') { $selected1 = 'selected'; }
		if($this->options['alignment'] == 'fullwidth') { $selected2 = 'selected'; }
		if($this->options['alignment'] == 'right') { $selected3 = 'selected'; }
	    printf(
		        '<select id="alignment" name="my_option_name[alignment]">
                    <option value="left" '.$selected1.'>Left</option>
                    <option value="fullwidth" '.$selected2.'>Full width</option>
                    <option value="right" '.$selected3.'>Right</option>
		        </select>',
			    isset( $this->options['alignment'] ) ? esc_attr( $this->options['alignment']) : __('Alignment','inventivo-cookie-notice')
		);
	}
}


if( is_admin() )
	$my_settings_page = new InvCookieSettingsPage();