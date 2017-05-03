<?php

/**
 * Dashboard Widgets controller.
 *
 * @category   apps
 * @package    user
 * @subpackage controllers
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

use \clearos\apps\accounts\Accounts_Factory as Accounts_Factory;
use \clearos\apps\accounts\Accounts_Engine as Accounts_Engine;
use \clearos\apps\groups\Group_Engine as Group_Engine;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Dashboard Widgets controller.
 *
 * @category   apps
 * @package    users
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2016 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/users/
 */

class Users_Dashboard extends ClearOS_Controller
{
    /**
     * Default controller
     *
     * @return string
     */

    function index()
    {
        echo "Invalid dashboard widget... not sure how you got here.";
    }

    /**
     * Version widget
     *
     * @return view
     */

    function summary()
    {
        $data = array(
            'num_users' => lang('base_not_available'),
            'num_groups' => lang('base_not_available')
        );

        // Show warning if we're not in a happy state
        //---------------------------------------------------------

        $this->load->module('accounts/status');

        if ($this->status->unhappy()) {
            // $data['not_available'];
            $this->page->view_form('users/dashboard/unavailable', $data, lang('users_users_and_groups'));
            return;
        }

        // Show cache widget if using remote accounts (e.g. AD)
        //-----------------------------------------------------

        $this->load->module('accounts/cache');

        if ($this->cache->needs_reset())
            $data['not_available'];

        // Load libraries and grab status information
        //-------------------------------------------

        if (!isset($data['not_available'])) {
            $this->lang->load('users');
            $this->load->factory('users/User_Manager_Factory');
            $this->load->factory('groups/Group_Manager_Factory');

            try {
                $data['num_users'] = count($this->user_manager->get_core_details());
                $groups = $this->group_manager->get_list();
                $data['num_groups'] = count($groups);
            } catch (\Exception $e) {
                $data['errmsg'] = clearos_exception_message($e);
            }

            // Load subscription information
            if (clearos_library_installed('clearcenter/Subscription_Manager')) {
                $this->load->library('clearcenter/Subscription_Manager');
                $data['subscriptions'] = $this->subscription_manager->get_subscriptions();
            } else {
                $data['subscriptions'] = array();
            }
        }

        // We need to know if adding user is possible
        $this->load->factory('accounts/Accounts_Factory');
        $data['read_write'] = FALSE;
        if ($this->accounts->get_capability() === Accounts_Engine::CAPABILITY_READ_WRITE)
            $data['read_write'] = TRUE;

        // Load views
        //-----------

        $this->page->view_form('users/dashboard/summary', $data, lang('users_users_and_groups'));
    }
}
