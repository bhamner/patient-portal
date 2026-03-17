<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Role hierarchy (highest to lowest)
    |--------------------------------------------------------------------------
    |
    | Higher roles can manage lower roles. Admin can manage all; staff can
    | send invites and view users; physicians and patients have limited access.
    |
    */
    'hierarchy' => [
        'admin' => 4,
        'staff' => 3,
        'physician' => 2,
        'patient' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles that can send invitations
    |--------------------------------------------------------------------------
    */
    'can_invite' => ['admin', 'staff'],

    /*
    |--------------------------------------------------------------------------
    | Roles that can manage user roles (assign/change roles)
    |--------------------------------------------------------------------------
    */
    'can_manage_roles' => ['admin'],

    /*
    |--------------------------------------------------------------------------
    | Roles that can view the users list
    |--------------------------------------------------------------------------
    */
    'can_view_users' => ['admin', 'staff'],
];
