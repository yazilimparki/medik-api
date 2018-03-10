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

use api\models\Speciality;
use Yii;
use yii\caching\DbDependency;
use yii\data\ActiveDataProvider;
use yii\filters\PageCache;

class SpecialityController extends ApiController
{
    /**
     * @api {get} /users/self/specialities List of specialities
     * @apiDescription This resource only returns specialities related with the user profession.
     * @apiVersion 0.0.1
     * @apiGroup Users
     * @apiName IndexSpeciality
     * @apiPermission User
     *
     * @apiUse ErrorServer
     *
     * @apiSuccess (200 OK) {Object[]} data List of specialities.
     * @apiSuccess (200 OK) {Integer} data.id Id number of the speciality object.
     * @apiSuccess (200 OK) {String} data.title Title of the speciality object.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "title": "Kulak Burun Boğaz"
     *              },
     *              {
     *                  "id": 2,
     *                  "title": "Aile Hekimliği"
     *              }
     *          ]
     *      }
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Speciality::find()->where(['profession_id' => Yii::$app->user->identity->profession_id])->orderBy(['title' => SORT_ASC]),
            'pagination' => false,
        ]);

        $dependency = new DbDependency();
        $dependency->sql = 'SELECT profession_id FROM user WHERE id = :user_id';
        $dependency->params = [':user_id' => Yii::$app->user->id];

        Yii::$app->db->cache(function($db) use ($dataProvider) {
            $dataProvider->prepare();
        }, 31536000, $dependency);

        return $dataProvider;
    }
}
