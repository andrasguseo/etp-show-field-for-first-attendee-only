/**
 * Show Attendee Registration Fields for the first attendee only.
 * (Remove field for subsequent attendees.)
 * Can handle multiple fields.
 *
 * IMPORTANT!!!
 * Only works if:
 * - there is only one type of ticket added to the cart
 * - or all tickets added to the cart have the same fields
 *
 * Usage: Add the snippet to your functions.php file or with a plugin like Code Snippets.
 *
 * @author: Andras Guseo
 * @author: Abz Abdul
 * @version: 1.1.0
 *
 * Plugins required: Event Tickets Plus
 * Created: January 30, 2024
 * Updated: February 1, 2024
 */
function et_hide_fields() {
	// Bail if we don't have Event Tickets Plus
	if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) ) {
		return;
	}

	// Generic functions. Load on single event pages and the attendee registration page.
	if (
		(
			is_single()
			&& get_post_type() == 'tribe_events'
		)
		|| tribe( 'Tribe__Tickets__Attendee_Registration__Template' )->is_on_ar_page()
	) {
		?>
		<script type="application/javascript" id="et_hide_attendee_fields">
			const hf_log = false;

            function fieldLabels() {
                /**
                 * ADD THE LABELS HERE
                 * Add the labels of the fields, which you only want to show for the first attendee.
                 * Only letters, numbers, and space.
                 *
                 * IMPORTANT! If you list fields that are not on the form, it will not work correctly.
                 */
                const labels = [
                    'Show for first only',
                    'Email Label',
                    'Telephone Label',
                    'URL Label',
                    'Birth date Label',
                    'Mydate Label',
	                'Radio label',
	                'Checkboxx label',
	                'Dropit'
                ];

                // Turn the labels into slugs.
                return labels.map(label => label.toLowerCase().replaceAll(" ", "-"));
            }

            function selectors() {
                // Use the slugs to create selectors.
                return fieldLabels().map(label => "input[name*='" + label + "'], select[name*='" + label + "']");
            }

            function waitForElement(selector, callback) {
                if (hf_log) { console.log("HE: starting... -" + selector); }
                let element;
                const intervalID = setInterval(function () {
                    if (hf_log) { console.log("HE: waiting..."); }
                    element = document.querySelectorAll(selector);
                    if (element.length > 0) {
                        // Stop waiting
                        clearInterval(intervalID);
                        callback();
                    }
                }, 500); // Check every 500 milliseconds
            }

            function delayHideOnModal() {
                // Need to wait for DOM to be generated
                setTimeout(hideOnModal, 200 );
            }

            function hideOnModal() {
                // Listen for button clicks and field change on the modal.
                document.querySelectorAll('.tribe-modal__wrapper--ar .tribe-tickets__tickets-item-quantity-number-input').forEach(
                    function(item) {
                        item.addEventListener("change", hideFields);
                    }
                );
                document.querySelectorAll('.tribe-modal__wrapper--ar .tribe-tickets__tickets-item-quantity-add').forEach(
                    function(item) {
                        item.addEventListener('click', hideFields);
                    }
                );
                document.querySelectorAll('.tribe-modal__wrapper--ar .tribe-tickets__tickets-item-quantity-remove').forEach(
                    function(item) {
                        item.addEventListener('click', hideFields);
                    }
                );
                hideFields();
            }

            function hideFields() {
                // Radio buttons and checkboxes can have more <input> elements, but we only need to remove
                // one parent element. We need to discount them accordingly.
                // Count ALL fields to be hidden on the form, and divide by the number of tickets.
                // This is how many fields we need to skip for the first attendee.
                let numAttendees = document.querySelectorAll('.tribe-tickets__attendee-tickets-item').length;

                // Get all the fields.
                const fields = document.querySelectorAll(selectors().join(', '));
                if (hf_log) { console.log("HF: fields found - " + fields.length); }
                // Hide the fields for attendees, except the first one.
                if (fields.length > 0) {
                    fields.forEach(
                        function (item, index) {
                            // Skip the first attendee
                            if (index < fields.length/numAttendees ) {
                                if (hf_log) { console.log("HE: skipping " + index); }
                                return;
                            }

                            if (hf_log) { console.log('HF: processing item - ' + index); }

                            // Unset required attribute.
                            item.removeAttribute("required");
                            item.removeAttribute("aria-required");

                            // Remove the field.
                            if (hf_log) { console.log('HF: closest - ' + item.closest('.tribe-tickets__form-field')); }
                            item.closest('.tribe-tickets__form-field').remove();
                        }
                    );
                }
            }
		</script><?php
	}

	// Listener. Load on Single Event pages.
	if (
		is_single()
		&& get_post_type() == 'tribe_events'
	) {
		?>
		<script type="application/javascript" id="et_hide_attendee_fields_modal_trigger">
			document.querySelector('#tribe-tickets__tickets-form #tribe-tickets__tickets-submit').addEventListener('click', delayHideOnModal);
		</script>
		<?php
	}

	// Listener. Load on the Attendee Registration page.
	if ( tribe( 'Tribe__Tickets__Attendee_Registration__Template' )->is_on_ar_page() ) {
		?>
		<script type="application/javascript" id="et_hide_attendee_fields_trigger">
            waitForElement('.tribe-tickets__form.tribe-tickets__attendee-tickets-item', hideFields);
		</script>
		<?php
	}
}

add_action( 'wp_footer', 'et_hide_fields' );
