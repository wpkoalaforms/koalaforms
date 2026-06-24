<?php

/**
 * Plugin Name:       Koala Forms
 * Description:       Build contact forms, multi-step forms, and registration forms using Gutenberg blocks.
 * Requires at least: 6.4
 * Tested up to:      7.0
 * Requires PHP:      7.4
 * Version:           1.1.0
 * Author:            Koala Forms
 * Text Domain:       koalaforms
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace KoalaForms;

if (!defined('ABSPATH')) {
	header( sanitize_text_field( wp_unslash( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) ) . ' 404 Not Found' );
	exit;
}

if (!class_exists('KoalaForms')) {

	final class KoalaForms{

		// One is the loneliest number that you'll ever do.
		private static $instance;

		public static function instance() {
            if (!isset(self::$instance) && !( self::$instance instanceof KoalaForms )) {
                self::$instance = new KoalaForms;
				self::$instance->constants();

				spl_autoload_register(array(self::$instance, 'classloader'));
				add_action('init', array(self::$instance, 'set_common_objects'));
				add_action( 'koalaforms_cleanup_logs', [ 'KoalaForms\Logger', 'cleanup' ] );
				add_action( 'koalaforms_log', [ 'KoalaForms\Logger', 'log' ], 10, 5 );
				
				
					if (is_admin()){
					add_action('admin_init', array(self::$instance,'admin_init'));
				}


				register_activation_hook(__FILE__, array(self::$instance, 'activation'));
                register_deactivation_hook(__FILE__, array(self::$instance, 'deactivation'));
            }
            return self::$instance;
        }

		// Setup plugin constants additional to defined in AppUtility class.
		private function constants() {
			// Plugin Folder URL.
			if (!defined('KOALAFORMS_PLUGIN_URL')) {
				define('KOALAFORMS_PLUGIN_URL', plugin_dir_url(__FILE__));
				define('KOALAFORMS_VERSION', '1.1.0' );
			}
		}

		// Loads PSR-4-style plugin classes.
		public function classloader($class) {
			static $ns_offset;
			if (strpos($class, __NAMESPACE__ . '\\') === 0) {
				if ($ns_offset === NULL) {
					$ns_offset = strlen(__NAMESPACE__) + 1;
				}
				include __DIR__ . '/inc/' . strtr(substr($class, $ns_offset), '\\', '/') . '.php';
			}
		}

		// Loads features for admin side
		public function admin_init(){
			Blocks::init();
			
		}

		// Setup common objects.
        public function set_common_objects() { 
			new RegisterTypes(); // Registering all the custom post types
			AdminLoader::create_instance();
			Email::create_instance();

			new AdminAjax();
			FormRender::get_instance();
			Form::create_instance();
			Analytics::create_instance();

			$this->load_text_domain();
			remove_post_type_support(AppUtility::SUBMISSION_POST_TYPE, 'editor');
        }

		// Translations are auto-loaded by WordPress since 4.6.
        public function load_text_domain() {}

		// Invoked on activation
        public function activation()
        {       
            if (is_multisite()) { 
                global $wpdb;
                foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $blog_id) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                    switch_to_blog($blog_id);
                    PluginOptions::create_default_options();
                    restore_current_blog();
                } 

            } else {
                PluginOptions::create_default_options();
            }
            Logger::create_table();
            if ( ! wp_next_scheduled( 'koalaforms_cleanup_logs' ) ) {
                wp_schedule_event( time(), 'daily', 'koalaforms_cleanup_logs' );
            }
            do_action( AppUtility::pluginKey('_installed') ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
        }
        
        // Invoked on Deactivation
        public function deactivation(){
            wp_clear_scheduled_hook( 'koalaforms_cleanup_logs' );
        }
	}
}

KoalaForms::instance();