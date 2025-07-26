var ScriptStyleMapperFrontend = (function($) {
	var overlay, closeButton, dupFilterCheckbox,
		dupPanelScripts, dupPanelStyles, dupListScripts, dupListStyles,
		dupPanelCloseScripts, dupPanelCloseStyles;
	var duplicateFilterActive = false;

	function createNode(handle, allAssets, processed) {
		if (processed.has(handle)) return null;
		processed.add(handle);

		var asset = allAssets[handle];
		if (!asset) return null;

		var isDuplicate = asset.duplicates && asset.duplicates.length > 1;
		var duplicateFlag = isDuplicate ? ' <span class="ssm-duplicate-flag">&#9888;</span>' : '';
		var text = $('<span/>').text(handle).html() + duplicateFlag;

		var $textWrapper = $('<span/>')
			.addClass('ssm-node-text')
			.attr('title', asset.src || 'No source URL')
			.html(text);

		var children = [];
		if (asset.deps && asset.deps.length) {
			asset.deps.forEach(function(dep) {
				var child = createNode(dep, allAssets, processed);
				if (child) children.push(child);
			});
		}

		return {
			text: $textWrapper.prop('outerHTML'),
			children: children,
			li_attr: {
				title: handle,
				'data-duplicates': isDuplicate ? asset.duplicates.join(',') : '',
				class: isDuplicate ? 'ssm-duplicate-node' : ''
			}
		};
	}

	function findRoots(allAssets) {
		var allHandles = new Set(Object.keys(allAssets));
		var childHandles = new Set();

		Object.values(allAssets).forEach(function(asset) {
			if (asset.deps && asset.deps.length) {
				asset.deps.forEach(function(dep) {
					childHandles.add(dep);
				});
			}
		});

		return Array.from(allHandles).filter(function(h) {
			return !childHandles.has(h);
		});
	}

	function buildTreeData(allAssets, duplicatesOnly) {
		var processed = new Set();
		var filteredAssets;

		if (duplicatesOnly) {
			filteredAssets = {};
			Object.entries(allAssets).forEach(function([handle, asset]) {
				if (asset.duplicates && asset.duplicates.length > 1) {
					filteredAssets[handle] = asset;
				}
			});
		} else {
			filteredAssets = allAssets;
		}

		var roots = findRoots(filteredAssets);
		var tree = [];

		if (roots.length === 0) {
			Object.keys(filteredAssets).forEach(function(handle) {
				var node = createNode(handle, filteredAssets, processed);
				if (node) tree.push(node);
			});
		} else {
			roots.forEach(function(root) {
				var node = createNode(root, filteredAssets, processed);
				if (node) tree.push(node);
			});
		}

		return tree;
	}

	function highlightDuplicates(handles, treeSelector) {
		$(treeSelector + ' .ssm-duplicate-highlight').removeClass('ssm-duplicate-highlight');
		handles.forEach(function(handle) {
			var node = $(treeSelector).jstree(true).get_node(handle, true);
			if (node && node.length) {
				node.find('> .jstree-anchor').addClass('ssm-duplicate-highlight');
			}
		});
	}

	function clearHighlights() {
		$('.ssm-duplicate-highlight').removeClass('ssm-duplicate-highlight');
	}

	function showDuplicatePanel(target, handles, selectedHandle) {
		var panel = (target === 'scripts' ? dupPanelScripts : dupPanelStyles),
			list = (target === 'scripts' ? dupListScripts : dupListStyles),
			otherPanel = (target === 'scripts' ? dupPanelStyles : dupPanelScripts);

		otherPanel.removeClass('active');

		list.empty();
		handles.forEach(function(h) {
			var $li = $('<li>').text(h);
			if (h === selectedHandle) {
				$li.css({fontWeight: 'bold', color: '#c33632'});
			}
			list.append($li);
		});

		panel.addClass('active');
	}

	function clearDuplicatePanels() {
		dupPanelScripts.removeClass('active');
		dupPanelStyles.removeClass('active');
		dupListScripts.empty();
		dupListStyles.empty();
	}

	function initTree(selector, assets, duplicatesOnly, context) {
		var data = buildTreeData(assets, duplicatesOnly);

		if ($(selector).jstree(true)) {
			$(selector).jstree('destroy');
		}

		$(selector).jstree({
			core: {
				data: data,
				themes: { stripes: false, dots: false },
				multiple: false
			},
			plugins: ['wholerow']
		}).off('hover_node.jstree duplicate')
		.on('hover_node.jstree', function(e, data) {
			var duplicates = data.node.li_attr['data-duplicates'];
			if (duplicates) {
				var handles = duplicates.split(',');
				highlightDuplicates(handles, selector);
			} else {
				clearHighlights();
			}
		}).on('dehover_node.jstree', function() {
			clearHighlights();
		}).on('select_node.jstree', function(e, data) {
			var duplicates = data.node.li_attr['data-duplicates'];
			if (duplicates) {
				var handles = duplicates.split(',');
				showDuplicatePanel(context, handles, data.node.id);
			} else {
				clearDuplicatePanels();
			}
		});
	}

	function openOverlay() {
		if (!overlay) {
			overlay = $('#ssm-frontend-overlay');
			closeButton = $('#ssm-overlay-close');
			dupFilterCheckbox = $('#ssm-duplicate-filter');

			dupPanelScripts = $('#ssm-duplicate-info-panel-scripts');
			dupPanelStyles = $('#ssm-duplicate-info-panel-styles');
			dupListScripts = dupPanelScripts.find('.ssm-duplicate-list');
			dupListStyles = dupPanelStyles.find('.ssm-duplicate-list');
			dupPanelCloseScripts = dupPanelScripts.find('.ssm-dup-panel-close');
			dupPanelCloseStyles = dupPanelStyles.find('.ssm-dup-panel-close');

			closeButton.on('click', closeOverlay);
			overlay.on('click', function(e) {
				if (e.target === overlay[0]) closeOverlay();
			});
			dupFilterCheckbox.on('change', function(e) {
				duplicateFilterActive = e.target.checked;
				initTree('#ssm-frontend-scripts-tree', window.ScriptStyleMapperPageData.scripts, duplicateFilterActive, 'scripts');
				initTree('#ssm-frontend-styles-tree', window.ScriptStyleMapperPageData.styles, duplicateFilterActive, 'styles');
				clearDuplicatePanels();
			});
			dupPanelCloseScripts.on('click', function() { dupPanelScripts.removeClass('active'); });
			dupPanelCloseStyles.on('click', function() { dupPanelStyles.removeClass('active'); });
		}

		if (!window.ScriptStyleMapperPageData) {
			alert('Asset data not available on this page.');
			return;
		}

		initTree('#ssm-frontend-scripts-tree', window.ScriptStyleMapperPageData.scripts, duplicateFilterActive, 'scripts');
		initTree('#ssm-frontend-styles-tree', window.ScriptStyleMapperPageData.styles, duplicateFilterActive, 'styles');

		// overlay.show();
		overlay.css({
			display: 'flex'
		});
		overlay.focus();
		clearDuplicatePanels();
	}

	function closeOverlay() {
		if (overlay) overlay.hide();
		clearHighlights();
		duplicateFilterActive = false;
		if (dupFilterCheckbox) dupFilterCheckbox.prop('checked', false);
		clearDuplicatePanels();
	}

	return {
		toggleOverlay: function() {
			if (!overlay || !overlay.is(':visible')) openOverlay();
			else closeOverlay();
		}
	};
})(jQuery);
