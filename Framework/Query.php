<?php

namespace Framework;

use App\Controllers\ErrorController;

class Query
{

    /**
     * Get all posts with filters
     * 
     * @param array $params associative array of column => value
     * @return object of posts
     */
    public function getPosts(array $args = []): object
    {

        global $db;

        // Default values
        $defaults = [
            'post_type'     => 'post',
            'post_status'   => 'published',
            'post_id'       => NULL,    // optional post ID
            'post_author'   => NULL,    // optional author ID
            'limit'         => NULL,    // optional limit
            'offset'        => NULL,    // optional offset
            'order'         => 'DESC',  // optional orders
            'orderby'       => 'post_date',  // optional orderby
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


        try {
            // Execute the query with bound parameters
            if (isset($args['post_id']) && $args['post_id'] !== NULL) {
                $result = $db->query($query, $bindParams)->fetch();

                if ($result === false) {
                    // If no result is found, return an empty stdClass
                    // inspectAndDie($args);
                    return new \stdClass();
                }
                return (object) $result; // Convert array result to object

            } else {
                $results = $db->query($query, $bindParams)->fetchAll();
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
     * Create post
     * 
     * @param array $postData
     * @return int post ID
     */

    public function insertPost($postData)
    {
        global $db;

        $defaults = [
            'post_type'     => 'post',
            'post_status'   => 'draft',
            'user_id'       => Session::get('user')['id'],
            'title'         => '',
            'content'       => '',
            'post_date'     => date('Y-m-d H:i:s'),
            'post_modified' => date('Y-m-d H:i:s'),
        ];

        // Merge defaults with the passed parameters
        $postData = array_merge($defaults, $postData);

        // Determine whether to insert or update based on post_id
        if (!empty($postData['post_id'])) {
            // UPDATE existing post
            $updateFields = [];
            foreach ($postData as $field => $value) {
                // Skip post_id in the update set as it’s used for the WHERE clause
                if ($field !== 'post_id') {
                    $updateFields[] = "{$field} = :{$field}";
                }
                // Convert empty strings to null
                if ($value === '') {
                    $postData[$field] = null;
                }
            }

            // Join fields into a string for the SET clause
            $updateFields = implode(', ', $updateFields);

            // Prepare the UPDATE query
            $query = "UPDATE posts SET {$updateFields} WHERE post_id = :post_id";

            // Execute the query
            $db->query($query, $postData);

            return $postData['post_id'];
        } else {
            // INSERT new post
            $insertFields = [];
            $insertPlaceholders = [];
            foreach ($postData as $field => $value) {
                // Skip post_id on insert since it’s usually auto-generated
                if ($field !== 'post_id') {
                    $insertFields[] = $field;
                    $insertPlaceholders[] = ":{$field}";
                }
                // Convert empty strings to null
                if ($value === '') {
                    $postData[$field] = null;
                }
            }

            // Join fields and placeholders for the INSERT clause
            $insertFields = implode(', ', $insertFields);
            $insertPlaceholders = implode(', ', $insertPlaceholders);

            // Prepare the INSERT query
            $query = "INSERT INTO posts ({$insertFields}) VALUES ({$insertPlaceholders})";

            // inspect($query);
            // inspectAndDie($postData);

            // Execute the query
            $db->query($query, $postData);

            // Return the ID of the newly inserted post
            return $db->conn->lastInsertId();
        }
    }


    /**
     * Update post
     * 
     * @param array $postData
     * @return int post ID
     * 
     */

    public function updatePost($postData)
    {

        global $db;

        $defaults = [
            'post_type'     => 'post',
            'post_status'   => 'draft',
            'post_id'       => '',
            'user_id'       => Session::get('user')['id'],
            'title'         => '',
            'content'       => '',
            'post_date'     => date('Y-m-d H:i:s'),
            'post_modified' => date('Y-m-d H:i:s'),  // Update modified date on update
        ];

        // Merge defaults with the passed parameters
        $postData = array_merge($defaults, $postData);

        // Ensure we have a post ID to update
        if (empty($postData['post_id'])) {
            throw new \Exception("Post ID is required for updating.");
        }

        $updateFields = [];
        foreach ($postData as $field => $value) {
            // Skip post_id in the update set as it’s used for the WHERE clause
            if ($field !== 'post_id') {
                // Set up field placeholders for each column
                $updateFields[] = "{$field} = :{$field}";
            }
            // Convert empty strings to null
            if ($value === '') {
                $postData[$field] = null;
            }
        }

        // Join fields into a string for the SET clause
        $updateFields = implode(', ', $updateFields);

        // Prepare the UPDATE query
        $query = "UPDATE posts SET {$updateFields} WHERE post_id = :post_id";

        // Execute the query
        $db->query($query, $postData);

        return $postData['post_id'];
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

        global $db;

        $bindParams = [
            'meta_key' => $key,
            'post_id' => $postId,
        ];

        $query = 'SELECT meta_value FROM post_meta WHERE post_id = :post_id AND meta_key = :meta_key';

        try {
            // Execute the query with bound parameters
            $result = $db->query($query, $bindParams)->fetch();

            // If the result is false (i.e., no rows found), return null
            if ($result === false) {
                return null;
            }

            // Extract the meta_value from the result
            return $result->meta_value;
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            error_log("Error executing query: " . $e->getMessage());
            return null; // Return null in case of an error
        }
    }


    /**
     * Get all post metadata by post id
     * 
     * @param int $postId
     * @return object
     */

    public function getAllPostMeta($postId)
    {
        global $db;

        $params = [
            'post_id' => $postId,
        ];

        $query = 'SELECT meta_key, meta_value FROM post_meta WHERE post_id = :post_id';

        try {
            $results = $db->query($query, $params)->fetchAll();

            if ($results === false) {
                return null;
            }

            // Create an object to store meta_key and meta_value pairs
            $metaObject = new \stdClass();
            foreach ($results as $row) {
                // Since $row is already an object, access its properties directly
                $metaObject->{$row->meta_key} = $row->meta_value;
            }

            return $metaObject;
        } catch (\Exception $exception) {
            error_log("Error executing query: " . $exception->getMessage());
            return null;
        }
    }

    /**
     * Create or Update post metadata by key and post id
     * 
     * @param string $key
     * @param int $postId
     * 
     * @return void
     */

    public function setPostMeta($key, $value, $postId)
    {
        global $db;

        $bindParams = [
            'meta_key' => $key,
            'post_id' => $postId,
            'meta_value' => $value,
        ];

        // The query will insert a new row or update the existing row if the (post_id, meta_key) pair already exists
        $query = 'INSERT INTO post_meta (post_id, meta_key, meta_value) 
        VALUES (:post_id, :meta_key, :meta_value) 
        ON DUPLICATE KEY UPDATE meta_value = :meta_value';

        try {
            // Execute the query with bound parameters
            $db->query($query, $bindParams);
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            error_log("Error executing query: " . $e->getMessage());
        }
    }
}
