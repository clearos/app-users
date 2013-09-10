<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'users';
$app['version'] = '1.4.21';
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
    'app-groups',
);

$app['core_requires'] = array(
    'app-accounts-core',
    'app-storage-core >= 1:1.4.7',
    'system-users-driver', 
    'openssl',
);


$app['core_file_manifest'] = array(
    'users_default.conf' => array ('target' => '/etc/clearos/storage.d/users_default.conf'),
    'userpasswd' => array(
        'target' => '/usr/sbin/userpasswd',
        'mode' => '0755',
    ),
);
