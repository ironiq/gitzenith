Some problems might occur when using GitZenith. Refer to this page for more info.

# Installation problems

If you're having problems during the installation of GitZenith, make sure your PHP is correctly configured. Certain PHP distributions can have very restrictive default PHAR settings. PHAR is used within GitZenith to load [Symfony](http://symfony.com/), a PHP framework. Setting the following directives on your php.ini might solve your problem:

```
detect_unicode = Off
phar.readonly = Off
phar.require_hash = Off
```

If you have the Suhosin patch (Ubuntu has it by default) you will also have to set this:

```
suhosin.executor.include.whitelist = phar
```

## SELinux

On Fedora Core with SELinux enabled, you may find that you need to set some SELinux properties like so (replace "/data/www/html/gitzenith" with the correct path to your GitZenith installation and "/home/git/repositories" with the correct path to your git repositories):

```
WWW_DIR=/data/www/html/gitzenith
GIT_DIR=/home/git/repositories
sudo semanage fcontext -a -t httpd_sys_rw_content_t $WWW_DIR/cache
sudo restorecon -v $WWW_DIR/cache
sudo chcon -R --reference=$WWW_DIR/src/SCM/System/Git/CommandLine.php $GIT_DIR
# Allow the webserver to read, home directories have stricter default rules.
sudo semanage fcontext -a -t httpd_sys_content_t $GIT_DIR
sudo restorecon -v $GIT_DIR
```

## Error: Compilation failed: missing )

Make sure you are using the latest libpcre version. GitZenith requires at least 8.x.

## Restrictive apache2 configuration (linux)

You can face some issues if `AllowOverride` is disabled and/or `FollowSymLinks` option is off in your main configuration.
To overcome this, create a file named `gitzenith.conf` inside the `/etc/apache2/conf.d/` folder with this content :

```apache
Alias /gitzenith /srv/www/htdocs/gitzenith/
<Directory /srv/www/htdocs/gitzenith>
  Options FollowSymLinks
  AllowOverride All
</Directory>
```

`/srv/www/htdocs/` is the default htdocs path on some GNU/Linux setups, you must change it to the actual `htdocs` base path if it is different.

_Note_: With Apache 2.4, the default configuration folder has changed from `/etc/apache2/conf.d` to `/etc/apache2/conf-available`. So if you're using Apache 2.4, you need to put the aforementioned `gitzenith.conf` into `/etc/apache2/conf-available`. Then you need to activate it (symlink it from 'conf-available' to 'conf-enabled') with

    a2enconf gitzenith

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
        "^/gitzenith/(.*)$" => "/gitzenith/index.php/$1"
)

# deny access to /gitzenith/config.ini
$HTTP["url"] =~ "^/gitzenith/config.ini" {
     url.access-deny = ("")
}
```

Replace `gitzenith` with your installation path.
