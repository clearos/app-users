<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'users';
$app['version'] = '2.1.27';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('users_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('users_app_name');
$app['category'] = lang('base_category_system');
$app['subcategory'] = lang('base_subcategory_accounts');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-accounts',
    'app-groups >= 1:1.5.10',
);

$app['core_requires'] = array(
    'app-base >= 1:1.5.5',
    'app-accounts-core',
    'app-storage-core >= 1:1.4.7',
    'openssl',
);


$app['core_file_manifest'] = array(
    'users_default.conf' => array ('target' => '/etc/clearos/storage.d/users_default.conf'),
    'userpasswd' => array(
        'target' => '/usr/sbin/userpasswd',
        'mode' => '0755',
    ),
);

/////////////////////////////////////////////////////////////////////////////
// Dashboard Widgets
/////////////////////////////////////////////////////////////////////////////

$app['dashboard_widgets'] = array(
    $app['category'] => array(
        'users/users_dashboard/summary' => array(
            'title' => lang('users_users_and_groups'),
            'restricted' => FALSE,
        )
    )
);

/////////////////////////////////////////////////////////////////////////////
// App Events
/////////////////////////////////////////////////////////////////////////////

$app['event_types'] = array(
    'USERS_ADD_USER',
    'USERS_UPDATE_USER',
    'USERS_DELETE_USER',
    'USERS_RESET_PASSWORD',
);

