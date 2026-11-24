<?php
add_action( 'wp_ajax_mp_newsletter', 'mp_newsletter_handler' );
add_action( 'wp_ajax_nopriv_mp_newsletter', 'mp_newsletter_handler' );

function mp_newsletter_handler() {
	check_ajax_referer( 'mp_newsletter', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'Ingresa un correo válido.' ) );
	}

	$subscribers = get_option( 'mp_newsletter_subscribers', array() );
	if ( ! is_array( $subscribers ) ) {
		$subscribers = array();
	}

	if ( isset( $subscribers[ $email ] ) ) {
		wp_send_json_success( array( 'message' => 'Ya estás suscrito. ¡Gracias!' ) );
	}

	$subscribers[ $email ] = current_time( 'mysql' );
	update_option( 'mp_newsletter_subscribers', $subscribers, false );

	wp_mail(
		get_option( 'admin_email' ),
		'Nuevo suscriptor MOTO PARTS CRISTHIAN',
		'Correo: ' . $email
	);

	wp_send_json_success( array( 'message' => '¡Gracias! Te avisaremos de las mejores ofertas.' ) );
}

add_filter( 'blocksy:footer:copyright:value', function () {
	return '&copy; ' . date( 'Y' ) . ' MOTO PARTS CRISTHIAN &mdash; Repuestos para Motos Chinas en Peru';
} );

add_action( 'blocksy:footer:before', function () {
	$shop_url     = wc_get_page_permalink( 'shop' );
	$cart_url     = wc_get_cart_url();
	$account_url  = wc_get_page_permalink( 'myaccount' );
	$contact_url  = home_url( '/contacto/' );
	$offers_url   = add_query_arg( 'on_sale', '1', $shop_url );
	$whatsapp_url = 'https://wa.me/51986947730';
	?>
	<div class="mp-footer">
		<div class="mp-news">
			<div class="mp-news-copy">
				<span class="mp-news-eyebrow">Newsletter</span>
				<h3>Ofertas y novedades en tu correo</h3>
				<p>Suscríbete y entérate primero de descuentos, repuestos nuevos y promociones.</p>
			</div>
			<form class="mp-news-form" novalidate>
				<div class="mp-news-field">
					<input type="email" name="email" placeholder="Tu correo electrónico" autocomplete="email" required aria-label="Correo electrónico">
					<button type="submit">Suscribirme</button>
				</div>
				<p class="mp-news-msg" aria-live="polite"></p>
				<p class="mp-news-note">Sin spam. Puedes darte de baja cuando quieras.</p>
			</form>
		</div>
		<script>window.mpNews = <?php echo wp_json_encode( array( 'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'mp_newsletter' ) ) ); ?>;</script>
		<div class="mp-wrap">
			<div class="mp-col">
				<div class="mp-brand">MOTO PARTS <span>CRISTHIAN</span></div>
				<p class="mp-tag">Repuestos para Motos Chinas en Peru</p>
				<p>Repuestos originales y accesorios de calidad para mantener tu moto en las mejores condiciones.</p>
				<div class="mp-social">
					<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">WA</a>
					<a href="mailto:crsthnemerson@gmail.com" aria-label="Correo">@</a>
				</div>
			</div>
			<div class="mp-col">
				<h4>Categorias</h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/product-category/motor/' ) ); ?>">Motor</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/frenos/' ) ); ?>">Frenos</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/transmision/' ) ); ?>">Transmision</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/electrico/' ) ); ?>">Electrico</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/suspension/' ) ); ?>">Suspension</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/filtracion/' ) ); ?>">Filtracion</a></li>
					<li><a href="<?php echo esc_url( home_url( '/product-category/accesorios/' ) ); ?>">Accesorios</a></li>
				</ul>
			</div>
			<div class="mp-col">
				<h4>Enlaces utiles</h4>
				<ul>
					<li><a href="<?php echo esc_url( $shop_url ); ?>">Tienda</a></li>
					<li><a href="<?php echo esc_url( $offers_url ); ?>">Ofertas</a></li>
					<li><a href="<?php echo esc_url( $account_url ); ?>">Mi cuenta</a></li>
					<li><a href="<?php echo esc_url( $cart_url ); ?>">Carrito</a></li>
					<li><a href="<?php echo esc_url( $contact_url ); ?>">Contacto</a></li>
				</ul>
			</div>
			<div class="mp-col">
				<h4>Contacto</h4>
				<ul class="mp-contact">
					<li><span class="mp-ico">DIR</span> Lima, Perú &mdash; Envíos a todo el país y extranjero por SHALOM y OLVA</li>
					<li><span class="mp-ico">TEL</span> <a href="tel:+51986947730">+51 986 947 730</a></li>
					<li><span class="mp-ico">WSP</span> <a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener">+51 986 947 730</a></li>
					<li><span class="mp-ico">MAIL</span> <a href="mailto:crsthnemerson@gmail.com">crsthnemerson@gmail.com</a></li>
					<li><span class="mp-ico">HOR</span> Lun - Sab: 9:00 a 19:00</li>
				</ul>
			</div>
		</div>
	</div>
	<?php
} );

add_action( 'wp_head', function () {
	?>
	<style>
		.mp-footer{background:#0F0F1A;color:rgba(255,255,255,.78);border-top:4px solid #C41E3A;}
		.mp-footer .mp-wrap{max-width:1200px;margin:0 auto;padding:50px 20px 34px;display:grid;grid-template-columns:1.4fr 1fr 1fr 1.2fr;gap:38px;}
		.mp-footer h4{color:#fff;font-size:15px;text-transform:uppercase;letter-spacing:1px;margin:0 0 16px;padding-bottom:10px;border-bottom:2px solid #C41E3A;display:inline-block;}
		.mp-footer p{font-size:14px;line-height:1.7;margin:0 0 12px;}
		.mp-footer ul{list-style:none;margin:0;padding:0;}
		.mp-footer li{margin-bottom:10px;font-size:14px;line-height:1.5;}
		.mp-footer a{color:rgba(255,255,255,.78);text-decoration:none;transition:color .2s;}
		.mp-footer a:hover{color:#FF6B00;}
		.mp-footer .mp-brand{font-size:26px;font-weight:900;color:#fff;line-height:1.05;letter-spacing:.5px;}
		.mp-footer .mp-brand span{color:#C41E3A;}
		.mp-footer .mp-tag{color:#FF6B00;font-size:12px;text-transform:uppercase;letter-spacing:2px;margin:8px 0 14px;}
		.mp-footer .mp-social{display:flex;gap:10px;margin-top:18px;}
		.mp-footer .mp-social a{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);font-size:13px;font-weight:700;color:#fff;}
		.mp-footer .mp-social a:hover{background:#C41E3A;border-color:#C41E3A;color:#fff;}
		.mp-footer .mp-contact li{display:flex;gap:10px;align-items:flex-start;}
		.mp-footer .mp-ico{color:#C41E3A;font-weight:700;font-size:11px;min-width:34px;padding-top:2px;}
		.ct-footer [data-row="bottom"]{border-top:1px solid rgba(255,255,255,.1);}
		.mp-news{max-width:1200px;margin:0 auto;padding:40px 20px 32px;display:grid;grid-template-columns:1.15fr 1fr;gap:30px;align-items:center;border-bottom:1px solid rgba(255,255,255,.1);}
		.mp-news-eyebrow{display:inline-block;margin-bottom:10px;padding:4px 10px;border-radius:999px;background:rgba(196,30,58,.18);border:1px solid rgba(196,30,58,.4);color:#FF8A3D;font-size:10px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;}
		.mp-news h3{color:#fff;font-family:'Barlow Condensed','Inter',sans-serif;font-size:30px;font-weight:800;line-height:1.05;letter-spacing:.3px;margin:0 0 8px;text-transform:uppercase;}
		.mp-news p{font-size:13.5px;line-height:1.6;color:rgba(255,255,255,.62);margin:0;}
		.mp-news-form{margin:0;}
		.mp-news-field{display:flex;gap:0;}
		.mp-news-field input{flex:1;min-width:0;padding:15px 16px;border:1px solid rgba(255,255,255,.18);border-right:0;border-radius:10px 0 0 10px;background:rgba(255,255,255,.06);color:#fff;font-family:'Inter',sans-serif;font-size:14px;outline:none;transition:border-color .2s,background .2s;}
		.mp-news-field input::placeholder{color:rgba(255,255,255,.45);}
		.mp-news-field input:focus{border-color:rgba(255,255,255,.45);background:rgba(255,255,255,.09);}
		.mp-news-field button{padding:15px 22px;border:0;border-radius:0 10px 10px 0;background:#C41E3A;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;cursor:pointer;transition:background .2s;}
		.mp-news-field button:hover{background:#FF6B00;}
		.mp-news-field button:disabled{opacity:.6;cursor:wait;}
		.mp-news-msg{min-height:16px;margin:10px 0 0!important;font-size:12.5px;line-height:1.3;color:#FF8A3D;}
		.mp-news-msg.is-ok{color:#4ADE80;}
		.mp-news-msg.is-err{color:#FF6B6B;}
		.mp-news-note{color:rgba(255,255,255,.42)!important;font-size:11.5px!important;margin-top:6px!important;}
		@media(max-width:900px){.mp-footer .mp-wrap{grid-template-columns:1fr 1fr;gap:30px;}.mp-news{grid-template-columns:1fr;gap:18px;padding:34px 20px 26px;}}
		@media(max-width:560px){.mp-footer .mp-wrap{grid-template-columns:1fr;}.mp-news h3{font-size:24px;}.mp-news-field{flex-direction:column;gap:8px;}.mp-news-field input{border-right:1px solid rgba(255,255,255,.18);border-radius:10px;}.mp-news-field button{border-radius:10px;}}
	</style>
	<?php
} );

