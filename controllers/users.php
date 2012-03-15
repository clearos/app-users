<?php

/**
 * Users controller.
 *
 * @category   Apps
 * @package    Users
 * @subpackage Controllers
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\accounts\Accounts_Engine as Accounts_Engine;
use \clearos\apps\accounts\Accounts_Not_Initialized_Exception as Accounts_Not_Initialized_Exception;
use \clearos\apps\accounts\Accounts_Driver_Not_Set_Exception as Accounts_Driver_Not_Set_Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Users controller.
 *
 * @category   Apps
 * @package    Users
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/users/
 */

class Users extends ClearOS_Controller
{
    /**
     * Users overview.
     *
     * @return view
     */

    function index()
    {
        // Show account status widget if we're not in a happy state
        //---------------------------------------------------------

        $this->load->module('accounts/status');

        if ($this->status->unhappy()) {
            $this->status->widget('users');
            return;
        }

        // Show cache widget if using remote accounts (e.g. AD)
        //-----------------------------------------------------

        $this->load->module('accounts/cache');

        if ($this->cache->needs_reset()) {
            $this->cache->widget('users');
            return;
        }

        // Load libraries and grab status information
        //-------------------------------------------

        $this->lang->load('users');
        $this->load->factory('users/User_Manager_Factory');
        $this->load->factory('accounts/Accounts_Factory');

        // Load view data
        //---------------

        try {
            $data['users'] = $this->user_manager->get_core_details();
            $data['mode'] = ($this->accounts->get_capability() === Accounts_Engine::CAPABILITY_READ_WRITE) ? 'edit' : 'view';
            $data['cache_action'] = ($this->accounts_configuration->get_driver() == 'active_directory') ? TRUE : FALSE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
 
        // Load views
        //-----------

        $options['javascript'] = array(clearos_app_htdocs('accounts') . '/cache.js.php');

        $this->page->view_form('users/summary', $data, lang('users_user_manager'), $options);
    }

    /**
     * User add view.
     *
     * @param string $username username
     *
     * @return view
     */

    function add($username = NULL)
    {
        if (!isset($username)) {
            $user_info = $this->input->post('user_info');
            $username = isset($user_info['core']['username']) ? $user_info['core']['username'] : '';
        }

        $this->_item($username, 'add');
    }

    /**
     * Delete user view.
     *
     * @param string $username username
     *
     * @return view
     */

    function delete($username = NULL)
    {
        $confirm_uri = '/app/users/destroy/' . $username;
        $cancel_uri = '/app/users';
        $items = array($username);

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Destroys user.
     *
     * @param string $username username
     *
     * @return view
     */

    function destroy($username)
    {
        // Load libraries
        //---------------

        $this->load->factory('users/User_Factory', $username);

        // Handle form submit
        //-------------------

        try {
            $this->user->delete();
            $this->page->set_status_deleted();
            redirect('/users');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * User edit view.
     *
     * @param string $username username
     *
     * @return view
     */

    function edit($username)
    {
        $this->_item($username, 'edit');
    }

    /**
     * User view.
     *
     * @param string $username username
     *
     * @return view
     */

    function view($username)
    {
        $this->_item($username, 'view');
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * User common add/edit form handler.
     *
     * @param string $username  username
     * @param string $form_type form type (add or edit)
     *
     * @return view
     */

    function _item($username, $form_type)
    {
        // Show account status widget if we're not in a happy state
        //---------------------------------------------------------

        $this->load->module('accounts/status');

        if ($this->status->unhappy()) {
            $this->status->widget();
            return;
        }

        // Load libraries
        //---------------

        $this->lang->load('users');
        $this->load->factory('users/User_Factory', $username);
        $this->load->factory('accounts/Accounts_Factory');

        // Validate prep
        //--------------

        // FIXME: catch validation for full name uniqueness
        // FIXME: add groups

        $info_map = $this->user->get_info_map();
        $password = ($this->input->post('password')) ? $this->input->post('password') : '';
        $verify = ($this->input->post('verify')) ? $this->input->post('verify') : '';

        // Validate core
        //--------------

        foreach ($info_map['core'] as $key => $details) {
            $required = (isset($details['required'])) ? $details['required'] : FALSE;
            $full_key = 'user_info[core][' . $key . ']';
            $check_exists = ($form_type === 'add') ? TRUE : FALSE;

            if (!(($key === 'username') && ($form_type === 'edit')))
                $this->form_validation->set_policy($full_key, $details['validator_class'], $details['validator'], $required, $check_exists);
        }

        // Validate extensions
        //--------------------

        if (! empty($info_map['extensions'])) {
            foreach ($info_map['extensions'] as $extension => $parameters) {
                foreach ($parameters as $key => $details) {
                    $required = (isset($details['required'])) ? $details['required'] : FALSE;
                    $full_key = 'user_info[extensions][' . $extension . '][' . $key . ']';

                    // Note: string_array handling is not fully baked.  It works okay for aliases.
                    if ($details['type'] === 'string_array') {
                        $user_info = $this->input->post('user_info');
                        for ($inx = 0; $inx < count($user_info['extensions'][$extension][$key]); $inx++) {
                            $full_key = 'user_info[extensions][' . $extension . '][' . $key . '][' . $inx . ']';
                            $this->form_validation->set_policy($full_key, $details['validator_class'], $details['validator'], $required);
                        }
                    } else {
                        $this->form_validation->set_policy($full_key, $details['validator_class'], $details['validator'], $required);
                    }
                }
            }
        }

        // Validate plugins
        //-----------------

        if (! empty($info_map['plugins'])) {
            foreach ($info_map['plugins'] as $plugin) {
                $full_key = 'user_info[plugins][' . $plugin . '][state]';
                $this->form_validation->set_policy($full_key, 'accounts/Accounts_Engine', 'validate_plugin_state');
            }
        }

        $form_ok = $this->form_validation->run();

        // Extra validation
        //-----------------

        if ($form_ok) {
            if ($password != $verify) {
                $this->form_validation->set_error('verify', lang('base_password_and_verify_do_not_match'));
                $form_ok = FALSE;
            }

        }

        // Mail aliases, users, and groups are all intertwined and must be unique.
        // This scenario just doesn't fit well with the existing framework, so we
        // kludge it a bit here.
        //-----------------------------------------------------------------------
        // TODO: sanity check this with Samba 4

        if ($form_ok && $this->input->post('user_info')) {
            $user_info = $this->input->post('user_info');

            if (isset($user_info['extensions']['mail']['aliases'])) {
                $this->load->library('mail_extension/OpenLDAP_User_Extension');

                for ($inx = 0; $inx < count($user_info['extensions']['mail']['aliases']); $inx++) {
                    $error = $this->openldap_user_extension->is_unique_alias($username, $user_info['extensions']['mail']['aliases'][$inx]);
                    if ($error) {
                        $this->form_validation->set_error('user_info[extensions][mail][aliases][' . $inx . ']', $error);
                        $form_ok = FALSE;
                    }
                }
            }
        }
        

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && ($form_ok)) {
            try {
                if ($form_type === 'add') {
                    $this->user->add($this->input->post('user_info'), $this->input->post('password'));
                } else if ($form_type === 'edit') {
                    $this->user->update($this->input->post('user_info'));

                    // Only upldate the password if it was changed
                    if ($password || $verify) {
                        $this->user->reset_password(
                            $this->input->post('password'),
                            $this->input->post('verify'),
                            $username
                        );
                    }
                }

                $this->page->set_status_updated();
                redirect('/users');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load the view data 
        //------------------- 

        try {
            $data['form_type'] = $form_type;
            $data['info_map'] = $info_map;
            $data['extensions'] = $this->accounts->get_extensions();
            $data['plugins'] = $this->accounts->get_plugins();

            if ($form_type === 'add')
                $data['user_info'] = $this->user->get_info_defaults();
            else
                $data['user_info'] = $this->user->get_info();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load the views
        //---------------

        $this->page->view_form('users/item', $data, lang('users_user_manager'));
    }
}
