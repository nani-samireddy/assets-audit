<?php
/**
 * Template for displaying the scripts and styles modal overlay.
 *
 * @package AssetsAudit\Templates
 */

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