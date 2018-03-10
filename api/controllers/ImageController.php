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

use api\models\ImageForm;
use Yii;
use yii\web\ServerErrorHttpException;

class ImageController extends ApiController
{
    /**
     * @api {post} /media/files Upload media files
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName CreateImage
     * @apiPermission User
     *
     * @apiHeader {String} Content-Type multipart/form-data
     *
     * @apiParam (Parameters) {Object} file File of the image.
     *
     * @apiUse SuccessCreate
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 201 Created
     *      {
     *          "id": 65,
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
     *              "message": "Sadece jpg, jpeg, png, gif uzantılı dosyaları yükleyebilirsiniz.",
     *              "field": "file"
     *          }
     *      ]
     */
    public function actionCreate()
    {
        $model = new ImageForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($images = $model->create()) {
            $response = Yii::$app->response;
            $response->setStatusCode(201);
            return $images;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create image object.');
        }

        return $model;
    }
}
