<?php
/**
 * Plugin Class for Asset Audit
 * Handles Initialization of components.
 *
 * @package AssetsAudit
 */

namespace AssetsAudit;

use AssetsAudit\Classes\Script_Style_Mapper;
use AssetsAudit\Traits\Singleton;

class AssetAudit {
	
	use Singleton;

	public function __construct() {
		$this->init();
	}

	private function init() {
		Assets::get_instance();
		Script_Style_Mapper::get_instance();
	}
}
