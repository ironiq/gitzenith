Some problems might occur when using GitList. Refer to this page for more info.

## Installation problems

If you're having problems during the installation of GitList, make sure your PHP is correctly configured. Certain PHP distributions can have very restrictive default PHAR settings. PHAR is used within GitList to load [Silex](http://silex.sensiolabs.org/), a microframework. Setting the following directives on your php.ini might solve your problem:

```
detect_unicode = Off
phar.readonly = Off
phar.require_hash = Off
```

If you have the Suhosin patch (Ubuntu has it by default) you will also have to set this:

```
suhosin.executor.include.whitelist = phar
```

### SELinux
On Fedora Core with SELinux enabled, you may find that you need to set some SELinux properties like so (replace "/data/www/html/gitlist" with the correct path to your GitList installation and "/home/git/repositories" with the correct path to your git repositories):

```
WWW_DIR=/data/www/html/gitlist
GIT_DIR=/home/git/repositories
sudo semanage fcontext -a -t httpd_sys_rw_content_t $WWW_DIR/cache
sudo restorecon -v $WWW_DIR/cache
sudo chcon -R --reference=$WWW_DIR/src/GitList/Git/Client.php $GIT_DIR
# Allow the webserver to read, home directories have stricter default rules.
sudo semanage fcontext -a -t httpd_sys_content_t $GIT_DIR
sudo restorecon -v $GIT_DIR
```

## Error: Compilation failed: missing )

Make sure you are using the latest libpcre version. GitList requires at least 8.x.

## Restrictive apache2 configuration (linux)

You can face some issues if `AllowOverride` is disabled and/or `FollowSymLinks` option is off in your main configuration.
To overcome this, create a file named `gitlist.conf` inside the `/etc/apache2/conf.d/` folder with this content :

```apache
Alias /gitlist /srv/www/htdocs/gitlist/
<Directory /srv/www/htdocs/gitlist>
  Options FollowSymLinks
  AllowOverride All
</Directory>
```

`/srv/www/htdocs/` is the default htdocs path on some GNU/Linux setups, you must change it to the actual `htdocs` base path if it is different.

_Note_: With Apache 2.4, the default configuration folder has changed from `/etc/apache2/conf.d` to `/etc/apache2/conf-available`. So if you're using Apache 2.4, you need to put the aforementioned `gitlist.conf` into `/etc/apache2/conf-available`. Then you need to activate it (symlink it from 'conf-available' to 'conf-enabled') with

    a2enconf gitlist

and restart Apache.

_Note_: The Apache `rewrite` module may have to enabled if it isnt already. Enable it with

    a2enmod rewrite

and restart Apache.

## lighttpd

lighttpd does not support `.htaccess` files, thus the url-rewrite will not work and result in a 404 when accessing a repository. To fix, add this to your `lighttpd.conf`:

```
server.modules += ("mod_rewrite", "mod_access")

# Configure url-rewriting
url.rewrite-if-not-file = (
        ...
        "^/gitlist/(.*)$" => "/gitlist/index.php/$1"
)

# deny access to /gitlist/config.ini
$HTTP["url"] =~ "^/gitlist/config.ini" {
     url.access-deny = ("")
}
```

Replace `gitlist` with your installation path.