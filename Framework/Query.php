<?php

namespace Framework;

use Framework\Database;

class Query
{
    protected $db;

    public function __construct()
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }


    /**
     * Get all posts with filters
     * 
     * @param array $params associative array of column => value
     * @return array of posts
     */
    public function getPosts(array $args = []): object
    {
        // Default values
        $defaults = [
            'post_type'     => 'post',
            'post_status'   => 'published',
            'post_id'       => NULL,    // optional post ID
            'post_author'   => NULL,    // optional author ID
            'limit'         => NULL,    // optional limit
            'offset'        => null,    // optional offset
            'order'         => 'DESC',  // optional orders
            'orderby'       => 'created_at',  // optional orderby
        ];

        // Merge defaults with the passed parameters
        $args = array_merge($defaults, $args);

        // Filters and parameter placeholders
        $filters = [];
        $bindParams = [];

        // Loop through parameters to create filters and bind params
        foreach ($args as $field => $value) {
            // Handle limit, offset, and order separately (not as bound parameters)
            if ($field === 'limit' || $field === 'offset' || $field === 'order' || $field === 'orderby') {
                continue;
            }

            // Skip parameters that are null or empty (empty strings or arrays)
            if ($value === null || $value === '') {
                continue;
            }

            // Add the filter for SQL query
            $filters[] = "{$field} = :{$field}";

            // Prepare the parameter for binding (with colon in the array key)
            $bindParams[$field] = $value;
        }

        // Base query
        $query = 'SELECT * FROM posts';

        // Append filters to the query
        if (!empty($filters)) {
            $query .= ' WHERE ' . implode(' AND ', $filters);
        }

        // Add ORDER BY clause
        $query .= ' ORDER BY ' . $args['orderby']  . ' ' . ($args['order'] === 'ASC' ? 'ASC' : 'DESC');

        // Handle LIMIT and OFFSET (as raw integers in the query)
        if (isset($args['limit']) && is_numeric($args['limit'])) {
            $query .= ' LIMIT ' . (int)$args['limit']; // casting to int for safety
        }

        if (isset($args['offset']) && is_numeric($args['offset'])) {
            $query .= ' OFFSET ' . (int)$args['offset']; // casting to int for safety
        }


        // inspectAndDie($query, $bindParams);

        try {
            // Execute the query with bound parameters
            if (!empty($args['post_id']) && $args['post_id'] !== NULL) {
                $result = $this->db->query($query, $bindParams)->fetch();
                if ($result === false) {
                    // If no result is found, return an empty stdClass
                    return new \stdClass();
                }
                return (object) $result; // Convert array result to object
            } else {
                $results = $this->db->query($query, $bindParams)->fetchAll();
                if (empty($results)) {
                    // If no results are found, return an empty stdClass
                    return new \stdClass();
                }
                // Convert each result into an object and return an array of objects
                return (object) array_map(function ($result) {
                    return (object) $result;
                }, $results);
            }
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            error_log($e->getMessage());
            return new \stdClass(); // Return an empty object in case of an error
        }
    }

    /**
     * Get post metadata by key and post id
     * 
     * @param string $key
     * @param int $postId
     * @return void
     */

    public function getPostMeta($key, $postId)
    {
        $bindParams = [
            'id' => $postId,
            'key' => $key
        ];

        $query = 'SELECT * FROM post_meta WHERE post_id = :id AND meta_key = :key';

        try {
            // Execute the query with bound parameters
            $result = $this->db->query($query, $bindParams)->fetch();
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            error_log($e->getMessage());
            return []; // Return an empty array in case of an error
        }

        return $result ?: []; // Ensure result is an array
    }
}
