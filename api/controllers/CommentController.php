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

use api\models\CommentForm;
use common\models\Comment;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class CommentController extends ApiController
{
    /**
     * @api {post} /media/:media_id/comments Submit a new comment
     * @apiDescription `422 Data Validation Error` occurs if the media object does not exists.
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName CreateComment
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} media_id Id number of the media object.
     * @apiParam (Parameters) {String{..250}} text Content of the comment object.
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
     *              "message": "Yorum boş bırakılamaz.",
     *              "field": "text"
     *          }
     *      ]
     */
    public function actionCreate($media_id)
    {
        $model = new CommentForm();
        $model->load(Yii::$app->request->getBodyParams(), '');
        $model->media_id = $media_id;

        if ($comment = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $comment;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create comment object.');
        }

        return $model;
    }

    /**
     * @api {delete} /comments/:id Delete a comment
     * @apiDescription `404 Not Found` occurs if the comment object is not owned by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Comments
     * @apiName DeleteComment
     * @apiPermission Owner
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the comment object.
     *
     * @apiUse SuccessDelete
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionDelete($id)
    {
        /**
         * @var $model null|\yii\db\ActiveRecord
         */
        $model = Comment::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id,
        ]);

        if (empty($model)) {
            throw new NotFoundHttpException('Comment object not found.');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete comment object.');
        }

        Yii::$app->response->setStatusCode(204);
    }
}
