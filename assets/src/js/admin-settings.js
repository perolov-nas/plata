/**
 * Platå – admin för tema-inställningar.
 */
jQuery(function ($) {
	function normalizeHex(value) {
		const raw = String(value || '').trim();
		if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(raw)) {
			if (raw.length === 4) {
				return (
					'#' +
					raw[1] + raw[1] +
					raw[2] + raw[2] +
					raw[3] + raw[3]
				).toLowerCase();
			}
			return raw.toLowerCase();
		}
		return '';
	}

	const $scheme = $('.plata-scheme');
	const $schemeToggle = $('.plata-scheme-toggle');

	const $specimen = $('.plata-specimen');

	function colorValue(scheme, key) {
		const id = scheme === 'dark' ? '#plata_color_dark_' + key : '#plata_color_' + key;
		return normalizeHex($(id).val()) || $(id).val() || '';
	}

	function updateSpecimenTheme(scheme) {
		const enabled = $schemeToggle.is(':checked');

		if (!enabled) {
			scheme = 'light';
		} else if (!scheme) {
			scheme = $specimen.attr('data-scheme') || 'light';
		}

		if (scheme !== 'dark') {
			scheme = 'light';
		}

		$specimen.attr('data-scheme', scheme);
		$specimen.css({
			'--specimen-bg': colorValue(scheme, 'background'),
			'--specimen-text': colorValue(scheme, 'text'),
			'--specimen-heading': colorValue(scheme, 'heading'),
			'--specimen-muted': colorValue(scheme, 'text_muted'),
		});
		$specimen.find('.plata-specimen__schemes').prop('hidden', !enabled);
		$specimen.find('.plata-specimen__scheme').removeClass('is-active');
		$specimen.find('.plata-specimen__scheme[data-scheme="' + scheme + '"]').addClass('is-active');
	}

	function setSchemeEnabled(enabled) {
		$scheme.attr('data-enabled', enabled ? '1' : '0');
		$scheme.find('.plata-scheme__tabs').prop('hidden', !enabled);

		if (!enabled) {
			$scheme.find('.plata-scheme__tab').removeClass('is-active');
			$scheme.find('.plata-scheme__tab[data-scheme="light"]').addClass('is-active');
			$scheme.find('.plata-scheme__panel').prop('hidden', true);
			$scheme.find('.plata-scheme__panel[data-scheme="light"]').prop('hidden', false);
			updateSpecimenTheme('light');
			return;
		}

		updateSpecimenTheme();
	}

	$schemeToggle.on('change', function () {
		setSchemeEnabled($schemeToggle.is(':checked'));
	});

	$scheme.on('click', '.plata-scheme__tab', function () {
		const scheme = $(this).data('scheme');
		$scheme.find('.plata-scheme__tab').removeClass('is-active');
		$(this).addClass('is-active');
		$scheme.find('.plata-scheme__panel').prop('hidden', true);
		$scheme.find('.plata-scheme__panel[data-scheme="' + scheme + '"]').prop('hidden', false);
		updateSpecimenTheme(scheme);
	});

	$specimen.on('click', '.plata-specimen__scheme', function () {
		updateSpecimenTheme($(this).data('scheme'));
	});

	$('.plata-token').each(function () {
		const $token = $(this);
		const $hex = $token.find('.plata-color-field');
		const $picker = $token.find('.plata-token__picker');
		const $swatch = $token.find('.plata-token__swatch');

		function apply(hex) {
			$hex.val(hex);
			$picker.val(hex);
			$swatch.css('background-color', hex);
		}

		$hex.on('input change', function () {
			const hex = normalizeHex($hex.val());
			if (hex) {
				apply(hex);
				updateSpecimenTheme();
			}
		});

		$picker.on('input', function () {
			apply($picker.val());
			updateSpecimenTheme();
		});
	});

	updateSpecimenTheme($schemeToggle.is(':checked') ? $specimen.attr('data-scheme') : 'light');

	function updateSpecimen() {
		const $heading = $('#plata_font_heading option:selected');
		const $body = $('#plata_font_body option:selected');
		$('.plata-specimen__heading').css('font-family', $heading.data('font-family') || '');
		$('.plata-specimen__body').css('font-family', $body.data('font-family') || '');
		$('.plata-specimen__heading-name').text($heading.text());
		$('.plata-specimen__body-name').text($body.text());
	}

	$('.plata-font-select').on('change', updateSpecimen);

	const $navLinks = $('.plata-nav__link');
	const sections = $navLinks.map(function () {
		const id = this.getAttribute('href');
		return id ? document.querySelector(id) : null;
	}).get().filter(Boolean);

	$navLinks.on('click', function (event) {
		const target = document.querySelector(this.getAttribute('href'));
		if (!target) {
			return;
		}
		event.preventDefault();
		target.scrollIntoView({ behavior: 'smooth', block: 'start' });
	});

	if ('IntersectionObserver' in window && sections.length) {
		const observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) {
					return;
				}
				$navLinks.removeClass('is-active');
				$navLinks.filter('[href="#' + entry.target.id + '"]').addClass('is-active');
			});
		}, {
			rootMargin: '-20% 0px -60% 0px',
			threshold: 0,
		});

		sections.forEach(function (section) {
			observer.observe(section);
		});
	}

	$('.plata-media-field').each(function () {
		const $field = $(this);
		const $input = $field.find('.plata-media-id');
		const $dropzone = $field.find('.plata-upload__dropzone');
		const $file = $field.find('.plata-upload__file');
		const $thumb = $field.find('.plata-upload__thumb');
		const $name = $field.find('.plata-upload__name');
		const $size = $field.find('.plata-upload__size');
		const title = $field.data('title') || 'Välj bild';
		let frame;

		function showFile(attachment) {
			const url = (attachment.sizes && attachment.sizes.medium)
				? attachment.sizes.medium.url
				: attachment.url;
			const filename = attachment.filename || attachment.title || '';
			const filesize = attachment.filesizeHumanReadable || '';

			$input.val(attachment.id);
			if ($thumb.length) {
				$thumb.attr('src', url);
			} else {
				$file.prepend('<img class="plata-upload__thumb" src="' + url + '" alt="" />');
			}
			$name.text(filename);
			$size.text(filesize);
			$dropzone.prop('hidden', true);
			$file.prop('hidden', false);
		}

		function clearFile() {
			$input.val('');
			$field.find('.plata-upload__thumb').attr('src', '');
			$name.text('');
			$size.text('');
			$file.prop('hidden', true);
			$dropzone.prop('hidden', false);
		}

		$field.on('click', '.plata-media-select', function (event) {
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
				showFile(frame.state().get('selection').first().toJSON());
			});

			frame.open();
		});

		$field.on('click', '.plata-media-remove', function (event) {
			event.preventDefault();
			clearFile();
		});

		$dropzone.on('dragenter dragover', function (event) {
			event.preventDefault();
			$field.addClass('is-dragging');
		});

		$dropzone.on('dragleave drop', function (event) {
			event.preventDefault();
			$field.removeClass('is-dragging');
		});

		$dropzone.on('drop', function (event) {
			event.preventDefault();
			$dropzone.trigger('click');
		});
	});

	$('.plata-social-field').each(function () {
		const $field = $(this);
		const $list = $field.find('.plata-social-list');
		const $add = $field.find('.plata-social-add');
		const template = $('#tmpl-plata-social-row').html() || '';

		function reindex() {
			$list.children('.plata-social-row').each(function (index) {
				$(this)
					.find('[id], [for], [name]')
					.each(function () {
						const $el = $(this);
						['id', 'for', 'name'].forEach(function (attr) {
							const value = $el.attr(attr);
							if (!value) {
								return;
							}
							$el.attr(
								attr,
								value
									.replace(/plata_social_links\[[^\]]*\]/, 'plata_social_links[' + index + ']')
									.replace(/plata_social_(network|url)_[^\s"']+/, 'plata_social_$1_' + index)
							);
						});
					});
			});
		}

		function addRow(network) {
			const nextIndex = $list.children('.plata-social-row').length;
			const markup = template.replace(/__INDEX__/g, String(nextIndex));
			const $row = $(markup);
			if (network) {
				$row.find('.plata-social-row__network').val(network);
			}
			$list.append($row);
			reindex();
			$row.find('.plata-social-row__url').trigger('focus');
		}

		$add.on('change', function () {
			const network = $add.val();
			if (!network) {
				return;
			}
			addRow(network);
			$add.val('');
		});

		$field.on('click', '.plata-social-row__remove', function (event) {
			event.preventDefault();
			$(this).closest('.plata-social-row').remove();
			reindex();
		});

		if (typeof $list.sortable === 'function') {
			$list.sortable({
				handle: '.plata-social-row__handle',
				items: '> .plata-social-row',
				axis: 'y',
				update: reindex,
			});
		}
	});
});
