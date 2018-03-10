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

use api\models\FollowForm;
use common\models\Follow;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class FollowController extends ApiController
{
    /**
     * @api {post} /users/self/following/:user_id Follow an user
     * @apiDescription `422 Data Validation Error` occurs if the user object does not exists or the user object is already followed by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName CreateFollow
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} user_id Id number of the user object.
     *
     * @apiUse SuccessCreate
     * @apiUse ExampleCreate
     * @apiUse ErrorAuthorization
     * @apiUse ErrorValidation
     * @apiUse ErrorServer
     *
     * @apiErrorExample {json} Example Validation Error Response
     *      HTTP/1.1 422 Data Validation Failed.
     *      [
     *          {
     *              "message": "User Id does not exist.",
     *              "field": "user_id"
     *          }
     *      ]
     */
    public function actionCreate($user_id)
    {
        $model = new FollowForm();
        $model->user_id = $user_id;

        if ($follow = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $follow;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create follow object.');
        }

        return $model;
    }

    /**
     * @api {delete} /users/self/following/:user_id Unfollow an user
     * @apiDescription `404 Not Found` occurs if the user object is not followed by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName DeleteFollow
     * @apiPermission Owner
     *
     * @apiParam (Url Parameters) {Integer} user_id Id number of the user object.
     *
     * @apiUse SuccessDelete
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionDelete($user_id)
    {
        /**
         * @var $model null|\yii\db\ActiveRecord
         */
        $model = Follow::findOne([
            'follower_id' => Yii::$app->user->id,
            'following_id' => $user_id,
        ]);

        if (empty($model)) {
            throw new NotFoundHttpException('Follow object not found.');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete follow object.');
        }

        Yii::$app->response->setStatusCode(204);
    }
}
