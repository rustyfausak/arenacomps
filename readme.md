# requirements

 - [PHP >= 5.6.4](http://php.net/downloads.php)
   - OpenSSL PHP Extension
   - PDO PHP Extension
   - Mbstring PHP Extension
   - Tokenizer PHP Extension
   - XML PHP Extension
 - [Composer](https://getcomposer.org/)
 - [git](https://git-scm.com/downloads)
 - [MySQL](https://www.mysql.com/downloads/)

# installation

    git clone https://github.com/rustyfausak/arenacomps arenacomps
    cd arenacomps
    composer install
    cp .env.example .env
    mysql -u .. -p ..
    > create database arenacomps character set utf8 collate utf8_unicode_ci;

# configuration

 - run `php artisan migrate:refresh --seed`
 - edit `.env` with your environment variables
 - set web root to `public/`
 - set `storage/` and `bootstrap/cache/` to be writeable by your web server
 - run `php artisan key:generate`

# developing

 - install [Node](https://nodejs.org/en/download/)
 - `npm install`
 - `gulp`
