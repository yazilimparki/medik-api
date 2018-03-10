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
use common\models\Comment;
use common\models\Media;
use Yii;

class UserComment extends Comment
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'media_type',
            'image_id',
            'image_file_base',
            'image_storage',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id',
            'text',
            'score',
            'media' => function ($model) {
                return [
                    'id' => $model->media_id,
                    'type' => $model->media_type == Media::TYPE_SINGLE ? 'single' : 'multiple',
                    'image' => [
                        'url' => Image::storageBaseUrl($model->image_storage) . '/media/' . $model->image_file_base . '_150.jpg',
                        'width' => 150,
                        'height' => 150,
                    ],
                ];
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
                'comment.id',
                'comment.media_id',
                'comment.text',
                'comment.score',
                'comment.created_at',
                'media_type' => 'media.type',
                'image_id' => 'image.id',
                'image_file_base' => 'image.image_file_base',
                'image_storage' => 'image.image_storage',
            ])
            ->innerJoinWith('media', false)
            ->innerJoinWith('media.images', false)
            ->where(['comment.user_id' => $user_id])
            ->orderBy(['comment.created_at' => SORT_DESC]);
    }
}
