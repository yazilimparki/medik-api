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

use api\models\UserActivity;
use common\models\Activity;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ActivityController extends ApiController
{
    /**
     * @api {get} /users/self/notifications List of user notifications
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName ActivityIndex
     * @apiPermission User
     *
     * @apiSuccess (200 OK) {Object[]} data List of the notifications.
     * @apiSuccess (200 OK) {Integer} data.id Id number of the notification object.
     * @apiSuccess (200 OK) {Object} data.user Activity maker of the notification object.
     * @apiSuccess (200 OK) {Integer} data.user.id Id number of the activity maker.
     * @apiSuccess (200 OK) {String} data.user.username Username of the activity maker.
     * @apiSuccess (200 OK) {String} data.user.real_name Real name of the activity maker.
     * @apiSuccess (200 OK) {String} data.user.screen_name Screen name of the activity maker.
     * @apiSuccess (200 OK) {Boolean} data.user.verified Specify if activity maker is verified.
     * @apiSuccess (200 OK) {Boolean} data.user.following Specify if authorized user is following activity maker.
     * @apiSuccess (200 OK) {Object} data.user.picture Picture of the activity maker(`null` if profile picture is not uploaded).
     * @apiSuccess (200 OK) {String} data.user.picture.url Url of the profile picture.
     * @apiSuccess (200 OK) {Integer} data.user.picture.width Width of the profile picture.
     * @apiSuccess (200 OK) {Integer} data.user.picture.height Height of the profile picture.
     * @apiSuccess (200 OK) {String=comment,favorite,follow} data.type Type of the activity.
     * @apiSuccess (200 OK) {Object} data.object Related object of the notification.
     * @apiSuccess (200 OK) {Integer} data.object.id Related object id.
     * @apiSuccess (200 OK) {String=media,user} data.object.type Related object type
     * @apiSuccess (200 OK) {Object} data.object.image Image of the object (only if object type is `media`).
     * @apiSuccess (200 OK) {Object} data.object.image.thumbnail Thumbnail image of the image object.
     * @apiSuccess (200 OK) {String} data.object.image.thumbnail.url Url of the thumbnail image.
     * @apiSuccess (200 OK) {Integer} data.object.image.thumbnail.width Width of the thumbnail image.
     * @apiSuccess (200 OK) {Integer} data.object.image.thumbnail.height Height of the thumbnail image.
     * @apiSuccess (200 OK) {Object} data.object.image.preview Preview image of the image object.
     * @apiSuccess (200 OK) {String} data.object.image.preview.url Url of the preview image.
     * @apiSuccess (200 OK) {Integer} data.object.image.preview.width Width of the preview image.
     * @apiSuccess (200 OK) {Integer} data.object.image.preview.height Height of the preview image.
     * @apiSuccess (200 OK) {Object} data.object.image.full Full image of the image object.
     * @apiSuccess (200 OK) {String} data.object.image.full.url Url of the full image.
     * @apiSuccess (200 OK) {Integer} data.object.image.full.width Width of the full image.
     * @apiSuccess (200 OK) {Integer} data.object.image.full.height Height of the full image.
     * @apiSuccess (200 OK) {String} data.message Formatted message of the notification.
     * @apiSuccess (200 OK) {Date} data.created_at Creation date and time of the notification object in *ISO 8601* format.
     * @apiSuccess (200 OK) {Date} data.read_at Read date and time of the notification object in *ISO 8601* format.
     *
     * @apiUse SuccessPagination
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "user": {
     *                      "id": 2,
     *                      "username": "ahmet",
     *                      "real_name": "",
     *                      "screen_name": "ahmet",
     *                      "verified": true,
     *                      "following": false,
     *                      "picture": {
     *                          "url: "https://api.example.com/uploads/profiles/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                          "width": 150,
     *                          "height": 150
     *                      }
     *                  },
     *                  "type": "follow",
     *                  "object": {
     *                      "id": 1,
     *                      "type": "user",
     *                      "image": null
     *                  },
     *                  "message": "ahmet seni takip etmeye başladı.",
     *                  "read_at": "2015-07-21T09:30:05+00:00",
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              },
     *              {
     *                  "id": 2,
     *                  "user': {
     *                      "id": 1,
     *                      "username": "hdogan",
     *                      "real_name": "Hidayet Doğan",
     *                      "screen_name": "Hidayet Doğan",
     *                      "verified": false,
     *                      "following": true,
     *                      "picture": null
     *                  },
     *                  "type": "comment",
     *                  "object": {
     *                      "id": 2,
     *                      "type": "media",
     *                      "image": {
     *                          "thumbnail": {
     *                              "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                              "width": 150,
     *                              "height": 150
     *                          },
     *                          "preview": {
     *                              "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_300.jpg",
     *                              "width": 300,
     *                              "height": 450
     *                          },
     *                          "full": {
     *                              "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35.jpg",
     *                              "width": 1024,
     *                              "height": 1750
     *                          }
     *                      }
     *                  },
     *                  "message": "hdogan gönderine yeni bir yorum gönderdi.",
     *                  "read_at": null,
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
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => UserActivity::findByUserId(Yii::$app->user->id),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }

    /**
     * @api {delete} /users/self/notifications/:id Mark notification as read
     * @apiDescription `404 Not Found` occurs if the notification object is not owned by the authorized user.
     * If id parameter is not send, all notifications marked as read.
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName ActivityDelete
     * @apiPermission Owner
     *
     * @apiParam (Url Parameters) {Integer} [id] Id number of the notification object.
     *
     * @apiUse SuccessDelete
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionDelete($id = null)
    {
        if ($id === null) {
            Activity::updateAll(['read_at' => time()], ['user_id' => Yii::$app->user->id]);
        } else {
            /**
             * @var $model null|\yii\db\ActiveRecord
             */
            $model = Activity::findOne([
                'id' => $id,
                'user_id' => Yii::$app->user->id,
            ]);

            if (empty($model)) {
                throw new NotFoundHttpException('Activity object not found.');
            }

            $model->setAttributes([
                'read_at' => time(),
            ], false);

            if ($model->save() === false) {
                throw new ServerErrorHttpException('Failed to update activity object.');
            }
        }

        Yii::$app->response->setStatusCode(204);
    }
}
