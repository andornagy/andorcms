<?php

// Routes    URI                    Controller                      Middleware
$router->get('/',                   'HomeController@index');
$router->get('/listings',           'ListingController@index');
$router->get('/listings/create',    'ListingController@create',     ['auth']);
$router->get('/listings/edit/{post_id}', 'ListingController@edit',       ['auth']);
$router->get('/listings/search',    'ListingController@search');
$router->get('/listings/{post_id}',      'ListingController@show');

$router->put('/listings/{post_id}',      'ListingController@update',     ['auth']);
$router->post('/listings',          'ListingController@store',      ['auth']);
$router->delete('/listings/{post_id}',   'ListingController@destroy',    ['auth']);


$router->get('/auth/register',      'UserController@create',        ['guest']);
$router->get('/auth/login',         'UserController@login',         ['guest']);

$router->post('/auth/register',     'UserController@store',         ['guest']);
$router->post('/auth/logout',       'UserController@logout',         ['auth']);
$router->post('/auth/login',        'UserController@authenticate',  ['guest']);
