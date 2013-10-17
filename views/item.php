<?php

/**
 * User account view.
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

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('users');
$this->lang->load('groups');

///////////////////////////////////////////////////////////////////////////////
// Form modes
///////////////////////////////////////////////////////////////////////////////

$username = isset($user_info['core']['username'])? $user_info['core']['username'] : '';

if ($form_type === 'edit') {
    $read_only = FALSE;
    $username_read_only = TRUE;

    $form_path = '/users/edit/' . $username;
    $buttons = array(
        form_submit_update('submit'),
        anchor_cancel('/app/users/'),
        anchor_delete('/app/users/delete/' . $username)
    );
} else if ($form_type === 'view') {
    $read_only = TRUE;
    $username_read_only = TRUE;

    $form_path = '/users/view/' . $username;
    $buttons = array(
        anchor_cancel('/app/users/')
    );
} else {
    $read_only = FALSE;
    $username_read_only = FALSE;

    $form_path = '/users/add';
    $buttons = array(
        form_submit_add('submit'),
        anchor_cancel('/app/users/')
    );
}

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open($form_path, array('autocomplete' => 'off'));
echo form_header(lang('users_user'));

///////////////////////////////////////////////////////////////////////////////
// Core fields
///////////////////////////////////////////////////////////////////////////////
//
// Some directory drivers separate first and last names into separate fields,
// while others only support the full name (common name).  If the separate 
// fields don't exist, fall back to the full name.
//
///////////////////////////////////////////////////////////////////////////////

echo fieldset_header(lang('users_name'));

foreach ($info_map['core'] as $key_name => $details) {
    $name = "user_info[core][$key_name]";
    $value = $user_info['core'][$key_name];
    $description =  $details['description'];

    if ($details['field_priority'] !== 'normal')
        continue;

    if (($key_name === 'username') && ($form_type === 'edit'))
        $core_read_only = TRUE;
    else
        $core_read_only = $read_only;

    // Trim long values in read-only mode (notably, long names coming out of AD)
    if ($core_read_only == TRUE)
        $value = (strlen($value) >= 40) ? substr($value, 0, 40) . '...' : $value;

    if ($details['field_type'] === 'list') {
        echo field_dropdown($name, $details['field_options'], $value, $description, $core_read_only);
    } else if ($details['field_type'] === 'simple_list') {
        echo field_simple_dropdown($name, $details['field_options'], $value, $description, $core_read_only);
    } else if ($details['field_type'] === 'text') {
        echo field_input($name, $value, $description, $core_read_only);
    } else if ($details['field_type'] === 'integer') {
        echo field_input($name, $value, $description, $core_read_only);
    }
}

echo fieldset_footer();

///////////////////////////////////////////////////////////////////////////////
// Password fields
///////////////////////////////////////////////////////////////////////////////
//
// Don't bother showing passwords when read_only.
//
///////////////////////////////////////////////////////////////////////////////

if (! $read_only) {
    echo fieldset_header(lang('users_password'));
    echo field_password('password', '', lang('users_password'), $read_only);
    echo field_password('verify', '', lang('users_verify'), $read_only);
    echo fieldset_footer();
}

///////////////////////////////////////////////////////////////////////////////
// Plugin groups
///////////////////////////////////////////////////////////////////////////////

if (! empty($plugins)) {
    echo fieldset_header(lang('users_app_policies'));

    foreach ($plugins as $plugin => $details) {
        $name = "user_info[plugins][$plugin][state]";
        $value = (!isset($user_info['plugins'][$plugin]) || $user_info['plugins'][$plugin]) ? TRUE : $user_info['plugins'][$plugin];
        echo field_toggle_enable_disable($name, $value, $details['name'], $read_only);
    }

    echo fieldset_footer();
}

///////////////////////////////////////////////////////////////////////////////
// Extensions
///////////////////////////////////////////////////////////////////////////////

foreach ($info_map['extensions'] as $extension => $parameters) {

    // Echo out the specific info field
    //---------------------------------

    $fields = '';

    if (! empty($parameters)) {
        foreach ($parameters as $key_name => $details) {
            $name = "user_info[extensions][$extension][$key_name]";
            $value = $user_info['extensions'][$extension][$key_name];
            $description =  $details['description'];
            $field_read_only = $read_only;

            // If an extension has reached its user limit, disable the field listed in user_key
            // to prevent administrator from borking their server by going over count.

            if (array_key_exists($extension, $limits) && ($key_name === $limits[$extension]['user_key'])) {
                // if in "add" mode, set to read-only and set value to disabled
                if ($form_type === 'add') {
                    $field_read_only = TRUE;
                    $value = 0;
                // If in edit mode, set to read-only if account is disabled (i.e. allow active accounts to be disabled)
                } else if (($form_type === 'edit') && ($value == 0)) {
                    $field_read_only = TRUE;
                    $value = 0;
                }
            }

            if (isset($details['field_priority']) && ($details['field_priority'] === 'hidden')) {
                continue;
            } else if (isset($details['field_priority']) && ($details['field_priority'] === 'read_only')) {
                if ($form_type === 'add')
                    continue;

                $field_read_only = TRUE;
            }

            if ($details['field_type'] === 'list') {
                $fields .= field_dropdown($name, $details['field_options'], $value, $description, $field_read_only);
            } else if ($details['field_type'] === 'simple_list') {
                $fields .= field_simple_dropdown($name, $details['field_options'], $value, $description, $field_read_only);
            } else if ($details['field_type'] === 'text') {
                $fields .= field_input($name, $value, $description, $field_read_only);
            } else if ($details['field_type'] === 'integer') {
                $fields .= field_input($name, $value, $description, $field_read_only);
            } else if ($details['field_type'] === 'text_array') {
                $fields .= field_input($name . "[0]", $value[0], $description, $field_read_only);

                for ($inx = 1; $inx < count($value); $inx++) {
                    $description = ($inx === 0) ? $description : '';
                    $fields .= field_input($name . "[$inx]", $value[$inx], $description, $field_read_only);
                }

                // Show an extra blank field
                if ($form_type !== 'view')
                    $fields .= field_input($name . "[$inx]", '', '', $field_read_only);
            }
        }
    }

    if (! empty($fields)) {
        echo fieldset_header($extensions[$extension]['nickname']);
        echo $fields;
        echo fieldset_footer();
    }
}

///////////////////////////////////////////////////////////////////////////////
// Groups
///////////////////////////////////////////////////////////////////////////////

if (! empty($groups)) {
    $group_radios = array();

    foreach ($groups as $group) {
        $group_state = in_array($group, $user_info['groups']);
        $group_key = strtr(base64_encode($group), '+/=', '-_:'); // spaces and dollars not allowed, so munge
        $group_radios[] = field_checkbox("group[$group_key]", $group_state, $group, $read_only);
    }

    echo fieldset_header(lang('users_groups'));
    echo field_radio_set('', $group_radios);
    echo fieldset_footer();
}

if (! empty($windows_groups)) {
    $group_radios = array();

    foreach ($windows_groups as $group) {
        $group_state = in_array($group, $user_info['groups']);
        $group_key = strtr(base64_encode($group), '+/=', '-_:'); // spaces and dollars not allowed, so munge
        $group_radios[] = field_checkbox("windows_group[$group_key]", $group_state, $group, $read_only);
    }

    echo fieldset_header(lang('groups_windows_groups'));
    echo field_radio_set('', $group_radios);
    echo fieldset_footer();
}

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo field_button_set($buttons);

echo form_footer();
echo form_close();
