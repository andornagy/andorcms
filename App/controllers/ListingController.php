<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Session;
use Framework\Validation;
use Framework\Authorization;
use Framework\Query;

class ListingController
{
    protected $query;

    public function __construct()
    {

        $this->query = new Query();
    }

    /**
     * Show all listings
     *
     * @return void
     */
    public function index(): void
    {

        $args = [
            'post_type' => 'listing',
            'order' => 'ASC'
        ];

        $listings = $this->query->getPosts($args);

        loadView('listings/index', [
            'listings' => $listings
        ]);
    }

    /**
     * Show the create listing form
     *
     * @return void
     */
    public function create()
    {
        loadView('listings/create');
    }

    /**
     * Show a single lsiting
     * 
     * @param array $params 
     * 
     * @return void
     */
    public function show($params): void
    {


        $id = $params['post_id'] ?? '';

        $args = [
            'post_type' => 'listing',
            'post_id' => $id
        ];

        $listing = $this->query->getPosts($args);

        // Check if listing exists
        if (!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        loadView('listings/show', [
            'listing' => $listing
        ]);
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function store()
    {
        $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];

        $newListingData = array_intersect_key($_POST, array_flip($allowedFields));

        $newListingData['user_id'] = Session::get('user')['id'];

        $newListingData = array_map('sanatize', $newListingData);

        $requiredFields = ['title', 'description', 'email', 'city', 'salary'];

        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($newListingData[$field]) || !Validation::string($newListingData[$field])) {
                $errors[$field] = ucfirst($field) . ' is required.';
            }
        };

        if (!empty($errors)) {
            // reload view with errors
            loadView('listings/create', ['errors' => $errors, 'listing' => $newListingData]);
        } else {

            $fields = [];

            foreach ($newListingData as $field => $value) {
                $fields[] = $field;
            }

            $fields = implode(', ', $fields);


            $values = [];

            foreach ($newListingData as $field => $value) {
                // convert empty string to null
                if ($value === '') {
                    $newListingData[$field] = null;
                }
                $values[] = ':' . $field;
            }

            $values = implode(', ', $values);


            $query = "INSERT INTO posts ({$fields}) VALUES ({$values})";

            $this->db->query($query, $newListingData);
            Session::setFlashMessage('success_message', 'Listing created successfully');


            redirect('/listings');
        }
    }

    /**
     * Delete a listing
     * 
     * @param array $params
     * 
     * @return void
     */

    public function destroy($params)
    {
        $id = $params['id'];

        $params = [
            'id' => $id
        ];

        $listing = $this->db->query('SELECT * FROM posts WHERE id = :id', $params)->fetch();

        if (!$listing) {
            ErrorController::notFound('Listing not found');
        }

        // Authorization
        if (!Authorization::isOwner($listing->user_id)) {
            Session::setFlashMessage('error_message', 'You are not authorized to delete this listing');
            return redirect('/listings/' . $id);
        }

        $this->db->query('DELETE FROM listings WHERE id =:id', $params);

        // set flash message
        Session::setFlashMessage('success_message', 'Listing deleted successfully');
        redirect('/listings');
    }

    /**
     * Show the lsiting edit form
     * 
     * @param array $params 
     * @return void
     */
    public function edit($params)
    {

        $id = $params['id'] ?? '';

        $params = [
            'id' => $id
        ];

        $listing = $this->db->query('SELECT * FROM posts WHERE id = :id', $params)->fetch();

        // Authorization
        if (!Authorization::isOwner($listing->user_id)) {
            Session::setFlashMessage('error_message', 'You are not authorized to edit this listing');
            return redirect('/listings/' . $id);
        }

        // Check if listing exists
        if (!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        loadView('listings/edit', [
            'listing' => $listing
        ]);
    }


    /**
     * Update listing
     * 
     * @param array $params 
     * @return void
     */
    public function update($params)
    {

        $id = $params['id'] ?? '';

        $params = [
            'id' => $id
        ];

        $listing = $this->db->query('SELECT * FROM posts WHERE id = :id', $params)->fetch();

        // Check if listing exists
        if (!$listing) {
            ErrorController::notFound('Listing not found');
            return;
        }

        // Authorization
        if (!Authorization::isOwner($listing->user_id)) {
            Session::setFlashMessage('error_message', 'You are not authorized to edit this listing');
            return redirect('/listings/' . $id);
        }

        $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'state', 'phone', 'email', 'requirements', 'benefits'];

        $updateValues = [];

        $updateValues = array_intersect_key($_POST, array_flip($allowedFields));

        $updateValues = array_map('sanatize', $updateValues);

        $requiredFields = ['title', 'description', 'email', 'city', 'salary'];

        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($updateValues[$field]) || !Validation::string($updateValues[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }

        if (!empty($errors)) {
            loadView('listings/edit', [
                'listing' => $listing,
                'errors' => $errors
            ]);
            exit;
        } else {

            $updateFields = [];

            foreach (array_keys($updateValues) as $field) {
                $updateFields[] = "{$field} = :{$field}";
            }

            $updateFields = implode(', ', $updateFields);

            $updateQuery = "UPDATE posts SET $updateFields WHERE id = :id";

            $updateValues['id'] = $id;
            $this->db->query($updateQuery, $updateValues);

            $_SESSION['success_message'] = "Listing updated!";

            redirect('/listings/' . $id);
        }

        inspectAndDie($updateFields);
    }

    /**
     * Search listings by keywords/location
     * 
     * @param keywords
     * @param location
     * 
     * @return void
     */

    public function search()
    {
        $keywords = $_GET['keywords'] ? sanatize($_GET['keywords']) : '';
        $location = $_GET['location'] ? sanatize($_GET['location']) : '';

        $query = "SELECT * FROM posts WHERE 
        (title LIKE :keywords OR 
        description LIKE :keywords OR 
        company LIKE :keywords OR 
        address LIKE :keywords OR
        tags LIKE :keywords) 
        AND (city LIKE :location OR state LIKE :location)";

        $params = [
            'keywords' => "%{$keywords}%",
            'location' => "%{$location}%"
        ];

        $listings = $this->db->query($query, $params)->fetchAll();

        loadView('listings/search', [
            'listings' => $listings,
            'keywords' => $keywords,
            'location' => $location
        ]);
    }
}
