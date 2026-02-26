<?php
/**
 * Gravity Perks // Bookings // Price Detection in Booking Management
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Experimental Snippet 🧪
 *
 * https://www.loom.com/share/56c0a90053ac4739981c08338f388087
 *
 * Displays original and updated price when rescheduling a booking in the booking management page.
 * The updated price is calculated based on the newly selected dates and the original time parts.
 */
add_action( 'wp_footer', function () {
	?>
	<style>
		.gpb-custom-reschedule-pricing {
			padding: 1rem;
			margin-top: 1rem;
			font-size: 16px;
			background: rgba(var(--gform-theme-color-danger-rgb, 192, 43, 10), 0.08);
			border: 1px solid var(--gform-theme-color-danger, #c02b0a);
			border-radius: 4px;
			color: var(--gform-theme-color-danger, #c02b0a);
		}
		.gpb-custom-reschedule-pricing p {
			margin: 0;
			line-height: 1.6;
		}
	</style>

	<script>
	(function () {
		if (!window.gpbBookingManagement || !Array.isArray(window.gpbBookingManagement.bookings)) {
			return;
		}

		function groupBookings(bookings) {
			const resourceGroups = new Map();
			bookings.forEach((b) => {
				if (b.objectType === 'resource' && b.parentBookingId) {
					if (!resourceGroups.has(b.parentBookingId)) {
						resourceGroups.set(b.parentBookingId, []);
					}
					resourceGroups.get(b.parentBookingId).push(b);
				}
			});

			const groups = [];
			resourceGroups.forEach((resources, parentId) => {
				if (resources.length) {
					const first = resources[0];
					groups.push({
						service: { ...first, id: parentId, objectType: 'service' },
						resources,
					});
				}
			});

			bookings.forEach((b) => {
				if (!(b.objectType === 'resource' && b.parentBookingId)) {
					groups.push({ service: { ...b, objectType: 'service' }, resources: [] });
				}
			});

			return groups;
		}

		function toDateTime(input) {
			return (input || '').toString().replace('T', ' ').slice(0, 19);
		}

		function getTimePart(dateTime) {
			const str = toDateTime(dateTime);
			return str.split(' ')[1] || '00:00:00';
		}

		function formatMoney(value) {
			const n = Number(value || 0);
			const code =
				window.gp_bookings_field_booking_time_strings?.currency?.code ||
				window.gp_bookings_field_booking_strings?.currency?.code ||
				'USD';

			try {
				return new Intl.NumberFormat(undefined, {
					style: 'currency',
					currency: code,
				}).format(n);
			} catch (e) {
				return n.toFixed(2);
			}
		}

		async function calcPrice(payload) {
			const res = await fetch('/wp-json/gp-bookings/v1/calculate-price', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload),
			});
			if (!res.ok) {
				throw new Error('Pricing request failed');
			}
			const json = await res.json();
			return Number(json.price || 0);
		}

		function getSelectedRange(card) {
			const startEl = card.querySelector('.rdp-day.rdp-range_start[data-day]');
			const endEl = card.querySelector('.rdp-day.rdp-range_end[data-day]');

			if (startEl && endEl) {
				return {
					startDate: startEl.getAttribute('data-day'),
					endDate: endEl.getAttribute('data-day'),
				};
			}

			// Fixed/single fallback
			const selected = card.querySelectorAll('.rdp-day.rdp-selected[data-day]');
			if (selected.length) {
				const first = selected[0].getAttribute('data-day');
				const last = selected[selected.length - 1].getAttribute('data-day');
				return { startDate: first, endDate: last };
			}

			return null;
		}

		function ensureBox(rangeInfoEl) {
			const wrap = rangeInfoEl.parentElement;
			if (!wrap) return null;

			let box = wrap.querySelector('.gpb-custom-reschedule-pricing');
			if (!box) {
				box = document.createElement('div');
				box.className = 'gpb-custom-reschedule-pricing';
				box.innerHTML = '<p>Original Price: —<br>Updated Price: —</p>';
			}

			// Always force correct order: blue range info first, red box immediately after.
			if (rangeInfoEl.nextElementSibling !== box) {
				rangeInfoEl.insertAdjacentElement('afterend', box);
			}

			return box;
		}


		const groups = groupBookings(window.gpbBookingManagement.bookings || []);

		function bindCard(card, group) {
			if (!group || card.dataset.gpbPricingBound === '1') return;
			card.dataset.gpbPricingBound = '1';

			let originalPrice = null;
			let lastKey = '';

			async function render() {
				const rangeInfo = card.querySelector('.gpb-manage-booking-reschedule .gpb-booking-time-picker__range-info');
				if (!rangeInfo) return;

				const box = ensureBox(rangeInfo);
				const sel = getSelectedRange(card);
				if (!sel?.startDate || !sel?.endDate) return;

				const service = group.service;
				const resourceIds = (group.resources || []).map((r) => Number(r.objectId)).filter(Boolean);

				const originalStart = toDateTime(service.start);
				const originalEnd = toDateTime(service.end);
				const startTime = getTimePart(originalStart);
				const endTime = getTimePart(originalEnd);

				const updatedStart = `${sel.startDate} ${startTime}`;
				const updatedEnd = `${sel.endDate} ${endTime}`;

				const key = `${updatedStart}|${updatedEnd}|${service.serviceId}|${resourceIds.join(',')}`;
				if (key === lastKey) return;
				lastKey = key;

				try {
					if (originalPrice === null) {
						originalPrice = await calcPrice({
							service_id: Number(service.serviceId),
							resource_ids: resourceIds,
							start_time: originalStart,
							end_time: originalEnd,
							quantity: 1,
						});
					}

					const updatedPrice = await calcPrice({
						service_id: Number(service.serviceId),
						resource_ids: resourceIds,
						start_time: updatedStart,
						end_time: updatedEnd,
						quantity: 1,
					});

					box.innerHTML =
						`<p>Original Price: ${formatMoney(originalPrice)}<br>` +
						`Updated Price: ${formatMoney(updatedPrice)}</p>`;
				} catch (e) {
					box.innerHTML = '<p>Original Price: —<br>Updated Price: —</p>';
				}
			}

			const mo = new MutationObserver(() => { render(); });
			mo.observe(card, { childList: true, subtree: true, attributes: true });

			render();
		}

		function init() {
			const cards = document.querySelectorAll('.gpb-manage-booking');
			cards.forEach((card, i) => bindCard(card, groups[i]));
		}

		document.addEventListener('DOMContentLoaded', init);
		const rootObserver = new MutationObserver(init);
		rootObserver.observe(document.body, { childList: true, subtree: true });
	})();
	</script>
	<?php
}, 99 );
