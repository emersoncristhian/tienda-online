<?php
/**
 * Plugin Name: Gestor de Comprobantes Yape y Tickets de Venta
 * Description: Permite al cliente subir su comprobante de Yape, muestra el comprobante en el admin, genera ticket de venta y lo envía por WhatsApp.
 * Author: MOTO PARTS CRISTHIAN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. INTERFAZ PARA SUBIR COMPROBANTE EN LA PÁGINA DE PEDIDO RECIBIDO (THANK YOU PAGE)
 */
add_action( 'woocommerce_thankyou_yape', 'mp_render_upload_receipt_section', 20 );

function mp_render_upload_receipt_section( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$receipt_url = $order->get_meta( '_yape_receipt_url' );
	$receipt_date = $order->get_meta( '_yape_receipt_date' );
	$order_key = $order->get_order_key();
	?>
	<div id="mp-yape-upload-card" style="background:#ffffff;border:2px dashed #742284;border-radius:14px;padding:24px;margin:24px 0;box-shadow:0 4px 14px rgba(116,34,132,0.08);text-align:center;">
		<div style="display:inline-flex;align-items:center;gap:10px;background:#f7f0fa;padding:6px 16px;border-radius:20px;margin-bottom:12px;">
			<span style="font-size:18px;">📸</span>
			<strong style="color:#742284;font-size:15px;">Adjuntar Comprobante de Pago Yape</strong>
		</div>

		<div id="mp-receipt-status-area">
			<?php if ( ! empty( $receipt_url ) ) : ?>
				<div style="background:#e8f7ee;border:1px solid #7cd69e;border-radius:10px;padding:14px;margin-bottom:16px;">
					<p style="margin:0 0 8px 0;color:#1e7e44;font-weight:700;font-size:15px;">
						✅ ¡Comprobante recibido con éxito!
					</p>
					<p style="margin:0 0 12px 0;color:#444;font-size:13px;">
						Subido el: <?php echo esc_html( $receipt_date ?: date('d/m/Y H:i') ); ?>. Tu pedido está en verificación para el empaque.
					</p>
					<div style="margin-bottom:12px;">
						<a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener" style="display:inline-block;border:1px solid #ccc;border-radius:8px;overflow:hidden;max-width:180px;">
							<img src="<?php echo esc_url( $receipt_url ); ?>" alt="Comprobante Yape" style="max-width:100%;height:auto;display:block;">
						</a>
					</div>
					<button type="button" onclick="document.getElementById('mp-receipt-form-wrap').style.display='block';" style="background:transparent;border:none;color:#742284;text-decoration:underline;cursor:pointer;font-size:13px;font-weight:600;">
						¿Deseas cambiar o resubir la imagen?
					</button>
				</div>
			<?php else : ?>
				<p style="margin:0 0 16px 0;font-size:14px;color:#555;">
					¿Ya realizaste tu transferencia por Yape? Sube aquí tu captura de pantalla o voucher para que nuestro equipo lo valide al instante:
				</p>
			<?php endif; ?>
		</div>

		<div id="mp-receipt-form-wrap" style="<?php echo ! empty( $receipt_url ) ? 'display:none;' : ''; ?>">
			<form id="mp-yape-upload-form" enctype="multipart/form-data" style="max-width:420px;margin:0 auto;">
				<input type="hidden" name="action" value="mp_upload_yape_receipt">
				<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
				<input type="hidden" name="order_key" value="<?php echo esc_attr( $order_key ); ?>">
				<?php wp_nonce_field( 'mp_upload_receipt_' . $order_id, 'receipt_nonce' ); ?>

				<div style="border:2px dashed #bba0c4;border-radius:10px;padding:20px;background:#fbf8fd;margin-bottom:14px;cursor:pointer;" onclick="document.getElementById('yape_receipt_file').click();">
					<span style="font-size:32px;display:block;margin-bottom:6px;">📤</span>
					<span style="font-weight:600;color:#742284;font-size:14px;display:block;">Haz clic aquí para seleccionar tu foto o captura</span>
					<span style="font-size:12px;color:#777;display:block;margin-top:4px;">Formatos: JPG, PNG o PDF (Máx. 8MB)</span>
					<input type="file" name="yape_receipt_file" id="yape_receipt_file" accept="image/*,.pdf" style="display:none;" onchange="mpPreviewFile(this);">
					<div id="mp-file-name-preview" style="margin-top:8px;font-weight:bold;color:#333;font-size:13px;"></div>
				</div>

				<button type="submit" id="mp-btn-upload-receipt" style="background:#742284;color:#fff;border:none;border-radius:50px;padding:12px 28px;font-size:15px;font-weight:700;cursor:pointer;width:100%;box-shadow:0 3px 10px rgba(116,34,132,0.25);">
					Subir mi Comprobante
				</button>
				<p id="mp-upload-msg" style="margin:10px 0 0 0;font-size:13px;font-weight:600;"></p>
			</form>
		</div>
	</div>

	<script>
	function mpPreviewFile(input) {
		var nameArea = document.getElementById('mp-file-name-preview');
		if (input.files && input.files[0]) {
			nameArea.textContent = '📄 Archivo seleccionado: ' + input.files[0].name;
		} else {
			nameArea.textContent = '';
		}
	}

	document.getElementById('mp-yape-upload-form').addEventListener('submit', function(e) {
		e.preventDefault();
		var fileInput = document.getElementById('yape_receipt_file');
		var msg = document.getElementById('mp-upload-msg');
		var btn = document.getElementById('mp-btn-upload-receipt');

		if (!fileInput.files || fileInput.files.length === 0) {
			msg.style.color = '#c41e3a';
			msg.textContent = 'Por favor selecciona una imagen antes de subir.';
			return;
		}

		var formData = new FormData(this);
		btn.disabled = true;
		btn.textContent = 'Subiendo comprobante...';
		msg.textContent = '';

		fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
			method: 'POST',
			body: formData
		})
		.then(function(res) { return res.json(); })
		.then(function(data) {
			btn.disabled = false;
			btn.textContent = 'Subir mi Comprobante';
			if (data.success) {
				msg.style.color = '#1e7e44';
				msg.textContent = '✅ ' + data.data.message;
				setTimeout(function() { window.location.reload(); }, 1200);
			} else {
				msg.style.color = '#c41e3a';
				msg.textContent = '❌ ' + (data.data && data.data.message ? data.data.message : 'Error al subir comprobante');
			}
		})
		.catch(function(err) {
			btn.disabled = false;
			btn.textContent = 'Subir mi Comprobante';
			msg.style.color = '#c41e3a';
			msg.textContent = 'Error de conexión. Inténtalo de nuevo.';
		});
	});
	</script>
	<?php
}

/**
 * 2. ENDPOINT AJAX PARA PROCESAR LA SUBIDA DEL COMPROBANTE
 */
add_action( 'wp_ajax_mp_upload_yape_receipt', 'mp_handle_yape_receipt_upload' );
add_action( 'wp_ajax_nopriv_mp_upload_yape_receipt', 'mp_handle_yape_receipt_upload' );

function mp_handle_yape_receipt_upload() {
	$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	$order_key = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';

	check_ajax_referer( 'mp_upload_receipt_' . $order_id, 'receipt_nonce' );

	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_order_key() !== $order_key ) {
		wp_send_json_error( array( 'message' => 'Pedido no válido o acceso denegado.' ) );
	}

	if ( empty( $_FILES['yape_receipt_file']['name'] ) ) {
		wp_send_json_error( array( 'message' => 'No se seleccionó ningún archivo.' ) );
	}

	$file = $_FILES['yape_receipt_file'];
	$allowed_types = array( 'image/jpeg', 'image/png', 'image/webp', 'application/pdf' );

	if ( ! in_array( $file['type'], $allowed_types, true ) ) {
		wp_send_json_error( array( 'message' => 'Tipo de archivo no permitido. Solo se aceptan imágenes (JPG, PNG, WEBP) o PDF.' ) );
	}

	if ( $file['size'] > 8 * 1024 * 1024 ) {
		wp_send_json_error( array( 'message' => 'El archivo supera el tamaño máximo permitido de 8MB.' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$upload = wp_handle_upload( $file, array( 'test_form' => false ) );

	if ( isset( $upload['error'] ) && $upload['error'] ) {
		wp_send_json_error( array( 'message' => 'Error al guardar el archivo: ' . $upload['error'] ) );
	}

	$receipt_url = $upload['url'];
	$receipt_date = current_time( 'd/m/Y H:i:s' );

	// Guardar en la orden
	$order->update_meta_data( '_yape_receipt_url', $receipt_url );
	$order->update_meta_data( '_yape_receipt_date', $receipt_date );
	$order->add_order_note( sprintf( '📸 Comprobante de pago Yape subido por el cliente. Ver: %s', $receipt_url ) );
	$order->save();

	wp_send_json_success( array(
		'message' => 'Comprobante recibido con éxito. Muchas gracias.',
		'url'     => $receipt_url,
		'date'    => $receipt_date,
	) );
}

/**
 * 3. META BOX EN EL PANEL DE ADMIN (DETALLES DEL PEDIDO EN WOOCOMMERCE)
 */
add_action( 'add_meta_boxes', 'mp_add_receipt_and_ticket_meta_box', 20 );

function mp_add_receipt_and_ticket_meta_box() {
	$screens = array( 'shop_order', 'woocommerce_page_wc-orders' );
	foreach ( $screens as $screen ) {
		add_meta_box(
			'mp_receipt_ticket_box',
			'🧾 Comprobante Yape & Ticket de Venta',
			'mp_render_admin_receipt_ticket_box',
			$screen,
			'side',
			'high'
		);
	}
}

function mp_render_admin_receipt_ticket_box( $post_or_order ) {
	$order = ( $post_or_order instanceof WC_Order ) ? $post_or_order : wc_get_order( $post_or_order->ID );
	if ( ! $order ) {
		echo '<p>No se pudo cargar el pedido.</p>';
		return;
	}

	$order_id = $order->get_id();
	$receipt_url = $order->get_meta( '_yape_receipt_url' );
	$receipt_date = $order->get_meta( '_yape_receipt_date' );
	$payment_method = $order->get_payment_method();

	// Limpiar teléfono del cliente para WhatsApp
	$billing_phone = preg_replace( '/\D/', '', $order->get_billing_phone() );
	if ( strlen( $billing_phone ) === 9 ) {
		$billing_phone = '51' . $billing_phone;
	}

	// URL del ticket digital
	$ticket_url = add_query_arg(
		array(
			'mp_ticket' => '1',
			'order_id'  => $order_id,
			'key'       => $order->get_order_key(),
		),
		home_url( '/' )
	);

	// Mensaje WhatsApp preformateado
	$client_name = $order->get_billing_first_name() ?: 'Cliente';
	$total_formatted = html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ) );
	$is_deposit = ( 'yes' === $order->get_meta( '_mp_has_deposit' ) );
	$full_total = (float) $order->get_meta( '_mp_order_full_total' );
	$remaining_balance = (float) $order->get_meta( '_mp_order_remaining_balance' );

	if ( $is_deposit ) {
		$wsp_msg = sprintf(
			"Hola %s, te saluda *MOTO PARTS CRISTHIAN* 🏍️.\nConfirmamos la reserva de tu pedido #%s con el adelanto del 30%% (%s).\n📌 Recuerda que el saldo pendiente de S/. %s se cancela antes del embarque por agencia (SHALOM / OLVA COURIER).\nTu ticket digital está listo aquí:\n%s\n\n¡Muchas gracias!",
			$client_name,
			$order->get_order_number(),
			$total_formatted,
			number_format( $remaining_balance, 2 ),
			$ticket_url
		);
	} else {
		$wsp_msg = sprintf(
			"Hola %s, te saluda *MOTO PARTS CRISTHIAN* 🏍️.\nConfirmamos tu pedido #%s por %s.\nTu ticket de venta digital está listo aquí:\n%s\n\n¡Muchas gracias por tu compra!",
			$client_name,
			$order->get_order_number(),
			$total_formatted,
			$ticket_url
		);
	}
	$wsp_link = 'https://wa.me/' . $billing_phone . '?text=' . rawurlencode( $wsp_msg );
	?>
	<div style="font-size:13px;line-height:1.5;">
		<?php if ( $is_deposit ) : ?>
			<div style="background:#faf5fc;border:1.5px solid #742284;border-radius:8px;padding:10px 12px;margin-bottom:14px;">
				<div style="font-weight:800;color:#742284;font-size:13px;margin-bottom:4px;">🔖 PEDIDO CON 30% DE ADELANTO</div>
				<p style="margin:0 0 4px 0;font-size:12px;"><strong>Total repuestos:</strong> S/. <?php echo esc_html( number_format( $full_total, 2 ) ); ?></p>
				<p style="margin:0 0 4px 0;font-size:12px;color:#1e7e44;"><strong>Adelanto cobrado (30%):</strong> <?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></p>
				<p style="margin:0;font-size:13px;color:#C41E3A;font-weight:bold;"><strong>Saldo por cobrar (70%): S/. <?php echo esc_html( number_format( $remaining_balance, 2 ) ); ?></strong></p>
				<p style="margin:4px 0 0 0;font-size:11px;color:#666;">⚠️ Cobrar saldo antes de embarcar por SHALOM / OLVA.</p>
			</div>
		<?php endif; ?>
		<!-- SECCIÓN 1: COMPROBANTE SUBIDO -->
		<div style="margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #ddd;">
			<strong style="display:block;margin-bottom:6px;color:#333;">📸 Comprobante de Pago Yape:</strong>
			<?php if ( ! empty( $receipt_url ) ) : ?>
				<div style="text-align:center;background:#f9f9f9;padding:10px;border-radius:6px;border:1px solid #ddd;margin-bottom:8px;">
					<a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener">
						<img src="<?php echo esc_url( $receipt_url ); ?>" alt="Comprobante" style="max-width:100%;max-height:180px;height:auto;border-radius:4px;display:inline-block;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
					</a>
					<p style="margin:6px 0 0 0;font-size:11px;color:#666;">
						Subido: <?php echo esc_html( $receipt_date ?: '-' ); ?>
					</p>
				</div>
				<a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener" class="button button-secondary" style="width:100%;text-align:center;display:block;">
					🔍 Ver Comprobante Completo
				</a>
			<?php else : ?>
				<div style="background:#fff3cd;color:#856404;padding:8px 10px;border-radius:4px;border:1px solid #ffeeba;font-size:12px;">
					⚠️ El cliente aún no ha subido comprobante en la web.
				</div>
			<?php endif; ?>
		</div>

		<!-- SECCIÓN 2: GENERAR TICKET -->
		<div style="margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #ddd;">
			<strong style="display:block;margin-bottom:6px;color:#333;">🖨️ Ticket de Venta:</strong>
			<a href="<?php echo esc_url( $ticket_url ); ?>" target="_blank" rel="noopener" class="button button-primary" style="width:100%;text-align:center;display:block;background:#0073aa;border-color:#0073aa;padding:4px 0;">
				📄 Generar / Imprimir Ticket
			</a>
		</div>

		<!-- SECCIÓN 3: ENVIAR TICKET POR WHATSAPP AL CLIENTE -->
		<div>
			<strong style="display:block;margin-bottom:6px;color:#333;">📲 Notificar al Cliente:</strong>
			<?php if ( ! empty( $billing_phone ) ) : ?>
				<p style="margin:0 0 6px 0;font-size:12px;color:#555;">
					WhatsApp: <strong>+<?php echo esc_html( $billing_phone ); ?></strong>
				</p>
				<a href="<?php echo esc_url( $wsp_link ); ?>" target="_blank" rel="noopener" class="button" style="background:#25D366;color:#fff;border-color:#25D366;font-weight:bold;width:100%;text-align:center;display:block;padding:4px 0;">
					💬 Enviar Ticket por WhatsApp
				</a>
			<?php else : ?>
				<p style="margin:0;color:#888;font-size:12px;">El cliente no registró teléfono.</p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * 5. COLUMNA VISUAL EN EL LISTADO PRINCIPAL DE PEDIDOS DE WOOCOMMERCE
 */
add_filter( 'manage_woocommerce_page_wc-orders_columns', 'mp_add_receipt_column' );
add_filter( 'manage_edit-shop_order_columns', 'mp_add_receipt_column' );

function mp_add_receipt_column( $columns ) {
	$new_columns = array();
	foreach ( $columns as $key => $column ) {
		$new_columns[ $key ] = $column;
		if ( 'order_status' === $key || 'order_total' === $key ) {
			$new_columns['mp_receipt_status'] = 'Comprobante Yape';
		}
	}
	if ( ! isset( $new_columns['mp_receipt_status'] ) ) {
		$new_columns['mp_receipt_status'] = 'Comprobante Yape';
	}
	return $new_columns;
}

add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'mp_render_receipt_column_content', 10, 2 );
add_action( 'manage_shop_order_posts_custom_column', 'mp_render_receipt_column_content_cpt', 10, 2 );

function mp_render_receipt_column_content( $column, $order ) {
	if ( 'mp_receipt_status' !== $column ) {
		return;
	}
	$receipt_url = $order->get_meta( '_yape_receipt_url' );
	$method = $order->get_payment_method();

	if ( 'yape' !== $method && empty( $receipt_url ) ) {
		echo '<span style="color:#aaa;">—</span>';
		return;
	}

	if ( ! empty( $receipt_url ) ) {
		echo '<a href="' . esc_url( $receipt_url ) . '" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:4px;background:#e8f7ee;color:#1e7e44;padding:4px 8px;border-radius:12px;font-weight:700;font-size:11px;text-decoration:none;border:1px solid #7cd69e;">';
		echo '📸 Ver Comprobante</a>';
	} else {
		echo '<span style="display:inline-block;background:#fff3cd;color:#856404;padding:3px 7px;border-radius:12px;font-size:11px;border:1px solid #ffeeba;">⚪ Pendiente</span>';
	}
}

function mp_render_receipt_column_content_cpt( $column, $post_id ) {
	if ( 'mp_receipt_status' === $column ) {
		$order = wc_get_order( $post_id );
		if ( $order ) {
			mp_render_receipt_column_content( $column, $order );
		}
	}
}

add_action( 'template_redirect', 'mp_handle_ticket_view' );

function mp_handle_ticket_view() {
	if ( ! isset( $_GET['mp_ticket'] ) || empty( $_GET['order_id'] ) ) {
		return;
	}

	$order_id = absint( $_GET['order_id'] );
	$key      = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
	$order    = wc_get_order( $order_id );

	if ( ! $order ) {
		wp_die( 'Pedido no encontrado.', 'Error', array( 'response' => 404 ) );
	}

	// Seguridad: Debe ser admin logueado O tener el order_key correspondiente
	if ( ! current_user_can( 'manage_woocommerce' ) && $order->get_order_key() !== $key ) {
		wp_die( 'Acceso denegado a este comprobante.', 'Seguridad', array( 'response' => 403 ) );
	}

	$order_number   = $order->get_order_number();
	$order_date     = $order->get_date_created() ? $order->get_date_created()->date_i18n( 'd/m/Y H:i' ) : date( 'd/m/Y H:i' );
	$payment_title  = $order->get_payment_method_title();
	$client_name    = $order->get_formatted_billing_full_name();
	$client_phone   = $order->get_billing_phone();
	$client_address = $order->get_billing_address_1() . ( $order->get_billing_city() ? ' - ' . $order->get_billing_city() : '' );
	$items          = $order->get_items();
	$total          = $order->get_formatted_order_total();
	?>
	<!DOCTYPE html>
	<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Ticket #<?php echo esc_html( $order_number ); ?> - MOTO PARTS CRISTHIAN</title>
		<style>
			body {
				font-family: 'Courier New', Courier, monospace, Arial, sans-serif;
				background: #eceff1;
				margin: 0;
				padding: 20px;
				color: #111;
			}
			.ticket-wrap {
				max-width: 380px;
				margin: 0 auto;
				background: #fff;
				padding: 24px 20px;
				border-radius: 8px;
				box-shadow: 0 4px 18px rgba(0,0,0,0.1);
				border-top: 5px solid #C41E3A;
			}
			.ticket-header {
				text-align: center;
				border-bottom: 1px dashed #777;
				padding-bottom: 14px;
				margin-bottom: 14px;
			}
			.brand-name {
				font-size: 19px;
				font-weight: 800;
				margin: 0 0 4px 0;
				letter-spacing: 0.5px;
			}
			.brand-tag {
				font-size: 11px;
				color: #555;
				margin: 0 0 6px 0;
			}
			.brand-contact {
				font-size: 11px;
				color: #444;
				margin: 0;
				line-height: 1.4;
			}
			.ticket-info {
				font-size: 12px;
				line-height: 1.5;
				border-bottom: 1px dashed #777;
				padding-bottom: 12px;
				margin-bottom: 12px;
			}
			.ticket-table {
				width: 100%;
				border-collapse: collapse;
				font-size: 12px;
				margin-bottom: 14px;
			}
			.ticket-table th {
				border-bottom: 1px solid #222;
				text-align: left;
				padding: 4px 0;
			}
			.ticket-table td {
				padding: 6px 0;
				vertical-align: top;
			}
			.ticket-totals {
				border-top: 1px dashed #777;
				padding-top: 10px;
				margin-bottom: 14px;
				font-size: 13px;
			}
			.total-row {
				display: flex;
				justify-content: space-between;
				margin-bottom: 4px;
			}
			.grand-total {
				font-size: 16px;
				font-weight: 800;
				color: #C41E3A;
				border-top: 1px solid #111;
				padding-top: 6px;
				margin-top: 6px;
			}
			.ticket-footer {
				text-align: center;
				font-size: 11px;
				color: #666;
				border-top: 1px dashed #777;
				padding-top: 12px;
			}
			.no-print {
				text-align: center;
				margin-top: 18px;
			}
			.btn-print {
				background: #C41E3A;
				color: #fff;
				border: none;
				padding: 10px 24px;
				font-size: 14px;
				font-weight: bold;
				border-radius: 4px;
				cursor: pointer;
			}
			@media print {
				body {
					background: #fff;
					padding: 0;
				}
				.ticket-wrap {
					box-shadow: none;
					border: none;
					padding: 0;
					max-width: 100%;
				}
				.no-print {
					display: none !important;
				}
			}
		</style>
	</head>
	<body>
		<div class="ticket-wrap">
			<div class="ticket-header">
				<div class="brand-name">MOTO PARTS CRISTHIAN</div>
				<div class="brand-tag">Repuestos para Motos Chinas en Perú</div>
				<div class="brand-contact">
					Lima - Perú &bull; Tel/WSP: +51 986 947 730<br>
					Correo: crsthnemerson@gmail.com
				</div>
			</div>

			<div class="ticket-info">
				<div><strong>TICKET DE PEDIDO:</strong> #<?php echo esc_html( $order_number ); ?></div>
				<div><strong>FECHA:</strong> <?php echo esc_html( $order_date ); ?></div>
				<div><strong>CLIENTE:</strong> <?php echo esc_html( $client_name ?: 'Cliente' ); ?></div>
				<?php if ( ! empty( $client_phone ) ) : ?>
					<div><strong>TELÉFONO:</strong> <?php echo esc_html( $client_phone ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $client_address ) ) : ?>
					<div><strong>DIRECCIÓN:</strong> <?php echo esc_html( $client_address ); ?></div>
				<?php endif; ?>
				<div><strong>PAGO:</strong> <?php echo esc_html( $payment_title ); ?></div>
			</div>

			<table class="ticket-table">
				<thead>
					<tr>
						<th>CANT.</th>
						<th>PRODUCTO</th>
						<th style="text-align:right;">TOTAL</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item_id => $item ) : ?>
						<tr>
							<td style="width:15%;"><?php echo esc_html( $item->get_quantity() ); ?>x</td>
							<td><?php echo esc_html( $item->get_name() ); ?></td>
							<td style="text-align:right;width:30%;">
								S/. <?php echo esc_html( number_format( (float) $item->get_total(), 2 ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div class="ticket-totals">
				<?php 
				$is_deposit = ( 'yes' === $order->get_meta( '_mp_has_deposit' ) );
				$full_total = (float) $order->get_meta( '_mp_order_full_total' );
				$remaining_balance = (float) $order->get_meta( '_mp_order_remaining_balance' );
				?>
				<?php if ( $is_deposit ) : ?>
					<div class="total-row" style="color:#555;">
						<span>VALOR TOTAL REPUESTOS:</span>
						<span>S/. <?php echo esc_html( number_format( $full_total, 2 ) ); ?></span>
					</div>
					<div class="total-row grand-total" style="color:#742284;">
						<span>ADELANTO PAGADO (30%):</span>
						<span><?php echo wp_kses_post( $total ); ?></span>
					</div>
					<div class="total-row" style="color:#C41E3A;font-weight:bold;margin-top:6px;border-top:1px dashed #C41E3A;padding-top:6px;">
						<span>SALDO PENDIENTE (70%):</span>
						<span>S/. <?php echo esc_html( number_format( $remaining_balance, 2 ) ); ?></span>
					</div>
					<div style="font-size:10px;color:#666;margin-top:8px;text-align:center;line-height:1.4;background:#faf5fc;padding:6px 8px;border-radius:4px;border:1px dashed #d9bfdf;">
						⚠️ <strong>Condición de embarque:</strong> El saldo pendiente (70%) debe cancelarse antes del despacho por agencia <strong>SHALOM / OLVA COURIER</strong>.
					</div>
				<?php else : ?>
					<div class="total-row">
						<span>Subtotal:</span>
						<span>S/. <?php echo esc_html( number_format( (float) $order->get_subtotal(), 2 ) ); ?></span>
					</div>
					<?php if ( (float) $order->get_shipping_total() > 0 ) : ?>
						<div class="total-row">
							<span>Envío:</span>
							<span>S/. <?php echo esc_html( number_format( (float) $order->get_shipping_total(), 2 ) ); ?></span>
						</div>
					<?php endif; ?>
					<div class="total-row grand-total">
						<span>TOTAL A PAGAR:</span>
						<span><?php echo wp_kses_post( $total ); ?></span>
					</div>
				<?php endif; ?>
			</div>

			<div class="ticket-footer">
				<p style="margin:0 0 4px 0;font-weight:bold;">¡Gracias por tu compra y confianza!</p>
				<p style="margin:0;">Conserva este ticket digital para el recojo o seguimiento de tu envío.</p>
			</div>

			<div class="no-print">
				<button class="btn-print" onclick="window.print();">🖨️ Imprimir Ticket / Guardar PDF</button>
			</div>
		</div>
	</body>
	</html>
	<?php
	exit;
}
