<?php
/*
Plugin Name: TM Replace Howdy
Plugin URI: http://technicalmastermind.com/plugins/tm-replace-howdy/
Description: The Replace Howdy Plugin is designed to replace the word "Howdy" in the WordPress backend header with something else. By default it randomly pulls from a list of several replacement words and phrases such as "Hello", "Welcome", and "Get to the choppa". The plugin also comes with a menu where you can limit it to "professional" sounding greetings, set a static word or phrase, or add your own list (which can be by itself or added into the default list for even more variety)! NOTE: If you have any issues or suggestions please post them in the WordPress.org forums and tag it with "tm-replace-howdy" so I will see it!
Author: David Wood
Author URI: http://davidwood.ninja/
Version: 1.4.2
License: GPL v3
Text Domain: tm-replace-howdy

Contributors: Micah Wood
Contributors URI: http://www.orderofbusiness.net/micah-wood/
*/

define( 'TM_REPLACE_HOWDY_URL', plugins_url( '', __FILE__ ) );
define( 'TM_REPLACE_HOWDY_PATH', dirname( __FILE__ ) );
define( 'TM_REPLACE_HOWDY_FILE', __FILE__ );

/**
 * Class TM_Replace_Howdy
 */
class TM_Replace_Howdy {

	protected
		$version = '1.4.2', // Plugin version number for internal use
		// Array containing standard greetings
		$tm_howdy_fun = array(
			'Chow,',
			'Hello,',
			'Aloha,',
			'Bonjour,',
			'Welcome,',
			'Greetings,',
			'Konnichiwa,',
			'Get to the choppa,',
			'Live long and prosper,',
			'We require a shrubbery,',
			'May the force be with you,',
			'Pay no attention to the man behind the curtain,',
			'Wassap,',
			'Don\'t mind me,',
			'Looking good today,',
			'Eat all your vegetables,',
			'I can see you right now,',
			'I can hear you breathing,',
			'Have you showered recently,',
			'There is a ninja behind you,',
			'Do you know what time it is,',
			'Wipe that grin off your face,',
			'Don\'t make me come down there,',
			'You just gained +5 WordPress skills,',
			'I know you are sitting at a computer,',
			'Did you remember to brush your teeth,',
			'Did you put on clean underwear this morning,',
			'Don\'t read this directly or you will get a lazy eye,',
		),
		// Array containing "professional" greetings
		$tm_howdy_pooper = array(
			'Chow,',
			'Hello,',
			'Aloha,',
			'Bonjour,',
			'Welcome,',
			'Greetings,',
			'Konnichiwa,',
		),
		$tm_help,
		$replace_all = false;

	function __construct() {
		// Check if an upgrade needs to be performed
		if ( get_option( 'tm_replace_howdy_ver' ) != $this->version ) {
			$this->upgrade();
		}

		if ( get_option( 'tm_replace_howdy_all_languages' ) == 'replace_all' ) {
			$this->replace_all = true;
		}

		// Register our actions
		add_action( 'plugins_loaded', array( $this, '_load_textdomain' ) );
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );

		// Register deactivation hook
		register_deactivation_hook( TM_REPLACE_HOWDY_FILE, array( $this, 'deactivation' ) );
	}

	/**
	 * Loads our plugins translations, if any.
	 * @since 1.4.2
	 */
	public function _load_textdomain() {
		load_plugin_textdomain( 'tm-replace-howdy' );
	}

	/**
	 * Register our filters that actually do the work
	 */
	function init() {
		if ( $this->replace_all ) {
			if ( is_admin() || is_user_logged_in() ) {
				add_filter( 'gettext', array( $this, 'replace_howdy' ), 10, 2 );
			}
		} else {
			add_filter( 'admin_bar_menu', array( $this, 'replace_howdy_admin_bar' ), 25 );
		}
	}

	/**
	 * Handles replacing text directly in the admin bar, does not work for non-english languages
	 * @param wp_admin_bar $wp_admin_bar
	 */
	function replace_howdy_admin_bar( $wp_admin_bar ) {
		$account  = $wp_admin_bar->get_node( 'my-account' );
		$new_title = str_replace( 'Howdy, ', $this->get_string(), $account->title );
		$wp_admin_bar->add_node(
			array(
				'id'    => 'my-account',
				'title' => $new_title,
			)
		);
	}

	/**
	 * Handles replacing text on translated howdy strings
	 * @param string $translated_text
	 * @param string $text
	 *
	 * @return mixed|string
	 */
	function replace_howdy( $translated_text, $text ) {
		if ( $this->replace_all ) {
			$tm_text = $text;
		} else {
			$tm_text = $translated_text;
		}
		// Find out what version is in use and set replacement value appropriately
		if ( $tm_text == 'Howdy, %1$s' ) {
			$translated_text = $this->get_string( '%1$s' );
		}

		return $translated_text;
	}

	/**
	 * Returns a string containing our text to replace `howdy` with
	 * @param string $sub_value
	 *
	 * @return mixed|string
	 */
	function get_string( $sub_value = '' ) {
		// Get what mode we are in
		$mode = get_option( 'tm_replace_howdy_mode' );
		switch ( $mode ) {
			case 'pooper': // We are in a non-custom mode, use list already in PHP
				$values = apply_filters( 'tm_replace_howdy_filter_pooper_list', $this->tm_howdy_pooper );
				break;
			case 'custom': // We are in a custom mode, get list from DB
				$options = get_option( 'tm_replace_howdy_values' );
				if ( is_array( $options ) && isset( $options[1] ) && is_array( $options[1] ) ) {
					$values = $options[1];
				} else {
					// Fallback if something is wrong with the list from the DB
					$values = $this->tm_howdy_fun;
				}
				$values = apply_filters( 'tm_replace_howdy_filter_custom_list', $values );
				break;
			default: // Fallback and regular mode, use list already in PHP
				$values = apply_filters( 'tm_replace_howdy_filter_fun_list', $this->tm_howdy_fun );
		};
		$values = apply_filters( 'tm_replace_howdy_filter_list', $values );
		// Get our random value from the array and return
		$no_howdy = stripslashes( $values[ rand( 0, count( $values ) - 1 ) ] );
		if ( strpos( $no_howdy, '%%' ) ) {
			$no_howdy = str_replace( '%%', trim( $sub_value ), $no_howdy );
		} else {
			$no_howdy .= ' ' . trim( $sub_value );
		}

		return $no_howdy;
	}

	/**
	 * Adds our admin page to the WP admin
	 */
	function add_admin_page() {
		// Add sub-menu under settings
		$this->tm_help = add_options_page(
			__( 'Replace Howdy Settings', 'tm-replace-howdy' ),
			__( 'Replace Howdy', 'tm-replace-howdy' ),
			apply_filters( 'tm_replace_howdy_admin_level', 'manage_options' ),
			'tm_replace_howdy',
			array( $this, 'options_page' )
		);
		add_action( 'load-' . $this->tm_help, array( $this, 'options_help' ) );
		add_action( 'load-' . $this->tm_help, array( $this, 'options_save_settings' ) );
	}

	function options_page() {
		// Check that the user has permission to be on this page
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'tm-replace-howdy' ) );
		}
		require( TM_REPLACE_HOWDY_PATH . '/options.php' );
	}

	function options_save_settings() {
		$redirect = false;
		if ( isset( $_POST['tm_replace_howdy_form'] ) ) {
			// Update settings in DB, we are saving
			if ( isset( $_POST['tm_rh_mode'] ) ) {
				$mode = sanitize_text_field( $_POST['tm_rh_mode'] );
				update_option( 'tm_replace_howdy_mode', esc_attr( $mode ) );
			}
			if ( isset( $_POST['tm_rh_list'] ) ) {
				$list = explode( ';', $_POST['tm_rh_list'] );
				$tmp  = array();
				foreach ( $list as $item ) {
					$test = sanitize_text_field( trim( $item ) );
					if ( ! empty( $test ) ) {
						$tmp[] = $test;
					}
				}
				$values[1] = $tmp;
			}
			if ( isset( $_POST['tm_rh_custom'] ) ) {
				$values[0] = sanitize_text_field( $_POST['tm_rh_custom'] );
			}
			if ( isset( $_POST['tm_replace_howdy_all_languages'] ) && 'replace_all' == $_POST['tm_replace_howdy_all_languages'] ) {
				$replace_all = sanitize_text_field( $_POST['tm_replace_howdy_all_languages'] );
			} else {
				$replace_all = '';
			}
			if ( isset( $_POST['tm_rh_save'] ) && $_POST['tm_rh_save'] == 'delete' ) {
				$save_data = sanitize_text_field( $_POST['tm_rh_save'] );
			} else {
				$save_data = '';
			}
			if ( isset( $values[0] ) && isset( $values[1] ) && $values[0] == 'custom_plus' && is_array( $values[1] ) ) {
				$values[1] = array_merge( $values[1], $this->tm_howdy_fun );
			}
			// Update options in DB
			update_option( 'tm_replace_howdy_values', $values );
			update_option( 'tm_replace_howdy_all_languages', esc_attr( $replace_all ) );
			update_option( 'tm_replace_howdy_save', esc_attr( $save_data ) );
			$redirect = true;
		} elseif ( isset( $_POST['tm_replace_howdy_form_defaults'] ) ) {
			// Clear settings in DB, we are resetting
			delete_option( 'tm_replace_howdy_mode' );
			delete_option( 'tm_replace_howdy_values' );
			delete_option( 'tm_replace_howdy_all_languages' );
			delete_option( 'tm_replace_howdy_save' );
			$redirect = true;
		}
		if ( $redirect ) {
			wp_redirect( admin_url( 'options-general.php?page=tm_replace_howdy' ) );
			exit();
		}
	}

	function options_help() {
		$screen = get_current_screen();
		if ( $screen->id != $this->tm_help ) {
			return;
		}

		$screen->add_help_tab( array(
			'id'      => 'tm_replace_howdy_normal',
			'title'   => __( 'Normal Mode', 'tm-replace-howdy' ),
			'content' => sprintf(
				__( '<h3>Normal Mode</h3> <p>"Howdy" is replaced by one of the words/phrases in the default list. The default list is available for viewing on <a href="%s">TechnicalMastermind.com</a></p>', 'tm-replace-howdy' ),
				esc_url( 'http://technicalmastermind.com/plugins/tm-replace-howdy/' )
		) ) );

		$screen->add_help_tab( array(
			'id'      => 'tm_replace_howdy_professional',
			'title'   => __( 'Professional Mode', 'tm-replace-howdy' ),
			'content' => sprintf(
				__( '<h3>Professional Mode</h3> <p>This mode is here because I realize that while many people enjoy random and exciting words/phrases to replace their "Howdy" with, many people (and businesses) prefer a more professional approach. Because of this, this mode contains only the more business appropriate greetings such as "Hello", "Aloha" and "Konnichiwa". The full list can be viewed on <a href="%s">TechnicalMastermind.com</a></p>', 'tm-replace-howdy' ),
				esc_url( 'http://technicalmastermind.com/plugins/tm-replace-howdy/' )
		) ) );

		$screen->add_help_tab( array(
			'id'      => 'tm_replace_howdy_custom',
			'title'   => __( 'Custom Mode', 'tm-replace-howdy' ),
			'content' => __( '<h3>Custom Mode</h3> <p>This mode is for people that like to be unique, want to add an item or two to the list, or simply want it to always same the same thing. To create your own word/phrase list, simply type them all into the field labeled as "Custom Word List". Make sure each word/phrase has no spaces before or after it (between words in a phrase is allowed). Separate each word/phrase with a semi-colon(;).</p>

		<p><table>
		    <tr><th colspan="2">Examples of different input/output cases<br/>(Assuming username is "admin")</th></tr>
		    <tr><th>Input</th><th>Output</th></tr>
		    <tr><td>Hello,</td><td>Hello, admin</td></tr>
		    <tr><td>Hello</td><td>Hello admin</td></tr>
		    <tr><td>Hello!</td><td>Hello! admin</td></tr>
		</table></p>

		<p><strong>Custom Mode Options:</strong> When you use custom mode there are two variations of it (chosen through the "Custom mode options" menu item). 1) "Custom list + our list", this takes what you put in the "Custom word list" and adds it to our default list of words and phrases. 2) "Custom list only", this mode takes what you enter into the "Custom word list" and uses that as the entire list.</p>', 'tm-replace-howdy' )
		) );
	}

	function upgrade() {
		// Check for signs of previous versions
		$old_options = get_option( 'techm_replace_howdy' );
		$old_list    = get_option( 'techm_replace_howdy_values' );
		if ( $old_options ) {
			if ( $old_options['mode'] == 'static' ) {
				$mode = 'custom';
			} else {
				$mode = $old_options['mode'];
			}
			// Remove old options from DB
			delete_option( 'techm_replace_howdy' );
			// Add new options to DB
			update_option( 'tm_replace_howdy_mode', $mode );
		}
		if ( is_array( $old_list ) ) {
			$values    = array( 'custom_plus', array() );
			$values[1] = array_diff( $old_list, $this->tm_howdy_fun );
			update_option( 'tm_replace_howdy_values', $values );
			delete_option( 'techm_replace_howdy_values' );
		}
		// Store new version number
		update_option( 'tm_replace_howdy_ver', $this->version );
	}

	function deactivation() {
		$save_data = get_option( 'tm_replace_howdy_save' );
		if ( $save_data == 'delete' ) {
			// Delete all saved data
			delete_option( 'tm_replace_howdy_mode' );
			delete_option( 'tm_replace_howdy_values' );
			delete_option( 'tm_replace_howdy_save' );
			delete_option( 'tm_replace_howdy_ver' );
		}
	}
}

new TM_Replace_Howdy;
