<?php

namespace AssetsAudit;

use AssetsAudit\Traits\Singleton;

/**
 * Class Assets
 *
 * Handles registration and enqueueing of plugin assets (JS/CSS),
 * including third-party libraries like jsTree.
 *
 * @package AssetsAudit
 */
class Assets {
	use Singleton;

	/**
	 * @var string Absolute path to JS directory.
	 */
	private $js_dir;

	/**
	 * @var string Absolute path to CSS directory.
	 */
	private $css_dir;

	/**
	 * @var string URL to JS directory.
	 */
	private $js_dir_url;

	/**
	 * @var string URL to CSS directory.
	 */
	private $css_dir_url;

	/**
	 * Constructor.
	 * Initializes paths and hooks.
	 */
	public function __construct() {
		$this->js_dir      = ASSETS_AUDIT_DIR . '/src/js';
		$this->css_dir     = ASSETS_AUDIT_DIR . '/src/css';

		// Adjust the plugin main file path to your actual plugin main PHP file
		$plugin_file = ASSETS_AUDIT_DIR . '/assets-audit.php';
		$this->js_dir_url  = plugins_url('src/js', $plugin_file);
		$this->css_dir_url = plugins_url('src/css', $plugin_file);

		$this->setup_hooks();
	}

	/**
	 * Register action hooks.
	 *
	 * @return void
	 */
	private function setup_hooks() {
		add_action('admin_enqueue_scripts', [$this, 'register_external_assets']);
		add_action('wp_enqueue_scripts', [$this, 'register_external_assets']);
	}

	/**
	 * Register and enqueue required external and plugin assets.
	 *
	 * Applies only for logged-in users with manage_options capability.
	 * Uses internal register_script and register_style methods for plugin assets.
	 *
	 * @return void
	 */
	public function register_external_assets() {
		if (!is_user_logged_in() || !current_user_can('manage_options')) {
			return;
		}
		// External.
		$this->register_style( 'assets-audit-jstree-style', 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/themes/default/style.min.css', [], '3.3.14' );

		$this->register_script( 'assets-audit-jstree-script', 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/jstree.min.js', ['jquery'], '3.3.14', true, true );

		// Internal.
		$this->register_script( 'assets-audit-frontend-js', 'mapper-frontend.js', ['jquery', 'assets-audit-jstree-script'], '2.2', true, true );

		$this->register_style( 'assets-audit-frontend-css', 'mapper.css', [], false, 'all', true );
	}

	/**
	 * Read asset metadata from "{file}.asset.php" file, if exists.
	 *
	 * @param string $file File name relative to JS directory.
	 * @param array $deps Additional dependencies.
	 * @param string|bool $ver Version, or false to use filemtime.
	 *
	 * @return array Metadata array with "dependencies" and "version".
	 */
	public function get_asset_meta($file, $deps = [], $ver = false) {
		$base_name = basename($file, '.' . pathinfo($file, PATHINFO_EXTENSION));
		$meta_file = sprintf('%s/%s.asset.php', $this->js_dir, $base_name);

		if (is_readable($meta_file)) {
			$meta = require $meta_file;
		} else {
			$meta = [
				'dependencies' => [],
				'version'      => $this->get_file_version($file, $ver, $this->js_dir),
			];
		}

		$meta['dependencies'] = array_merge($deps, $meta['dependencies']);

		return $meta;
	}

	/**
	 * Register or enqueue a JavaScript file.
	 *
	 * @param string       $handle  Unique handle.
	 * @param string       $file    File path relative to src/js.
	 * @param array        $deps    Dependencies.
	 * @param string|bool  $ver     Version string or bool false to use filemtime.
	 * @param bool|array   $args    Either boolean for $in_footer or an array of args.
	 * @param bool         $enqueue True to enqueue, false to register only.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function register_script($handle, $file, $deps = [], $ver = false, $args = false, $enqueue = true) {
		// Determine if $file is an external URL or local file
		$is_external = filter_var($file, FILTER_VALIDATE_URL) !== false;

		// For external URLs, use $file directly as $src
		if ($is_external) {
			$src = $file;
			// No local asset meta for external scripts, so default meta
			$meta = [
				'dependencies' => is_array($deps) ? $deps : [],
				'version' => $ver ?: false,
			];
		} else {
			// Local file paths
			$file_path = $this->js_dir . '/' . $file;

			// Bail early if file does not exist
			if (!file_exists($file_path)) {
				return false;
			}

			$src = $this->js_dir_url . '/' . $file;

			// Get meta info from asset meta file or fallback
			$meta = $this->get_asset_meta($file, $deps, $ver);
		}

		// Register or enqueue the script
		if ($enqueue) {
			return wp_enqueue_script($handle, $src, $meta['dependencies'], $meta['version'], $args);
		}

		return wp_register_script($handle, $src, $meta['dependencies'], $meta['version'], $args);
	}

	/**
	 * Register or enqueue a CSS stylesheet.
	 *
	 * Supports both local files (relative to src/css) and external URLs.
	 *
	 * @param string       $handle  Unique handle.
	 * @param string       $file    File path relative to src/css or full external URL.
	 * @param array        $deps    Dependencies.
	 * @param string|bool  $ver     Version string or bool false to use filemtime.
	 * @param string       $media   Media attribute (default 'all').
	 * @param bool         $enqueue True to enqueue, false to register only.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function register_style($handle, $file, $deps = [], $ver = false, $media = 'all', $enqueue = true) {
		// Check if $file is an external URL
		$is_external = filter_var($file, FILTER_VALIDATE_URL) !== false;

		if ($is_external) {
			$src = $file;
			// No local file meta for external styles; use passed deps and version directly
			$version = $ver ?: false;
		} else {
			// Local file path
			$file_path = $this->css_dir . '/' . $file;

			// Bail if file does not exist locally
			if (!file_exists($file_path)) {
				return false;
			}

			$src = $this->css_dir_url . '/' . $file;
			$version = $this->get_file_version($file, $ver, $this->css_dir);
		}

		// Register or enqueue style
		if ($enqueue) {
			return wp_enqueue_style($handle, $src, $deps, $version, $media);
		}

		return wp_register_style($handle, $src, $deps, $version, $media);
	}


	/**
	 * Get file version string for a file.
	 *
	 * @param string       $file  File relative to directory.
	 * @param string|bool  $ver   Explicit version or false.
	 * @param string       $dir   Directory absolute path.
	 *
	 * @return string|int|false File modification time or passed version.
	 */
	public function get_file_version($file, $ver = false, $dir = '') {
		if (!empty($ver)) {
			return $ver;
		}

		$dir = $dir ?: $this->js_dir;
		$file_path = $dir . '/' . $file;

		return file_exists($file_path) ? filemtime($file_path) : false;
	}
}
