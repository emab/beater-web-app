<?php

use Slim\Http\Request;
use Slim\Http\Response;
require 'loaddb.php';

// Routes
$app->get('/', function ($request, $response) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates'); $twig = new Twig_Environment($loader, array());
    return $response->write($twig->render('homepage.twig', array('page' => 'Home')));
});

$app->get('/run', function ($request, $response) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    include __DIR__.'/../lib/hack.php';
    return $response->write($twig->render('page.twig', array('page' => 'Hack')));
});

$app->get('/beaters', function ($request, $response) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    
    $beaters = R::findAll('beater');
    return $response->write($twig->render('beaters.twig', array('page' => 'Beaters', 'beaters' => $beaters)));
});

$app->post('/beaters', function ($request, $response) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    
    $input = $request->getParsedBody();
    
    $beater = R::dispense('beater');
    $beater->name = $input['bname'];
    $beater->number = $input['tnumber'];
    $id = R::store($beater);
    
    $beaters = R::findAll('beater');
    
    return $response->write($twig->render('beaters.twig', array('page' => 'Beaters', 'beaters' => $beaters)));
});

$app->get('/beaters/add', function ($request, $response) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    return $response->write($twig->render('addbeater.twig', array('page' => 'Add Beater')));
});

$app->get('/beaters/allocate/{id}', function ($request, $response, $args) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    $days = R::findAll('day');
     $beater_days = R::findAll('beater_day', 'beater_id = ?', [$args['id']]);
    return $response->write($twig->render('allocate.twig', array('page' => 'Add Beater', 'days' => $days, 'bid' => $args['id'], 'beater_days' => $beater_days)));
});

$app->post('/beaters/allocate/{id}', function ($request, $response, $args) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    $input = $request->getParsedBody();
    
    if (isset($input['allocatebeater'])) {
            $beater = R::findOne('beater', 'id = ?', [$args['id']]);
            $allocateday = R::findOne('day', 'id = ?', [$input['allocatebeater']]);
            $allocateday->sharedBeater[] = $beater;
            R::storeAll([$allocateday, $beater]);
    }

    if (isset($input['removebeater'])) {
        $toDelete = R::findOne('beater_day', 'beater_id = ? AND day_id = ?', [$args['id'], $input['removebeater']]);
        R::trash($toDelete);
    }
    
    $beater_days = R::findAll('beater_day', 'beater_id = ?', [$args['id']]);
    
    $days = R::findAll('day');
    return $response->write($twig->render('allocate.twig', array('page' => 'Add Beater', 'days' => $days, 'bid' => $args['id'], 'beater_days' => $beater_days)));
});

$app->get('/days', function ($request, $response) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    
    $days = R::findAll('day');
    return $response->write($twig->render('days.twig', array('page' => 'Beaters', 'days' => $days)));
});

$app->post('/days', function ($request, $response) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    
    $input = $request->getParsedBody();
    
    $day = R::dispense('day');
    $day->name = $input['name'];
    $day->date = $input['date'];
    $id = R::store($day);
    
    $days = R::findAll('day');
    
    return $response->write($twig->render('days.twig', array('page' => 'Days', 'days' => $days)));
});

$app->get('/days/add', function ($request, $response) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    return $response->write($twig->render('addday.twig', array('page' => 'Add Day')));
});

$app->get('/days/{id}', function ($request, $response, $args) {
    $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
    $twig = new Twig_Environment($loader, array());
    
    $day = R::findOne('day', 'id = ?', [$args['id']]);
    $beaters = $day->sharedBeater;
    $nobeaters = $day->countShared('beater');
    
    return $response->write($twig->render('day.twig', array('page' => 'Beaters', 'd' => $day, 'bnumber' => $nobeaters, 'beaters' => $beaters)));
});

?>
