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
use common\models\Category as CommonCategory;
use Yii;
use yii\helpers\Url;

class Category extends CommonCategory
{
    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'user_subscribed',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [
            'id',
            /* 'type' => function ($model) {
                return $model->type == CommonCategory::TYPE_ANATOMY ? 'anatomy' : 'speciality';
            }, */
            'type' => function ($model) {
                return 'speciality';
            },
            'title',
            'cover' => function ($model) {
                return empty($model->cover_file_base) ? null : [
                    'url' => Image::storageBaseUrl(STORAGE_AMAZON) . '/categories/' . $model->cover_file_base . '_320.jpg',
                    'width' => 320,
                    'height' => 200,
                ];
            },
            'subscribed' => function ($model) {
                return $model->user_subscribed > 0;
            },
            'has_children' => function ($model) {
                return false;
            },
            'children' => function ($model) {
                return [];

                /*
                 * todo: categories do not have any children at the moment
                if ($model->parent_id > 0) {
                    return [];
                }

                $children = [];

                foreach ($model->children as $child) {
                    $children[] = [
                        'id' => $child->id,
                        'title' => $child->title,
                        'cover' => empty($child->cover_file_base) ? null : [
                            'url' => Url::to('@web/uploads/categories/' . $child->cover_file_base . '_320.jpg', true),
                            'width' => 320,
                            'height' => 200,
                        ],
                        'subscribed' => $child->user_subscribed > 0,
                    ];
                }

                return $children;
                */
            },
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findAllWithSubscription()
    {
        return self::find()
            ->select([
                'category.id',
                'category.title',
                'category.cover_file_base',
                'user_subscribed' => 'subscription.id',
            ])
            ->leftJoin(
                'subscription',
                'subscription.category_id = category.id AND subscription.user_id = :user_id',
                [':user_id' => Yii::$app->user->id]
            )
            ->orderBy(['type' => SORT_ASC, 'title' => SORT_ASC]);

        /*
        return self::find()
            ->select([
                'category.id',
                'category.parent_id',
                'category.type',
                'category.title',
                'category.cover_file_base',
                'user_subscribed' => 'subscription.id',
            ])
            ->with([
                'children' => function ($query) {
                    $query
                        ->select([
                            'category.id',
                            'category.parent_id',
                            'category.title',
                            'category.cover_file_base',
                            'user_subscribed' => 'subscription.id'
                        ])
                        ->leftJoin(
                            'subscription',
                            'subscription.category_id = category.id AND subscription.user_id = :user_id',
                            [':user_id' => Yii::$app->user->id]
                        )
                        ->orderBy(['category.title' => SORT_ASC]);
                }
            ])
            ->leftJoin(
                'subscription',
                'subscription.category_id = category.id AND subscription.user_id = :user_id',
                [':user_id' => Yii::$app->user->id]
            )
            ->where(['parent_id' => 0])
            ->orderBy(['type' => SORT_ASC, 'title' => SORT_ASC]);
        */
    }
}
