<?php
/**
 * Plugin Name: Sistema de Reserva con 30% de Adelanto y Envíos Shalom / Olva
 * Description: Permite al cliente pagar el 100% o separar su repuesto con el 30% de adelanto (saldo restante a cancelar antes del embarque a agencia Shalom u Olva).
 * Author: MOTO PARTS CRISTHIAN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. SELECTOR DE PAGO COMPLETO VS RESERVA CON 30% EN LA FICHA DEL PRODUCTO
 */
add_action( 'woocommerce_before_add_to_cart_button', 'mp_render_deposit_options_single_product', 15 );

function mp_render_deposit_options_single_product() {
	global $product;
	if ( ! $product || ! $product->is_purchasable() ) {
		return;
	}

	$price = (float) $product->get_price();
	if ( $price <= 0 ) {
		return;
	}

	$deposit_30 = round( $price * 0.30, 2 );
	$remaining_70 = round( $price - $deposit_30, 2 );
	?>
	<div class="mp-deposit-box" style="background:#fdfcfd;border:2px solid #742284;border-radius:12px;padding:16px 18px;margin:18px 0 20px 0;box-shadow:0 3px 10px rgba(116,34,132,0.06);">
		<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
			<span style="font-size:16px;">⚡</span>
			<strong style="color:#742284;font-size:14px;letter-spacing:0.3px;">Elige cómo deseas adquirir este repuesto:</strong>
		</div>

		<!-- Opción 1: Pago Completo -->
		<label class="mp-opt-label" style="display:flex;align-items:flex-start;gap:12px;padding:10px 14px;border:1.5px solid #dcd1e0;border-radius:8px;margin-bottom:10px;cursor:pointer;background:#fff;transition:all .2s;">
			<input type="radio" name="mp_purchase_type" value="full" checked style="margin-top:3px;accent-color:#742284;">
			<div style="flex:1;">
				<div style="display:flex;justify-content:space-between;align-items:center;">
					<strong style="color:#222;font-size:14px;">Pago Completo (100%)</strong>
					<span style="font-weight:800;color:#C41E3A;font-size:15px;">S/. <?php echo esc_html( number_format( $price, 2 ) ); ?></span>
				</div>
				<span style="display:block;font-size:12px;color:#666;margin-top:2px;">Paga el total y tu repuesto se despacha inmediatamente.</span>
			</div>
		</label>

		<!-- Opción 2: Separar con 30% -->
		<label class="mp-opt-label" style="display:flex;align-items:flex-start;gap:12px;padding:10px 14px;border:1.5px solid #dcd1e0;border-radius:8px;cursor:pointer;background:#fff;transition:all .2s;">
			<input type="radio" name="mp_purchase_type" value="deposit" style="margin-top:3px;accent-color:#742284;">
			<div style="flex:1;">
				<div style="display:flex;justify-content:space-between;align-items:center;">
					<strong style="color:#742284;font-size:14px;">Separar con 30% de Adelanto</strong>
					<span style="font-weight:800;color:#742284;font-size:15px;">Pagas hoy: S/. <?php echo esc_html( number_format( $deposit_30, 2 ) ); ?></span>
				</div>
				<span style="display:block;font-size:12px;color:#444;margin-top:4px;">
					📌 <strong>Saldo restante: S/. <?php echo esc_html( number_format( $remaining_70, 2 ) ); ?></strong> a cancelar antes de embarcarlo por agencia (SHALOM / OLVA).
				</span>
			</div>
		</label>

		<!-- Aviso de Envíos Nacionales e Internacionales -->
		<div style="background:#f4f9f6;border:1px solid #c8e6d5;border-radius:8px;padding:10px 12px;margin-top:12px;display:flex;align-items:center;gap:10px;font-size:12px;color:#1e5233;line-height:1.4;">
			<span style="font-size:18px;">🚚</span>
			<div>
				<strong>Envíos a todo el Perú y el extranjero:</strong> Despachos diarios garantizados por agencias <strong>SHALOM</strong> y <strong>OLVA COURIER</strong>.
			</div>
		</div>
	</div>

	<script>
	(function() {
		var labels = document.querySelectorAll('.mp-deposit-box .mp-opt-label');
		labels.forEach(function(l) {
			l.addEventListener('click', function() {
				labels.forEach(function(item) {
					item.style.borderColor = '#dcd1e0';
					item.style.backgroundColor = '#fff';
				});
				var radio = l.querySelector('input[type="radio"]');
				if (radio) {
					radio.checked = true;
					l.style.borderColor = '#742284';
					l.style.backgroundColor = '#faf5fc';
				}
			});
		});
	})();
	</script>
	<?php
}

/**
 * 2. CAPTURAR LA SELECCIÓN AL AÑADIR AL CARRITO
 */
add_filter( 'woocommerce_add_cart_item_data', 'mp_capture_deposit_cart_item_data', 10, 3 );

function mp_capture_deposit_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
	if ( isset( $_POST['mp_purchase_type'] ) && 'deposit' === $_POST['mp_purchase_type'] ) {
		$product = wc_get_product( $variation_id ? $variation_id : $product_id );
		if ( $product ) {
			$full_price = (float) $product->get_price();
			$deposit_amount = round( $full_price * 0.30, 2 );
			$remaining_amount = round( $full_price - $deposit_amount, 2 );

			$cart_item_data['mp_is_deposit']       = 'yes';
			$cart_item_data['mp_full_price']       = $full_price;
			$cart_item_data['mp_deposit_amount']   = $deposit_amount;
			$cart_item_data['mp_remaining_amount'] = $remaining_amount;

			// Clave única para evitar que se fusione con productos a precio completo
			$cart_item_data['unique_key'] = md5( microtime() . rand() );
		}
	}
	return $cart_item_data;
}

/**
 * 3. AJUSTAR EL PRECIO DEL ÍTEM EN EL CARRITO AL 30% SI ES RESERVA
 */
add_action( 'woocommerce_before_calculate_totals', 'mp_apply_deposit_price_in_cart', 20, 1 );

function mp_apply_deposit_price_in_cart( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item ) {
		if ( ! empty( $cart_item['mp_is_deposit'] ) && 'yes' === $cart_item['mp_is_deposit'] ) {
			$cart_item['data']->set_price( (float) $cart_item['mp_deposit_amount'] );
		}
	}
}

/**
 * 4. MOSTRAR EL DETALLE DE RESERVA EN EL CARRITO Y CHECKOUT
 */
add_filter( 'woocommerce_get_item_data', 'mp_display_deposit_data_in_cart', 10, 2 );

function mp_display_deposit_data_in_cart( $item_data, $cart_item ) {
	if ( ! empty( $cart_item['mp_is_deposit'] ) && 'yes' === $cart_item['mp_is_deposit'] ) {
		$item_data[] = array(
			'key'     => 'Modalidad',
			'value'   => '🔖 Reserva con 30% de adelanto',
			'display' => '',
		);
		$item_data[] = array(
			'key'     => 'Precio Total del Repuesto',
			'value'   => 'S/. ' . number_format( (float) $cart_item['mp_full_price'], 2 ),
			'display' => '',
		);
		$item_data[] = array(
			'key'     => 'Saldo Restante por Pagar',
			'value'   => 'S/. ' . number_format( (float) $cart_item['mp_remaining_amount'], 2 ) . ' (antes de embarcar a agencia SHALOM / OLVA)',
			'display' => '',
		);
	}
	return $item_data;
}

/**
 * 5. GUARDAR METADATOS DE RESERVA EN EL PEDIDO FINAL
 */
add_action( 'woocommerce_checkout_create_order_line_item', 'mp_save_deposit_to_order_line_item', 10, 4 );

function mp_save_deposit_to_order_line_item( $item, $cart_item_key, $values, $order ) {
	if ( ! empty( $values['mp_is_deposit'] ) && 'yes' === $values['mp_is_deposit'] ) {
		$item->add_meta_data( 'Modalidad', 'Reserva con 30% de adelanto', true );
		$item->add_meta_data( '_mp_is_deposit', 'yes', true );
		$item->add_meta_data( '_mp_full_price', (float) $values['mp_full_price'], true );
		$item->add_meta_data( '_mp_remaining_amount', (float) $values['mp_remaining_amount'], true );
	}
}

add_action( 'woocommerce_checkout_order_created', 'mp_save_order_level_deposit_meta', 10, 1 );

function mp_save_order_level_deposit_meta( $order ) {
	$has_deposit = false;
	$total_full = 0.0;
	$total_remaining = 0.0;

	foreach ( $order->get_items() as $item ) {
		$is_dep = $item->get_meta( '_mp_is_deposit' );
		$qty = (int) $item->get_quantity();

		if ( 'yes' === $is_dep ) {
			$has_deposit = true;
			$full_price = (float) $item->get_meta( '_mp_full_price' );
			$remaining = (float) $item->get_meta( '_mp_remaining_amount' );

			$total_full += ( $full_price * $qty );
			$total_remaining += ( $remaining * $qty );
		} else {
			$total_full += (float) $item->get_total();
		}
	}

	if ( $has_deposit ) {
		$order->update_meta_data( '_mp_has_deposit', 'yes' );
		$order->update_meta_data( '_mp_order_full_total', $total_full );
		$order->update_meta_data( '_mp_order_remaining_balance', $total_remaining );
		$order->update_meta_data( '_mp_deposit_terms', 'Saldo restante a cancelar antes de embarcar por agencia SHALOM / OLVA COURIER.' );
		$order->add_order_note( sprintf(
			'🔖 Pedido registrado como RESERVA CON 30%% DE ADELANTO. Total de repuestos: S/. %s. Adelanto pagado hoy: S/. %s. Saldo pendiente a cancelar antes del embarque por agencia: S/. %s.',
			number_format( $total_full, 2 ),
			number_format( (float) $order->get_total(), 2 ),
			number_format( $total_remaining, 2 )
		) );
		$order->save();
	}
}
