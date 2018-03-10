<?php
namespace api\controllers;

use api\models\CommentReportForm;
use api\models\MediaReportForm;
use api\models\UserReportForm;
use Yii;
use yii\web\ServerErrorHttpException;

class ReportController extends ApiController
{
    /**
     * @api {post} /comments/:comment_id/report Report a comment
     * @apiDescription `422 Data Validation Error` occurs if the comment object does not exists.
     * @apiVersion 0.0.1
     * @apiGroup Comments
     * @apiName ReportComment
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} comment_id Id number of the comment object.
     * @apiParam (Parameters) {String{..250}} text Text of the report object.
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
     *              "message": "Report text cannot be left blank.",
     *              "field": "text"
     *          }
     *      ]
     */
    public function actionReportComment($comment_id)
    {
        $model = new CommentReportForm();
        $model->load(Yii::$app->request->getBodyParams(), '');
        $model->comment_id = $comment_id;

        if ($report = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $report;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create comment report object.');
        }

        return $model;
    }

    /**
     * @api {post} /media/:media_id/report Report a media
     * @apiDescription `422 Data Validation Error` occurs if the media object does not exists.
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName ReportMedia
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} media_id Id number of the media object.
     * @apiParam (Parameters) {String{..250}} text Text of the report object.
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
     *              "message": "Report text cannot be left blank.",
     *              "field": "text"
     *          }
     *      ]
     */
    public function actionReportMedia($media_id)
    {
        $model = new MediaReportForm();
        $model->load(Yii::$app->request->getBodyParams(), '');
        $model->media_id = $media_id;

        if ($report = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $report;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create media report object.');
        }

        return $model;
    }

    /**
     * @api {post} /users/:user_id/report Report an user
     * @apiDescription `422 Data Validation Error` occurs if the user object does not exists.
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName ReportUser
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} user_id Id number of the user object.
     * @apiParam (Parameters) {String{..250}} text Text of the report object.
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
     *              "message": "Report text cannot be left blank.",
     *              "field": "text"
     *          }
     *      ]
     */
    public function actionReportUser($user_id)
    {
        $model = new UserReportForm();
        $model->load(Yii::$app->request->getBodyParams(), '');
        $model->user_id = $user_id;

        if ($report = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $report;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create user report object.');
        }

        return $model;
    }
}