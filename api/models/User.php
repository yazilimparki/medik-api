<?php
namespace api\models;

use common\helpers\Image;
use Yii;

// todo: official account badge
// todo: leader badge
class User extends \common\models\User
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'user_following',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id',
            'username',
            'email',
            'profession' => function ($model) {
                if ($model->profession_id > 0 && isset($model->profession) && is_object($model->profession)) {
                    return [
                        'id' => $model->profession->id,
                        'title' => $model->profession->title,
                    ];
                } else {
                    return null;
                }
            },
            'speciality' => function ($model) {
                if ($model->speciality_id > 0 && isset($model->speciality) && is_object($model->speciality)) {
                    return [
                        'id' => $model->speciality->id,
                        'title' => $model->speciality->title,
                    ];
                } else {
                    return null;
                }
            },
            'real_name',
            'screen_name',
            'screen_speciality',
            'city' => function ($model) {
                if ($model->city_id > 0 && isset($model->city) && is_object($model->city)) {
                    return [
                        'id' => $model->city->id,
                        'title' => $model->city->title,
                    ];
                } else {
                    return null;
                }
            },
            'bio',
            'institution',
            'web',
            'picture' => function ($model) {
                if (empty($model->picture_file_base)) {
                    $picture_url = Image::storageBaseUrl(STORAGE_AMAZON) . '/profiles/default_150.jpg';
                } else {
                    $picture_url = Image::storageBaseUrl($model->picture_storage) . '/profiles/' . $model->picture_file_base . '_150.jpg';
                }

                return [
                    'url' => $picture_url,
                    'width' => 150,
                    'height' => 150,
                ];
            },
            'verified' => function ($model) {
                return $model->verified > 0;
            },
            'following' => function ($model) {
                return $model->id != Yii::$app->user->id && isset($model->user_following) && $model->user_following > 0;
            },
            'can_verify' => function ($model) {
                if ($model->id != Yii::$app->user->id) {
                    return false;
                }

                if ($model->verified) {
                    return false;
                }

                if (isset($model->verification) && is_object($model->verification)) {
                    return false;
                }

                return true;
            },
            'can_send_media' => function ($model) {
                if ($model->profession_id > 0 && in_array($model->profession_id, [1, 2, 3, 4, 5, 6])) {
                    return true;
                }

                if ($model->id == 1) {
                    return true;
                }

                return false;
            },
            'counts' => function ($model) {
                return [
                    'comments' => $model->comment_count,
                    'favorites' => $model->favorite_count,
                    'followers' => $model->follower_count,
                    'following' => $model->following_count,
                    'media' => $model->media_count,
                ];
            },
            'notifications' => function ($model) {
                return [
                    'comments' => $model->notify_comments > 0,
                    'favorites' => $model->notify_favorites > 0,
                    'followers' => $model->notify_followers > 0,
                ];
            },
            'subscriptions' => function ($model) {
                return [
                    'monthly' => $model->subscribe_monthly > 0,
                    'weekly' => $model->subscribe_weekly > 0,
                ];
            },
            'created_at' => function ($model) {
                return Yii::$app->formatter->asDatetime($model->created_at);
            },
        ];
    }

    /**
     * @param $id
     * @return \yii\db\ActiveQuery
     */
    public static function findById($id)
    {
        $query = self::find()
            ->select([
                'user.id',
                'user.username',
                'user.email',
                'user.profession_id',
                'user.speciality_id',
                'user.real_name',
                'user.city_id',
                'user.bio',
                'user.institution',
                'user.web',
                'user.verified',
                'user.picture_file_base',
                'user.picture_storage',
                'user.comment_count',
                'user.favorite_count',
                'user.follower_count',
                'user.following_count',
                'user.media_count',
                'user.notify_comments',
                'user.notify_favorites',
                'user.notify_followers',
                'user.subscribe_monthly',
                'user.subscribe_weekly',
                'user.created_at',
            ])
            ->with([
                'profession' => function ($subQuery) {
                    /** @var $subQuery \yii\db\ActiveQuery */
                    $subQuery->select(['id', 'title']);
                },
                'speciality' => function ($subQuery) {
                    /** @var $subQuery \yii\db\ActiveQuery */
                    $subQuery->select(['id', 'title']);
                },
                'city' => function ($subQuery) {
                    /** @var $subQuery \yii\db\ActiveQuery */
                    $subQuery->select(['id', 'title']);
                },
            ])
            ->where(['user.id' => $id]);

        if ($id != Yii::$app->user->id) {
            $query
                ->addSelect(['user_following' => 'follow.id'])
                ->leftJoin(
                    'follow',
                    'follow.following_id = user.id AND follow.follower_id = :user_id',
                    [':user_id' => Yii::$app->user->id]
                );
        } else {
            $query->with([
                'verification' => function ($subQuery) {
                    /** @var $subQuery \yii\db\ActiveQuery */
                    $subQuery->select(['id']);
                },
            ]);
        }

        return $query;
    }
}