## MESSENGER-LIGHT!

You have just downloaded "Messenger-Light" [A lightweight, minimalistic real-time chat application]

Setup To get it working, follow these steps:

Download Composer dependencies

Make sure you have Composer installed and then run:

composer install You may alternatively need to run php composer.phar install, depending on how you installed Composer.

Configure the Firebase credentials

Configure the .env File

First, make sure you have an .env file (you should). If you don't, copy .env.dist to create it.

Next, look at the configuration and make any adjustments you need - specifically DATABASE_URL.

**Setup the Database**

Again, make sure .env is setup for your computer. Then, create the database & tables!

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

If you get an error that the database exists, that should be ok. But if you have problems, completely drop the database (doctrine:database:drop --force) and try again.

**Start the built-in web server**

You can use Nginx or Apache, but the built-in web server works great:

php bin/console server:run OR symfony serve . Now check out the site at 
http://localhost:8000/register && http://127.0.0.1:8000/login

**Have fun!**

**Chris Sabaoth :)**