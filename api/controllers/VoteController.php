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

use api\models\VoteForm;
use common\models\Vote;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class VoteController extends ApiController
{
    /**
     * @api {post} /comments/:comment_id/votes Vote a comment
     * @apiDescription `422 Data Validation Error` occurs if the comment object does not exists.
     * @apiVersion 0.0.1
     * @apiGroup Comments
     * @apiName CreateVote
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} comment_id Id number of the comment object.
     * @apiParam (Parameters) {String=up,down} score Score of the vote object.
     *
     * @apiSuccess (201 Created) {Integer} id Id number of the vote object.
     * @apiSuccess (201 Created) {Object} comment Comment object.
     * @apiSuccess (201 Created) {Integer} comment.id Id number of the comment object.
     * @apiSuccess (201 Created) {Integer} comment.score Average score of the comment object.
     * @apiSuccess (201 Created) {Date} created_at Creation date and time of the vote object in *ISO 8601* format.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 201 Created
     *      {
     *          "id": 1,
     *          "comment": {
     *              "id": 1,
     *              "score": 5
     *          },
     *          "created_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorValidation
     * @apiUse ErrorServer
     *
     * @apiErrorExample {json} Example Validation Error Response
     *      HTTP/1.1 422 Data Validation Failed.
     *      [
     *          {
     *              "message": "Invalid score.",
     *              "field": "score"
     *          }
     *      ]
     */
    public function actionCreate($comment_id)
    {
        $model = new VoteForm();
        $model->load(Yii::$app->request->getBodyParams(), '');
        $model->comment_id = $comment_id;

        if ($vote = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $vote;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create vote object.');
        }

        return $model;
    }

    /**
     * @api {delete} /comments/:comment_id/votes Unvote a comment
     * @apiDescription `404 Not Found` occurs if the comment object is not voted by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Comments
     * @apiName DeleteVote
     * @apiPermission Owner
     *
     * @apiParam (Url Parameters) {Integer} comment_id Id number of the comment object.
     *
     * @apiUse SuccessDelete
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionDelete($comment_id)
    {
        /**
         * @var $model null|\yii\db\ActiveRecord
         */
        $model = Vote::findOne([
            'comment_id' => $comment_id,
            'user_id' => Yii::$app->user->id,
        ]);

        if (empty($model)) {
            throw new NotFoundHttpException('Vote object not found.');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete vote object.');
        }

        Yii::$app->response->setStatusCode(204);
    }
}
