(function () {
	'use strict';

	const form = document.getElementById('instapass-preview-form');
	if (!form || !window.InstapassAutomationPreview) return;

	const config = window.InstapassAutomationPreview;
	const result = document.getElementById('instapass-preview-result');
	const history = document.getElementById('instapass-operation-history');
	const button = document.getElementById('instapass-preview-submit');
	const commandInput = document.getElementById('instapass-command');

	function element(tag, className, text) {
		const node = document.createElement(tag);
		if (className) node.className = className;
		if (text !== undefined) node.textContent = String(text);
		return node;
	}

	function valueOrLabel(value, emptyLabel) {
		return value === '' || value === null || value === undefined ? (emptyLabel || '—') : value;
	}

	async function api(route, method, body) {
		const options = {
			method: method,
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': config.nonce }
		};
		if (body) {
			options.headers['Content-Type'] = 'application/json';
			options.body = JSON.stringify(body);
		}
		const response = await fetch(config.urls[route], options);
		const data = await response.json();
		if (!response.ok) throw new Error(data.message || 'The request could not be completed.');
		return data;
	}

	function addPriceRow(table, label, before, after) {
		const row = document.createElement('tr');
		const emptyLabel = 'Regular price' === label ? 'Not set' : 'No sale';
		row.append(element('th', '', label), element('td', '', valueOrLabel(before, emptyLabel)), element('td', '', valueOrLabel(after, emptyLabel)));
		table.append(row);
	}

	function renderPreview(data) {
		const panel = element('section', 'instapass-preview-card');
		panel.append(element('h2', '', data.name + (data.sku ? ' (' + data.sku + ')' : '')));
		panel.append(element('p', '', 'Currency: ' + (data.currency || 'store currency')));
		const table = document.createElement('table');
		table.className = 'widefat striped instapass-preview-table';
		const head = document.createElement('thead');
		const header = document.createElement('tr');
		header.append(element('th', '', 'Price'), element('th', '', 'Current'), element('th', '', 'Proposed'));
		head.append(header);
		table.append(head);
		const body = document.createElement('tbody');
		addPriceRow(body, 'Regular price', data.before.regular_price, data.after.regular_price);
		addPriceRow(body, 'Sale price', data.before.sale_price, data.after.sale_price);
		table.append(body);
		panel.append(table);
		const discount = Number(data.actual_discount_percent);
		panel.append(element('p', 'instapass-preview-discount', 'Actual discount: ' + (Number.isFinite(discount) ? discount.toFixed(2) : '0.00') + '%'));
		panel.append(element('p', 'description', 'Existing sale dates are preserved. Preview expires at ' + data.expires_at + ' UTC.'));
		panel.append(element('p', 'instapass-operation-id', 'Operation: ' + data.operation_id));

		const confirmation = document.createElement('button');
		confirmation.type = 'button';
		confirmation.className = 'button button-primary';
		confirmation.textContent = 'Confirm and apply this price change';
		confirmation.addEventListener('click', async function () {
			confirmation.disabled = true;
			confirmation.textContent = 'Applying…';
			try {
				const applied = await api('confirm', 'POST', { operation_id: data.operation_id });
				showMessage(applied.state === 'already_applied' ? 'This operation was already applied.' : 'Price change applied and recorded.', false);
				confirmation.remove();
				await loadHistory();
			} catch (error) {
				showMessage(error.message, true);
				confirmation.disabled = false;
				confirmation.textContent = 'Confirm and apply this price change';
			}
		});
		panel.append(confirmation);
		result.replaceChildren(panel);
		result.hidden = false;
	}

	function showMessage(message, isError) {
		result.replaceChildren(element('p', isError ? 'notice notice-error inline' : 'notice notice-success inline', message));
		result.hidden = false;
	}

	function renderHistory(items) {
		if (!Array.isArray(items) || items.length === 0) {
			history.replaceChildren(element('p', '', 'No price operations have been recorded yet.'));
			return;
		}
		const list = document.createElement('div');
		list.className = 'instapass-history-list';
		items.forEach(function (item) {
			const card = element('article', 'instapass-history-card');
			card.append(element('h3', '', item.name + ' — ' + item.state.replaceAll('_', ' ')));
			card.append(element('p', '', item.command));
			card.append(element('p', '', 'Before: regular ' + valueOrLabel(item.before.regular_price, 'Not set') + ', sale ' + valueOrLabel(item.before.sale_price, 'No sale')));
			card.append(element('p', '', 'After: regular ' + valueOrLabel(item.after.regular_price, 'Not set') + ', sale ' + valueOrLabel(item.after.sale_price, 'No sale')));
			card.append(element('p', 'description', 'Created UTC: ' + item.created_at + (item.confirmed_at ? ' · Confirmed UTC: ' + item.confirmed_at : '') + (item.undone_at ? ' · Undone UTC: ' + item.undone_at : '')));

			if (Array.isArray(item.audit_log) && item.audit_log.length) {
				const details = document.createElement('details');
				details.append(element('summary', '', 'Audit trail'));
				const events = document.createElement('ul');
				item.audit_log.forEach(function (event) {
					events.append(element('li', '', event.at_utc + ' — ' + event.event + ' (user ' + event.actor_id + ')'));
				});
				details.append(events);
				card.append(details);
			}

			if (item.state === 'applied') {
				const undo = document.createElement('button');
				undo.type = 'button';
				undo.className = 'button';
				undo.textContent = 'Undo this change';
				undo.addEventListener('click', async function () {
					undo.disabled = true;
					undo.textContent = 'Checking and restoring…';
					try {
						await api('undo', 'POST', { operation_id: item.operation_id });
						showMessage('Previous prices restored and recorded.', false);
						await loadHistory();
					} catch (error) {
						showMessage(error.message, true);
						undo.disabled = false;
						undo.textContent = 'Undo this change';
					}
				});
				card.append(undo);
			}
			list.append(card);
		});
		history.replaceChildren(list);
	}

	async function loadHistory() {
		try {
			renderHistory(await api('history', 'GET'));
		} catch (error) {
			history.replaceChildren(element('p', 'notice notice-error inline', error.message));
		}
	}

	form.addEventListener('submit', async function (event) {
		event.preventDefault();
		const command = commandInput.value.trim();
		if (!command) return;
		button.disabled = true;
		button.textContent = 'Preparing preview…';
		result.hidden = true;
		try {
			renderPreview(await api('preview', 'POST', { command: command }));
			await loadHistory();
		} catch (error) {
			showMessage(error.message || 'Could not reach the pricing service. Check the connection and try again.', true);
		} finally {
			button.disabled = false;
			button.textContent = 'Preview change';
		}
	});

	loadHistory();
}());
