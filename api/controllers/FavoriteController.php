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

use api\models\FavoriteForm;
use common\models\Favorite;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class FavoriteController extends ApiController
{
    /**
     * @api {post} /media/:media_id/favorites Favorite a media
     * @apiDescription `422 Data Validation Error` occurs if the media object does not exists. `201 Created` returns if the media object is already favorited by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName CreateFavorite
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} media_id Id number of the media object.
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
     *              "message": "Media Id does not exist.",
     *              "field": "media_id"
     *          }
     *      ]
     */
    public function actionCreate($media_id)
    {
        $model = new FavoriteForm();
        $model->media_id = $media_id;

        if ($favorite = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $favorite;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create favorite object.');
        }

        return $model;
    }

    /**
     * @api {delete} /media/:media_id/favorites Unfavorite a media
     * @apiDescription `404 Not Found` occurs if the media object is not favorited by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName DeleteFavorite
     * @apiPermission Owner
     *
     * @apiParam (Url Parameters) {Integer} media_id Id number of the media object.
     *
     * @apiUse SuccessDelete
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionDelete($media_id)
    {
        /**
         * @var $model null|\yii\db\ActiveRecord
         */
        $model = Favorite::findOne([
            'media_id' => $media_id,
            'user_id' => Yii::$app->user->id,
        ]);

        if (empty($model)) {
            throw new NotFoundHttpException('Favorite object not found.');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete favorite object.');
        }

        Yii::$app->response->setStatusCode(204);
    }
}
