<?php /*
Contributors: inventivogermany
Plugin Name:  Cookie Notice | inventivo
Plugin URI:   https://www.inventivo.de/webseiten/wordpress-plugins
Description:  Display a Cookie Notice in a popup
Version:      1.0.7
Author:       Nils Harder
Author URI:   https://www.inventivo.de
Tags: cookie notice, cookie hinweis, cookie popup, inventivo
Requires at least: 5.0
Requires PHP: 7.4
Stable tag: 1.0.7
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
define('INVENTIVO_COOKIE_NOTICE_CURRENT_VERSION', '1.0.7' );

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
        add_action('init', array($this, 'inventivo_cookie_notice_load_textdomain'));
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        add_action('admin_notices', array($this, 'admin_notice_get_pro'));
        add_action('admin_enqueue_scripts', array($this, 'inv_admin_style'));
        add_action( 'admin_init', array($this, 'notice_dismissed') );
        register_deactivation_hook( __FILE__, array($this, 'plugin_deactivate') );
    }


    function inventivo_cookie_notice_load_textdomain() {
        load_plugin_textdomain( 'inventivo-cookie-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    }


    /**
     * Reads an option and always returns an array.
     *
     * A cast to (array) would turn false into the stray key 0.
     */
    public static function get_options_array( $name )
    {
        $stored = get_option( $name, array() );

        return is_array( $stored ) ? $stored : array();
    }

    /**
     * Standardwerte der Einstellungen.
     *
     * Ohne sie liefert get_option() vor dem ersten Speichern false, und jeder
     * Feldzugriff erzeugt unter PHP 8 eine Warnung.
     */
    public static function get_default_options()
    {
        return array(
            'domain'           => '',
            'privacylink'      => '',
            'privacylinktext'  => '',
            'cookietext'       => '',
            'cookietextcolor'  => '#555555',
            'buttontext'       => '',
            'buttontextcolor'  => '#FFFFFF',
            'buttoncolor'      => '#646464',
            'buttonradius'     => '20px',
            'layerradius'      => '5px 5px 0 0',
            'backgroundcolor'  => '#EFEFEF',
            'backgroundcolor1' => '#EFEFEF',
            'backgroundcolor2' => '#EFEFEF',
            'backgroundcolor3' => '#EFEFEF',
            'alignment'        => 'right',
            'cookieduration'   => '365',
        );
    }

    // Load Frontend Scripts + Styles
    public function inv_cookie_notice_styles() {

        $options = wp_parse_args(
            self::get_options_array( 'inventivo_cookienotice_option_name' ),
            self::get_default_options()
        );

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
            case 'fullwidthtop':
                $cssFile = 'inv-cookie-notice-fullwidthtop.css';
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


        $invcookienoticeoptions = array(
            //'domain' => __( 'inventivo.de', 'plugin-domain' ),
            'domain' => esc_attr($options['domain']),
            'privacylink' => esc_attr($options['privacylink']),
            'privacylinktext' => esc_attr($options['privacylinktext']),
            'cookietext' => esc_attr($options['cookietext']),
            'cookietextcolor' => esc_attr($options['cookietextcolor']),
            'buttontext' => esc_attr($options['buttontext']),
            'buttontextcolor' => esc_attr($options['buttontextcolor']),
            'buttoncolor' => esc_attr($options['buttoncolor']),
            'buttonradius' => esc_attr($options['buttonradius']),
            'layerradius' => esc_attr($options['layerradius']),
            'backgroundcolor' => esc_attr($options['backgroundcolor']),
            'backgroundcolor1' => esc_attr($options['backgroundcolor1']),
            'backgroundcolor2' => esc_attr($options['backgroundcolor2']),
            'backgroundcolor3' => esc_attr($options['backgroundcolor3']),
            'alignment' => esc_attr($options['alignment']),
            'cookieduration' => esc_attr($options['cookieduration']),
        );
        wp_localize_script( 'cookienotice', 'invcookienoticeoptions', $invcookienoticeoptions );

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
            'inventivo_cookienotice_setting_admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = wp_parse_args(
            self::get_options_array( 'inventivo_cookienotice_option_name' ),
            self::get_default_options()
        );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Cookie Notice | inventivo', 'inventivo-cookie-notice' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'inventivo_cookienotice_option_group' );
                do_settings_sections( 'inventivo_cookienotice_setting_admin' );
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
            'inventivo_cookienotice_option_group', // Option group
            'inventivo_cookienotice_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            esc_html__('Setup', 'inventivo-cookie-notice'), // Title
            array( $this, 'print_section_info' ), // Callback
            'inventivo_cookienotice_setting_admin' // Page
        );

        add_settings_field(
            'domain', // ID
            'Domain', // Title
            array( $this, 'domain_callback' ), // Callback
            'inventivo_cookienotice_setting_admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'privacylink',
            esc_html__( 'Link to Privacy Notice', 'inventivo-cookie-notice' ),
            array( $this, 'privacylink_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_section_id'
        );
        add_settings_field(
            'privacylinktext',
            esc_html__( 'Privacy Link Text', 'inventivo-cookie-notice' ),
            array( $this, 'privacylinktext_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_section_id'
        );

        add_settings_field(
            'cookietext',
            esc_html__( 'Cookie Notice Text', 'inventivo-cookie-notice' ),
            array( $this, 'cookietext_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_section_id'
        );

        add_settings_field(
            'cookietextcolor',
            esc_html__( 'Cookie Notice Text Color', 'inventivo-cookie-notice' ),
            array( $this, 'cookietextcolor_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_section_id'
        );

        add_settings_field(
            'buttontext',
            esc_html__( 'Button Text', 'inventivo-cookie-notice' ),
            array( $this, 'buttontext_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_section_id'
        );

        add_settings_field(
            'alignment',
            esc_html__( 'Alignment', 'inventivo-cookie-notice' ),
            array( $this, 'alignment_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_section_id'
        );
        add_settings_field(
            'cookieduration',
            esc_html__( 'Cookie duration in days', 'inventivo-cookie-notice' ),
            array( $this, 'cookieduration_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_section_id'
        );

        // Colors
        add_settings_section(
            'setting_sections_colors', // ID
            esc_html__('Colors', 'inventivo-cookie-notice'), // Title
            array( $this, 'print_section_info_colors' ), // Callback
            'inventivo_cookienotice_setting_admin' // Page
        );

        add_settings_field(
            'buttontextcolor',
            esc_html__( 'Button Text Color', 'inventivo-cookie-notice' ),
            array( $this, 'buttontextcolor_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_sections_colors'
        );

        add_settings_field(
            'buttoncolor',
            esc_html__( 'Button Color', 'inventivo-cookie-notice' ),
            array( $this, 'buttoncolor_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_sections_colors'
        );

        add_settings_field(
            'buttonradius',
            esc_html__( 'Button Border Radius', 'inventivo-cookie-notice' ),
            array( $this, 'buttonradius_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_sections_colors'
        );
        add_settings_field(
            'layerradius',
            esc_html__( 'Layer Border Radius', 'inventivo-cookie-notice' ),
            array( $this, 'layerradius_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_sections_colors'
        );

        add_settings_field(
            'backgroundcolor',
            esc_html__( 'Background Color', 'inventivo-cookie-notice' ),
            array( $this, 'backgroundcolor_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_sections_colors'
        );

        add_settings_field(
            'backgroundcolor2',
            esc_html__( 'Background Color 2', 'inventivo-cookie-notice' ),
            array( $this, 'backgroundcolor2_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_sections_colors'
        );

        add_settings_field(
            'backgroundcolor3',
            esc_html__( 'Background Color 3', 'inventivo-cookie-notice' ),
            array( $this, 'backgroundcolor3_callback' ),
            'inventivo_cookienotice_setting_admin',
            'setting_sections_colors'
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

        if( isset( $input['buttonradius'] ) )
            $new_input['buttonradius'] = sanitize_text_field( $input['buttonradius'] );

        if( isset( $input['layerradius'] ) )
            $new_input['layerradius'] = sanitize_text_field( $input['layerradius'] );

        if( isset( $input['backgroundcolor'] ) )
            $new_input['backgroundcolor'] = sanitize_text_field( $input['backgroundcolor'] );

        if( isset( $input['backgroundcolor2'] ) )
            $new_input['backgroundcolor2'] = sanitize_text_field( $input['backgroundcolor2'] );

        if( isset( $input['backgroundcolor3'] ) )
            $new_input['backgroundcolor3'] = sanitize_text_field( $input['backgroundcolor3'] );

        if( isset( $input['alignment'] ) )
            $new_input['alignment'] = sanitize_text_field( $input['alignment'] );

        if( isset( $input['cookieduration'] ) )
            $new_input['cookieduration'] = sanitize_text_field( $input['cookieduration'] );

        return $new_input;
    }


    public function print_section_info()
    {
        _e( 'Please adapt texts and colors to your needs:', 'inventivo-cookie-notice' );
    }

    public function print_section_info_colors()
    {
        _e( 'You can set up to three colors for using a gradient styled background. If you want to have a one colored background, just use the same color code for each background color field', 'inventivo-cookie-notice' );
    }


    public function domain_callback()
    {
        printf(
            '<input type="text" id="domain" name="inventivo_cookienotice_option_name[domain]" value="%s" /> '.__( 'Example: my-website.de', 'inventivo-cookie-notice' ),
            isset( $this->options['domain'] ) ? esc_attr( $this->options['domain']) : __( 'my-website.de', 'inventivo-cookie-notice' )
        );
    }
    public function privacylink_callback()
    {
        printf(
            '<input type="text" id="privacylink" name="inventivo_cookienotice_option_name[privacylink]" value="%s" /> '.__( 'Example: /privacy', 'inventivo-cookie-notice' ),
            isset( $this->options['privacylink'] ) ? esc_attr( $this->options['privacylink']) : __( '/privacy', 'inventivo-cookie-notice' )
        );
    }
    public function privacylinktext_callback()
    {
        printf(
            '<input type="text" id="privacylinktext" name="inventivo_cookienotice_option_name[privacylinktext]" value="%s" /> '.__( 'Example: Privacy policy', 'inventivo-cookie-notice' ),
            isset( $this->options['privacylinktext'] ) ? esc_attr( $this->options['privacylinktext']) : __( 'Privacy policy', 'inventivo-cookie-notice' )
        );
    }
    public function cookietext_callback()
    {
        printf(
            '<input type="text" id="cookietext" name="inventivo_cookienotice_option_name[cookietext]" value="%s" /> '.__( 'Example: uses cookies to enhance your user experience.', 'inventivo-cookie-notice' ),
            isset( $this->options['cookietext'] ) ? esc_attr( $this->options['cookietext']) : __( 'uses cookies to enhance your user experience.', 'inventivo-cookie-notice' )
        );
    }
    public function cookietextcolor_callback()
    {
        printf(
            '<input type="text" id="cookietextcolor" name="inventivo_cookienotice_option_name[cookietextcolor]" value="%s" /> '.__('Example: #555555','inventivo-cookie-notice'),
            isset( $this->options['cookietextcolor'] ) ? esc_attr( $this->options['cookietextcolor']) : __('#555555','inventivo-cookie-notice')
        );
    }
    public function buttontext_callback()
    {
        printf(
            '<input type="text" id="buttontext" name="inventivo_cookienotice_option_name[buttontext]" value="%s" /> '.__('Example: Accept.','inventivo-cookie-notice'),
            isset( $this->options['buttontext'] ) ? esc_attr( $this->options['buttontext']) : __('Accept','inventivo-cookie-notice')
        );
    }

    public function buttontextcolor_callback()
    {
        printf(
            '<input type="text" id="buttontextcolor" name="inventivo_cookienotice_option_name[buttontextcolor]" value="%s" /> '.__('Example: #FFFFFF','inventivo-cookie-notice'),
            isset( $this->options['buttontextcolor'] ) ? esc_attr( $this->options['buttontextcolor']) : __('#FFFFFF','inventivo-cookie-notice')
        );
    }

    public function buttoncolor_callback()
    {
        printf(
            '<input type="text" id="buttoncolor" name="inventivo_cookienotice_option_name[buttoncolor]" value="%s" /> '.__('Example: #646464','inventivo-cookie-notice'),
            isset( $this->options['buttoncolor'] ) ? esc_attr( $this->options['buttoncolor']) : __('#646464','inventivo-cookie-notice')
        );
    }

    public function buttonradius_callback()
    {
        printf(
            '<input type="text" id="buttonradius" name="inventivo_cookienotice_option_name[buttonradius]" value="%s" /> '.__('Example: 20px','inventivo-cookie-notice'),
            isset( $this->options['buttonradius'] ) ? esc_attr( $this->options['buttonradius']) : __('20px','inventivo-cookie-notice')
        );
    }

    public function layerradius_callback()
    {
        printf(
            '<input type="text" id="layerradius" name="inventivo_cookienotice_option_name[layerradius]" value="%s" /> '.__('Only for fullwidth Popup. Example: 5px 5px 0 0','inventivo-cookie-notice'),
            isset( $this->options['layerradius'] ) ? esc_attr( $this->options['layerradius']) : __('5px 5px 0 0','inventivo-cookie-notice')
        );
    }

    public function backgroundcolor_callback()
    {
        printf(
            '<input type="text" id="backgroundcolor" name="inventivo_cookienotice_option_name[backgroundcolor]" value="%s" /> '.__('Example: #EFEFEF','inventivo-cookie-notice'),
            isset( $this->options['backgroundcolor'] ) ? esc_attr( $this->options['backgroundcolor']) : __('#EFEFEF','inventivo-cookie-notice')
        );
    }

    public function backgroundcolor2_callback()
    {
        printf(
            '<input type="text" id="backgroundcolor2" name="inventivo_cookienotice_option_name[backgroundcolor2]" value="%s" /> '.__('Example: #EFEFEF','inventivo-cookie-notice'),
            isset( $this->options['backgroundcolor2'] ) ? esc_attr( $this->options['backgroundcolor2']) : __('#EFEFEF','inventivo-cookie-notice')
        );
    }

    public function backgroundcolor3_callback()
    {
        printf(
            '<input type="text" id="backgroundcolor3" name="inventivo_cookienotice_option_name[backgroundcolor3]" value="%s" /> '.__('Example: #EFEFEF','inventivo-cookie-notice'),
            isset( $this->options['backgroundcolor3'] ) ? esc_attr( $this->options['backgroundcolor3']) : __('#EFEFEF','inventivo-cookie-notice')
        );
    }

    public function alignment_callback()
    {
        $selected1 = $selected2 = $selected3 = $selected4 = '';
        if($this->options['alignment'] == 'left') { $selected1 = 'selected'; }
        if($this->options['alignment'] == 'fullwidth') { $selected2 = 'selected'; }
        if($this->options['alignment'] == 'right') { $selected3 = 'selected'; }
        if($this->options['alignment'] == 'fullwidthtop') { $selected4 = 'selected'; }
        printf(
            '<select id="alignment" name="inventivo_cookienotice_option_name[alignment]">
                    <option value="left" '.$selected1.'>Left</option>
                    <option value="fullwidth" '.$selected2.'>Full width</option>
                    <option value="right" '.$selected3.'>Right</option>
                    <option value="fullwidthtop" '.$selected4.'>Full width (Top)</option>
		        </select>',
            isset( $this->options['alignment'] ) ? esc_attr( $this->options['alignment']) : __('Alignment','inventivo-cookie-notice')
        );
    }

    public function cookieduration_callback()
    {
        printf(
            '<input type="text" id="cookieduration" name="inventivo_cookienotice_option_name[cookieduration]" value="%s" /> '.__('Example: 365','inventivo-cookie-notice'),
            isset( $this->options['cookieduration'] ) ? esc_attr( $this->options['cookieduration']) : __('365','inventivo-cookie-notice')
        );
    }

    public function admin_notice_get_pro() {
        $user_id = get_current_user_id();
        if ( ! get_user_meta( $user_id, 'inv_notice_dismissed_cookie_notice' ) && ! get_user_meta( $user_id, 'inv_notice_dismissed' ) ) {
            echo '<div class="notice notice-success is-dismissible">
                <div class="hreflang-x-default-tag-for-wpml-inventivo-wrapper">
                    <div class="hreflang-x-default-tag-for-wpml-inventivo-element">
                        <a href="https://www.inventivo.de/webseiten/wordpress-plugins" target="_blank">
                            <img src="' . esc_url( plugins_url( 'admin/images/icon-256x256-1.png', __FILE__ ) ) . '" />
                        </a>
                    </div>
                    <div class="hreflang-x-default-tag-for-wpml-inventivo-element">
                        <h2>More free WordPress plugins</h2>
                        <p><strong>Thanks for using this plugin.</strong><br />
                        We build small and focused WordPress plugins and publish them for free.<br />
                        </p>
                        <p>
                            <a class="wp-core-ui button" target="_blank" href="https://www.inventivo.de/webseiten/wordpress-plugins" style="color: #FFFFFF; background: #A6CE38; border-color: #A6CE38">
                            See all our plugins
                            </a>
                            <br /><br />
                            <a href="' . esc_url( wp_nonce_url( add_query_arg( 'inv-notice-dismissed', '1' ), 'inv_notice_dismiss' ) ) . '" style="color: #AAAAAA;">Dismiss</a>
                        </p>
                    </div>
                </div>
            </div>';
        }
    }

    public function inv_admin_style() {
        if ( get_user_meta( get_current_user_id(), 'inv_notice_dismissed_cookie_notice' ) || get_user_meta( get_current_user_id(), 'inv_notice_dismissed' ) ) {
        	return;
        }

        wp_enqueue_style('inventivo-cookie-notice-admin-styles', plugins_url('admin/css/admin-styles.css', __FILE__));
    }

    public function notice_dismissed()
    {
        if ( ! isset( $_GET['inv-notice-dismissed'] ) ) {
            return;
        }

        if ( ! current_user_can( 'read' ) ) {
            return;
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'inv_notice_dismiss' ) ) {
            return;
        }

        add_user_meta( get_current_user_id(), 'inv_notice_dismissed_cookie_notice', 'true', true );
    }

    public function plugin_deactivate()
    {
        $user_id = get_current_user_id();
        delete_user_meta( $user_id, 'inv_notice_dismissed_cookie_notice' );
    }
}


//if (is_admin()) {
$my_settings_page = new InvCookieSettingsPage();
add_action( 'wp_enqueue_scripts', array($my_settings_page,'inv_cookie_notice_styles' ));
//}