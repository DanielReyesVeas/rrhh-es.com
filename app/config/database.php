<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| PDO Fetch Style
	|--------------------------------------------------------------------------
	|
	| By default, database results will be returned as instances of the PHP
	| stdClass object; however, you may desire to retrieve records in an
	| array format for simplicity. Here you can tweak the fetch style.
	|
	*/

	'fetch' => PDO::FETCH_CLASS,

	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

	'default' => 'principal',

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => array(

		'sqlite' => array(
			'driver'   => 'sqlite',
			'database' => __DIR__.'/../database/production.sqlite',
			'prefix'   => '',
		),
    
                'admin' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_admin',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
    
                'principal' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_principal',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),

                
                'esystems_me_111111111' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_111111111',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),              
                
                'esystems_me_258585855' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_258585855',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                

                
                'esystems_me_66666666' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_66666666',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_888888888' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_888888888',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_3132131' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_3132131',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_342423423' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_342423423',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_213213213' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_213213213',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_6765756756' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_6765756756',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_333333333' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_333333333',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_22222222' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_22222222',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_33333333' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_33333333',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_77777777' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_77777777',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                
                'esystems_me_33333333' => array(
                    'driver'    => 'mysql',
                    'host'      => 'localhost',
                    'database'  => 'esystems_me_33333333',
                    'username'  => 'root',
                    'password'  => '1234',
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                ),
                /*INICIO_PLANTILLA*/
	),

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk haven't actually been run in the database.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer set of commands than a typical key-value systems
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => array(

		'cluster' => false,

		'default' => array(
			'host'     => '127.0.0.1',
			'port'     => 6379,
			'database' => 0,
		),

	),

);
