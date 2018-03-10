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

namespace api\models;

use common\helpers\Image;
use common\models\Media as CommonMedia;
use common\models\User;
use Yii;

class Media extends CommonMedia
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'user_username',
            'user_verified',
            'user_following',
            'user_real_name',
            'user_picture_file_base',
            'user_picture_storage',
            'user_profession_id',
            'user_profession_title',
            'user_speciality_id',
            'user_speciality_title',
            'media_favorited',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id',
            'type' => function ($model) {
                return $model->type == CommonMedia::TYPE_SINGLE ? 'single' : 'multiple';
            },
            'caption',
            'favorited' => function ($model) {
                return $model->media_favorited > 0;
            },
            'public_id',
            'public_url' => function ($model) {
                return 'http://app.example.com/sharing/' . $model->public_id;
            },
            'user' => function ($model) {
                if (empty($model->user_picture_file_base)) {
                    $picture_url = Image::storageBaseUrl(STORAGE_AMAZON) . '/profiles/default_150.jpg';
                } else {
                    $picture_url = Image::storageBaseUrl($model->user_picture_storage) . '/profiles/' . $model->user_picture_file_base . '_150.jpg';
                }

                return [
                    'id' => $model->user_id,
                    'username' => $model->user_username,
                    'verified' => $model->user_verified > 0,
                    'following' => $model->user_following > 0,
                    'real_name' => $model->user_real_name,
                    'screen_name' => empty($model->user_real_name) ? $model->user_username : $model->user_real_name,
                    'screen_speciality' => User::getScreenSpeciality(
                        $model->user_profession_id,
                        $model->user_profession_title,
                        $model->user_speciality_title
                    ),
                    'profession' => [
                        'id' => $model->user_profession_id,
                        'title' => $model->user_profession_title,
                    ],
                    'speciality' => [
                        'id' => $model->user_speciality_id,
                        'title' => $model->user_speciality_title,
                    ],
                    'picture' => [
                        'url' => $picture_url,
                        'width' => 150,
                        'height' => 150,
                    ],
                ];
            },
            'categories',
            'images' => function ($model) {
                $images = [];

                foreach ($model->images as $image) {
                    $images[] = [
                        'thumbnail' => [
                            'url' => Image::storageBaseUrl($image->image_storage) . '/media/' . $image->image_file_base . '_150.jpg',
                            'width' => 150,
                            'height' => 150,
                        ],
                        'preview' => [
                            'url' => Image::storageBaseUrl($image->image_storage) . '/media/' . $image->image_file_base . '_300.jpg',
                            'width' => 300,
                            'height' => Image::calculateHeight($image->image_width, 300, $image->image_height),
                        ],
                        'full' => [
                            'url' => Image::storageBaseUrl($image->image_storage) . '/media/' . $image->image_file_base . '.' . $image->image_file_extension,
                            'width' => $image->image_width,
                            'height' => $image->image_height,
                        ],
                    ];
                }

                return $images;
            },
            'counts' => function ($model) {
                return [
                    'comments' => $model->comment_count,
                    'favorites' => $model->favorite_count,
                    'images' => $model->image_count,
                ];
            },
            'created_at' => function ($model) {
                return Yii::$app->formatter->asDatetime($model->created_at);
            },
        ];
    }

    /**
     * @param $attributes
     * @return \yii\db\ActiveQuery
     */
    public static function findByAttributes($attributes)
    {
        $keys = array_map(function ($value) {
            return preg_match('/^\w+\./', $value) ? $value : 'media.' . $value;
        }, array_keys($attributes));

        $attributes = array_combine($keys, array_values($attributes));

        return self::find()
            ->select([
                'media.id',
                'media.public_id',
                'media.user_id',
                'media.type',
                'media.caption',
                'media.comment_count',
                'media.favorite_count',
                'media.image_count',
                'media.created_at',
                'user_username' => 'user.username',
                'user_verified' => 'user.verified',
                'user_real_name' => 'user.real_name',
                'user_profession_id' => 'user.profession_id',
                'user_profession_title' => 'profession.title',
                'user_speciality_id' => 'user.speciality_id',
                'user_speciality_title' => 'speciality.title',
                'user_picture_file_base' => 'user.picture_file_base',
                'user_picture_storage' => 'user.picture_storage',
                'media_favorited' => 'favorite.id',
                'user_following' => 'follow.id',
            ])
            ->innerJoinWith([
                'user' => function ($query) {
                    /**
                     * @var $query \yii\db\ActiveQuery
                     */
                    $query->joinWith([
                        'profession',
                        'speciality',
                    ], false);
                },
            ], false)
            ->with([
                'images' => function ($query) {
                    /**
                     * @var $query \yii\db\ActiveQuery
                     */
                    $query
                        ->select([
                            'id',
                            'media_id',
                            'image_file_base',
                            'image_file_extension',
                            'image_width',
                            'image_height',
                            'image_storage',
                        ])
                        ->orderBy(['created_at' => SORT_ASC]);
                },
                'categories' => function ($query) {
                    /**
                     * @var $query \yii\db\ActiveQuery
                     */
                    $query->select(['id', 'title']);
                },
            ])
            ->leftJoin(
                'favorite',
                'favorite.media_id = media.id AND favorite.user_id = :user_id',
                [':user_id' => Yii::$app->user->id]
            )
            ->leftJoin(
                'follow',
                'follow.following_id = media.user_id AND follow.follower_id = :user_id',
                [':user_id' => Yii::$app->user->id]
            )
            ->filterWhere($attributes);
    }

    public static function findPopular()
    {
        return self::find()
            ->select([
                'media.id',
                'media.public_id',
                'media.user_id',
                'media.type',
                'media.caption',
                'media.comment_count',
                'media.favorite_count',
                'media.image_count',
                'media.created_at',
                'user_username' => 'user.username',
                'user_verified' => 'user.verified',
                'user_real_name' => 'user.real_name',
                'user_profession_id' => 'user.profession_id',
                'user_profession_title' => 'profession.title',
                'user_speciality_id' => 'user.speciality_id',
                'user_speciality_title' => 'speciality.title',
                'user_picture_file_base' => 'user.picture_file_base',
            ])
            ->innerJoinWith([
                'user' => function ($query) {
                    /**
                     * @var $query \yii\db\ActiveQuery
                     */
                    $query->joinWith([
                        'profession',
                        'speciality',
                    ], false);
                },
            ], false)
            ->with([
                'images' => function ($query) {
                    /**
                     * @var $query \yii\db\ActiveQuery
                     */
                    $query
                        ->select([
                            'id',
                            'media_id',
                            'image_file_base',
                            'image_file_extension',
                            'image_width',
                            'image_height',
                        ])
                        ->orderBy(['created_at' => SORT_ASC]);
                },
                'categories' => function ($query) {
                    /**
                     * @var $query \yii\db\ActiveQuery
                     */
                    $query->select(['id', 'title']);
                },
            ])
            ->where(['media.verified' => 1])
            ->orderBy([
                'media.comment_count' => SORT_DESC,
                'media.favorite_count' => SORT_DESC,
                'media.created_at' => SORT_DESC,
                'media.id' => SORT_DESC,
            ]);
    }
}
