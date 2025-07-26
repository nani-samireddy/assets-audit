<?php
/*
Plugin Name: Assets Audit
Description: Visualizes enqueued scripts and styles with dependency trees and contextual duplicates slide panels inside respective tree cards.
Version: 1.0
Author: Your Name
License: GPL2
*/

use AssetsAudit\AssetAudit;

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
if (!defined( 'ASSETS_AUDIT_DIR' ) ) {
	define( 'ASSETS_AUDIT_DIR',  __DIR__ );
}
if (!defined( 'ASSETS_AUDIT_URL' )) {
	define( 'ASSETS_AUDIT_URL', plugins_url('', __FILE__) );
}

// load the autoloader.
require_once ASSETS_AUDIT_DIR . '/vendor/autoload.php';

AssetAudit::get_instance();
