(function () {
	'use strict';

	const form = document.getElementById('instapass-preview-form');
	if (!form || !window.InstapassAutomationPreview) return;

	const result = document.getElementById('instapass-preview-result');
	const button = document.getElementById('instapass-preview-submit');
	const commandInput = document.getElementById('instapass-command');

	function element(tag, className, text) {
		const node = document.createElement(tag);
		if (className) node.className = className;
		if (text !== undefined) node.textContent = String(text);
		return node;
	}

	function showMessage(message, isError) {
		result.replaceChildren(element('p', isError ? 'notice notice-error inline' : 'notice notice-info inline', message));
		result.hidden = false;
	}

	function valueOrLabel(value, emptyLabel) {
		return value === '' || value === null || value === undefined ? emptyLabel : value;
	}

	function addRow(table, label, before, after) {
		const row = document.createElement('tr');
		row.append(element('th', '', label), element('td', '', valueOrLabel(before, 'No sale')),
			element('td', '', valueOrLabel(after, 'No sale')));
		table.append(row);
	}

	function renderPreview(data) {
		const panel = element('section', 'instapass-preview-card');
		panel.append(element('h2', '', data.name + (data.sku ? ' (' + data.sku + ')' : '')));
		panel.append(element('p', '', 'Currency: ' + (data.currency || 'store currency')));

		const table = document.createElement('table');
		table.className = 'widefat striped instapass-preview-table';
		const head = document.createElement('thead');
		const headerRow = document.createElement('tr');
		headerRow.append(element('th', '', 'Price'), element('th', '', 'Current'), element('th', '', 'Proposed'));
		head.append(headerRow);
		table.append(head);
		const body = document.createElement('tbody');
		addRow(body, 'Regular price', data.before.regular_price, data.after.regular_price);
		addRow(body, 'Sale price', data.before.sale_price, data.after.sale_price);
		table.append(body);
		panel.append(table);

		const discount = Number(data.actual_discount_percent);
		panel.append(element('p', 'instapass-preview-discount', 'Actual discount: ' + (Number.isFinite(discount) ? discount.toFixed(2) : '0.00') + '%'));
		panel.append(element('p', 'description', 'Existing sale start/end dates are preserved. This is a preview only; no product has been updated.'));
		result.replaceChildren(panel);
		result.hidden = false;
	}

	form.addEventListener('submit', async function (event) {
		event.preventDefault();
		const command = commandInput.value.trim();
		if (!command) return;

		button.disabled = true;
		button.textContent = 'Preparing preview…';
		result.hidden = true;
		try {
			const response = await fetch(window.InstapassAutomationPreview.restUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.InstapassAutomationPreview.nonce
				},
				body: JSON.stringify({ command: command })
			});
			const data = await response.json();
			if (!response.ok) throw new Error(data.message || 'Could not prepare this preview.');
			renderPreview(data);
		} catch (error) {
			showMessage(error.message || 'Could not reach the preview service. Check the connection and try again.', true);
		} finally {
			button.disabled = false;
			button.textContent = 'Preview change';
		}
	});
}());
