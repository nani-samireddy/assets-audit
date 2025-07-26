<?php

namespace AssetsAudit\Classes;

use AssetsAudit\Traits\Singleton;

class Script_Style_Mapper {

	use Singleton;
	
	public function __construct() {
		add_action('admin_bar_menu', [$this, 'add_admin_bar_node'], 100);
		add_action('admin_footer', [$this, 'localize_current_page_assets_to_js']);
		add_action('wp_footer', [$this, 'localize_current_page_assets_to_js'], 105);
		add_action('admin_footer', [$this, 'print_overlay_html']);
		add_action('wp_footer', [$this, 'print_overlay_html'], 110);
	}

	public function add_admin_bar_node($wp_admin_bar) {
		if (!is_user_logged_in() || !current_user_can('manage_options')) return;
		$wp_admin_bar->add_node([
			'id'    => 'assets_audit_view',
			'title' => 'Assets Audit',
			'href'  => '#',
			'meta'  => [
				'class' => 'assets-audit-admin-bar',
				'title' => 'View currently loaded scripts and styles',
				'onclick' => 'event.preventDefault(); ScriptStyleMapperFrontend.toggleOverlay();',
			]
		]);
	}

	public function collect_current_page_assets() {
		global $wp_scripts, $wp_styles;

		$scripts = [];
		if (isset($wp_scripts) && !empty($wp_scripts->queue)) {
			foreach ($wp_scripts->queue as $handle) {
				if (!isset($wp_scripts->registered[$handle])) continue;
				$script = $wp_scripts->registered[$handle];
				$scripts[$handle] = [
					'handle'    => $handle,
					'src'       => $this->make_abs_url($script->src),
					'deps'      => $script->deps,
					'ver'       => $script->ver,
					'in_footer' => property_exists($script, 'in_footer') ? $script->in_footer : false,
				];
			}
		}

		$styles = [];
		if (isset($wp_styles) && !empty($wp_styles->queue)) {
			foreach ($wp_styles->queue as $handle) {
				if (!isset($wp_styles->registered[$handle])) continue;
				$style = $wp_styles->registered[$handle];
				$styles[$handle] = [
					'handle' => $handle,
					'src'    => $this->make_abs_url($style->src),
					'deps'   => $style->deps,
					'ver'    => $style->ver,
				];
			}
		}

		$this->mark_duplicates($scripts);
		$this->mark_duplicates($styles);

		return [
			'scripts' => $scripts,
			'styles'  => $styles,
		];
	}

	public function localize_current_page_assets_to_js() {
		if (!is_user_logged_in() || !current_user_can('manage_options')) return;
		$data = $this->collect_current_page_assets();
		?><script>
			window.ScriptStyleMapperPageData = <?php echo wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
		</script><?php
	}

	public function print_overlay_html() {
		if (!is_user_logged_in() || !current_user_can('manage_options')) return;
		include ASSETS_AUDIT_DIR . '/src/Templates/scripts_and_styles_modal.php';
	}

	private function make_abs_url($url) {
		if (empty($url)) return '';
		if (strpos($url, '//') === false) return site_url($url);
		return $url;
	}

	private function mark_duplicates(&$assets) {
		$src_map = [];
		foreach ($assets as $handle => $asset) {
			if (empty($asset['src'])) continue;
			if (!isset($src_map[$asset['src']])) {
				$src_map[$asset['src']] = [$handle];
			} else {
				$src_map[$asset['src']][] = $handle;
			}
		}
		foreach ($assets as $handle => &$asset) {
			if (empty($asset['src'])) {
				$asset['duplicates'] = [];
				continue;
			}
			$asset['duplicates'] = count($src_map[$asset['src']]) > 1 ? $src_map[$asset['src']] : [];
		}
	}
}