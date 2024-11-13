<?php
function lvn_add_update_form_billing( $fragments ) {

	$checkout = WC()->checkout();

	parse_str( $_POST['post_data'], $fields_values );

	ob_start();

	echo '<div class="woocommerce-billing-fields__field-wrapper">';

	$fields = $checkout->get_checkout_fields( 'billing' );

	foreach ( $fields as $key => $field ) {
		$value = $checkout->get_value( $key );

		if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
			$field['country'] = $checkout->get_value( $field['country_field'] );
		}

		if ( ! $value && ! empty( $fields_values[ $key ] ) ) {
			$value = $fields_values[ $key ];
		}

		woocommerce_form_field( $key, $field, $value );
	}

	echo '</div>';

	$fragments['.woocommerce-billing-fields__field-wrapper'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_update_order_review_fragments', 'lvn_add_update_form_billing', 99 );


function lvn_override_checkout_fields( $fields ) {

	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

	/* Список полей, которые нужно убрать для выбранного способа доставки */

	/** Доставка */ 
	if ( false !== strpos( $chosen_methods[0], 'free_shipping' ) ) {
		unset(
			// $fields['billing']['billing_first_name'],
			// $fields['billing']['billing_last_name'],
			$fields['billing']['billing_company'],
			// $fields['billing']['billing_address_1'],
			$fields['billing']['billing_address_2'],
			// $fields['billing']['billing_city'],
			$fields['billing']['billing_postcode'],
			$fields['billing']['billing_state'],
			// $fields['billing']['billing_phone'],
			// $fields['billing']['billing_email']
		);
	}

	/** Самовывоз */ 
	if ( false !== strpos( $chosen_methods[0], 'local_pickup' ) ) {
		unset(
			// $fields['billing']['billing_first_name'],
			// $fields['billing']['billing_last_name'],
			$fields['billing']['billing_company'],
			$fields['billing']['billing_address_1'],
			$fields['billing']['billing_address_2'],
			$fields['billing']['billing_city'],
			$fields['billing']['billing_postcode'],
			$fields['billing']['billing_state'],
			// $fields['billing']['billing_phone'],
			// $fields['billing']['billing_email']
		);
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'lvn_override_checkout_fields' );


function lvn_add_script_update_shipping_method() {
	if ( is_checkout() ) {
		?>
	
		<style>
			#billing_country_field {
				display: none !important;
			}
		</style>
		<script>
			  jQuery( document ).ready( function( $ ) {
				  $( document.body ).on( 'updated_checkout updated_shipping_method', function( event, xhr, data ) {
					  $( 'input[name^="shipping_method"]' ).on( 'change', function() {
						  $( '.woocommerce-billing-fields__field-wrapper' ).block( {
							  message: null,
							  overlayCSS: {
								  background: '#fff',
								  'z-index': 1000000,
								  opacity: 0.3
							  }
						  } );
					  } );
				  } );
			  } );
		</script>
		<?php
	}
}
add_action( 'wp_footer', 'lvn_add_script_update_shipping_method' );
