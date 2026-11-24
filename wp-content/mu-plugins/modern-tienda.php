<?php
/**
 * Modern UI enhancements for MOTO PARTS CRISTHIAN.
 */

add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1 );

add_action( 'wp_head', function () {
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#0F0F1A"/><text x="32" y="46" font-family="Arial,sans-serif" font-size="40" font-weight="800" fill="#FF6B00" text-anchor="middle">M</text></svg>';
	echo '<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,' . rawurlencode( $svg ) . '">' . "\n";
}, 2 );

add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment, $size ) {
	if ( is_admin() || (int) $attachment->ID !== (int) get_theme_mod( 'custom_logo' ) ) {
		return $attr;
	}

	$medium = wp_get_attachment_image_src( $attachment->ID, 'medium' );
	if ( ! $medium ) {
		return $attr;
	}

	$attr['src']    = $medium[0];
	$attr['width']  = $medium[1];
	$attr['height'] = $medium[2];
	$attr['srcset'] = $medium[0] . ' 300w';
	$attr['sizes']  = '(max-width: 780px) 150px, 180px';

	return $attr;
}, 10, 3 );

add_action( 'wp_enqueue_scripts', function () {
	$base = __DIR__ . '/assets';
	$uri  = content_url( 'mu-plugins/assets' );

	wp_enqueue_style(
		'mp-fonts',
		'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'mp-modern',
		$uri . '/modern.css',
		array( 'mp-fonts' ),
		file_exists( $base . '/modern.css' ) ? filemtime( $base . '/modern.css' ) : '1.0'
	);

	wp_enqueue_script(
		'mp-modern',
		$uri . '/modern.js',
		array(),
		file_exists( $base . '/modern.js' ) ? filemtime( $base . '/modern.js' ) : '1.0',
		true
	);

	wp_deregister_script( 'wp-embed' );
}, 20 );

add_action( 'init', function () {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
} );


add_action( 'wp_body_open', function () {
	$whatsapp = 'https://wa.me/51986947730';
	?>
	<div class="mp-topbar">
		<div class="mp-topbar-inner">
			<div class="mp-tb-left">
				<span class="mp-tb-item">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
					Envíos a todo el Perú y extranjero por <strong>SHALOM</strong> y <strong>OLVA COURIER</strong>
				</span>
				<span class="mp-tb-item mp-tb-hide-mobile">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
					+51 986 947 730
				</span>
			</div>
			<div class="mp-tb-right">
				<span class="mp-tb-item mp-tb-hide-mobile">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
					Lun - Sáb: 9:00 a 19:00
				</span>
				<a class="mp-tb-item" href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
					WhatsApp <strong class="mp-tb-strong">+51 986 947 730</strong>
				</a>
			</div>
		</div>
	</div>
	<?php
} );

// Force crisp high-definition image sizes across shop & archive catalog
add_filter( 'woocommerce_thumbnail_size', function ( $size ) {
	return 'woocommerce_single';
}, 99 );

// Display all catalog products on shop page (or 64 per page)
add_filter( 'loop_shop_per_page', function ( $cols ) {
	return 64;
}, 20 );

add_filter( 'woocommerce_get_image_size_thumbnail', function ( $size ) {
	return array(
		'width'  => 600,
		'height' => 600,
		'crop'   => 1,
	);
}, 99 );

add_filter( 'woocommerce_get_image_size_archive_thumbnail', function ( $size ) {
	return array(
		'width'  => 600,
		'height' => 600,
		'crop'   => 1,
	);
}, 99 );

add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}

	$data = array();
	$products = wc_get_products( array( 'limit' => -1, 'status' => 'publish' ) );
	foreach ( $products as $product ) {
		$id      = $product->get_id();
		$gallery = $product->get_gallery_image_ids();
		$desc    = wp_strip_all_tags( $product->get_short_description() );
		if ( '' === trim( $desc ) ) {
			$desc = wp_strip_all_tags( $product->get_description() );
		}
		$desc = wp_trim_words( $desc, 26, '…' );

		$data[ $id ] = array(
			'name'   => $product->get_name(),
			'url'    => get_permalink( $id ),
			'price'  => $product->get_price_html(),
			'image'  => wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_single' ),
			'hover'  => $gallery ? wp_get_attachment_image_url( $gallery[0], 'woocommerce_single' ) : '',
			'add'    => $product->add_to_cart_url(),
			'desc'   => $desc,
			'sku'    => $product->get_sku(),
			'stock'  => $product->get_stock_quantity(),
			'status' => $product->get_stock_status(),
			'badge'  => get_post_meta( $id, '_mp_badge', true ),
		);
	}
	$shop    = wc_get_page_permalink( 'shop' );
	$promo   = 84;
	$site    = array(
		'shop'     => $shop,
		'offers'   => add_query_arg( 'on_sale', '1', $shop ),
		'promoUrl' => get_permalink( $promo ) ? get_permalink( $promo ) : $shop,
		'promoImg' => get_the_post_thumbnail_url( $promo, 'woocommerce_single' ),
	);
	?>
	<script>window.mpProducts = <?php echo wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>;window.mpSite = <?php echo wp_json_encode( $site, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>;</script>
	<?php
}, 5 );

add_action( 'wp_footer', function () {
	$whatsapp = 'https://wa.me/51986947730?text=' . rawurlencode( 'Hola, necesito ayuda con un repuesto.' );
	?>
	<a class="mp-wa" href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener" aria-label="Escríbenos por WhatsApp">
		<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
		<span class="mp-wa-text">¿Necesitas ayuda?</span>
	</a>
	<?php
}, 30 );

/**
 * Shop filters: categories + price + offers.
 */
add_action( 'woocommerce_before_shop_loop', function () {
	if ( is_admin() ) {
		return;
	}

	$shop = wc_get_page_permalink( 'shop' );

	$active_cat = isset( $_GET['product_cat'] ) ? sanitize_title( wp_unslash( $_GET['product_cat'] ) ) : '';
	if ( ! $active_cat && is_product_category() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->slug ) ) {
			$active_cat = $term->slug;
		}
	}

	$min = isset( $_GET['min_price'] ) ? wc_clean( wp_unslash( $_GET['min_price'] ) ) : '';
	$max = isset( $_GET['max_price'] ) ? wc_clean( wp_unslash( $_GET['max_price'] ) ) : '';
	$on_sale = isset( $_GET['on_sale'] ) && '1' === $_GET['on_sale'];

	$make_url = function ( $overrides = [], $remove = [] ) use ( $shop, $active_cat, $min, $max, $on_sale ) {
		$args = array();
		if ( $active_cat ) {
			$args['product_cat'] = $active_cat;
		}
		if ( '' !== $min ) {
			$args['min_price'] = $min;
		}
		if ( '' !== $max ) {
			$args['max_price'] = $max;
		}
		if ( $on_sale ) {
			$args['on_sale'] = '1';
		}
		foreach ( $remove as $key ) {
			unset( $args[ $key ] );
		}
		foreach ( $overrides as $key => $value ) {
			if ( null === $value || '' === $value ) {
				unset( $args[ $key ] );
			} else {
				$args[ $key ] = $value;
			}
		}
		return $args ? add_query_arg( $args, $shop ) : $shop;
	};

	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
	$ranges = array(
		array( 'label' => 'Menos de S/ 50', 'min' => '', 'max' => '50' ),
		array( 'label' => 'S/ 50 - 100', 'min' => '50', 'max' => '100' ),
		array( 'label' => 'S/ 100 - 200', 'min' => '100', 'max' => '200' ),
		array( 'label' => 'Más de S/ 200', 'min' => '200', 'max' => '' ),
	);

	$has_filters  = $active_cat || '' !== $min || '' !== $max || $on_sale;
	$active_count = ( $active_cat ? 1 : 0 ) + ( ( '' !== $min || '' !== $max ) ? 1 : 0 ) + ( $on_sale ? 1 : 0 );
	?>
	<div class="mp-shop-filters">
		<button type="button" class="mp-sf-toggle" aria-expanded="false">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
			<span>Filtrar</span>
			<?php if ( $active_count ) : ?>
				<span class="mp-sf-toggle-badge"><?php echo (int) $active_count; ?></span>
			<?php endif; ?>
		</button>
		<div class="mp-sf-body">
		<div class="mp-sf-row">
			<div class="mp-sf-group">
				<span class="mp-sf-title">Categoría</span>
				<div class="mp-sf-pills">
					<a class="mp-sf-pill<?php echo $active_cat ? '' : ' is-active'; ?>" href="<?php echo esc_url( $make_url( [], array( 'product_cat' ) ) ); ?>">Todas</a>
					<?php if ( ! is_wp_error( $terms ) ) : ?>
						<?php foreach ( $terms as $term ) : ?>
							<a class="mp-sf-pill<?php echo $active_cat === $term->slug ? ' is-active' : ''; ?>" href="<?php echo esc_url( $make_url( array( 'product_cat' => $term->slug ) ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="mp-sf-group">
				<span class="mp-sf-title">Precio</span>
				<div class="mp-sf-pills">
					<a class="mp-sf-pill<?php echo ( '' === $min && '' === $max ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( $make_url( [], array( 'min_price', 'max_price' ) ) ); ?>">Todos</a>
					<?php foreach ( $ranges as $range ) : ?>
						<?php
						$is_range = ( (string) $range['min'] === (string) $min && (string) $range['max'] === (string) $max );
						$url      = $make_url( array( 'min_price' => $range['min'], 'max_price' => $range['max'] ), array( 'min_price', 'max_price' ) );
						?>
						<a class="mp-sf-pill<?php echo $is_range ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $range['label'] ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="mp-sf-group mp-sf-sale">
				<a class="mp-sf-pill is-sale<?php echo $on_sale ? ' is-active' : ''; ?>" href="<?php echo esc_url( $make_url( array( 'on_sale' => $on_sale ? null : '1' ) ) ); ?>">Ofertas</a>
			</div>
			<?php if ( $has_filters ) : ?>
				<a class="mp-sf-clear" href="<?php echo esc_url( $shop ); ?>">Limpiar filtros</a>
			<?php endif; ?>
		</div>
		</div>
	</div>
	<?php
}, 35 );

add_action( 'woocommerce_product_query', function ( $query ) {
	if ( is_admin() || ! isset( $_GET['on_sale'] ) || '1' !== $_GET['on_sale'] ) {
		return;
	}

	$ids = wc_get_product_ids_on_sale();
	if ( empty( $ids ) ) {
		$ids = array( 0 );
	}

	$existing = $query->get( 'post__in' );
	if ( ! empty( $existing ) ) {
		$ids = array_intersect( $existing, $ids );
	}

	$query->set( 'post__in', $ids ? $ids : array( 0 ) );
}, 20 );

/**
 * Mobile bottom navigation.
 */
add_action( 'wp_footer', function () {
	if ( is_admin() || is_checkout() ) {
		return;
	}

	$home    = home_url( '/' );
	$shop    = wc_get_page_permalink( 'shop' );
	$cart    = wc_get_cart_url();
	$account = wc_get_page_permalink( 'myaccount' );

	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

	$shop_active  = is_shop() || is_product_category() || is_product_tag() || is_product();
	$cart_active  = is_cart();
	$acc_active   = is_account_page();
	?>
	<nav class="mp-bn" aria-label="Navegación rápida">
		<a class="mp-bn-item<?php echo is_front_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( $home ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
			<span>Inicio</span>
		</a>
		<a class="mp-bn-item<?php echo $shop_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $shop ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.4"/><rect x="14" y="3" width="7" height="7" rx="1.4"/><rect x="3" y="14" width="7" height="7" rx="1.4"/><rect x="14" y="14" width="7" height="7" rx="1.4"/></svg>
			<span>Tienda</span>
		</a>
		<button type="button" class="mp-bn-item mp-bn-search" aria-label="Abrir buscador">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
			<span>Buscar</span>
		</button>
		<a class="mp-bn-item<?php echo $cart_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $cart ); ?>">
			<span class="mp-bn-ico">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 7h12l1 13H5L6 7Z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
				<span class="mp-bn-count<?php echo $count > 0 ? '' : ' is-empty'; ?>"><?php echo (int) $count; ?></span>
			</span>
			<span>Carrito</span>
		</a>
		<a class="mp-bn-item<?php echo $acc_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $account ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></svg>
			<span>Cuenta</span>
		</a>
	</nav>
	<?php
}, 40 );
