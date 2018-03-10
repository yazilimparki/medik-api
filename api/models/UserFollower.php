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
use common\models\Follow;
use common\models\User;
use Yii;

class UserFollower extends Follow
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'user_username',
            'user_verified',
            'user_real_name',
            'user_picture_file_base',
            'user_picture_storage',
            'user_profession_id',
            'user_profession_title',
            'user_speciality_id',
            'user_speciality_title',
            'user_following',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id' => function ($model) {
                return $model->follower_id;
            },
            'username' => function ($model) {
                return $model->user_username;
            },
            'verified' => function ($model) {
                return $model->user_verified > 0;
            },
            'real_name' => function ($model) {
                return $model->user_real_name;
            },
            'screen_name' => function ($model) {
                return empty($model->user_real_name) ? $model->user_username : $model->user_real_name;
            },
            'screen_speciality' => function ($model) {
                return User::getScreenSpeciality(
                    $model->user_profession_id,
                    $model->user_profession_title,
                    $model->user_speciality_title
                );
            },
            'following' => function ($model) {
                return $model->user_following > 0;
            },
            'profession' => function ($model) {
                return [
                    'id' => $model->user_profession_id,
                    'title' => $model->user_profession_title,
                ];
            },
            'speciality' => function ($model) {
                return [
                    'id' => $model->user_speciality_id,
                    'title' => $model->user_speciality_title,
                ];
            },
            'picture' => function ($model) {
                if (empty($model->user_picture_file_base)) {
                    $picture_url = Image::storageBaseUrl(STORAGE_AMAZON) . '/profiles/default_150.jpg';
                } else {
                    $picture_url = Image::storageBaseUrl($model->user_picture_storage) . '/profiles/' . $model->user_picture_file_base . '_150.jpg';
                }

                return [
                    'url' => $picture_url,
                    'width' => 150,
                    'height' => 150,
                ];
            },
            'created_at' => function ($model) {
                return Yii::$app->formatter->asDatetime($model->created_at);
            },
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'follower_id']);
    }

    /**
     * @param $user_id
     * @return \yii\db\ActiveQuery
     */
    public static function findByUserId($user_id)
    {
        $query = self::find()
            ->select([
                'follow.follower_id',
                'follow.created_at',
                'user_username' => 'user.username',
                'user_verified' => 'user.verified',
                'user_real_name' => 'user.real_name',
                'user_picture_file_base' => 'user.picture_file_base',
                'user_picture_storage' => 'user.picture_storage',
                'user_profession_id' => 'user.profession_id',
                'user_profession_title' => 'profession.title',
                'user_speciality_id' => 'user.speciality_id',
                'user_speciality_title' => 'speciality.title',
                'user_following' => 'f.id',
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
            ->where(['follow.following_id' => $user_id])
            ->orderBy(['follow.created_at' => SORT_DESC]);

        $query->leftJoin(
            'follow f',
            'f.following_id = follow.follower_id AND f.follower_id = :user_id',
            [':user_id' => Yii::$app->user->id]
        );

        return $query;
    }
}
