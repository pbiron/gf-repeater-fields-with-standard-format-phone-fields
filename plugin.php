<?php

/**
 * Plugin Name: GF Repeater Fields With Standard Format Phone Fields
 * Description: Support phone fields with "standard" format in GF repeater fields
 * Author: Paul V. Biron/Sparrow Hawk Computing
 * Author URI: https://sparrowhawkcomputing.com/
 * Plugin URI: https://github.com/pbiron/gf-repeater-fields-with-standard-format-phone-fields/
 * GitHub Plugin URI: https://github.com/pbiron/gf-repeater-fields-with-standard-format-phone-fields/
 * Version: 0.1.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * {@link https://gravityforms.com GravityForms} fields of type {@link https://docs.gravityforms.com/repeater-fields/ repeater} can
 * include fields of type {@link https://docs.gravityforms.com/field-object/#phone phone}.
 *
 * Unfortunately, as of GF 2.4.20, such `phone` fields with `phoneFormat = 'standard'` don't
 * get the necessary JS enqueued so that the input masking works.  See
 * {@link https://docs.gravityforms.com/repeater-fields/#limitations Limitations}.
 *
 * This plugin attempts to remedy that situation, so that `repeater` fields can have
 * "standard" format `phone` fields.  It is not guaranteed to work in all cases, but it
 * seems to work for the `repeater` fields I have created.
 *
 * It supports any number of `repeater` fields in a form, each of which has any number of
 * `phone` fields with "standard" format.
 */

namespace SHC\GFRFWSFPF;

defined( 'ABSPATH' ) || die;

add_filter( 'gform_get_form_filter', __NAMESPACE__ . '\\add_phone_mask_js', 10, 2 );

/**
 * Add the necessary JS
 *
 * @since 0.1.0
 *
 * @param string $form_string The HTML for the form.
 * @param array  $form        The form object.
 * @return string
 */
function add_phone_mask_js( $form_string, $form ) {
	$repeaters = wp_list_filter( $form['fields'], array( 'type' => 'repeater' ) );
	if ( ! $repeaters ) {
		// No repeater fields.
		// Nothing to do, so bail.
		return $form_string;
	}

	// Collect all the phone fields with "standard" format in all of the repeaters.
	$standard_phone_fields = array();
	foreach ( $repeaters as $repeater ) {
		$standard_phone_fields = array_merge(
			$standard_phone_fields,
			wp_list_filter( $repeater->fields, array( 'type' => 'phone', 'phoneFormat' => 'standard' ) )
		);
	}

	if ( ! $standard_phone_fields ) {
		// No "stanard" format phone fields.
		// Nothing to do, so bail.
		return $form_string;
	}

	// Enqueue the masked input JS.
	wp_enqueue_script( 'gform_masked_input' );

	// Grab the first phone field with "standard" format and get it's inline JS.
	// @todo this assumes that all subsequent phone field will have the SAME JS.
	$standard_phone_field = $standard_phone_fields[0];
	$masked_input_script  = $standard_phone_field->get_form_inline_script_on_page_render( $form );
	if ( ! $masked_input_script ) {
		// No script (shouldn't happen).
		// Nothing to do, so bail.
		return $form_string;
	}

	// Strip off the jQuery selector, so that we can use the script "body" later with different selectors.
	// @todo this is a kludge because it assumes the returned script will ALWAYS starts with this selector
	//       (and will break if even the whitespace in the selector changes)!!
	$masked_input_script = str_replace( "jQuery('#input_{$form['id']}_{$standard_phone_field->id}').", '', $masked_input_script );

	// Now, generate the JS to mask the first repeat of each phone field with "standard" format.
	$first_repeats_masked_input_script = array();
	foreach ( $standard_phone_fields as $field ) {
		$first_repeats_masked_input_script[] = "jQuery( '#input_{$form['id']}_{$field->id}-0' ).{$masked_input_script}";
	}
	$first_repeats_masked_input_script = implode( "\n\t\t\t", $first_repeats_masked_input_script );

	// Finally, generate the script to add to the form.
	$script = <<<EOF
<script type='text/javascript'>
	jQuery( document ).bind( 'gform_post_render', function( event, formId, currentPage ) {
		if( formId == {$form['id']} ) {
			{$first_repeats_masked_input_script}
			
			// Bind the input masking for each phone field with "standard" format when a repeat is added.
			gform.addAction( 'gform_repeater_post_item_add', function ( clone, container ) {
				jQuery( '.ginput_container_phone input', clone ).each( function() {
					var id = jQuery( this ).attr( 'id' );
					
					jQuery( '#' + id ).{$masked_input_script}
				} );
			} );
		}
	} );
</script>
EOF;

	return $form_string . $script;
}
