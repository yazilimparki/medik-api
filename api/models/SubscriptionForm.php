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

use common\models\Category;
use common\models\Subscription;
use Yii;
use yii\base\Model;

class SubscriptionForm extends Model
{
    public $category_id;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'category_id' => 'Kategori',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['category_id', 'required'],
            ['category_id', 'integer'],
            [
                'category_id',
                'exist',
                'targetClass' => Category::className(),
                'targetAttribute' => 'id',
                'filter' => ['not', ['type' => Category::TYPE_ANATOMY, 'parent_id' => 0]],
            ],
            [
                'category_id',
                'unique',
                'targetClass' => Subscription::className(),
                'filter' => ['user_id' => Yii::$app->user->id],
            ],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $subscription = new Subscription();
            $subscription->setAttributes([
                'user_id' => Yii::$app->user->id,
                'category_id' => $this->category_id,
            ], false);

            if ($subscription->save()) {
                return [
                    'id' => $subscription->getPrimaryKey(),
                    'created_at' => Yii::$app->formatter->asDatetime($subscription->getAttribute('created_at')),
                ];
            }
        }

        return false;
    }
}
