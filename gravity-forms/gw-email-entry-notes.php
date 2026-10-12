<?php
/**
 * Gravity Wiz // Gravity Forms // Email Entry Notes
 *
 * Adds an "Email Notes" option to the bulk action dropdown in the Notes box on the
 * entry detail screen. Select one or more notes, choose "Email Notes", click Apply,
 * then pick the recipient in the dialog that appears. The dropdown lists the email
 * addresses found in the entry, and (optionally) lets you type a different address.
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *   2. Optionally set form and field IDs in the configuration at the bottom
 *
 *     new GW_Email_Entry_Notes( array(
 *         'form_id'                => 123,   // Optional. Limit to one form. Leave false for all forms.
 *         'email_field_id'         => 4,     // Optional. Field listed first / used as the default recipient.
 *         'allow_custom_recipient' => true,  // Set to false to only allow addresses found in the entry.
 *     ) );
 */
class GW_Email_Entry_Notes {

	const ACTION = 'gw_email_notes';
	const FIELD  = 'gw_email_notes_recipient';

	private $_args    = array();
	private $_notices = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'                => false,
			'email_field_id'         => false,
			'allow_custom_recipient' => true,
			// Merge tags: {entry_id}, {form_title}
			'subject'                => 'Notes for {form_title} (Entry #{entry_id})',
			'action_label'           => 'Email Notes',
		) );

		add_action( 'admin_init', array( $this, 'maybe_email_notes' ) );
		add_action( 'admin_footer', array( $this, 'output_script' ) );

	}

	private function is_entry_detail() {
		return is_admin() && rgget( 'page' ) === 'gf_entries' && rgget( 'view' ) === 'entry';
	}

	private function is_enabled_for_form( $form_id ) {
		return ! $this->_args['form_id'] || (int) $this->_args['form_id'] === (int) $form_id;
	}

	/**
	 * Email addresses found in the entry, as array( email => field label ).
	 * The configured email_field_id (if any) comes first, then the form's Email fields.
	 */
	private function get_candidates( $form, $entry ) {

		$field_ids = array();

		if ( $this->_args['email_field_id'] ) {
			$field_ids[] = (string) $this->_args['email_field_id'];
		}

		foreach ( GFAPI::get_fields_by_type( $form, 'email' ) as $field ) {
			$field_ids[] = (string) $field->id;
		}

		$candidates = array();

		foreach ( array_unique( $field_ids ) as $field_id ) {
			$value = trim( (string) rgar( $entry, $field_id ) );
			if ( ! is_email( $value ) || isset( $candidates[ $value ] ) ) {
				continue;
			}
			$field                = GFAPI::get_field( $form, $field_id );
			$candidates[ $value ] = $field ? GFCommon::get_label( $field ) : 'Field ' . $field_id;
		}

		return $candidates;
	}

	/**
	 * Gravity Forms doesn't offer a filter for the note bulk actions, so inject the option
	 * (and the recipient dialog) via JS.
	 */
	public function output_script() {

		if ( ! $this->is_entry_detail() ) {
			return;
		}

		$entry = GFAPI::get_entry( absint( rgget( 'lid' ) ) );
		if ( is_wp_error( $entry ) ) {
			return;
		}

		$form = GFAPI::get_form( $entry['form_id'] );
		if ( ! $form || ! $this->is_enabled_for_form( $form['id'] ) ) {
			return;
		}

		$candidates = array();
		foreach ( $this->get_candidates( $form, $entry ) as $email => $label ) {
			$candidates[] = array( 'email' => $email, 'label' => $label );
		}

		$config = array(
			'action'      => self::ACTION,
			'field'       => self::FIELD,
			'label'       => $this->_args['action_label'],
			'candidates'  => $candidates,
			'allowCustom' => (bool) $this->_args['allow_custom_recipient'],
			'i18n'        => array(
				'title'       => 'Email selected notes to:',
				'other'       => 'Other address…',
				'placeholder' => 'name@example.com',
				'send'        => 'Send',
				'cancel'      => 'Cancel',
				'noNotes'     => 'Please select at least one note to email.',
				'noOptions'   => 'No email address was found in this entry.',
			),
		);

		?>
		<script type="text/javascript">
			jQuery( function( $ ) {

				var cfg      = <?php echo wp_json_encode( $config ); ?>;
				var $selects = $( 'select[name="bulk_action"], select[name="bulk_action2"]' );

				if ( ! $selects.length ) {
					return;
				}

				$selects.append( $( '<option>', { value: cfg.action, text: cfg.label } ) );

				var $form   = $selects.first().closest( 'form' );
				var $hidden = $( '<input type="hidden">' ).attr( 'name', cfg.field ).appendTo( $form );

				// Recipient dialog.
				var $dialog = $( '<dialog style="padding:20px 24px;border:1px solid #c3c4c7;border-radius:4px;min-width:320px;"></dialog>' );
				var $title  = $( '<p style="margin:0 0 12px;font-weight:600;"></p>' ).text( cfg.i18n.title );
				var $choice = $( '<select style="width:100%;margin-bottom:8px;"></select>' );
				var $custom = $( '<input type="email" style="width:100%;margin-bottom:8px;display:none;">' ).attr( 'placeholder', cfg.i18n.placeholder );
				var $error  = $( '<p style="color:#b32d2e;margin:0 0 8px;display:none;"></p>' );
				var $send   = $( '<button type="button" class="button button-primary" style="margin-right:6px;"></button>' ).text( cfg.i18n.send );
				var $cancel = $( '<button type="button" class="button"></button>' ).text( cfg.i18n.cancel );

				$.each( cfg.candidates, function( i, c ) {
					$choice.append( $( '<option>', { value: c.email, text: c.label + ' — ' + c.email } ) );
				} );

				if ( cfg.allowCustom ) {
					$choice.append( $( '<option>', { value: '__other__', text: cfg.i18n.other } ) );
				}

				if ( ! $choice.children().length ) {
					$error.text( cfg.i18n.noOptions ).show();
					$choice.hide();
					$send.prop( 'disabled', true );
				}

				function toggleCustom() {
					$custom.toggle( $choice.val() === '__other__' );
				}

				$choice.on( 'change', toggleCustom );
				toggleCustom();

				$dialog.append( $title, $choice, $custom, $error, $send, $cancel ).appendTo( 'body' );

				$cancel.on( 'click', function() {
					$hidden.val( '' );
					$dialog[0].close();
				} );

				$send.on( 'click', function() {
					var value = $choice.val() === '__other__' ? $.trim( $custom.val() ) : $choice.val();

					if ( $choice.val() === '__other__' && ( ! value || ! $custom[0].checkValidity() ) ) {
						$custom[0].reportValidity();
						return;
					}

					$hidden.val( value );
					$dialog[0].close();
					// Bypass any "submit" input shadowing the native method.
					HTMLFormElement.prototype.submit.call( $form[0] );
				} );

				// Intercept Apply when "Email Notes" is selected and no recipient has been chosen yet.
				$form.on( 'submit', function( e ) {

					var isEmailAction = $selects.filter( function() {
						return $( this ).val() === cfg.action;
					} ).length > 0;

					if ( ! isEmailAction || $hidden.val() ) {
						return;
					}

					e.preventDefault();

					if ( ! $form.find( 'input[name="note[]"]:checked' ).length ) {
						window.alert( cfg.i18n.noNotes );
						return;
					}

					$dialog[0].showModal();
				} );

			} );
		</script>
		<?php

	}

	/**
	 * Handle the submitted bulk action.
	 */
	public function maybe_email_notes() {

		if ( ! $this->is_entry_detail() || rgpost( 'action' ) !== 'bulk' ) {
			return;
		}

		$bulk_action = rgpost( 'bulk_action' ) ? rgpost( 'bulk_action' ) : rgpost( 'bulk_action2' );
		if ( $bulk_action !== self::ACTION ) {
			return;
		}

		// Same nonce Gravity Forms uses for its own note actions.
		check_admin_referer( 'gforms_update_note', 'gforms_update_note' );

		if ( ! GFCommon::current_user_can_any( 'gravityforms_edit_entry_notes' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'gravityforms' ) );
		}

		$entry = GFAPI::get_entry( absint( rgget( 'lid' ) ) );
		if ( is_wp_error( $entry ) ) {
			return $this->add_notice( 'The entry could not be found.', 'error' );
		}

		$form = GFAPI::get_form( $entry['form_id'] );
		if ( ! $form || ! $this->is_enabled_for_form( $form['id'] ) ) {
			return;
		}

		$note_ids = array_filter( array_map( 'absint', (array) rgpost( 'note' ) ) );
		if ( empty( $note_ids ) ) {
			return $this->add_notice( 'Please select at least one note to email.', 'error' );
		}

		// Recipient chosen in the dialog; fall back to the first address in the entry.
		$candidates = $this->get_candidates( $form, $entry );
		$to         = trim( (string) rgpost( self::FIELD ) );

		if ( $to === '' ) {
			$to = (string) key( $candidates );
		} elseif ( ! $this->_args['allow_custom_recipient'] && ! isset( $candidates[ $to ] ) ) {
			return $this->add_notice( 'That email address is not allowed.', 'error' );
		}

		if ( ! is_email( $to ) ) {
			return $this->add_notice( 'No valid email address was provided.', 'error' );
		}

		$notes = array();
		foreach ( GFFormsModel::get_lead_notes( $entry['id'] ) as $note ) {
			if ( in_array( (int) $note->id, $note_ids, true ) ) {
				$notes[] = $note;
			}
		}

		if ( empty( $notes ) ) {
			return $this->add_notice( 'The selected notes could not be found.', 'error' );
		}

		$subject = str_replace(
			array( '{entry_id}', '{form_title}' ),
			array( $entry['id'], $form['title'] ),
			$this->_args['subject']
		);

		$sent = wp_mail(
			$to,
			$subject,
			$this->build_message( $notes ),
			array( 'Content-Type: text/html; charset=UTF-8' )
		);

		if ( $sent ) {
			$this->add_notice( sprintf( '%d note(s) emailed to %s.', count( $notes ), $to ), 'success' );
		} else {
			$this->add_notice( 'The email could not be sent. Please check your site\'s email configuration.', 'error' );
		}

	}

	private function build_message( $notes ) {

		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		$html        = '';

		foreach ( $notes as $note ) {
			$author = $note->user_name ? $note->user_name : 'System';
			$date   = get_date_from_gmt( $note->date_created, $date_format );

			$html .= sprintf(
				'<div style="margin:0 0 20px;padding:0 0 15px;border-bottom:1px solid #ddd;"><p style="margin:0 0 8px;color:#666;font-size:13px;">%s &middot; %s</p>%s</div>',
				esc_html( $author ),
				esc_html( $date ),
				wp_kses_post( wpautop( $note->value ) )
			);
		}

		return $html;
	}

	private function add_notice( $message, $type = 'success' ) {

		$this->_notices[] = array( 'message' => $message, 'type' => $type );

		if ( ! has_action( 'admin_notices', array( $this, 'display_notices' ) ) ) {
			add_action( 'admin_notices', array( $this, 'display_notices' ) );
		}

	}

	public function display_notices() {
		foreach ( $this->_notices as $notice ) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( $notice['type'] ),
				esc_html( $notice['message'] )
			);
		}
	}

}

# Configuration
new GW_Email_Entry_Notes( array(
  // 'form_id'                => 123, // Optional. Limit to one form. Leave false for all forms.
	// 'email_field_id'         => 4, // Optional. Sets the default recipient.
	// 'allow_custom_recipient' => false, // Optional. Set to false to only allow addresses found in the entry.
) );
