<?php /*
Contributors: Nils Harder (inventivo.de)
Plugin Name:  Cookie Notice | inventivo
Plugin URI:   https://www.inventivo.de/wordpress-agentur/wordpress-plugins
Description:  Anzeige eines Hinweises zur Nutzung von Cookies (EU-Cookie-Richtlinie)
Version:      19042018
Author:       Nils Harder
Author URI:   https://www.inventivo.de
Tags: cookie notice, cookie hinweis, eu cookie richtlinie, cookie popup, inventivo
Requires at least: 3.0
Tested up to: 4.9.5
Stable tag: 0.0.2
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


add_action( 'wp_enqueue_scripts', array('InvCookieSettingsPage','inv_cookie_notice_styles' ));


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
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}


    // Load Frontend Scripts + Styles
	public function inv_cookie_notice_styles() {
		wp_register_style( 'cookie-notice-front-styles', plugins_url('/public/css/inv-cookie-notice.css', __FILE__) );

		wp_enqueue_style( 'cookie-notice-front-styles' );


		wp_register_script( 'js-cookie', plugins_url('/public/js/js.cookie.js', __FILE__) );
		wp_enqueue_script( 'js-cookie' );


		// Register the script
		wp_register_script( 'cookienotice', plugins_url('/public/js/inv-cookie-notice.js', __FILE__) );
		// Localize the script with new data

		$options = get_option( 'my_option_name' );
		$invoptions = array(
			//'domain' => __( 'inventivo.de', 'plugin-domain' ),
			'domain' => esc_attr($options['domain']),
			'privacylink' => esc_attr($options['privacylink']),
			'cookietext' => esc_attr($options['cookietext']),
			'cookietextcolor' => esc_attr($options['cookietextcolor']),
			'buttontext' => esc_attr($options['buttontext']),
			'buttontextcolor' => esc_attr($options['buttontextcolor']),
			'buttoncolor' => esc_attr($options['buttoncolor']),
			'backgroundcolor' => esc_attr($options['backgroundcolor'])
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
			'Cookie Notice | inventivo',
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
			<h1>Cookie Notice | inventivo</h1>
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
			'Konfiguration', // Title
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
			'Link zur Datenschutzerklärung',
			array( $this, 'privacylink_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'cookietext',
			'Cookie Hinweistext',
			array( $this, 'cookietext_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'cookietextcolor',
			'Cookie Hinweistext Farbe',
			array( $this, 'cookietextcolor_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'buttontext',
			'Button Text',
			array( $this, 'buttontext_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'buttontextcolor',
			'Button Text Farbe',
			array( $this, 'buttontextcolor_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'buttoncolor',
			'Button Farbe',
			array( $this, 'buttoncolor_callback' ),
			'my-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'backgroundcolor',
			'Hintergrundfarbe',
			array( $this, 'backgroundcolor_callback' ),
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

		return $new_input;
	}


	public function print_section_info()
	{
		print 'Bitte tragen Sie folgende Angaben ein:';
	}


	public function domain_callback()
	{
		printf(
			'<input type="text" id="domain" name="my_option_name[domain]" value="%s" /> Beispiel: inventivo.de',
			isset( $this->options['domain'] ) ? esc_attr( $this->options['domain']) : 'Diese Webseite'
		);
	}
	public function privacylink_callback()
	{
		printf(
			'<input type="text" id="privacylink" name="my_option_name[privacylink]" value="%s" /> Beispiel: /datenschutzerklaerung',
			isset( $this->options['privacylink'] ) ? esc_attr( $this->options['privacylink']) : '/datenschutzerklaerung'
		);
	}
	public function cookietext_callback()
	{
		printf(
			'<input type="text" id="cookietext" name="my_option_name[cookietext]" value="%s" /> Beispiel: nutzt Cookies, um Ihnen eine bestmögliche Funktionalität der Website bieten zu können.',
			isset( $this->options['cookietext'] ) ? esc_attr( $this->options['cookietext']) : 'nutzt Cookies, um Ihnen eine bestmögliche Funktionalität der Website bieten zu können.'
		);
	}
	public function cookietextcolor_callback()
	{
		printf(
			'<input type="text" id="cookietextcolor" name="my_option_name[cookietextcolor]" value="%s" /> Beispiel: #555555.',
			isset( $this->options['cookietextcolor'] ) ? esc_attr( $this->options['cookietextcolor']) : '#555555'
		);
	}
	public function buttontext_callback()
	{
		printf(
			'<input type="text" id="buttontext" name="my_option_name[buttontext]" value="%s" /> Beispiel: Akzeptieren',
			isset( $this->options['buttontext'] ) ? esc_attr( $this->options['buttontext']) : 'Akzeptieren'
		);
	}

    public function buttontextcolor_callback()
	{
		printf(
			'<input type="text" id="buttontextcolor" name="my_option_name[buttontextcolor]" value="%s" /> Beispiel: #FFFFFF',
			isset( $this->options['buttontextcolor'] ) ? esc_attr( $this->options['buttontextcolor']) : '#FFFFFF'
		);
	}

	public function buttoncolor_callback()
	{
		printf(
			'<input type="text" id="buttoncolor" name="my_option_name[buttoncolor]" value="%s" /> Beispiel: #646464',
			isset( $this->options['buttoncolor'] ) ? esc_attr( $this->options['buttoncolor']) : '#646464'
		);
	}

	public function backgroundcolor_callback()
	{
		printf(
			'<input type="text" id="backgroundcolor" name="my_option_name[backgroundcolor]" value="%s" /> Beispiel: #efefef',
			isset( $this->options['backgroundcolor'] ) ? esc_attr( $this->options['backgroundcolor']) : '#efefef'
		);
	}
}


if( is_admin() )
	$my_settings_page = new InvCookieSettingsPage();