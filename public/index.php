<?php

try {

    $config = new Phalcon\Config\Adapter\Ini('../app/config/config.ini');
    //Register an autoloader
    $loader = new \Phalcon\Loader();
    $loader->registerDirs(
        array(
            __DIR__.$config->application->controllersDir,
            __DIR__.$config->application->pluginsDir,
            __DIR__.$config->application->libraryDir,
            __DIR__.$config->application->modelsDir,
        )
    )->register();

    //Create a DI
    $di = new Phalcon\DI\FactoryDefault();

	//Setup the database service
    $di->set('db', function() use ($config) {
        return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
            "host" => $config->database->host,
            "username" => $config->database->username,
            "password" => $config->database->password,
            "dbname" => $config->database->dbname,
            'charset' => 'UTF8'
        ));
    });

    //Setup the view component
    $di->set('view', function() use($config){
        $view = new \Phalcon\Mvc\View();
        $view->setViewsDir( __DIR__.$config->application->viewsDir );
        $view->registerEngines(array(
        ".volt" => 'volt',
        ".phtml" => 'Phalcon\Mvc\View\Engine\Php'
        ));
        return $view;
    });

    // $di->set('filter',function() {   
    //     return new \Phalcon\Filter();
    // });

    // $di->set('validation',function ()
    // {
    //     return new Phalcon\Validation();
    // });

    // $di->set('security', function(){

    //     $security = new Phalcon\Security();

    //     //Set the password hashing factor to 12 rounds
    //     $security->setWorkFactor(12);

    //     return $security;
    // }, true);

    $di['router'] = function() {

        //Use the annotations router
        $router = new \Phalcon\Mvc\Router\Annotations();

        //Read the annotations from ProductsController if the uri starts with /api/products
        $router->addResource('UserApi', '/UserApi');
        $router->addResource('UserData', '/UserData');
        $router->addResource('AccountApi', '/AccountApi');
        $router->addResource('AuthApi', '/AuthApi');
        $router->addResource('UserGroup', '/UserGroup');
        

        return $router;
    };

    //Setup a base URI so that all generated URIs include the "tutorial" folder
    $di->set('url', function() use($config){
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri($config->application->baseUri);
        return $url;
    });

    //Start the session the first time when some component request the session service
    $di->set('session', function(){
        $session = new Phalcon\Session\Adapter\Files();
        $session->start();
        return $session;
    });

    // $di->set('dispatcher', function() use ($di) {

    //     $eventsManager = $di->getShared('eventsManager');

    //     // $security = new Security($di);
        
    //     // * We listen for events in the dispatcher using the Security plugin
         
    //     // $eventsManager->attach('dispatch', $security);

    //     $dispatcher = new Phalcon\Mvc\Dispatcher();
    //     $dispatcher->setEventsManager($eventsManager);

    //     return $dispatcher;
    // });

    $di->set('elements', function(){
        return new Elements();
    });
    /**
     * Setting up volt
     */
    $di->set('volt', function($view, $di) use($config) {

        $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);

        $volt->setOptions(array(
            "compiledPath" => $config->cache->voltCacheDir
        ));

        return $volt;
    }, true);

    //Handle the request
    $app = new \Phalcon\Mvc\Application($di);

    echo $app->handle()->getContent();

} catch(\Phalcon\Exception $e) {
     echo "PhalconException: ", $e->getMessage();
}


?>