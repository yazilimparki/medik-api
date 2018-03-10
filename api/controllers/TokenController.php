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

use api\models\TokenForm;
use common\models\Token;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class TokenController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /**
         * Allow create action.
         */
        return ($action->id == 'create') ? true : parent::beforeAction($action);
    }

    /**
     * @api {post} /oauth2/token Client credentials
     * @apiVersion 0.0.1
     * @apiGroup Authentication
     * @apiName GrantTypeClientCredentials
     * @apiPermission Public
     *
     * @apiParam {String=client_credentials} grant_type
     * @apiParam {String{32}} client_id
     * @apiParam {String{32}} client_secret
     *
     * @apiSuccess (200 OK) {String} access_token Access token value.
     * @apiSuccess (200 OK) {Integer} expires_in Access token expire value in seconds.
     * @apiSuccess (200 OK) {String} scope Unused.
     * @apiSuccess (200 OK) {String=Bearer} token_type Access token type.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "access_token": "bc8018d4bc8441bc1d79aac3f1d06ffe6368fe87",
     *          "expires_in": 86400,
     *          "scope": null,
     *          "token_type": "Bearer"
     *      }
     *
     * @apiUse ErrorBadRequest
     */

    /**
     * @api {post} /oauth2/token Password credentials
     * @apiVersion 0.0.1
     * @apiGroup Authentication
     * @apiName GrantTypePassword
     * @apiPermission Public
     *
     * @apiParam {String=password} grant_type
     * @apiParam {String} username
     * @apiParam {String} password
     * @apiParam {String{32}} client_id
     * @apiParam {String{32}} client_secret
     *
     * @apiSuccess (200 OK) {String} access_token Access token value.
     * @apiSuccess (200 OK) {Integer} expires_in Access token expire value in seconds.
     * @apiSuccess (200 OK) {String} refresh_token Refresh token value.
     * @apiSuccess (200 OK) {String} scope Unused.
     * @apiSuccess (200 OK) {String=Bearer} token_type Access token type.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "access_token": "bc8018d4bc8441bc1d79aac3f1d06ffe6368fe87",
     *          "expires_in": 86400,
     *          "refresh_token": "de6ca59ea494b5707b4ec71e8c90c03c0cc76502",
     *          "scope": null,
     *          "token_type": "Bearer"
     *      }
     *
     * @apiUse ErrorBadRequest
     */

    /**
     * @api {post} /oauth2/token Refresh token
     * @apiVersion 0.0.1
     * @apiGroup Authentication
     * @apiName GrantTypeRefreshToken
     * @apiPermission Public
     *
     * @apiParam {String=refresh_token} grant_type
     * @apiParam {String{32}} refresh_token
     * @apiParam {String{32}} client_id
     * @apiParam {String{32}} client_secret
     *
     * @apiSuccess (200 OK) {String} access_token Access token value.
     * @apiSuccess (200 OK) {Integer} expires_in Access token expire value in seconds.
     * @apiSuccess (200 OK) {String} refresh_token Refresh token value.
     * @apiSuccess (200 OK) {String} scope Unused.
     * @apiSuccess (200 OK) {String=Bearer} token_type Access token type.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "access_token": "bc8018d4bc8441bc1d79aac3f1d06ffe6368fe87",
     *          "expires_in": 86400,
     *          "refresh_token": "de6ca59ea494b5707b4ec71e8c90c03c0cc76502",
     *          "scope": null,
     *          "token_type": "Bearer"
     *      }
     *
     * @apiUse ErrorBadRequest
     */

    /**
     * @Xapi {post} /tokens Create a token
     * @XapiVersion 0.0.1
     * @XapiGroup Authentication
     * @XapiName CreateToken
     * @XapiPermission Public
     *
     * @XapiParam {String} email E-Mail address.
     * @XapiParam {String} password Password.
     * @XapiParam {String=iphone,ipad,android,web} client_type Client type of the installation.
     * @XapiParam {String{32}} client_id Client id of the Installation.
     *
     * @XapiSuccess (201 Created) {Integer} id Id number of the token.
     * @XapiSuccess (201 Created) {String{32}} token Access token.
     * @XapiSuccess (201 Created) {Object} user User of the token.
     * @XapiSuccess (201 Created) {Integer} user.id Id number of the user.
     * @XapiSuccess (201 Created) {Boolean} user.verified Specify if the user is verified.
     * @XapiSuccess (201 Created) {Date} created_at Creation date and time of the object in ISO 8601 format.
     *
     * @XapiSuccessExample {json} Example Response
     *      HTTP/1.1 201 Created
     *      {
     *          "id": 1,
     *          "token": "9ctOATb7OvrdIz2Ke14S6C8PJweSb0D0",
     *          "user": {
     *              "id": 1,
     *              "verified": true
     *          },
     *          "created_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @XapiUse ErrorValidation
     * @XapiUse ErrorServer
     */
    public function actionCreate()
    {
        $model = new TokenForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($token = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $token;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create token object.');
        }

        return $model;
    }

    /**
     * @Xapi {delete} /tokens/:id Delete a token
     * @XapiVersion 0.0.1
     * @XapiGroup Authentication
     * @XapiName DeleteToken
     * @XapiPermission User
     *
     * @XapiParam (Url Parameters) {Integer} id Id number of the token.
     *
     * @XapiUse SuccessDelete
     * @XapiUse ErrorAuthorization
     * @XapiUse ErrorNotFound
     * @XapiUse ErrorServer
     */
    public function actionDelete($id)
    {
        $model = Token::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id,
        ]);

        if (is_null($model)) {
            throw new NotFoundHttpException('Token object not found.');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete token object.');
        }

        Yii::$app->response->setStatusCode(204);
    }
}
