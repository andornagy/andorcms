<?php

namespace Framework;

use Framework\Session;

class Authorization
{
    /**
     * Check if user owns a resource
     * 
     * @param int $resourceId
     * 
     * @return bool
     */
    public static function isOwner($resourceId): bool
    {
        $sessionUser = Session::get('user');

        if ($sessionUser !== null && $sessionUser['id']) {
            $sessionUserId = (int) $sessionUser['id'];

            return $sessionUserId === $resourceId;
        }

        return false;
    }
}
