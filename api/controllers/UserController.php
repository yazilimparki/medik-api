<?php
/**
 * Medik (r) Photo Sharing Platform for Health Professionals (http://medik.com)
 * Copyright (c) Yazılım Parkı Bilişim Teknolojileri D.O.R.P. Ltd. Şti. (http://yazilimparki.com.tr)
 *
 * Licensed under The MIT License (https://opensource.org/licenses/mit-license.php)
 * For full copyright and license information, please see the LICENSE.txt file.
 * Redistributions of files must retain the above copyright notice.
 *
 * Medik (r) is registered trademark of Yazılım Parkı Bilişim Teknolojileri D.O.R.P. Ltd. Şti.
 *
 * @copyright Copyright (c) Yazılım Parkı Bilişim Teknolojileri D.O.R.P. Ltd. Şti. (http://yazilimparki.com.tr)
 * @link http://medik.com Medik (r) Photo Sharing Platform for Health Professionals
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */

namespace api\controllers;

use api\models\PasswordResetForm;
use api\models\UserPictureForm;
use Yii;
use api\models\Media;
use api\models\SettingsForm;
use api\models\User;
use api\models\UserComment;
use api\models\UserFavorite;
use api\models\UserFollower;
use api\models\UserFollowing;
use app\models\UserForm;
use common\models\Follow;
use common\models\Subscription;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class UserController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /**
         * Allow create and password-reset action.
         */
        if ($action->id == 'create' || $action->id == 'reset-password') {
            $this->setApplicationUser();
        }

        return parent::beforeAction($action);
    }

    /**
     * @api {post} /users Create an user
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName CreateUser
     * @apiPermission Client Credentials
     *
     * @apiParam {String} username Username of the user.
     * @apiParam {String{6..}} password Password of the user.
     * @apiParam {String} email E-Mail address of the user.
     * @apiParam {Integer} [speciality_id] Id number of the speciality.
     *
     * @apiSuccess (201 Created) {Integer} id Id number of the user object.
     * @apiSuccess (201 Created) {Boolean} verified Specify if the user object is verified.
     * @apiSuccess (201 Created) {Date} created_at Creation date and time of the user object in *ISO 8601* format.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 201 Created
     *      Location: https://api.example.com/users/1
     *      {
     *          "id": 1,
     *          "created_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @apiUse ErrorValidation
     * @apiUse ErrorServer
     *
     * @apiErrorExample {json} Example Validation Error Response
     *      HTTP/1.1 422 Data Validation Failed.
     *      [
     *          "message": "email",
     *          "field": "Geçersiz e-posta adresi."
     *      ]
     */
    public function actionCreate()
    {
        $model = new UserForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($user = $model->create()) {
            $response = Yii::$app->response;
            $response->setStatusCode(201);
            $response->headers->set('Location', Url::toRoute(['user/view', 'id' => $user['id']], true));
            return $user;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create user object.');
        }

        return $model;
    }

    /**
     * @api {patch} /users/self Update an user
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName UpdateUser
     * @apiPermission User
     *
     * @apiParam {String} [username] Username of the user object.
     * @apiParam {String{6..}} password New password of the user.
     * @apiParam {String} [email] E-Mail address of the user object.
     * @apiParam {Integer} [speciality_id] Id number of the speciality.
     * @apiParam {String{6..50}} [real_name] Real name of the user object.
     * @apiParam {Integer} [city_id] Id number of the city.
     * @apiParam {String{..250}} [bio] Biography information of the user object.
     * @apiParam {String{..250}} [institution] Institution of the user object.
     * @apiParam {String} [web] Web url of the user object.
     *
     * @apiSuccess (200 OK) {Integer} id Id number of the user object.
     * @apiSuccess (200 OK) {Date} updated_at Update date and time of the user object in *ISO 8601* format.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "id": 1,
     *          "updated_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorValidation
     * @apiUse ErrorServer
     *
     * @apiErrorExample {json} Example Validation Error Response
     *      HTTP/1.1 422 Data Validation Failed.
     *      [
     *          "message": "email",
     *          "field": "Geçersiz e-posta adresi."
     *      ]
     */
    public function actionUpdate()
    {
        $model = new UserForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($user = $model->update()) {
            return $user;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update user object.');
        }

        return $model;
    }

    /**
     * @api {post} /users/self/picture Upload user profile picture
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName CreatePicture
     * @apiPermission Owner
     *
     * @apiHeader {String=multipart/form-data} Content-Type
     *
     * @apiParam {Object} file Picture file of the user object.
     *
     * @apiSuccess (200 OK) {Integer} id Id number of the user object.
     * @apiSuccess (200 OK) {Date} updated_at Update date and time of the user object in *ISO 8601* format.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "id": 1,
     *          "updated_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorValidation
     * @apiUse ErrorServer
     *
     * @apiErrorExample {json} Example Validation Error Response
     *      HTTP/1.1 422 Data Validation Failed.
     *      [
     *          "message": "Geçersiz Profil Fotoğrafı",
     *          "field": "file"
     *      ]
     */
    public function actionCreatePicture()
    {
        $model = new UserPictureForm();

        if ($user = $model->create()) {
            $response = Yii::$app->response;
            $response->setStatusCode(201);
            return $user;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to upload user picture.');
        }

        return $model;
    }

    /**
     * @api {delete} /users/self/picture Delete user profile picture
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName DeletePicture
     * @apiPermission Owner
     *
     * @apiUse SuccessDelete
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionDeletePicture()
    {
        $model = new UserPictureForm();

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete user picture.');
        }

        Yii::$app->response->setStatusCode(204);
    }

    /**
     * @api {patch} /users/self/settings Update settings
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName SettingsUser
     * @apiPermission User
     *
     * @apiHeader {String=application/x-www-form-urlencoded} [Content-Type] Required if method injection (POST method) is used.
     *
     * @apiParam {String=PUT} [_method] Used for the HTTP method injection. Required if method injection (POST method) is used.
     * @apiParam {boolean} [notify_comments] Toggle notifications for new comments.
     * @apiParam {boolean} [notify_favorites] Toggle notifications for new favorites.
     * @apiParam {boolean} [notify_followers] Toggle notifications for new followers.
     * @apiParam {boolean} [subscribe_monthly] Toggle subscription for monthly mailing.
     * @apiParam {boolean} [subscribe_weekly] Toggle subscription for weekly mailing.
     *
     * @apiSuccess (200 OK) {Integer} id Id number of the user object.
     * @apiSuccess (200 OK) {Date} updated_at Update date and time of the user object in *ISO 8601* format.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "id": 1,
     *          "updated_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @apiErrorExample {json} Example Validation Error Response
     *      HTTP/1.1 422 Data Validation Failed.
     *      [
     *          "message": "notify_comments",
     *          "field": "Yeni Yorum Bildirimleri 1 veya 0 olmalı."
     *      ]
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorValidation
     * @apiUse ErrorServer
     */
    public function actionSettings()
    {
        $model = new SettingsForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($user = $model->save()) {
            return $user;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update user object.');
        }

        return $model;
    }

    /**
     * @api {post} /users/self/reset-password Send password reset request
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName ResetPasswordUser
     * @apiPermission Client Credentials
     *
     * @apiParam {String} email E-Mail address.
     *
     * @apiUse SuccessCreate
     * @apiUse ErrorValidation
     * @apiUse ErrorServer
     */
    public function actionResetPassword()
    {
        $model = new PasswordResetForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($model->requestReset()) {
            Yii::$app->response->setStatusCode(201);
            return null;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to process request.');
        }

        return $model;
    }

    /**
     * @api {get} /users/:id Get information about an user
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName ViewUser
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the user object.
     *
     * @apiSuccess (200 OK) {Integer} id Id number of the user object.
     * @apiSuccess (200 OK) {String} username Username of the user object.
     * @apiSuccess (200 OK) {String} email E-Mail address of the user.
     * @apiSuccess (200 OK) {Object} profession Profession of the user object.
     * @apiSuccess (200 OK) {Integer} profession.id Id number of the profession.
     * @apiSuccess (200 OK) {String} profession.title Title of the profession.
     * @apiSuccess (200 OK) {Object} speciality Speciality of the user object.
     * @apiSuccess (200 OK) {Integer} speciality.id Id number of the speciality.
     * @apiSuccess (200 OK) {String} speciality.title Title of the speciality.
     * @apiSuccess (200 OK) {String} real_name Real name of the user object.
     * @apiSuccess (200 OK) {String} screen_name Screen name of the user object.
     * @apiSuccess (200 OK) {Object} city City of the user object.
     * @apiSuccess (200 OK) {Integer} city.id Id number of the city.
     * @apiSuccess (200 OK) {String} city.title Title of the city.
     * @apiSuccess (200 OK) {String} bio Biography information of the user object.
     * @apiSuccess (200 OK) {String} institution Institution of the user object.
     * @apiSuccess (200 OK) {String} web Web url of the user object.
     * @apiSuccess (200 OK) {Object} picture Picture of the user object.
     * @apiSuccess (200 OK) {String} picture.url Url of the picture.
     * @apiSuccess (200 OK) {Integer} picture.width Width of the picture.
     * @apiSuccess (200 OK) {Integer} picture.height Height of the picture.
     * @apiSuccess (200 OK) {Boolean} verified Specify if the user object is verified.
     * @apiSuccess (200 OK) {Boolean} following Specify if the user object is followed by the authorized user.
     * @apiSuccess (200 OK) {Object} counts Counters of the user object.
     * @apiSuccess (200 OK) {Integer} counts.comments Comment count of the user object.
     * @apiSuccess (200 OK) {Integer} counts.favorites Favorite count of the user object.
     * @apiSuccess (200 OK) {Integer} counts.followers Follower count of the user object.
     * @apiSuccess (200 OK) {Integer} counts.following Following count of the user object.
     * @apiSuccess (200 OK) {Integer} counts.media Media count of the user object.
     * @apiSuccess (200 OK) {Object} notifications Notification settings of the user object.
     * @apiSuccess (200 OK) {Boolean} notifications.comments Specify if the user object is enabled comments notifications.
     * @apiSuccess (200 OK) {Boolean} notifications.favorites Specify if the user object is enabled favorites notifications.
     * @apiSuccess (200 OK) {Boolean} notifications.followers Specify if the user object is enabled followers notifications.
     * @apiSuccess (200 OK) {Object} subscriptions Subscription settings of the user object.
     * @apiSuccess (200 OK) {Boolean} subscriptions.monthly Specify if the user object is subscribed monthly mailing.
     * @apiSuccess (200 OK) {Boolean} subscriptions.weekly Specify if the user object is subscribed weekly mailing.
     * @apiSuccess (200 OK) {Date} created_at Creation date and time of the user object in *ISO 8601* format.
     *
     * @apiSuccessExample {json} ExampleResponse
     *      HTTP/1.1 200 OK
     *      {
     *          "id": 1,
     *          "username": "hdogan",
     *          "email": "hdogan@gmail.com",
     *          "profession": {
     *              "id": 1,
     *              "title": "Hekim"
     *          },
     *          "speciality": {
     *              "id": 1,
     *              "title": "Acil Tıp"
     *          },
     *          "real_name": "",
     *          "screen_name": "hdogan",
     *          "city": {
     *              "id": 6,
     *              "title": "Ankara"
     *          },
     *          "bio": "",
     *          "institution": "Yazılım Parkı",
     *          "web": "http://www.yazilimparki.com.tr",
     *          "picture": {
     *              "url": "https://api.example.com/uploads/profiles/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *              "width": 150,
     *              "height": 150
     *          },
     *          "verified": true,
     *          "following": false,
     *          "counts": {
     *              "comments": 2,
     *              "favorites": 1,
     *              "followers": 0,
     *              "following": 1,
     *              "media": 2
     *          },
     *          "notifications": {
     *              "comments": true,
     *              "favorites": true,
     *              "followers": true
     *          },
     *          "subscriptions": {
     *              "monthly": false,
     *              "weekly": true
     *          },
     *          "created_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionView($id)
    {
        $model = User::findById($id)->one();

        if (empty($model)) {
            throw new NotFoundHttpException('User object not found.');
        }

        return $model;
    }

    /**
     * @api {get} /users/self Get information about an authenticated user
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName ViewUserSelf
     * @apiPermission User
     *
     * @apiSuccess (200 OK) {Integer} id Id number of the user object.
     * @apiSuccess (200 OK) {String} username Username of the user object.
     * @apiSuccess (200 OK) {String} email E-Mail address of the user.
     * @apiSuccess (200 OK) {Object} profession Profession of the user object.
     * @apiSuccess (200 OK) {Integer} profession.id Id number of the profession.
     * @apiSuccess (200 OK) {String} profession.title Title of the profession.
     * @apiSuccess (200 OK) {Object} speciality Speciality of the user object.
     * @apiSuccess (200 OK) {Integer} speciality.id Id number of the speciality.
     * @apiSuccess (200 OK) {String} speciality.title Title of the speciality.
     * @apiSuccess (200 OK) {String} real_name Real name of the user object.
     * @apiSuccess (200 OK) {String} screen_name Screen name of the user object.
     * @apiSuccess (200 OK) {Object} city City of the user object.
     * @apiSuccess (200 OK) {Integer} city.id Id number of the city.
     * @apiSuccess (200 OK) {String} city.title Title of the city.
     * @apiSuccess (200 OK) {String} bio Biography information of the user object.
     * @apiSuccess (200 OK) {String} institution Institution of the user object.
     * @apiSuccess (200 OK) {String} web Web url of the user object.
     * @apiSuccess (200 OK) {Object} picture Picture of the user object.
     * @apiSuccess (200 OK) {String} picture.url Url of the picture.
     * @apiSuccess (200 OK) {Integer} picture.width Width of the picture.
     * @apiSuccess (200 OK) {Integer} picture.height Height of the picture.
     * @apiSuccess (200 OK) {Boolean} verified Specify if the user object is verified.
     * @apiSuccess (200 OK) {Boolean} can_verify Specify if the user verify himself/herself.
     * @apiSuccess (200 OK) {Boolean} can_send_media Specify if the user can send media.
     * @apiSuccess (200 OK) {Boolean} following Specify if the user object is followed by the authorized user.
     * @apiSuccess (200 OK) {Object} counts Counters of the user object.
     * @apiSuccess (200 OK) {Integer} counts.comments Comment count of the user object.
     * @apiSuccess (200 OK) {Integer} counts.favorites Favorite count of the user object.
     * @apiSuccess (200 OK) {Integer} counts.followers Follower count of the user object.
     * @apiSuccess (200 OK) {Integer} counts.following Following count of the user object.
     * @apiSuccess (200 OK) {Integer} counts.media Media count of the user object.
     * @apiSuccess (200 OK) {Object} notifications Notification settings of the user object.
     * @apiSuccess (200 OK) {Boolean} notifications.comments Specify if the user object is enabled comments notifications.
     * @apiSuccess (200 OK) {Boolean} notifications.favorites Specify if the user object is enabled favorites notifications.
     * @apiSuccess (200 OK) {Boolean} notifications.followers Specify if the user object is enabled followers notifications.
     * @apiSuccess (200 OK) {Object} subscriptions Subscription settings of the user object.
     * @apiSuccess (200 OK) {Boolean} subscriptions.monthly Specify if the user object is subscribed monthly mailing.
     * @apiSuccess (200 OK) {Boolean} subscriptions.weekly Specify if the user object is subscribed weekly mailing.
     * @apiSuccess (200 OK) {Date} created_at Creation date and time of the user object in *ISO 8601* format.
     *
     * @apiSuccessExample {json} ExampleResponse
     *      HTTP/1.1 200 OK
     *      {
     *          "id": 1,
     *          "username": "hdogan",
     *          "email": "hdogan@gmail.com",
     *          "profession": {
     *              "id": 1,
     *              "title": "Hekim"
     *          },
     *          "speciality": {
     *              "id": 1,
     *              "title": "Acil Tıp"
     *          },
     *          "real_name": "",
     *          "screen_name": "hdogan",
     *          "city": {
     *              "id": 6,
     *              "title": "Ankara"
     *          },
     *          "bio": "",
     *          "institution": "Yazılım Parkı",
     *          "web": "http://www.yazilimparki.com.tr",
     *          "picture": {
     *              "url": "https://api.example.com/uploads/profiles/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *              "width": 150,
     *              "height": 150
     *          },
     *          "verified": true,
     *          "following": false,
     *          "counts": {
     *              "comments": 2,
     *              "favorites": 1,
     *              "followers": 0,
     *              "following": 1,
     *              "media": 2
     *          },
     *          "notifications": {
     *              "comments": true,
     *              "favorites": true,
     *              "followers": true
     *          },
     *          "subscriptions": {
     *              "monthly": false,
     *              "weekly": true
     *          },
     *          "created_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionViewSelf()
    {
        return $this->actionView(Yii::$app->user->id);
    }

    /**
     * @api {get} /users/:id/comments List of user comments
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName CommentsUser
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the user object.
     *
     * @apiSuccess (200 OK) {Object[]} data List of the comments.
     * @apiSuccess (200 OK) {Integer} data.id Id number of the comment object.
     * @apiSuccess (200 OK) {String} data.text Text of the comment object.
     * @apiSuccess (200 OK) {Integer} data.score Score of the comment object.
     * @apiSuccess (200 OK) {Object} data.media Media of the comment object.
     * @apiSuccess (200 OK) {Integer} data.media.id Id number of the media.
     * @apiSuccess (200 OK) {String=single,multiple} data.media.type Type of the media.
     * @apiSuccess (200 OK) {Object} data.media.image Image of the media.
     * @apiSuccess (200 OK) {String} data.media.image.url Url of the image.
     * @apiSuccess (200 OK) {Integer} data.media.image.width Width of the image.
     * @apiSuccess (200 OK) {Integer} data.media.image.height Height of the image.
     * @apiSuccess (200 OK) {Date} data.created_at Creation date and time of the comment object in *ISO 8601* format.
     *
     * @apiUse SuccessPagination
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "text": "Örnek yorum.",
     *                  "score": 5,
     *                  "media": {
     *                      "id": 1,
     *                      "type": "single",
     *                      "image": {
     *                          "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                          "width": 150,
     *                          "height": 150
     *                      }
     *                  },
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              },
     *              {
     *                  "id": 2,
     *                  "text": "Diğer yorum.",
     *                  "score": 0,
     *                  "media": {
     *                      "id": 2,
     *                      "type": "multiple",
     *                      "image": {
     *                          "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                          "width": 150,
     *                          "height": 150
     *                      }
     *                  },
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              }
     *          ],
     *          "meta": {
     *              "total_count": 2,
     *              "page_count": 1,
     *              "current_page": 1,
     *              "per_page": 50
     *          }
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionComments($id)
    {
        return new ActiveDataProvider([
            'query' => UserComment::findByUserId($id),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }

    /**
     * @api {get} /users/:id/favorites List of user favorites
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName FavoritesUser
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the user object.
     *
     * @apiSuccess (200 OK) {Object[]} data List of user favorites.
     * @apiSuccess (200 OK) {Integer} data.id Id number of the media object.
     * @apiSuccess (200 OK) {String=single,multiple} data.type Type of the media object.
     * @apiSuccess (200 OK) {Object} data.image Image of the media object.
     * @apiSuccess (200 OK) {String} data.image.url Url of the image.
     * @apiSuccess (200 OK) {Integer} data.image.width Width of the image.
     * @apiSuccess (200 OK) {Integer} data.image.height Height of the image.
     * @apiSuccess (200 OK) {Date} data.created_at Creation date and time of the favorite object in *ISO 8601* format.
     *
     * @apiUse SuccessPagination
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "type": "single",
     *                  "image": {
     *                      "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                      "width": 150,
     *                      "height": 150
     *                  },
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              },
     *              {
     *                  "id": 2,
     *                  "type": "multiple",
     *                  "image": {
     *                      "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                      "width": 150,
     *                      "height": 150
     *                  },
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              }
     *          ],
     *          "meta": {
     *              "total_count": 2,
     *              "page_count": 1,
     *              "current_page": 1,
     *              "per_page": 80
     *          }
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionFavorites($id)
    {
        return new ActiveDataProvider([
            'query' => UserFavorite::findByUserId($id),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }

    /**
     * @api {get} /users/self/feed User media feed
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName FeedUser
     * @apiPermission User
     *
     * @apiUse MediaList
     * @apiUse SuccessPagination
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionFeed()
    {
        $followQuery = Follow::find()->select(['following_id'])->where(['follower_id' => Yii::$app->user->id]);
        $subscriptionQuery = Subscription::find()->select(['category_id'])->where(['user_id' => Yii::$app->user->id]);

        $query = Media::findByAttributes([])
            ->joinWith('categories')
            ->orWhere(['media.user_id' => Yii::$app->user->id])
            ->orWhere(['media.user_id' => $followQuery, 'media.verified' => 1])
            ->orWhere(['category.id' => $subscriptionQuery, 'media.verified' => 1])
            ->groupBy('media.id')
            ->orderBy([
                'media.created_at' => SORT_DESC,
                'media.id' => SORT_DESC,
            ]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }

    /**
     * @api {get} /users/:id/followers List of user followers
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName FollowersUser
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the user object.
     *
     * @apiSuccess (200 OK) {Object[]} data List of user followers.
     * @apiSuccess (200 OK) {Integer} data.id Id number of the follower object.
     * @apiSuccess (200 OK) {String} data.username Username of the follower object.
     * @apiSuccess (200 OK) {String} data.real_name Real name of the follower object.
     * @apiSuccess (200 OK) {String} data.screen_name Screen name of the follower object.
     * @apiSuccess (200 OK) {String} data.screen_speciality Screen speciality of the follower object.
     * @apiSuccess (200 OK) {Boolean} data.verified Specify if the follower user is verified.
     * @apiSuccess (200 OK) {Boolean} data.following Specify if the follower user is followed by the authorized user.
     * @apiSuccess (200 OK) {Object} data.picture Picture of the follower user (`null` if profile picture is not uploaded).
     * @apiSuccess (200 OK) {String} data.picture.url Url of the profile picture.
     * @apiSuccess (200 OK) {Integer} data.picture.width Width of the profile picture.
     * @apiSuccess (200 OK) {Integer} data.picture.height Height of the profile picture.
     * @apiSuccess (200 OK) {Object} data.profession Profession of the follower user.
     * @apiSuccess (200 OK) {Integer} data.profession.id Id number of the profession.
     * @apiSuccess (200 OK) {String} data.profession.title Title of the profession.
     * @apiSuccess (200 OK) {Object} data.speciality Speciality of the follower user.
     * @apiSuccess (200 OK) {Integer} data.speciality.id Id number of the speciality.
     * @apiSuccess (200 OK) {String} data.speciality.title Title of the speciality.
     * @apiSuccess (200 OK) {Date} data.created_at Creation date and time of the follow object in *ISO 8601* format.
     *
     * @apiUse SuccessPagination
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "username": "hdogan",
     *                  "real_name": "Hidayet Doğan",
     *                  "screen_name": "Hidayet Doğan",
     *                  "screen_speciality": "Aile Hekimi",
     *                  "verified": true,
     *                  "following": false,
     *                  "picture": {
     *                      "url: "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                      "width": 150,
     *                      "height": 150
     *                  },
     *                  "profession": {
     *                      "id": 1,
     *                      "title": "Hekim"
     *                  },
     *                  "speciality": {
     *                      "id": 1,
     *                      "title": "Aile Hekimliği"
     *                  },
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              },
     *              {
     *                  "id": 1,
     *                  "username": "cigdem",
     *                  "real_name": "",
     *                  "screen_name": "cigdem",
     *                  "screen_speciality": "Diğer Sağlık Çalışanı",
     *                  "verified": false,
     *                  "following": true,
     *                  "picture": null,
     *                  "profession": {
     *                      "id": 1,
     *                      "title": "Diğer Sağlık Çalışanı"
     *                  },
     *                  "speciality": null,
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              },
     *          ],
     *          "meta": {
     *              "total_count": 2,
     *              "page_count": 1,
     *              "current_page": 1,
     *              "per_page": 50
     *          }
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionFollowers($id)
    {
        return new ActiveDataProvider([
            'query' => UserFollower::findByUserId($id),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }

    /**
     * @api {get} /users/:id/following List of user following
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName FollowingUser
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the user object.
     *
     * @apiSuccess (200 OK) {Object[]} data List of user following.
     * @apiSuccess (200 OK) {Integer} data.id Id number of the following object.
     * @apiSuccess (200 OK) {String} data.username Username of the following object.
     * @apiSuccess (200 OK) {String} data.real_name Real name of the following object.
     * @apiSuccess (200 OK) {String} data.screen_name Screen name of the following object.
     * @apiSuccess (200 OK) {String} data.screen_speciality Screen speciality of the following object.
     * @apiSuccess (200 OK) {Boolean} data.verified Specify if the following user is verified.
     * @apiSuccess (200 OK) {Boolean} data.following Specify if the following user is followed by the authorized user.
     * @apiSuccess (200 OK) {Object} data.picture Picture of the following user (`null` if profile picture is not uploaded).
     * @apiSuccess (200 OK) {String} data.picture.url Url of the profile picture.
     * @apiSuccess (200 OK) {Integer} data.picture.width Width of the profile picture.
     * @apiSuccess (200 OK) {Integer} data.picture.height Height of the profile picture.
     * @apiSuccess (200 OK) {Object} data.profession Profession of the following user.
     * @apiSuccess (200 OK) {Integer} data.profession.id Id number of the profession.
     * @apiSuccess (200 OK) {String} data.profession.title Title of the profession.
     * @apiSuccess (200 OK) {Object} data.speciality Speciality of the following user.
     * @apiSuccess (200 OK) {Integer} data.speciality.id Id number of the speciality.
     * @apiSuccess (200 OK) {String} data.speciality.title Title of the speciality.
     * @apiSuccess (200 OK) {Date} data.created_at Creation date and time of the follow object in *ISO 8601* format.
     *
     * @apiUse SuccessPagination
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "username": "hdogan",
     *                  "real_name": "Hidayet Doğan",
     *                  "screen_name": "Hidayet Doğan",
     *                  "screen_speciality": "Aile Hekimi",
     *                  "verified": true,
     *                  "following": false,
     *                  "picture": {
     *                      "url: "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                      "width": 150,
     *                      "height": 150
     *                  },
     *                  "profession": {
     *                      "id": 1,
     *                      "title": "Hekim"
     *                  },
     *                  "speciality": {
     *                      "id": 1,
     *                      "title": "Aile Hekimliği"
     *                  },
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              },
     *              {
     *                  "id": 1,
     *                  "username": "cigdem",
     *                  "real_name": "",
     *                  "screen_name": "cigdem",
     *                  "screen_speciality": "Diğer Sağlık Çalışanı",
     *                  "verified": false,
     *                  "following": true,
     *                  "picture": null,
     *                  "profession": {
     *                      "id": 1,
     *                      "title": "Diğer Sağlık Çalışanı"
     *                  },
     *                  "speciality": null,
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              },
     *          ],
     *          "meta": {
     *              "total_count": 2,
     *              "page_count": 1,
     *              "current_page": 1,
     *              "per_page": 50
     *          }
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionFollowing($id)
    {
        /*
        $dataProvider = new ActiveDataProvider([
            'query' => UserFollowing::findByUserId($id),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);

        $dependency = new DbDependency();
        $dependency->sql = 'SELECT CONCAT(MAX(created_at), COUNT(*)) FROM follow WHERE follower_id = :user_id';
        $dependency->params = [':user_id' => $id];

        Yii::$app->db->cache(function($db) use ($dataProvider) {
            $dataProvider->prepare();
        }, 31536000, $dependency);

        return $dataProvider;
        */

        return new ActiveDataProvider([
            'query' => UserFollowing::findByUserId($id),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }

    /**
     * @api {get} /users/:id/media List of user media
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName MediaUser
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the user object.
     *
     * @apiUse MediaList
     * @apiUse SuccessPagination
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionMedia($id)
    {
        $query = Media::findByAttributes(['media.user_id' => $id])
            ->andWhere([
                'or',
                ['media.user_id' => Yii::$app->user->id],
                ['media.verified' => 1],
            ])
            ->orderBy([
                'media.created_at' => SORT_DESC,
                'media.id' => SORT_DESC,
            ]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }
}
