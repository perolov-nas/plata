/**
 * Platå – huvudskript
 */
const CHEVRON =
	'<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6" /></svg>';

const getString = (key, fallback) =>
	(window.plataStrings && window.plataStrings[key]) || fallback;

/**
 * Ger varje menypost med barn en knapp som fäller ut undermenyn.
 */
const initSubmenus = (nav) => {
	const parents = nav.querySelectorAll('.menu-item-has-children');
	const toggles = [];

	parents.forEach((parent, index) => {
		const link = parent.querySelector(':scope > a');
		const submenu = parent.querySelector(':scope > .sub-menu');

		if (!link || !submenu) {
			return;
		}

		if (!submenu.id) {
			submenu.id = `site-submenu-${index + 1}`;
		}

		const label = getString('submenuToggle', 'Visa undermeny för %s').replace(
			'%s',
			link.textContent.trim()
		);

		const toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'site-nav__submenu-toggle';
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-controls', submenu.id);
		toggle.innerHTML = `${CHEVRON}<span class="screen-reader-text"></span>`;
		toggle.querySelector('.screen-reader-text').textContent = label;

		link.after(toggle);
		toggles.push({ parent, toggle });

		toggle.addEventListener('click', () => {
			const isOpen = parent.classList.contains('is-submenu-open');

			// Bara en undermeny åt gången, annars växer menyn okontrollerat.
			closeAll(toggles);

			if (!isOpen) {
				parent.classList.add('is-submenu-open');
				toggle.setAttribute('aria-expanded', 'true');
			}
		});
	});

	return toggles;
};

const closeAll = (toggles) => {
	toggles.forEach(({ parent, toggle }) => {
		parent.classList.remove('is-submenu-open');
		toggle.setAttribute('aria-expanded', 'false');
	});
};

const initNavigation = () => {
	const header = document.querySelector('.site-header');
	const toggle = document.querySelector('.site-nav-toggle');
	const nav = document.querySelector('.site-nav');

	if (!header || !toggle || !nav) {
		return;
	}

	const submenuToggles = initSubmenus(nav);

	const closeNav = () => {
		header.classList.remove('is-nav-open');
		toggle.setAttribute('aria-expanded', 'false');
		closeAll(submenuToggles);
	};

	toggle.addEventListener('click', () => {
		const isOpen = header.classList.toggle('is-nav-open');
		toggle.setAttribute('aria-expanded', String(isOpen));

		if (!isOpen) {
			closeAll(submenuToggles);
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key !== 'Escape') {
			return;
		}

		const openSubmenu = nav.querySelector('.is-submenu-open');

		// Escape stänger den öppna undermenyn först och hela menyn därefter.
		if (openSubmenu) {
			const openToggle = openSubmenu.querySelector(
				':scope > .site-nav__submenu-toggle'
			);
			closeAll(submenuToggles);
			openToggle?.focus();
			return;
		}

		if (header.classList.contains('is-nav-open')) {
			closeNav();
			toggle.focus();
		}
	});

	document.addEventListener('click', (event) => {
		if (header.contains(event.target)) {
			return;
		}

		closeAll(submenuToggles);

		if (header.classList.contains('is-nav-open')) {
			closeNav();
		}
	});

	// Menyn är alltid synlig på breda skärmar, så det utfällda läget nollställs
	// för att knappen inte ska hamna i otakt med det som visas.
	window.matchMedia('(min-width: 48em)').addEventListener('change', (event) => {
		if (event.matches) {
			closeNav();
		}
	});
};

/**
 * Innehållsförteckningen: fällbar på mobil och markerar avsnittet man är i.
 */
const initToc = () => {
	const toc = document.querySelector('.toc');

	if (!toc) {
		return;
	}

	const toggle = toc.querySelector('.toc__toggle');
	const links = Array.from(toc.querySelectorAll('.toc__link'));

	if (toggle) {
		const closeToc = () => {
			toc.classList.remove('is-open');
			toggle.setAttribute('aria-expanded', 'false');
		};

		toggle.addEventListener('click', () => {
			const isOpen = toc.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', String(isOpen));
		});

		// Förteckningen ligger över innehållet, så den fälls ihop när man
		// valt ett avsnitt och när man klickar utanför den.
		links.forEach((link) => link.addEventListener('click', closeToc));

		document.addEventListener('click', (event) => {
			if (!toc.contains(event.target)) {
				closeToc();
			}
		});

		document.addEventListener('keydown', (event) => {
			if (event.key === 'Escape' && toc.classList.contains('is-open')) {
				closeToc();
				toggle.focus();
			}
		});
	}

	const headings = links
		.map((link) => document.getElementById(decodeURIComponent(link.hash.slice(1))))
		.filter(Boolean);

	if (!headings.length) {
		return;
	}

	let frame = null;

	const markCurrentSection = () => {
		frame = null;

		// Läses av varje gång, eftersom avståndet skiljer sig mellan mobil
		// och desktop och kan ändras när fönstret ändrar storlek.
		const offset = parseFloat(getComputedStyle(headings[0]).scrollMarginTop) || 32;
		let activeIndex = -1;

		headings.forEach((heading, index) => {
			if (heading.getBoundingClientRect().top <= offset + 4) {
				activeIndex = index;
			}
		});

		links.forEach((link, index) => {
			const isActive = index === activeIndex;

			link.classList.toggle('is-active', isActive);

			if (isActive) {
				link.setAttribute('aria-current', 'true');
			} else {
				link.removeAttribute('aria-current');
			}
		});
	};

	const scheduleUpdate = () => {
		if (frame === null) {
			frame = requestAnimationFrame(markCurrentSection);
		}
	};

	window.addEventListener('scroll', scheduleUpdate, { passive: true });
	window.addEventListener('resize', scheduleUpdate);
	markCurrentSection();
};

/**
 * En tabell som är bredare än sin spalt scrollar i sidled. Ett område som
 * scrollar måste gå att nå med tangentbord, men bara när det faktiskt
 * scrollar – annars blir det en tom tabbstopp.
 */
const initScrollableTables = () => {
	const wrappers = Array.from(document.querySelectorAll('.wp-block-table'));

	if (!wrappers.length) {
		return;
	}

	const update = () => {
		wrappers.forEach((wrapper) => {
			const overflows = wrapper.scrollWidth > wrapper.clientWidth + 1;

			if (overflows) {
				wrapper.setAttribute('tabindex', '0');
				wrapper.setAttribute('role', 'region');
				wrapper.setAttribute(
					'aria-label',
					getString('scrollableTable', 'Tabell, går att scrolla i sidled')
				);
			} else {
				wrapper.removeAttribute('tabindex');
				wrapper.removeAttribute('role');
				wrapper.removeAttribute('aria-label');
			}
		});
	};

	update();

	if ('ResizeObserver' in window) {
		const observer = new ResizeObserver(update);
		wrappers.forEach((wrapper) => observer.observe(wrapper));
		return;
	}

	window.addEventListener('resize', update);
};

/**
 * Ljust/mörkt läge. color-scheme styr light-dark() i CSS.
 */
const initThemeSwitch = () => {
	const button = document.querySelector('.theme-switch');

	if (!button) {
		return;
	}

	const apply = (scheme) => {
		document.documentElement.style.colorScheme = scheme;
		document.documentElement.setAttribute('data-color-scheme', scheme);
		button.setAttribute('aria-pressed', String(scheme === 'dark'));
		button.setAttribute(
			'aria-label',
			scheme === 'dark' ? button.dataset.labelDark : button.dataset.labelLight
		);

		try {
			localStorage.setItem('plata-color-scheme', scheme);
		} catch (error) {
			// localStorage kan vara avstängt.
		}
	};

	const current =
		document.documentElement.getAttribute('data-color-scheme') === 'dark'
			? 'dark'
			: 'light';

	apply(current);

	button.addEventListener('click', () => {
		const next =
			document.documentElement.getAttribute('data-color-scheme') === 'dark'
				? 'light'
				: 'dark';
		apply(next);
	});
};

document.addEventListener('DOMContentLoaded', () => {
	initNavigation();
	initToc();
	initScrollableTables();
	initThemeSwitch();
});
