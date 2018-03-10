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

use api\models\ContactForm;
use Yii;
use yii\web\ServerErrorHttpException;

class ContactController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /**
         * Allow index action.
         */
        return ($action->id == 'create') ? true : parent::beforeAction($action);
    }

    public function actionCreate()
    {
        $headers = Yii::$app->response->headers;
        $headers->add('Access-Control-Allow-Origin', 'http://example.com');
        $headers->add('Access-Control-Allow-Credentials', 'true');
        $headers->add('Access-Control-Allow-Methods', 'POST');

        $model = new ContactForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($comment = $model->create()) {
            Yii::$app->response->setStatusCode(201);
            return $comment;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create contact object.');
        }

        return $model;
    }
}
