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

use api\models\Profession;
use yii\data\ActiveDataProvider;
use yii\filters\PageCache;

class ProfessionController extends ApiController
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
     * @api {get} /professions List of professions
     * @apiVersion 0.0.1
     * @apiGroup Cities and Professions
     * @apiName IndexProfession
     * @apiPermission Client Credentials
     *
     * @apiUse ErrorServer
     *
     * @apiSuccess (200 OK) {Object[]} data List of professions.
     * @apiSuccess (200 OK) {Integer} data.id Id number of the profession object.
     * @apiSuccess (200 OK) {String} data.title Title of the profession object.
     * @apiSuccess (200 OK) {Object[]} data.specialities List of specialities.
     * @apiSuccess (200 OK) {Integer} data.specialities.id Id number of the speciality.
     * @apiSuccess (200 OK) {String} data.specialities.title Title of the specility.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "title": "Hekim",
     *                  "specialities": [
     *                      {
     *                          "id": 11,
     *                          "title": "Acil Tıp"
     *                      },
     *                      {
     *                          "id": 12,
     *                          "title": "Kalp ve Damar Cerrahisi"
     *                      }
     *                  ]
     *              },
     *              {
     *                  "id": 2,
     *                  "title": "Diğer Sağlık Dışı Çalışanı",
     *                  "specialities": []
     *              }
     *          ]
     *      }
     */
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Profession::find()
                ->with([
                    'specialities' => function ($query) {
                        /** @var $query \yii\db\ActiveQuery */
                        $query->orderBy(['title' => SORT_ASC]);
                    },
                ]),
            'pagination' => false,
        ]);
    }
}
