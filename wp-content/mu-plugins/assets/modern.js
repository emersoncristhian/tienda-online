(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	function initSticky() {
		var sticky = document.querySelector('.ct-sticky-container');
		if (!sticky) {
			return;
		}

		var ticking = false;
		function update() {
			sticky.classList.toggle('mp-stuck', window.scrollY > 24);
			ticking = false;
		}

		window.addEventListener('scroll', function () {
			if (!ticking) {
				window.requestAnimationFrame(update);
				ticking = true;
			}
		}, { passive: true });

		update();
	}

	function initHero() {
		var hero = document.querySelector('.mp-hero2');
		if (!hero) {
			return;
		}

		var slides = Array.prototype.slice.call(hero.querySelectorAll('.mp-hero2-slide'));
		var dots = Array.prototype.slice.call(hero.querySelectorAll('.mp-hero2-dots button'));
		var current = 0;
		var timer = null;

		function loadSlideImage(slide) {
			var img = slide.querySelector('img[data-src]');
			if (!img) {
				return;
			}

			if (img.getAttribute('data-srcset')) {
				img.setAttribute('srcset', img.getAttribute('data-srcset'));
			}
			img.setAttribute('src', img.getAttribute('data-src'));
			img.removeAttribute('data-src');
			img.removeAttribute('data-srcset');
		}

		function preloadNext() {
			var nextSlide = slides[(current + 1) % slides.length];
			var img = nextSlide && nextSlide.querySelector('img[data-src]');
			if (!img || img.getAttribute('data-preloaded')) {
				return;
			}

			var preloader = new Image();
			if (img.getAttribute('data-srcset')) {
				preloader.srcset = img.getAttribute('data-srcset');
			}
			preloader.src = img.getAttribute('data-src');
			img.setAttribute('data-preloaded', '1');
		}

		function show(index) {
			current = index;
			slides.forEach(function (slide, i) {
				if (i === index) {
					loadSlideImage(slide);
				}
				slide.classList.toggle('mp-active', i === index);
			});
			dots.forEach(function (dot, i) {
				dot.classList.toggle('is-active', i === index);
			});
			window.setTimeout(preloadNext, 350);
		}

		function next() {
			show((current + 1) % slides.length);
		}

		function restart() {
			window.clearInterval(timer);
			timer = window.setInterval(next, 6500);
		}

		dots.forEach(function (dot, i) {
			dot.addEventListener('click', function () {
				show(i);
				restart();
			});
		});

		hero.addEventListener('mouseenter', function () {
			window.clearInterval(timer);
		});

		hero.addEventListener('mouseleave', restart);

		restart();
		window.setTimeout(preloadNext, 1500);
	}

	function initMegaMenu() {
		var sub = document.querySelector('#menu-principal > #menu-item-256 > .sub-menu');
		if (!sub || sub.querySelector('.mp-mega-promo')) {
			return;
		}

		var site = window.mpSite || {};
		var promo = document.createElement('li');
		promo.className = 'mp-mega-promo';
		promo.innerHTML = [
			'<a href="' + (site.offers || site.shop || '#') + '">',
			'<span class="mp-mega-eyebrow">Ofertas de la semana</span>',
			'<strong>Hasta -25%</strong>',
			'<span class="mp-mega-sub">en repuestos seleccionados</span>',
			'<em>Ver ofertas &rarr;</em>',
			'</a>'
		].join('');

		if (site.promoImg) {
			promo.querySelector('a').style.setProperty('--mp-mega-img', 'url("' + site.promoImg + '")');
		}

		sub.appendChild(promo);
	}

	function initSearchEnhance() {
		var modal = document.getElementById('search-modal');
		if (!modal || modal.querySelector('.mp-search-sugg')) {
			return;
		}

		var form = modal.querySelector('.ct-search-form');
		if (!form) {
			return;
		}

		if (!form.querySelector('[name="post_type"]')) {
			var scope = document.createElement('input');
			scope.type = 'hidden';
			scope.name = 'post_type';
			scope.value = 'product';
			form.appendChild(scope);
		}

		var sugg = document.createElement('div');
		sugg.className = 'mp-search-sugg';
		sugg.innerHTML = [
			'<span>Búsquedas populares:</span>',
			'<button type="button" data-q="filtro">Filtro de aire</button>',
			'<button type="button" data-q="frenos">Pastillas de freno</button>',
			'<button type="button" data-q="arrastre">Kit de arrastre</button>',
			'<button type="button" data-q="bateria">Batería</button>',
			'<button type="button" data-q="amortiguador">Amortiguador</button>'
		].join('');

		form.parentNode.insertBefore(sugg, form.nextSibling);

		sugg.addEventListener('click', function (event) {
			var chip = event.target.closest('button[data-q]');
			if (!chip) {
				return;
			}

			var input = form.querySelector('input[type="search"]');
			if (!input) {
				return;
			}

			input.focus();
			input.value = chip.getAttribute('data-q');
			input.dispatchEvent(new Event('input', { bubbles: true }));
		});

		var searchInput = form.querySelector('input[type="search"]');
		if (searchInput) {
			searchInput.addEventListener('input', function () {
				sugg.classList.toggle('mp-hide', searchInput.value.trim().length > 0);
			});
		}
	}

	function initNewsletter() {
		var form = document.querySelector('.mp-news-form');
		var config = window.mpNews;
		if (!form || !config) {
			return;
		}

		var input = form.querySelector('input[type="email"]');
		var button = form.querySelector('button');
		var message = form.querySelector('.mp-news-msg');

		form.addEventListener('submit', function (event) {
			event.preventDefault();

			var email = (input.value || '').trim();
			if (!email || email.indexOf('@') < 1) {
				message.textContent = 'Ingresa un correo válido.';
				message.className = 'mp-news-msg is-err';
				input.focus();
				return;
			}

			button.disabled = true;
			button.textContent = 'Enviando...';
			message.textContent = '';
			message.className = 'mp-news-msg';

			var body = new URLSearchParams();
			body.append('action', 'mp_newsletter');
			body.append('nonce', config.nonce);
			body.append('email', email);

			fetch(config.url, {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			})
				.then(function (response) {
					return response.json();
				})
				.then(function (response) {
					var data = response && response.data ? response.data : {};
					message.textContent = data.message || (response && response.success ? '¡Gracias!' : 'Ocurrió un error.');
					message.className = 'mp-news-msg ' + (response && response.success ? 'is-ok' : 'is-err');

					if (response && response.success) {
						form.reset();
					}
				})
				.catch(function () {
					message.textContent = 'No pudimos suscribirte. Inténtalo de nuevo.';
					message.className = 'mp-news-msg is-err';
				})
				.finally(function () {
					button.disabled = false;
					button.textContent = 'Suscribirme';
				});
		});
	}

	function initBottomNav() {
		var searchBtn = document.querySelector('.mp-bn-search');
		if (searchBtn) {
			searchBtn.addEventListener('click', function () {
				var trigger = document.querySelector('[data-toggle-panel="#search-modal"]');
				if (trigger) {
					trigger.click();
				}
			});
		}

		var source = document.querySelector('.ct-dynamic-count-cart');
		var target = document.querySelector('.mp-bn-count');
		if (!source || !target) {
			return;
		}

		function sync() {
			var count = parseInt(source.getAttribute('data-count') || source.textContent, 10) || 0;
			target.textContent = count;
			target.classList.toggle('is-empty', count < 1);
		}

		sync();

		if (window.MutationObserver) {
			new MutationObserver(sync).observe(source, {
				attributes: true,
				childList: true,
				subtree: true,
				characterData: true
			});
		}
	}

	function initShopFilters() {
		var bar = document.querySelector('.mp-shop-filters');
		if (!bar) {
			return;
		}

		var toggle = bar.querySelector('.mp-sf-toggle');
		if (!toggle) {
			return;
		}

		toggle.addEventListener('click', function () {
			var open = bar.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	var productData = window.mpProducts || {};
	var qv = null;

	function productIdFor(card) {
		var match = String(card.className).match(/\bpost-(\d+)\b/);
		return match ? parseInt(match[1], 10) : 0;
	}

	function initProductCards() {
		var cards = document.querySelectorAll('li.product');
		if (!cards.length) {
			return;
		}

		Array.prototype.forEach.call(cards, function (card) {
			var id = productIdFor(card);
			var data = productData[id];
			if (!id || !data) {
				return;
			}

			var figure = card.querySelector('figure');
			var media = card.querySelector('.ct-media-container');

			if (figure && data.badge && !figure.querySelector('.mp-badge')) {
				var badge = document.createElement('span');
				badge.className = 'mp-badge';
				badge.textContent = data.badge;
				figure.appendChild(badge);
			}

			if (media && data.hover && !media.querySelector('.mp-hover-img')) {
				var hover = document.createElement('img');
				hover.className = 'mp-hover-img';
				hover.src = data.hover;
				hover.alt = '';
				hover.loading = 'lazy';
				media.appendChild(hover);
			}

			if (figure && !figure.querySelector('.mp-quickview')) {
				var button = document.createElement('button');
				button.type = 'button';
				button.className = 'mp-quickview';
				button.textContent = 'Vista rápida';
				button.setAttribute('data-product', id);
				button.addEventListener('click', function (event) {
					event.preventDefault();
					event.stopPropagation();
					openQuickView(id);
				});
				figure.appendChild(button);
			}

			if (data.status === 'instock' && data.stock !== null && data.stock !== '' && !card.querySelector('.mp-stock-low')) {
				var stock = parseInt(data.stock, 10);
				if (!isNaN(stock) && stock > 0 && stock <= 5) {
					var price = card.querySelector('.price');
					if (price) {
						var stockEl = document.createElement('span');
						stockEl.className = 'mp-stock-low';
						stockEl.textContent = 'Últimas ' + stock + ' unidades';
						price.parentNode.insertBefore(stockEl, price.nextSibling);
					}
				}
			}
		});
	}

	function buildQuickView() {
		if (document.querySelector('.mp-qv-overlay')) {
			qv = document.querySelector('.mp-qv-overlay');
			return qv;
		}

		var overlay = document.createElement('div');
		overlay.className = 'mp-qv-overlay';
		overlay.innerHTML = [
			'<div class="mp-qv" role="dialog" aria-modal="true" aria-label="Vista rápida del producto">',
			'<button type="button" class="mp-qv-close" aria-label="Cerrar">&times;</button>',
			'<div class="mp-qv-media"><img src="" alt=""></div>',
			'<div class="mp-qv-body">',
			'<span class="mp-qv-cat"></span>',
			'<h3 class="mp-qv-title"></h3>',
			'<div class="mp-qv-price"></div>',
			'<p class="mp-qv-desc"></p>',
			'<div class="mp-qv-meta"></div>',
			'<div class="mp-qv-actions">',
			'<a class="mp-qv-add" href="#">Añadir al carrito</a>',
			'<a class="mp-qv-link" href="#">Ver producto</a>',
			'</div>',
			'</div>',
			'</div>'
		].join('');

		document.body.appendChild(overlay);

		overlay.addEventListener('click', function (event) {
			if (event.target === overlay || event.target.closest('.mp-qv-close')) {
				closeQuickView();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeQuickView();
			}
		});

		qv = overlay;
		return overlay;
	}

	function openQuickView(id) {
		var data = productData[id];
		if (!data) {
			return;
		}

		var overlay = buildQuickView();
		var image = overlay.querySelector('.mp-qv-media img');

		image.src = data.image || '';
		image.alt = data.name;
		var isSkuName = data.sku && data.sku.trim().toLowerCase() === data.name.trim().toLowerCase();
		overlay.querySelector('.mp-qv-cat').textContent = data.sku && !isSkuName ? 'SKU: ' + data.sku : 'Producto original';
		overlay.querySelector('.mp-qv-title').textContent = data.name;
		overlay.querySelector('.mp-qv-price').innerHTML = data.price;
		overlay.querySelector('.mp-qv-desc').textContent = data.desc;

		var meta = overlay.querySelector('.mp-qv-meta');
		if (data.status === 'instock') {
			meta.innerHTML = '<span class="in">En stock</span>' + (data.stock ? ' · ' + data.stock + ' disponibles' : '');
		} else {
			meta.innerHTML = '<span class="out">Agotado</span>';
		}

		overlay.querySelector('.mp-qv-add').href = data.add;
		overlay.querySelector('.mp-qv-link').href = data.url;

		overlay.classList.add('is-open');
		document.body.classList.add('mp-qv-lock');
	}

	function closeQuickView() {
		if (!qv) {
			return;
		}
		qv.classList.remove('is-open');
		document.body.classList.remove('mp-qv-lock');
	}

	ready(function () {
		initSticky();
		initHero();
		initMegaMenu();
		initSearchEnhance();
		initShopFilters();
		initBottomNav();
		initNewsletter();
		initProductCards();
	});
})();
