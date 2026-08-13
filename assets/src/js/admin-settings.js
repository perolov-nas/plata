/**
 * Platå – admin färgväljare + mediaväljare
 */
jQuery(function ($) {
	$('.plata-color-field').wpColorPicker();

	$('.plata-media-field').each(function () {
		const $field = $(this);
		const $input = $field.find('.plata-media-id');
		const $preview = $field.find('.plata-media-preview');
		const $select = $field.find('.plata-media-select');
		const $remove = $field.find('.plata-media-remove');
		const title = $field.data('title') || 'Välj bild';
		let frame;

		$select.on('click', function (event) {
			event.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: title,
				button: { text: 'Använd bild' },
				library: { type: 'image' },
				multiple: false,
			});

			frame.on('select', function () {
				const attachment = frame.state().get('selection').first().toJSON();
				const url = (attachment.sizes && attachment.sizes.medium)
					? attachment.sizes.medium.url
					: attachment.url;

				$input.val(attachment.id);
				$preview.html('<img src="' + url + '" alt="" />').prop('hidden', false);
				$select.text('Byt bild');
				$remove.prop('hidden', false);
			});

			frame.open();
		});

		$remove.on('click', function (event) {
			event.preventDefault();
			$input.val('');
			$preview.empty().prop('hidden', true);
			$select.text('Välj bild');
			$remove.prop('hidden', true);
		});
	});
});
