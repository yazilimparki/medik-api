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

use api\models\City;
use yii\data\ActiveDataProvider;
use yii\filters\PageCache;

class CityController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                [
                    'class' => PageCache::className(),
                    'only' => ['index'],
                    'duration' => 31536000,
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /**
         * Allow index action.
         */
        // return ($action->id == 'index') ? true : parent::beforeAction($action);
        if ($action->id == 'index') {
            $this->setApplicationUser();
        }

        return parent::beforeAction($action);
    }

    /**
     * @api {get} /cities List of cities
     * @apiVersion 0.0.1
     * @apiGroup Cities and Professions
     * @apiName IndexCity
     * @apiPermission Client Credentials
     *
     * @apiUse ErrorServer
     *
     * @apiSuccess (200 OK) {Object[]} data List of cities.
     * @apiSuccess (200 OK) {Integer} id Id number of the city.
     * @apiSuccess (200 OK) {String} title Title of the city.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "title": "Adana"
     *              },
     *              {
     *                  "id": 2,
     *                  "title": "Adıyaman"
     *              }
     *          ]
     *      }
     */
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => City::find()->orderBy('title'),
            'pagination' => false,
        ]);
    }
}
