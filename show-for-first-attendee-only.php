/**
 * Show Attendee Registration Field for first attendee only.
 * (Hide field for subsequent attendees.)
 * Can handle multiple fields. Works with <select> fields only.
 *
 * Usage: Add the snippet to your functions.php file or with a plugin like Code Snippets.
 *
 * @author: Andras Guseo
 * @version: 1.0.0
  *
 * Plugins required: Event Tickets Plus
 * Created: January 30, 2024
 */
function et_hide_fields() {
	// Bail if we don't have Event Tickets Plus
	if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) ) {
		return;
	}

	// Generic functions.
	if (
		(
			is_single()
			&& get_post_type() == 'tribe_events'
		)
		|| tribe( 'Tribe__Tickets__Attendee_Registration__Template' )->is_on_ar_page()
	) {
		?>
		<script type="application/javascript">
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
                    'Another Label',
                    'Yet Another Label'
                ];
                return labels.map(label => label.toLowerCase().replaceAll(" ", "-"));
            }

            function selectors() {
                return fieldLabels().map(label => "input[name*='" + label + "'], select[name*='" + label + "']");
            }

            function waitForElement(selector, callback) {
                console.log("HE: starting... -" + selector);
                let element;
                const intervalID = setInterval(function () {
                    console.log("HE: waiting...");
                    element = document.querySelectorAll(selector);
                    console.log("HE: length " + element.length);
                    if (element.length > 0) {
                        clearInterval(intervalID);
                        callback();
                    }
                }, 500); // Check every 500 milliseconds
            }

            function hideEm() {
                // Count how many of the given fields do we have in the form.
                // We use this count to make sure we show/hide the correct number of fields.
                let count = 0;
                selectors().forEach(
                    function (item, index){
                        if (document.querySelectorAll(item).length > 0) {
                            count++;
                        }
                    }
                );
                console.log("HF: count " + count);

                // Get all the fields.
                const fields = document.querySelectorAll(selectors().join(', '));

                // Hide the fields for attendees, except the first one.
                if (fields.length > 0) {
                    console.log("HF: " + fields[0]);
                    fields.forEach(
                        function (item, index) {
                            //console.log("HF: " + item + "-" + fieldLabels().length);
                            // Skip the first attendee
                            if (index < count ) { //fieldLabels().length) {
                                //console.log("HE: skipping " + index);
                                return;
                            }

                            // Unset required attribute.
	                        //console.log("HE: processing " + index);
	                        item.removeAttribute("required");
	                        item.removeAttribute("aria-required");

                            // Hide the field.
                            item.parentElement.parentElement.style.display = 'none';
                        }
                    );
                }
            }
		</script><?php
	}

	// Run on Single Event pages.
	if (
		is_single()
		&& get_post_type() == 'tribe_events'
	) {
		?>
		<script type="application/javascript">
            // Wait for the ticket modal to appear.
            waitForElement('div.tribe-modal__wrapper--ar', hideOnModal);

            function hideOnModal() {
                // Listen for button clicks and field change.
                document.querySelector('.tribe-modal__wrapper--ar .tribe-tickets__tickets-item-quantity-number-input').addEventListener("change", hideEm);
                document.querySelector('.tribe-modal__wrapper--ar .tribe-tickets__tickets-item-quantity-add').addEventListener("click", hideEm);
                document.querySelector('.tribe-modal__wrapper--ar .tribe-tickets__tickets-item-quantity-remove').addEventListener("click", hideEm);

                    hideEm();
            }
		</script>
		<?php
	}

	// Run on Attendee Registration page
	if ( tribe( 'Tribe__Tickets__Attendee_Registration__Template' )->is_on_ar_page() ) {
		?>
		<script type="application/javascript">
            waitForElement('.tribe-tickets__form.tribe-tickets__attendee-tickets-item', hideEm);
		</script>
		<?php
	}
}

add_action( 'wp_footer', 'et_hide_fields' );
