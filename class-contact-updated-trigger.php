<?php
/**
 * Contact Updated Trigger for FluentCRM
 *
 * @package FluentCRM
 * @since   1.1.0
 */

use FluentCrm\App\Services\Funnel\BaseTrigger;
use FluentCrm\App\Services\Funnel\FunnelHelper;
use FluentCrm\App\Services\Funnel\FunnelProcessor;
use FluentCrm\Framework\Support\Arr;

/**
 * Class Contact_Updated_Trigger
 *
 * Triggers automations when a standard field is updated on a contact.
 *
 * @since 1.1.0
 */
class Contact_Updated_Trigger extends BaseTrigger {

	/**
	 * Get things started.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->{'triggerName'}  = 'fluentcrm_contact_updated';
		$this->{'priority'}     = 15;
		$this->{'actionArgNum'} = 3;
		parent::__construct();
	}



	/**
	 * Defines the trigger.
	 *
	 * @since 1.1.0
	 *
	 * @return array The trigger definition.
	 */
	public function getTrigger() {
		return array(
			'category'    => __( 'CRM', 'fluentcampaign-pro' ),
			'label'       => __( 'A standard field is updated', 'fluentcampaign-pro' ),
			'description' => __( 'This funnel will start when a standard field is updated on a contact.', 'fluentcampaign-pro' ),
		);
	}


	/**
	 * Get the funnel setting defaults.
	 *
	 * @since 1.1.0
	 *
	 * @return array The funnel setting defaults.
	 */
	public function getFunnelSettingsDefaults() {
		return array(
			'field_name'  => 'any',
			'update_type' => 'any',
			'field_value' => '',
			'run_multiple' => 'no',
		);
	}

	/**
	 * Adds the settings fields.
	 *
	 * @since 1.1.0
	 *
	 * @param FluentCrm\App\Models\Funnel $funnel The funnel.
	 * @return array The settings fields.
	 */
	public function getSettingsFields( $funnel ) {
		$standard_fields = array(
			'hash',
			'prefix',
			'first_name',
			'last_name',
			'user_id',
			'company_id',
			'email',
			'status', // pending / subscribed / bounced / unsubscribed; Default: subscriber.
			'contact_type', // lead / customer.
			'address_line_1',
			'address_line_2',
			'postal_code',
			'city',
			'state',
			'country',
			'phone',
			'timezone',
			'date_of_birth',
			'source',
			'life_time_value',
			'last_activity',
			'total_points',
			'latitude',
			'longitude',
			'ip',
			'created_at',
			'updated_at',
			'avatar',
		);

		$options = array();
		foreach ( $standard_fields as $field ) {
			$options[] = array(
				'id'    => $field,
				'title' => ucfirst( str_replace( '_', ' ', $field ) ),
			);
		}

		return array(
			'title'     => __( 'Field Updated', 'fluentcampaign-pro' ),
			'sub_title' => __( 'This Funnel will start when the selected standard field is updated for a contact.', 'fluentcampaign-pro' ),
			'fields'    => array(
				'field_name' => array(
					'type'    => 'select',
					'options' => $options,
					'label'   => __( 'Field', 'fluentcampaign-pro' ),
				),
			),
		);
	}


	/**
	 * Get the defaults for the funnel.
	 *
	 * @since 1.1.0
	 *
	 * @param FluentCrm\App\Models\Funnel $funnel The funnel.
	 * @return array The funnel condition defaults.
	 */
	public function getFunnelConditionDefaults( $funnel ) {
		return array(
			'update_type'  => 'any',
			'run_multiple' => 'no',
		);
	}

	/**
	 * Get the conditional fields for the funnel.
	 *
	 * @since 1.1.0
	 *
	 * @param FluentCrm\App\Models\Funnel $funnel The funnel.
	 * @return array The condition fields.
	 */
	public function getConditionFields( $funnel ) {

		return array(
			'update_type'  => array(
				'type'    => 'radio',
				'label'   => __( 'If the field changes to?', 'fluentcampaign-pro' ),
				'options' => array(
					array(
						'id'    => 'any',
						'title' => __( 'Any Value', 'fluentcampaign-pro' ),
					),
					array(
						'id'    => 'specific',
						'title' => __( 'A Specific Value', 'fluentcampaign-pro' ),
					),
				),
			),
			'field_value'  => array(
				'type'       => 'input-text',
				'label'      => __( 'Field Value', 'fluentcampaign-pro' ),
				'help'       => __( 'Enter the field value that must match to trigger the automation.', 'fluentcampaign-pro' ),
				'dependency' => array(
					'depends_on' => 'update_type',
					'operator'   => '=',
					'value'      => 'specific',
				),
			),
			'field_empty'  => array(
				'type'       => 'radio',
				'label'      => __( 'Can the field be empty ?', 'fluentcampaign-pro' ),
				'options' => array(
					array(
						'id'    => 'yes',
						'title' => __( 'Yes', 'fluentcampaign-pro' ),
					),
					array(
						'id'    => 'no',
						'title' => __( 'No', 'fluentcampaign-pro' ),
					),
				),
				'help'       => __( 'If the field can be empty, the automation will run. Otherwise it will be skipped.', 'fluentcampaign-pro' ),
			),
			'run_multiple' => array(
				'type'        => 'yes_no_check',
				'label'       => '',
				'check_label' => __( 'Restart the Automation Multiple times for a contact for this event. (Only enable if you want to restart automation for the same contact)', 'fluentcampaign-pro' ),
				'inline_help' => __( 'If you enable, then it will restart the automation for a contact even if the contact is already in the automation. Otherwise, It will just skip if already exist.', 'fluentcampaign-pro' ),
			),
		);
	}

	/**
	 * Handle the action.
	 *
	 * @since 1.1.0
	 *
	 * @param FluentCrm\App\Models\Funnel $funnel        The funnel.
	 * @param array                       $original_args The original arguments.
	 */
	public function handle( $funnel, $original_args ) {
		$subscriber   = $original_args[0];
		$updated_data = $original_args[1];

		$will_process = $this->isProcessable( $funnel, $subscriber, $updated_data );
		$will_process = apply_filters( 'fluentcrm_funnel_will_process_' . $this->{'triggerName'}, $will_process, $funnel, $subscriber, $original_args );

		if ( ! $will_process ) {
			return;
		}

		( new FunnelProcessor() )->startFunnelSequence(
			$funnel,
			array(),
			array( 'source_trigger_name' => $this->{'triggerName'} ),
			$subscriber
		);
	}

	/**
	 * Determines if the funnel should be processed.
	 *
	 * @since 1.1.0
	 *
	 * @param FluentCrm\App\Models\Funnel $funnel       The funnel.
	 * @param object                      $subscriber   The subscriber.
	 * @param array                       $updated_data The updated data.
	 * @return bool Whether the funnel should be processed.
	 */
	private function isProcessable( $funnel, $subscriber, $updated_data ) {
		$field = Arr::get( $funnel->settings, 'field_name' );

		if ( 'any' === $field ) {
			return true; // Always process if 'any' is the field name.
		}

		if ( ! array_key_exists( $field, $updated_data ) ) {
			return false;
		}

		$update_type = Arr::get( $funnel->conditions, 'update_type' );

		if ( 'specific' === $update_type && Arr::get( $funnel->conditions, 'field_value' ) !== $updated_data[ $field ] ) {
			return false;
		}

		$field_empty = Arr::get( $funnel->conditions, 'field_empty');
		
		// Check if the updated data field is empty
		if ( 'no' === $field_empty && empty( $updated_data[ $field ] )) {
			return false;
		} 

		if ( $subscriber && FunnelHelper::ifAlreadyInFunnel( $funnel->id, $subscriber->id ) ) {
			if ( 'yes' === Arr::get( $funnel->conditions, 'run_multiple' ) ) {
				FunnelHelper::removeSubscribersFromFunnel( $funnel->id, array( $subscriber->id ) );
			} else {
				return false;
			}
		}

		return true;
	}
}

new Contact_Updated_Trigger();
