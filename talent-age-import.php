<?php
/**
 * Plugin Name: Talent Age Import
 * Description: Talent Age Import
 * Version:     1.0.0
 * Author:      AlexK
 */

namespace Talent_Age_Import;
use Talent_Age_Import\classes\Import;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


final class App {

	const TEXT_DOMAIN = 'talent-page-import';
	const PLUGIN_VERSION = '1.0.0';
	public static $pluginURL = null;
	public static $pluginPath = null;

	public function __construct() {
		self::$pluginURL = plugin_dir_url(__FILE__);
		self::$pluginPath = plugin_dir_path(__FILE__);

		add_action('init', function () {
			Import::init();
		});

		add_action('wp_enqueue_scripts', [__CLASS__, 'registerAssets']);
	}

	public static function registerAssets() {
		wp_register_style('styles', self::$pluginURL . 'assets/css/styles.css', '',self::PLUGIN_VERSION);

		wp_enqueue_script('jquery');
		wp_register_script('script', self::$pluginURL . 'assets/js/script.js', ['jquery'], self::PLUGIN_VERSION);
	}

}

require_once 'classes/Import.php';
require_once 'classes/CSVReader.php';

new App();

