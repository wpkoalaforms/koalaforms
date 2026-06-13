<?php
/**
 * @description: The Blocks class provides functionality for managing Gutenberg block categories 
 * 				 and registering custom Gutenberg blocks.
 */

namespace KoalaForms;

class Blocks {
	

	// Initializes Blocks
	public static function init(): void {
		add_filter('block_categories_all', __CLASS__ . '::registerCategory', 10, 2);
		add_action('admin_enqueue_scripts', __CLASS__.'::registerScripts');
		add_action('enqueue_block_editor_assets', __CLASS__.'::load_editor' );
	}

	public static function load_editor($hook){
		$current_screen = get_current_screen();
		if ($current_screen && self::isFormScreen($current_screen->post_type) && $current_screen->is_block_editor){
			wp_enqueue_script(
				AppUtility::PREFIX.'form-settings', // Handle for the script
				KOALAFORMS_PLUGIN_URL. 'build/index.js' , // Path to editor.js
				array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'), // Dependencies
				KOALAFORMS_VERSION,
				true
			);

			
			$captcha_options = Captcha::active_captcha_options();

			wp_localize_script(
				AppUtility::PREFIX.'form-settings',
				'koalaformsConfig',
				array(
					'formPostType'    => AppUtility::FORM_POST_TYPE,
					'captchaOptions'  => $captcha_options,
					'inputBlockTypes' => AppUtility::INPUT_BLOCK_TYPES,
				)
			);
		}
	}

	public static function registerScripts(){
		$current_screen = get_current_screen();
		if (isset($current_screen->post_type) && self::isFormScreen($current_screen->post_type) && $current_screen->is_block_editor) {
			// Register global CSS
			wp_enqueue_style(
				'koalaforms-gutenberg-style',
				KOALAFORMS_PLUGIN_URL. 'assets/admin/gutenberg.css',
				array(),
				KOALAFORMS_VERSION
			);
		}
		
	}

	// Registoring block category
	public static function registerCategory($categories, $editor): array {
		if (isset($editor->post->post_type) && self::isFormScreen($editor->post->post_type)){
			return [
				[
					'slug'  => 'koalaforms',
					'title' => __('Koala Form', 'koalaforms'),
					'icon' => 'data:image/svg+xml;base64,' . base64_encode(self::formIcon())
				],
				...$categories,
			];
		}
		return $categories;
	}

	private static function isFormScreen($post_type){
		return $post_type === AppUtility::FORM_POST_TYPE;
	}

	// Returns icon for the category
	private static function formIcon(): string{
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"> <path d="M19.5,0.5h-15C3.2,0.5,2,1.7,2,3v18c0,1.3,1.2,2.5,2.5,2.5h15c1.3,0,2.5-1.2,2.5-2.5V3C22,1.7,20.8,0.5,19.5,0.5z M20,21 c0,0.3-0.2,0.5-0.5,0.5h-15C4.2,21.5,4,21.3,4,21V3c0-0.3,0.2-0.5,0.5-0.5h15C19.8,2.5,20,2.7,20,3V21z"></path> <rect x="5.5" y="5.5" width="13" height="2"></rect> <rect x="5.5" y="9.5" width="9" height="2"></rect> <rect x="5.5" y="13.5" width="10" height="2"></rect> <rect x="5.5" y="17.5" width="4" height="2"></rect> </svg>';
	}

	// Returns a list of all the blocks
	public static function getBlocksName(): array {
		return AppUtility::BLOCKS;
	}

	public static function renderBlock($attributes){
		return FormRender::renderField($attributes);
	}

	//The absolute filesystem base path of this plugin.
	public static function getBasePath(): string {
		return dirname(__DIR__);
	}
}