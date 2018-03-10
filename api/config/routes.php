<?php
return [
    /**
     * OAuth2 resources.
     */
    ['verb' => 'POST', 'pattern' => 'oauth2/<action:\w+>', 'route' => 'oauth2/default/<action>'],

    /**
     * City resources.
     */
    ['verb' => 'GET', 'pattern' => 'cities', 'route' => 'city/index'],

    /**
     * Profession resources.
     */
    ['verb' => 'GET', 'pattern' => 'professions', 'route' => 'profession/index'],
    ['verb' => 'GET', 'pattern' => 'professions-hidden', 'route' => 'profession/index'],

    /**
     * Activity resources.
     */
    ['verb' => 'GET', 'pattern' => 'users/self/notifications', 'route' => 'activity/index'],
    ['verb' => 'DELETE', 'pattern' => 'users/self/notifications/<id:\d+>', 'route' => 'activity/delete'],
    ['verb' => 'DELETE', 'pattern' => 'users/self/notifications', 'route' => 'activity/delete'],

    /**
     * Category resources.
     */
    ['verb' => 'GET', 'pattern' => 'categories', 'route' => 'category/index'],

    /**
     * Token resources.
     */
    /*
    ['verb' => 'POST', 'pattern' => 'tokens', 'route' => 'token/create'],
    ['verb' => 'DELETE', 'pattern' => 'tokens', 'route' => 'token/delete'],
    */

    /**
     * User resources.
     */
    ['verb' => 'GET', 'pattern' => 'users/self', 'route' => 'user/view-self'],
    ['verb' => 'GET', 'pattern' => 'users/<id:\d+>', 'route' => 'user/view'],
    ['verb' => 'POST', 'pattern' => 'users', 'route' => 'user/create'],
    ['verb' => ['PUT', 'PATCH'], 'pattern' => 'users/self', 'route' => 'user/update'],
    ['verb' => 'POST', 'pattern' => 'users/self/reset-password', 'route' => 'user/reset-password'],
    ['verb' => ['PUT', 'PATCH'], 'pattern' => 'users/self/settings', 'route' => 'user/settings'],

    ['verb' => 'POST', 'pattern' => 'users/self/picture', 'route' => 'user/create-picture'],
    ['verb' => 'DELETE', 'pattern' => 'users/self/picture', 'route' => 'user/delete-picture'],

    /**
     * Follow resources.
     */
    ['verb' => 'GET', 'pattern' => 'users/<id:\d+>/followers', 'route' => 'user/followers'],
    ['verb' => 'GET', 'pattern' => 'users/<id:\d+>/following', 'route' => 'user/following'],
    ['verb' => 'POST', 'pattern' => 'users/self/following/<user_id:\d+>', 'route' => 'follow/create'],
    ['verb' => 'DELETE', 'pattern' => 'users/self/following/<user_id:\d+>', 'route' => 'follow/delete'],

    /**
     * Media resources.
     */
    ['verb' => 'GET', 'pattern' => 'media/<id:\d+>', 'route' => 'media/view'],
    ['verb' => 'GET', 'pattern' => 'users/<id:\d+>/media', 'route' => 'user/media'],
    ['verb' => 'GET', 'pattern' => 'categories/<id:\d+>/media', 'route' => 'category/media'],
    ['verb' => 'POST', 'pattern' => 'media', 'route' => 'media/create'],
    ['verb' => 'DELETE', 'pattern' => 'media/<id:\d+>', 'route' => 'media/delete'],
    ['verb' => 'GET', 'pattern' => 'users/self/feed', 'route' => 'user/feed'],
    ['verb' => 'GET', 'pattern' => 'media/popular', 'route' => 'media/popular'],

    ['verb' => 'POST', 'pattern' => 'media/files', 'route' => 'image/create'],

    /**
     * Favorite resources.
     */
    ['verb' => 'GET', 'pattern' => 'users/<id:\d+>/favorites', 'route' => 'user/favorites'],
    ['verb' => 'POST', 'pattern' => 'media/<media_id:\d+>/favorites', 'route' => 'favorite/create'],
    ['verb' => 'DELETE', 'pattern' => 'media/<media_id:\d+>/favorites', 'route' => 'favorite/delete'],

    /**
     * Comment resources.
     */
    ['verb' => 'GET', 'pattern' => 'users/<id:\d+>/comments', 'route' => 'user/comments'],
    ['verb' => 'GET', 'pattern' => 'media/<id:\d+>/comments', 'route' => 'media/comments'],

    ['verb' => 'POST', 'pattern' => 'media/<media_id:\d+>/comments', 'route' => 'comment/create'],
    ['verb' => 'DELETE', 'pattern' => 'comments/<id:\d+>', 'route' => 'comment/delete'],

    /**
     * Vote resources.
     */
    ['verb' => 'POST', 'pattern' => 'comments/<comment_id:\d+>/votes', 'route' => 'vote/create'],
    ['verb' => 'DELETE', 'pattern' => 'comments/<comment_id:\d+>/votes', 'route' => 'vote/delete'],

    /**
     * Subscription resources.
     */
    ['verb' => 'POST', 'pattern' => 'categories/<category_id:\d+>/subscriptions', 'route' => 'subscription/create'],
    ['verb' => 'DELETE', 'pattern' => 'categories/<category_id:\d+>/subscriptions', 'route' => 'subscription/delete'],

    /**
     * Report resources.
     */
    ['verb' => 'POST', 'pattern' => 'comments/<comment_id:\d+>/report', 'route' => 'report/report-comment'],
    ['verb' => 'POST', 'pattern' => 'media/<media_id:\d+>/report', 'route' => 'report/report-media'],
    ['verb' => 'POST', 'pattern' => 'users/<user_id:\d+>/report', 'route' => 'report/report-user'],

    /**
     * Verification resources.
     */
    ['verb' => 'POST', 'pattern' => 'users/self/verify', 'route' => 'verification/create'],

    /**
     * Speciality resources.
     */
    ['verb' => 'GET', 'pattern' => 'users/self/specialities', 'route' => 'speciality/index'],

    /**
     * Contact Form resource.
     */
    ['verb' => 'POST', 'pattern' => 'contact', 'route' => 'contact/create'],
];
