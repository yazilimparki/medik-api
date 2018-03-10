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

use api\models\Media;
use api\models\MediaComment;
use api\models\MediaForm;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class MediaController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /**
         * Allow popular media action.
         */
        if ($action->id == 'popular') {
            $this->setApplicationUser();
        }

        return parent::beforeAction($action);
    }

    /**
     * @api {post} /media Create a new media
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName CreateMedia
     * @apiPermission User
     **
     * @apiParam (Parameters) {String{..250}} caption Caption of the media.
     * @apiParam (Parameters) {Integer[]}     images   Images of the media.
     * @apiParam (Parameters) {Integer[]}     categories Categories of the media.
     *
     * @apiUse SuccessCreate
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 201 Created
     *      Location: https://api.media.com/media/1
     *      {
     *          "id": 1,
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
     *              "message": "Açıklama boş bırakılamaz.",
     *              "field": "caption"
     *          },
     *          {
     *              "message": "Geçersiz dosya.",
     *              "field": "images"
     *          }
     *      ]
     */
    public function actionCreate()
    {
        $model = new MediaForm();
        $model->load(Yii::$app->request->getBodyParams(), '');

        if ($media = $model->create()) {
            $response = Yii::$app->response;
            $response->setStatusCode(201);
            $response->headers->set('Location', Url::toRoute(['media/view', 'id' => $media['id']], true));
            return $media;
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create media object.');
        }

        return $model;
    }

    /**
     * @api {get} /media/:id Get information about a media
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName ViewMedia
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the media object.
     *
     * @apiSuccess (200 OK) {Integer} id Id number of the media object.
     * @apiSuccess (200 OK) {String=single,multiple} type Type of the media object.
     * @apiSuccess (200 OK) {String} caption Caption of the media object.
     * @apiSuccess (200 OK) {Boolean} favorited Specify if the media object is favorited by the authorized user.
     * @apiSuccess (200 OK) {String} public_id Public id number of the media object.
     * @apiSuccess (200 OK) {String} public_url Public (sharing) url of the media object.
     * @apiSuccess (200 OK) {Object} user Owner user of the media object.
     * @apiSuccess (200 OK) {Integer} user.id Id number of the owner user.
     * @apiSuccess (200 OK) {String} user.username Username of the owner user.
     * @apiSuccess (200 OK) {String} user.real_name Real name of the owner user.
     * @apiSuccess (200 OK) {String} user.screen_name Screen name of the owner user.
     * @apiSuccess (200 OK) {String} user.screen_speciality Screen speciality of the owner user.
     * @apiSuccess (200 OK) {Boolean} user.verified Specify if the owner user is verified.
     * @apiSuccess (200 OK) {Boolean} user.following Specify if the owner user is followed by the authorized user.
     * @apiSuccess (200 OK) {Object} user.picture Picture of the owner user (`null` if profile picture is not uploaded).
     * @apiSuccess (200 OK) {String} user.picture.url Url of the profile picture.
     * @apiSuccess (200 OK) {Integer} user.picture.width Width of the profile picture.
     * @apiSuccess (200 OK) {Integer} user.picture.height Height of the profile picture.
     * @apiSuccess (200 OK) {Object} user.profession Profession of the owner user.
     * @apiSuccess (200 OK) {Integer} user.profession.id Id number of the profession.
     * @apiSuccess (200 OK) {String} user.profession.title Title of the profession.
     * @apiSuccess (200 OK) {Object} user.speciality Speciality of the owner user.
     * @apiSuccess (200 OK) {Integer} user.speciality.id Id number of the speciality.
     * @apiSuccess (200 OK) {String} user.speciality.title Title of the speciality.
     * @apiSuccess (200 OK) {Object[]} categories List of the categories.
     * @apiSuccess (200 OK) {Integer} categories.id Id number of the category object.
     * @apiSuccess (200 OK) {String} categories.title Title of the category object.
     * @apiSuccess (200 OK) {Object[]} images List of the images.
     * @apiSuccess (200 OK) {Object} images.thumbnail Thumbnail image of the image object.
     * @apiSuccess (200 OK) {String} images.thumbnail.url Url of the thumbnail image.
     * @apiSuccess (200 OK) {Integer} images.thumbnail.width Width of the thumbnail image.
     * @apiSuccess (200 OK) {Integer} images.thumbnail.height Height of the thumbnail image.
     * @apiSuccess (200 OK) {Object} images.preview Preview image of the image object.
     * @apiSuccess (200 OK) {String} images.preview.url Url of the preview image.
     * @apiSuccess (200 OK) {Integer} images.preview.width Width of the preview image.
     * @apiSuccess (200 OK) {Integer} images.preview.height Height of the preview image.
     * @apiSuccess (200 OK) {Object} images.full Full image of the image object.
     * @apiSuccess (200 OK) {String} images.full.url Url of the full image.
     * @apiSuccess (200 OK) {Integer} images.full.width Width of the full image.
     * @apiSuccess (200 OK) {Integer} images.full.height Height of the full image.
     * @apiSuccess (200 OK) {Object} counts Counters of the media object.
     * @apiSuccess (200 OK) {Integer} counts.comments Comment count of the media object.
     * @apiSuccess (200 OK) {Integer} counts.favorites Favorite count of the media object.
     * @apiSuccess (200 OK) {Integer} counts.images Image count of the media object.
     * @apiSuccess (200 OK) {Date} created_at Creation date and time of the media object in *ISO 8601* format.
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "id": 1,
     *          "type": "single",
     *          "caption": "Örnek media.",
     *          "favorited": false,
     *          "public_id": "zwukbo4tdueug6svck1iek1uc1c-hy35",
     *          "public_url": "https://app.medik.com/sharing/zwukbo4tdueug6svck1iek1uc1c-hy35",
     *          "user": {
     *              "id": 1,
     *              "username": "hdogan",
     *              "real_name": "Hidayet Doğan",
     *              "screen_name": "Hidayet Doğan",
     *              "screen_speciality": "Dermatoloji Hekimi",
     *              "verified": true,
     *              "following": false,
     *              "picture": {
     *                  "url: "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                  "width": 150,
     *                  "height": 150
     *              },
     *              "profession": {
     *                  "id": 1,
     *                  "title": "Hekim"
     *              },
     *              "speciality": {
     *                  "id": 50,
     *                  "title": "Dermatolog"
     *              }
     *          },
     *          "categories": [
     *              {
     *                  "id": 1,
     *                  "title": "Baş"
     *              },
     *              {
     *                  "id": 2,
     *                  "title": "Nefroloji"
     *              },
     *          ],
     *          "images": [
     *              {
     *                  "thumbnail": {
     *                      "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                      "width": 150,
     *                      "height": 150
     *                  },
     *                  "preview": {
     *                      "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_300.jpg",
     *                      "width": 300,
     *                      "height": 450
     *                  },
     *                  "full": {
     *                      "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35.jpg",
     *                      "width": 1024,
     *                      "height": 1750
     *                  }
     *              }
     *          ],
     *          "counts": {
     *              "comments": 2,
     *              "favorites": 1,
     *              "images": 1
     *          },
     *          "created_at": "2015-02-13T15:26:55+00:00"
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionView($id)
    {
        $model = Media::findByAttributes(['media.id' => $id])
            ->andWhere([
                'or',
                ['media.user_id' => Yii::$app->user->id],
                ['media.verified' => 1],
            ])
            ->one();

        if (empty($model)) {
            throw new NotFoundHttpException('Media object not found.');
        }

        return $model;
    }

    /**
     * @api {delete} /media/:id Delete a media
     * @apiDescription `404 Not Found` occurs if the media object is not owned by the authorized user.
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName DeleteMedia
     * @apiPermission Owner
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the media object.
     *
     * @apiUse SuccessDelete
     * @apiUse ErrorAuthorization
     * @apiUse ErrorNotFound
     * @apiUse ErrorServer
     */
    public function actionDelete($id)
    {
        /**
         * @var $model null|\yii\db\ActiveRecord
         */
        $model = \common\models\Media::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id,
        ]);

        if (empty($model)) {
            throw new NotFoundHttpException('Media object not found.');
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete media object.');
        }

        Yii::$app->response->setStatusCode(204);
    }

    /**
     * @api {get} /media/:id/comments List of media comments
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName CommentsMedia
     * @apiPermission User
     *
     * @apiParam (Url Parameters) {Integer} id Id number of the media object.
     *
     * @apiSuccess (200 OK) {Object[]} data List of the comments.
     * @apiSuccess (200 OK) {Integer} data.id Id number of the comment object.
     * @apiSuccess (200 OK) {String} data.text Text of the comment object.
     * @apiSuccess (200 OK) {Integer} data.score Score of the comment object.
     * @apiSuccess (200 OK) {Object} data.user Owner user of the comment object.
     * @apiSuccess (200 OK) {Integer} data.user.id Id number of the owner user.
     * @apiSuccess (200 OK) {String} data.user.username Username of the owner user.
     * @apiSuccess (200 OK) {String} data.user.real_name Real name of the owner user.
     * @apiSuccess (200 OK) {String} data.user.screen_name Screen name of the owner user.
     * @apiSuccess (200 OK) {String} data.user.screen_speciality Screen speciality of the owner user.
     * @apiSuccess (200 OK) {Boolean} data.user.verified Specify if the owner user is verified.
     * @apiSuccess (200 OK) {Object} data.user.picture Picture of the owner user (`null` if profile picture is not uploaded).
     * @apiSuccess (200 OK) {String} data.user.picture.url Url of the profile picture.
     * @apiSuccess (200 OK) {Integer} data.user.picture.width Width of the profile picture.
     * @apiSuccess (200 OK) {Integer} data.user.picture.height Height of the profile picture.
     * @apiSuccess (200 OK) {Object} data.user.profession Profession of the owner user.
     * @apiSuccess (200 OK) {Integer} data.user.profession.id Id number of the profession.
     * @apiSuccess (200 OK) {String} data.user.profession.title Title of the profession.
     * @apiSuccess (200 OK) {Object} data.user.speciality Speciality of the owner user.
     * @apiSuccess (200 OK) {Integer} data.user.speciality.id Id number of the speciality.
     * @apiSuccess (200 OK) {String} data.user.speciality.title Title of the speciality.
     * @apiSuccess (200 OK) {Date} data.created_at Creation date and time of the comment object in *ISO 8601* format.
     *
     * @apiUse SuccessPagination
     *
     * @apiSuccessExample {json} Example Response
     *      HTTP/1.1 200 OK
     *      {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "text": "Örnek yorum.",
     *                  "score": 5,
     *                  "user": {
     *                      "id": 1,
     *                      "username": "hdogan",
     *                      "real_name": "Hidayet Doğan",
     *                      "screen_name": "Hidayet Doğan",
     *                      "screen_speciality": "Dermatoloji Hekimi",
     *                      "verified": true,
     *                      "picture": {
     *                          "url: "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
     *                          "width": 150,
     *                          "height": 150
     *                      },
     *                      "profession": {
     *                          "id": 1,
     *                          "title": "Hekim"
     *                      },
     *                      "speciality": {
     *                          "id": 50,
     *                          "title": "Dermatoloji"
     *                      }
     *                  },
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              },
     *              {
     *                  "id": 2,
     *                  "text": "Diğer yorum.",
     *                  "score": 0,
     *                  "user": {
     *                      "id": 2,
     *                      "username": "cigdem",
     *                      "real_name": null,
     *                      "screen_name": "cigdem",
     *                      "screen_speciality": "Şirket Müdürü",
     *                      "verified": false,
     *                      "picture": null,
     *                      "profession": {
     *                          "id": 2,
     *                          "title": "Diğer"
     *                      },
     *                      "speciality": {
     *                          "id": 10,
     *                          "title": "Şirket Müdürü"
     *                      }
     *                  },
     *                  "created_at": "2015-02-13T15:26:55+00:00"
     *              }
     *          ],
     *          "meta": {
     *              "total_count": 2,
     *              "page_count": 1,
     *              "current_page": 1,
     *              "per_page": 50
     *          }
     *      }
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionComments($id)
    {
        /*
        $dataProvider = new ActiveDataProvider([
            'query' => MediaComment::findByMediaId($id),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);

        $dependency = new DbDependency();
        $dependency->sql = 'SELECT COUNT(*) FROM comment WHERE media_id = :media_id';
        $dependency->params = [':media_id' => $id];

        Yii::$app->db->cache(function($db) use ($dataProvider) {
            $dataProvider->prepare();
        }, 31536000, $dependency);

        return $dataProvider;
        */

        return new ActiveDataProvider([
            'query' => MediaComment::findByMediaId($id),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }

    /**
     * @api {get} /media/popular List of popular media
     * @apiVersion 0.0.1
     * @apiGroup Media
     * @apiName PopularMedia
     * @apiPermission Client Credentials
     *
     * @apiUse MediaList
     * @apiUse SuccessPagination
     *
     * @apiUse ErrorAuthorization
     * @apiUse ErrorServer
     */
    public function actionPopular()
    {
        return new ActiveDataProvider([
            'query' => Media::findPopular(),
            'pagination' => [
                'pageSizeParam' => 'per_page',
                'validatePage' => false,
            ],
        ]);
    }
}
