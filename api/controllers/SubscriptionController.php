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

use api\models\SubscriptionForm;
use common\models\Subscription;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class SubscriptionController extends ApiController
{
    /**
     * @api {post} /categories/:category_id/subscriptions Subscribe to category
     * @apiDescription `422 Data Validation Error` occurs if the category object does not exists, the category object type is `anatomy` or the category object is already subscribed by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Categories
     * @apiName CreateSubscription
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} category_id Id number of the category object.
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
     *              "message": "Category Id does not exist.",
     *              "field": "category_id"
     *          }
     *      ]
     */
    public function actionCreate($category_id)
    {
        $model = new SubscriptionForm();
        $model->category_id = $category_id;

        if ($subscription = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $subscription;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create subscription object.');
        }

        return $model;
    }

    /**
     * @api {delete} /categories/:category_id/subscriptions Unsubscribe from category
     * @apiDescription `404 Not Found` occurs if the category object is not subscribed by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Categories
     * @apiName DeleteSubscription
     * @apiPermission Owner
     *
     * @apiParam (Url Parameters) {Integer} category_id Id number of the category object.
     *
     * @apiUse SuccessDelete
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionDelete($category_id)
    {
        /**
         * @var $model null|\yii\db\ActiveRecord
         */
        $model = Subscription::findOne([
            'user_id' => Yii::$app->user->id,
            'category_id' => $category_id,
        ]);

        if (empty($model)) {
            throw new NotFoundHttpException('Subscription object not found.');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete subscription object.');
        }

        Yii::$app->response->setStatusCode(204);
    }
}
