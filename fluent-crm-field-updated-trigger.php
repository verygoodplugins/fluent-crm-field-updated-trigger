<?php

/**
 * Plugin Name: FluentCRM - Field Updated Trigger
 * Description: Adds a FluentCRM trigger for when a custom field is updated.
 * Plugin URI: https://github.com/verygoodplugins/fluent-crm-field-updated-trigger/
 * Version: 1.0.1
 * Author: Very Good Plugins
 * Author URI: https://verygoodplugins.com/
*/

/**
 * @copyright Copyright (c) 2023. All rights reserved.
 *
 * @license   Released under the GPL license http://www.opensource.org/licenses/gpl-license.php
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

// deny direct access.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

function fluentcrm_field_updated_trigger_boot() {
	include_once __DIR__ . '/class-field-updated-trigger.php';
	new Field_Updated_Trigger();
}

add_action( 'fluentcrm_loaded', 'fluentcrm_field_updated_trigger_boot' );
