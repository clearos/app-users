<?php

/**
 * User summary for dashboard manager view.
 *
 * @category   apps
 * @package    users
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/users/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//  
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\clearcenter\Subscription_Engine as Subscription_Engine;

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('accounts');
$this->lang->load('users');

///////////////////////////////////////////////////////////////////////////////
// Subscription warnings (if applicable)
///////////////////////////////////////////////////////////////////////////////

if (!empty($subscriptions)) {
    $warnings = '';
    foreach ($subscriptions as $app => $subscription) {
        if ($subscription['warning_message'] && $subscription['type'] === Subscription_Engine::TYPE_USER)
            $warnings .= "<li>" . $subscription['app_name'] . ' - ' . $subscription['warning_message'] . "</li>"; 
    }
}

if ($read_write) {
    $options = array (
        'buttons' => array(
            anchor_custom('/app/users/add', lang('users_add_user')),
            anchor_custom('/app/groups/add', lang('users_create_group'), 'low')
        )
    );
} else {
    $options = array (
        'buttons' => array(
            anchor_custom('/app/users', lang('users_users')),
            anchor_custom('/app/groups', lang('users_groups'), 'low')
        )
    );
}

echo form_open();
echo form_header(lang('users_users_and_groups'));
echo field_view(lang('users_users'), $num_users);
echo field_view(lang('users_groups'), $num_groups);
if (!empty($warnings)) {
    echo modal_info(
        'dashboard_user_error',
        lang('base_warning'),
        "<ol>" . $warnings . "</ol>",
        array('type' => 'warning')
    );
    echo field_view(lang('base_subscription_warnings'), anchor_custom('#', lang('base_view'), 'high', array('id' => 'dashboard_user_error_trigger')));
}
echo form_footer($options);
echo form_close();
echo "<script type='text/javascript'>\n";
echo "    $('#dashboard_user_error_trigger').on('click', function (e) {\n";
echo "      clearos_modal_infobox_open('dashboard_user_error');\n";
echo "    });";
echo "</script>\n";
