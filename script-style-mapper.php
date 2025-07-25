<?php
/*
Plugin Name: Script & Style Dependency Mapper (Wireframe-style, Duplicates, Panel Inside Cards)
Description: Visualizes enqueued scripts/styles with dependency trees and contextual duplicates slide panels inside respective tree cards.
Version: 2.2
Author: Your Name
License: GPL2
*/

defined('ABSPATH') || exit;

class Script_Style_Mapper {
    public function __construct() {
        add_action('admin_bar_menu', [$this, 'add_admin_bar_node'], 100);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_footer', [$this, 'localize_current_page_assets_to_js']);
        add_action('wp_footer', [$this, 'localize_current_page_assets_to_js'], 105);
        add_action('admin_footer', [$this, 'print_overlay_html']);
        add_action('wp_footer', [$this, 'print_overlay_html'], 110);
    }

    public function add_admin_bar_node($wp_admin_bar) {
        if (!is_user_logged_in() || !current_user_can('manage_options')) return;
        $wp_admin_bar->add_node([
            'id'    => 'script_style_mapper_view',
            'title' => 'Script & Style Mapper',
            'href'  => '#',
            'meta'  => [
                'class' => 'script-style-mapper-admin-bar',
                'title' => 'View currently loaded scripts and styles',
                'onclick' => 'event.preventDefault(); ScriptStyleMapperFrontend.toggleOverlay();',
            ]
        ]);
    }

    public function enqueue_assets() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) return;
        wp_enqueue_style('jstree-style', 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/themes/default/style.min.css');
        wp_enqueue_script('jstree-script', 'https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.14/jstree.min.js', ['jquery'], '3.3.14', true);
        wp_enqueue_script('script-style-mapper-frontend-js', plugin_dir_url(__FILE__) . 'admin/assets/mapper-frontend.js', ['jquery', 'jstree-script'], '2.2', true);
        wp_enqueue_style('script-style-mapper-css', plugin_dir_url(__FILE__) . 'admin/assets/mapper.css');
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
        ?>
        <div id="ssm-frontend-overlay" class="ssm-flat-modal">
          <div id="ssm-frontend-overlay-content">
            <button id="ssm-overlay-close" aria-label="Close">&times;</button>
            <div id="ssm-flat-header">Assets Audit</div>
            <div id="ssm-flat-controls">
              <label><input type="checkbox" id="ssm-duplicate-filter" value="1" /> Show duplicates only</label>
            </div>
            <div class="ssm-flat-box" id="ssm-scripts-box">
              <div id="ssm-flat-scripts-label">scripts tree</div>
              <div id="ssm-frontend-scripts-tree"></div>
              <div id="ssm-duplicate-info-panel-scripts" class="ssm-duplicate-info-panel">
                <button class="ssm-dup-panel-close" title="Close">&times;</button>
                <h3>Duplicate Asset Instances</h3>
                <ul class="ssm-duplicate-list"></ul>
              </div>
            </div>
            <div class="ssm-flat-box" id="ssm-styles-box">
              <div id="ssm-flat-styles-label">styles tree</div>
              <div id="ssm-frontend-styles-tree"></div>
              <div id="ssm-duplicate-info-panel-styles" class="ssm-duplicate-info-panel">
                <button class="ssm-dup-panel-close" title="Close">&times;</button>
                <h3>Duplicate Asset Instances</h3>
                <ul class="ssm-duplicate-list"></ul>
              </div>
            </div>
          </div>
        </div>
        <?php
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

new Script_Style_Mapper();
