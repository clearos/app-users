
Name: app-users
Epoch: 1
Version: 2.0.21
Release: 1%{dist}
Summary: Users
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-accounts
Requires: app-groups >= 1:1.5.10

%description
The users app allows an administrator to create, delete and modify users on the system.  Other apps that plugin directly to the user directory will automatically display options available to a user account.

%package core
Summary: Users - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-base >= 1:1.5.5
Requires: app-accounts-core
Requires: app-storage-core >= 1:1.4.7
Requires: openssl

%description core
The users app allows an administrator to create, delete and modify users on the system.  Other apps that plugin directly to the user directory will automatically display options available to a user account.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/users
cp -r * %{buildroot}/usr/clearos/apps/users/

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

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/users/controllers
/usr/clearos/apps/users/htdocs
/usr/clearos/apps/users/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/users/packaging
%dir /usr/clearos/apps/users
/usr/clearos/apps/users/deploy
/usr/clearos/apps/users/language
/usr/clearos/apps/users/libraries
/usr/sbin/userpasswd
/etc/clearos/storage.d/users_default.conf
