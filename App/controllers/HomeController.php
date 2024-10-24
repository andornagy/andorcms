<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Query;

class HomeController
{
    protected $query;

    public function __construct()
    {
        $this->query = new Query();
    }

    /**
     * Show the latest listings
     *
     * @return void
     */
    public function index()
    {
        $params = [
            'post_type' => 'listing',
            'limit' => 6,
            'order' => 'ASC'
        ];

        $listings = $this->query->getPosts($params);

        loadView('home', [
            'listings' => $listings
        ]);
    }
}
