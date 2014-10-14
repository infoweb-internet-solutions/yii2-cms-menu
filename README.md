Menu module for Yii 2
=====================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require infoweb-internet-solutions/yii2-cms-menu "*"
```

or add

```
"infoweb-internet-solutions/yii2-cms-menu": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'modules' => [
        ...
        'menu' => [
            'class' => 'infoweb\menu\Module',
        ],
    ],
];
```

To use the module, execute yii migration
```
yii migrate/up --migrationPath=@vendor/infoweb-internet-solutions/yii2-cms-menu/migrations
```