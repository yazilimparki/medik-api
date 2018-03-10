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

use api\models\VerificationForm;
use Yii;
use yii\web\ServerErrorHttpException;

class VerificationController extends ApiController
{
    /**
     * @api {post} /users/self/verify Submit verification request
     * @apiDescription `422 Data Validation Error` occurs if the user is already validated or has a verification request before.
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName CreateVerification
     * @apiPermission User
     *
     * @apiUse SuccessCreate
     * @apiUse ExampleCreate
     * @apiUse ErrorAuthorization
     * @apiUse ErrorValidation
     * @apiUse ErrorServer
     */
    public function actionCreate()
    {
        $model = new VerificationForm();

        if ($verification = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $verification;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create verification object.');
        }

        return $model;
    }
}
