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

use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\Response;

/**
 * @apiDefine ErrorBadRequest
 * @apiError (400 Bad Request) {Integer} status  Status (response) code of the error.
 * @apiError (400 Bad Request) {Integer} code    Code of the error.
 * @apiError (400 Bad Request) {String}  message Message of the error.
 * @apiError (400 Bad Request) {String}  name    Name of the error.
 */

/**
 * @apiDefine ErrorAuthorization
 * @apiError (401 Authorization Error) {Integer}  status  Status (response) code of the error.
 * @apiError (401 Authorization Error) {Integer}  code    Code of the error.
 * @apiError (401 Authorization Error) {String}   message Message of the error.
 * @apiError (401 Authorization Error) {String}   name    Name of the error.
 */

/**
 * @apiDefine ErrorNotFound
 * @apiError (404 Not Found) {Integer} status  Status (response) code of the error.
 * @apiError (404 Not Found) {Integer} code    Code of the error.
 * @apiError (404 Not Found) {String}  message Message of the error.
 * @apiError (404 Not Found) {String}  name    Name of the error.
 */

/**
 * @apiDefine ErrorValidation
 * @apiError (422 Data Validation Error) {Object[]}  error           Data validation error object.
 * @apiError (422 Data Validation Error) {String}    error.message   Message of the error.
 * @apiError (422 Data Validation Error) {String}    error.field     Field name of the error.
 */

/**
 * @apiDefine ErrorServer
 * @apiError (500 Server Error) {Integer}    status  Status (response) code of the error.
 * @apiError (500 Server Error) {Integer}    code    Code of the error.
 * @apiError (500 Server Error) {String}     message Message of the error.
 * @apiError (500 Server Error) {String}     name    Name of the error.
 */

/**
 * @apiDefine SuccessCreate
 * @apiSuccess (201 Created) {Integer} id         Id number of the object.
 * @apiSuccess (201 Created) {Date}    created_at Creation date and time of the object in *ISO 8601* format.
 */

/**
 * @apiDefine SuccessDelete
 * @apiSuccess (204 No Content) N/A N/A
 */

/**
 * @apiDefine SuccessPagination
 * @apiParam (Query String Parameters) {Integer} [page] Page number.
 * @apiParam (Query String Parameters) {Integer} [per_page] Number of the records per page.
 * @apiSuccess (200 OK) {Object} meta Pagination information.
 * @apiSuccess (200 OK) {Integer} meta.total_count Total number of the records.
 * @apiSuccess (200 OK) {Integer} meta.page_count Total number of the pages.
 * @apiSuccess (200 OK) {Integer} meta.current_page Current page number.
 * @apiSuccess (200 OK) {Integer} meta.per_page Number of the records per page.
 */

/**
 * @apiDefine ExampleCreate
 * @apiSuccessExample {json} Example Response
 *      HTTP/1.1 201 Created
 *      {
 *          "id": 1,
 *          "created_at": "2015-02-13T15:26:55+00:00"
 *      }
 */

/**
 * @apiDefine User Authorized user.
 * Authorization can be done with using either `access_token` query string or `Authorization` header with **Bearer** token.
 */

/**
 * @apiDefine Owner Owner user of the object.
 * If the authorized user is not owner of the requested object 404 error occurs.
 */

/**
 * @apiDefine MediaList
 * @apiSuccess (200 OK) {Object[]} data List of media.
 * @apiSuccess (200 OK) {Integer} data.id Id number of media media.
 * @apiSuccess (200 OK) {String=single,multiple} data.type Type of the media.
 * @apiSuccess (200 OK) {String} data.caption Caption of the media.
 * @apiSuccess (200 OK) {Boolean} data.favorited Specify if the media is favorited by the user.
 * @apiSuccess (200 OK) {String} data.public_id Public id of the media.
 * @apiSuccess (200 OK) {String} data.public_url Public (sharing) url of the media.
 * @apiSuccess (200 OK) {Object} data.user User of the media.
 * @apiSuccess (200 OK) {Integer} data.user.id Id number of the user.
 * @apiSuccess (200 OK) {String} data.user.username Username of the user.
 * @apiSuccess (200 OK) {Boolean} data.user.verified Specify if the user is verified.
 * @apiSuccess (200 OK) {Boolean} data.user.following Specify if the user is followed.
 * @apiSuccess (200 OK) {String} data.user.real_name Real name of the user.
 * @apiSuccess (200 OK) {String} data.user.screen_name Screen name of the user.
 * @apiSuccess (200 OK) {String} data.user.screen_speciality Screen speciality of the user.
 * @apiSuccess (200 OK) {Object} data.user.picture Picture of the user (`null` if profile picture is not uploaded).
 * @apiSuccess (200 OK) {String} data.user.picture.url Url of the profile picture.
 * @apiSuccess (200 OK) {Integer} data.user.picture.width Width of the profile picture.
 * @apiSuccess (200 OK) {Integer} data.user.picture.height Height of the profile picture.
 * @apiSuccess (200 OK) {Object} data.user.profession Profession of the user.
 * @apiSuccess (200 OK) {Integer} data.user.profession.id Id number of the profession.
 * @apiSuccess (200 OK) {String} data.user.profession.title Title of the profession.
 * @apiSuccess (200 OK) {Object} data.user.speciality Speciality of the user.
 * @apiSuccess (200 OK) {Integer} data.user.speciality.id Id number of the speciality.
 * @apiSuccess (200 OK) {String} data.user.speciality.title Title of the speciality.
 * @apiSuccess (200 OK) {Object[]} data.categories List of categories.
 * @apiSuccess (200 OK) {Integer} data.categories.id Id number of the category.
 * @apiSuccess (200 OK) {String} data.categories.title Title of the category.
 * @apiSuccess (200 OK) {Object[]} data.images List of images.
 * @apiSuccess (200 OK) {Object} data.images.thumbnail Thumbnail image of the image.
 * @apiSuccess (200 OK) {String} data.images.thumbnail.url Url of the thumbnail image.
 * @apiSuccess (200 OK) {Integer} data.images.thumbnail.width Width of the thumbnail image.
 * @apiSuccess (200 OK) {Integer} data.images.thumbnail.height Height of the thumbnail image.
 * @apiSuccess (200 OK) {Object} data.images.preview Preview image of the image.
 * @apiSuccess (200 OK) {String} data.images.preview.url Url of the preview image.
 * @apiSuccess (200 OK) {Integer} data.images.preview.width Width of the preview image.
 * @apiSuccess (200 OK) {Integer} data.images.preview.height Height of the preview image.
 * @apiSuccess (200 OK) {Object} data.images.full Full image of the image.
 * @apiSuccess (200 OK) {String} data.images.full.url Url of the full image.
 * @apiSuccess (200 OK) {Integer} data.images.full.width Width of the full image.
 * @apiSuccess (200 OK) {Integer} data.images.full.height Height of the full image.
 * @apiSuccess (200 OK) {Object} data.counts Counters of the media.
 * @apiSuccess (200 OK) {Integer} data.counts.comments Comment count of the media.
 * @apiSuccess (200 OK) {Integer} data.counts.favorites Favorite count of the media.
 * @apiSuccess (200 OK) {Integer} data.counts.images Image count of the media.
 * @apiSuccess (200 OK) {Date} data.created_at Creation date and time of the media in *ISO 8601* format.
 * @apiSuccessExample {json} Example Response
 *      HTTP/1.1 200 OK
 *      {
 *          "data": [
 *              {
 *                  "id": 1,
 *                  "type": "single",
 *                  "caption": "Örnek media.",
 *                  "favorited": true,
 *                  "public_id": "zwukbo4tdueug6svck1iek1uc1c-hy35",
 *                  "public_url": "https://app.medik.com/sharing/zwukbo4tdueug6svck1iek1uc1c-hy35",
 *                  "user": {
 *                      "id": 1,
 *                      "username": "hdogan",
 *                      "real_name": "Hidayet Doğan",
 *                      "screen_name": "Hidayet Doğan",
 *                      "screen_speciality": "Dermatoloji Hekimi",
 *                      "verified": true,
 *                      "following": false,
 *                      "picture": {
 *                          "url": "https://api.example.com/uploads/profiles/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
 *                          "width": 150,
 *                          "height": 150
 *                      },
 *                      "profession": {
 *                          "id": 1,
 *                          "title": "Hekim"
 *                      },
 *                      "speciality": {
 *                          "id": 10,
 *                          "title": "Dermatolog"
 *                      }
 *                  },
 *                  "categories": [
 *                      {
 *                          "id": 1,
 *                          "title": "Burun"
 *                      },
 *                      {
 *                          "id": 2,
 *                          "title": "Nefroloji"
 *                      }
 *                  ],
 *                  "images": [
 *                      {
 *                          "thumbnail": {
 *                              "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_150.jpg",
 *                              "width": 150,
 *                              "height": 150
 *                          },
 *                          "preview": {
 *                              "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35_300.jpg",
 *                              "width": 300,
 *                              "height": 200
 *                          },
 *                          "full": {
 *                              "url": "https://api.example.com/uploads/media/zwukbo4tdueug6svck1iek1uc1c-hy35.jpg",
 *                              "width": 1024,
 *                              "height": 700
 *                          }
 *                      }
 *                  ],
 *                  "counts": {
 *                      "comments": 2,
 *                      "favorites": 1,
 *                      "images": 1
 *                  },
 *                  "created_at": "2015-02-13T15:26:55+00:00"
 *              }
 *          ],
 *          "meta": {
 *              "total_count": 1,
 *              "page_count": 1,
 *              "current_page": 1,
 *              "per_page": 10
 *          }
 *      }
 */
class ApiController extends Controller
{
    public $serializer = [
        'class' => 'api\rest\Serializer',
        'collectionEnvelope' => 'data',
        'linksEnvelope' => 'links',
        'metaEnvelope' => 'meta',
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
                [
                    'class' => QueryParamAuth::className(),
                    'tokenParam' => 'access_token',
                ],
            ],
        ];
        $behaviors['exceptionFilter'] = [
            'class' => ErrorToExceptionFilter::className(),
        ];

        return $behaviors;
    }

    public function setApplicationUser()
    {
        \Yii::$app->set('user', [
            'class' => 'yii\web\User',
            'identityClass' => 'api\models\Client',
            'enableAutoLogin' => false,
            'enableSession' => false,
            'loginUrl' => null,
        ]);
    }
}
