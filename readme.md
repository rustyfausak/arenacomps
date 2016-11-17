# requirements

 - LAMP or [WAMP](http://www.wampserver.com/en/)
   - [PHP >= 5.6.4](http://php.net/downloads.php)
     - OpenSSL PHP Extension
     - PDO PHP Extension
     - Mbstring PHP Extension
     - Tokenizer PHP Extension
     - XML PHP Extension
   - [MySQL](https://www.mysql.com/downloads/)
   - [Apache](https://httpd.apache.org/download.cgi)
     - `rewrite_module`
 - [Composer](https://getcomposer.org/)
 - [git](https://git-scm.com/downloads)
 - [Node >= 6.x](https://nodejs.org/en/download/)
 - [Bower](https://www.npmjs.com/package/bower)

# installation

    $ git clone https://github.com/rustyfausak/arenacomps arenacomps
    // set `storage/` and `bootstrap/cache/` to be writeable by your web server
    // set web root to `arenacomps/public/`
    $ cd arenacomps
    $ composer install
    $ cp .env.example .env
    // edit `.env` with your config
    $ php artisan key:generate
    $ mysql -u .. -p ..
    > create database arenacomps character set utf8 collate utf8_unicode_ci;
    $ php artisan migrate:refresh --seed
    $ npm install
    $ bower install
    $ gulp

# commands

    - `php artisan leaderboard:get` collect leaderboard data
    - `php artisan comps:generate` generate comp data
    - `php artisan comps:clear` clear comp data
    - `php artisan performance:generate` generate performance data
    - `php artisan performance:clear` clear performance data
