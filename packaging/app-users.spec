
Name: app-users
Epoch: 1
Version: 2.5.0
Release: 1%{dist}
Summary: Users
License: GPLv3
Group: Applications/Apps
Packager: ClearFoundation
Vendor: ClearFoundation
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-accounts
Requires: app-base
Requires: app-groups >= 1:1.5.10

%description
The users app allows an administrator to create, delete and modify users on the system.  Other apps that plugin directly to the user directory will automatically display options available to a user account.

%package core
Summary: Users - API
License: LGPLv3
Group: Applications/API
Requires: app-base-core
Requires: app-base-core >= 1:1.5.5
Requires: app-accounts-core
Requires: app-storage-core >= 1:1.4.7
Requires: openssl
Requires: csplugin-events

%description core
The users app allows an administrator to create, delete and modify users on the system.  Other apps that plugin directly to the user directory will automatically display options available to a user account.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/users
cp -r * %{buildroot}/usr/clearos/apps/users/

install -D -m 0755 packaging/clearos_user %{buildroot}/usr/sbin/clearos_user
install -D -m 0755 packaging/userpasswd %{buildroot}/usr/sbin/userpasswd
install -D -m 0644 packaging/users_default.conf %{buildroot}/etc/clearos/storage.d/users_default.conf

%post
logger -p local6.notice -t installer 'app-users - installing'

%post core
logger -p local6.notice -t installer 'app-users-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/users/deploy/install ] && /usr/clearos/apps/users/deploy/install
fi

[ -x /usr/clearos/apps/users/deploy/upgrade ] && /usr/clearos/apps/users/deploy/upgrade

if [ -x /usr/bin/eventsctl -a -S /var/lib/csplugin-events/eventsctl.socket ]; then
    /usr/bin/eventsctl -R --type USERS_ADD_USER --basename users
    /usr/bin/eventsctl -R --type USERS_UPDATE_USER --basename users
    /usr/bin/eventsctl -R --type USERS_DELETE_USER --basename users
    /usr/bin/eventsctl -R --type USERS_RESET_PASSWORD --basename users
else
    logger -p local6.notice -t installer 'app-users - events system not running, unable to register custom types.'
fi

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-users - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-users-core - uninstalling'
    [ -x /usr/clearos/apps/users/deploy/uninstall ] && /usr/clearos/apps/users/deploy/uninstall
fi

if [ -x /usr/bin/eventsctl -a -S /var/lib/csplugin-events/eventsctl.socket ]; then
    /usr/bin/eventsctl -D --type USERS_ADD_USER
    /usr/bin/eventsctl -D --type USERS_UPDATE_USER
    /usr/bin/eventsctl -D --type USERS_DELETE_USER
    /usr/bin/eventsctl -D --type USERS_RESET_PASSWORD
else
    logger -p local6.notice -t installer 'app-users - events system not running, unable to unregister custom types.'
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/users/controllers
/usr/clearos/apps/users/htdocs
/usr/clearos/apps/users/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/users/packaging
%exclude /usr/clearos/apps/users/unify.json
%dir /usr/clearos/apps/users
/usr/clearos/apps/users/deploy
/usr/clearos/apps/users/language
/usr/clearos/apps/users/libraries
/usr/sbin/clearos_user
/usr/sbin/userpasswd
/etc/clearos/storage.d/users_default.conf
