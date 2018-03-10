<?php
namespace api\models;

use common\helpers\Image;
use common\models\Comment;
use Yii;

class MediaComment extends Comment
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
            'created_at' => function ($model) {
                return Yii::$app->formatter->asDatetime($model->created_at);
            },
        ];
    }

    /**
     * @param $media_id
     * @return \yii\db\ActiveQuery
     */
    public static function findByMediaId($media_id)
    {
        return self::find()
            ->select([
                'comment.id',
                'comment.user_id',
                'comment.text',
                'comment.score',
                'comment.created_at',
                'user_username' => 'user.username',
                'user_verified' => 'user.verified',
                'user_real_name' => 'user.real_name',
                'user_picture_file_base' => 'user.picture_file_base',
                'user_picture_storage' => 'user.picture_storage',
                'user_profession_id' => 'user.profession_id',
                'user_profession_title' => 'profession.title',
                'user_speciality_id' => 'user.speciality_id',
                'user_speciality_title' => 'speciality.title',
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
            ->where(['comment.media_id' => $media_id])
            ->orderBy(['comment.created_at' => SORT_ASC]);
    }
}