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
use common\models\Activity;
use Yii;

class UserActivity extends Activity
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'message',
            'source_username',
            'source_real_name',
            'source_verified',
            'source_following',
            'source_picture_file_base',
            'source_picture_storage',
            'media_image_file_base',
            'media_image_file_extension',
            'media_image_width',
            'media_image_height',
            'media_image_storage',
        ]);
    }


    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id',
            'user' => function ($model) {
                if (empty($model->source_picture_file_base)) {
                    $picture_url = Image::storageBaseUrl(STORAGE_AMAZON) . '/profiles/default_150.jpg';
                } else {
                    $picture_url = Image::storageBaseUrl($model->source_picture_storage) . '/profiles/' . $model->source_picture_file_base . '_150.jpg';
                }

                return [
                    'id' => $model->source_id,
                    'username' => $model->source_username,
                    'real_name' => $model->source_real_name,
                    'screen_name' => empty($model->source_real_name) ? $model->source_username : $model->source_real_name,
                    'verified' => $model->source_verified > 0,
                    'following' => $model->source_following > 0,
                    'picture' => [
                        'url' => $picture_url,
                        'width' => 150,
                        'height' => 150,
                    ],
                ];
            },
            'type' => function ($model) {
                switch ($model->type) {
                    case self::TYPE_COMMENT:
                        return 'comment';
                    case self::TYPE_FAVORITE:
                        return 'favorite';
                    case self::TYPE_FOLLOW:
                        return 'follow';
                    default:
                        return 'unknown';
                }
            },
            'object' => function ($model) {
                $image = null;

                if (!empty($model->media_image_file_base)) {
                    $image = [
                        'thumbnail' => [
                            'url' => Image::storageBaseUrl($model->media_image_storage) . '/media/' . $model->media_image_file_base . '_150.jpg',
                            'width' => 150,
                            'height' => 150,
                        ],
                        'preview' => [
                            'url' => Image::storageBaseUrl($model->media_image_storage) . '/media/' . $model->media_image_file_base . '_300.jpg',
                            'width' => 300,
                            'height' => Image::calculateHeight($model->media_image_width, 300, $model->media_image_height),
                        ],
                        'full' => [
                            'url' => Image::storageBaseUrl($model->media_image_storage) . '/media/' . $model->media_image_file_base . '.' . $model->media_image_file_extension,
                            'width' => $model->media_image_width,
                            'height' => $model->media_image_height,
                        ],
                    ];
                }

                return [
                    'id' => $model->object_id,
                    'type' => $model->object_type == self::OBJECT_TYPE_MEDIA
                        ? 'media'
                        : 'user',
                    'images' => $image,
                ];
            },
            'message' => function ($model) {
                $screen_name = empty($model->source_real_name) ? $model->source_username : $model->source_real_name;

                if ($model->type == self::TYPE_COMMENT) {
                    return sprintf('%s gönderine yeni bir yorum gönderdi.', $screen_name);
                } elseif ($model->type == self::TYPE_FAVORITE) {
                    return sprintf('%s gönderini favorilerine ekledi.', $screen_name);
                } elseif ($model->type == self::TYPE_FOLLOW) {
                    return sprintf('%s seni takip etmeye başladı.', $screen_name);
                } else {
                    return '';
                }
            },
            'read_at' => function ($model) {
                return $model->read_at !== null ? Yii::$app->formatter->asDatetime($model->created_at) : null;
            },
            'created_at' => function ($model) {
                return Yii::$app->formatter->asDatetime($model->created_at);
            },
        ];
    }

    /**
     * @param $user_id
     * @return \yii\db\ActiveQuery
     */
    public static function findByUserId($user_id)
    {
        return self::find()
            ->select([
                'activity.id',
                'activity.type',
                'activity.source_id',
                'activity.object_id',
                'activity.object_type',
                'activity.created_at',
                'activity.read_at',
                'source_username' => 'source.username',
                'source_real_name' => 'source.real_name',
                'source_verified' => 'source.verified',
                'source_following' => 'follow.id',
                'source_picture_file_base' => 'source.picture_file_base',
                'source_picture_storage' => 'source.picture_storage',
                'media_image_file_base' => 'image.image_file_base',
                'media_image_file_extension' => 'image.image_file_extension',
                'media_image_width' => 'image.image_width',
                'media_image_height' => 'image.image_height',
                'media_image_storage' => 'image.image_storage',
            ])
            ->innerJoinWith('source', false)
            ->leftJoin(
                'image',
                'image.media_id = activity.object_id AND activity.object_type = :object_type',
                [':object_type' => self::OBJECT_TYPE_MEDIA]
            )
            ->leftJoin(
                'follow',
                'follow.following_id = activity.source_id AND follow.follower_id = :user_id',
                [':user_id' => Yii::$app->user->id]
            )
            ->where(['activity.user_id' => $user_id])
            ->orderBy(['activity.created_at' => SORT_DESC]);
    }
}
