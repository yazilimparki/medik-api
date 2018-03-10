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

use api\models\Category;
use api\models\Media;
use Yii;
use yii\caching\DbDependency;
use yii\data\ActiveDataProvider;

class CategoryController extends ApiController
{
    /**
     * @api {get} /categories List of categories
     * @apiDescription
     * If the category object type is `speciality`
     * - `has_children` is always `false`
     * - `children` is always `null`
     *
     * If the category object type is `anatomy`
     * - `has_children` is always `true`
     * - `cover` is always `null`
     * - `subscribed` is always `false`
     * @apiVersion 0.0.1
     * @apiGroup Categories
     * @apiName IndexCategory
     * @apiPermission User
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     *
     * @apiSuccess {Object[]}   data                          List of the categories.
     * @apiSuccess {Integer}    data.id                       Id number of the category.
     * @apiSuccess {String=anatomy,speciality} data.type                     Type of the category.
     * @apiSuccess {String}     data.title                    Title of the category.
     * @apiSuccess {Object}     data.cover                    Cover image of the category.
     * @apiSuccess {String}     data.cover.url                Url of the category cover image.
     * @apiSuccess {Integer}    data.cover.width              Width of the category cover image.
     * @apiSuccess {Integer}    data.cover.height             Height of the category cover image.
     * @apiSuccess {Boolean}    data.subscribed               Specify if the category is subscribed by the authorized user.
     * @apiSuccess {Boolean}    data.has_children             Specify if the category has child nodes.
     * @apiSuccess {Object[]}   data.children                 List of the category children.
     * @apiSuccess {Integer}    data.children.id              Id number of the child category.
     * @apiSuccess {String}     data.children.title           Title of the child category.
     * @apiSuccess {Object}     data.children.cover           Cover image of the child category.
     * @apiSuccess {String}     data.children.cover.url       Url of the child category cover image.
     * @apiSuccess {Integer}    data.children.cover.width     Width of the child category cover image.
     * @apiSuccess {Integer}    data.children.cover.height    Height of the child category cover image.
     * @apiSuccess {Boolean}    data.children.subscribed      Specify if the child category is subscribed by the authorized user.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "type": "anatomy",
     *                  "title": "Baş",
     *                  "subscribed": false,
     *                  "cover": null,
     *                  "has_children": true,
     *                  "children": [
     *                      {
     *                          "id": 11,
     *                          "title": "Burun",
     *                          "subscribed": true,
     *                          "cover": {
     *                              "url": "https://api.example.com/uploads/categories/l4qbdknkoij_jtkwvevo0re_vnjn_pnp_320.jpg",
     *                              "width": 320,
     *                              "height": 80
     *                          }
     *                      }
     *                  ]
     *              }
     *          ]
     *      }
     */
    public function actionIndex()
    {
        /*
        return new ActiveDataProvider([
            'query' => Category::findAllWithSubscription(),
            'pagination' => false,
        ]);
        */

        $dataProvider = new ActiveDataProvider([
            'query' => Category::findAllWithSubscription(),
            'pagination' => false,
        ]);

        $dependency = new DbDependency();
        $dependency->sql = 'SELECT CONCAT(MAX(created_at), COUNT(*)) FROM subscription WHERE user_id = :user_id';
        $dependency->params = [':user_id' => Yii::$app->user->id];

        Yii::$app->db->cache(function($db) use ($dataProvider) {
            $dataProvider->prepare();
        }, 31536000, $dependency);

        return $dataProvider;
    }

    /**
     * @api {get} /categories/:id/media List of category media
     * @apiVersion 0.0.1
     * @apiGroup Categories
     * @apiName MediaCategory
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the category.
     *
     * @apiUse MediaList
     * @apiUse SuccessPagination
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionMedia($id)
    {
        $query = Media::findByAttributes([
            'category.id' => $id,
            'media.verified' => 1,
        ])
            ->innerJoinWith('categories', false)
            ->groupBy('media.id')
            ->orderBy([
                'media.created_at' => SORT_DESC,
                'media.id' => SORT_DESC,
            ]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }
}
