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

use common\models\User;
use Yii;
use yii\base\Model;

class SettingsForm extends Model
{
    public $notify_comments;
    public $notify_favorites;
    public $notify_followers;
    public $subscribe_monthly;
    public $subscribe_weekly;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'notify_comments' => 'Yeni Yorum Bildirimleri',
            'notify_favorites' => 'Yeni Favori Bildirimleri',
            'notify_followers' => 'Yeni Takipçi Bildirimleri',
            'subscribe_monthly' => 'Aylık Bülten Üyeliği',
            'subscribe_weekly' => 'Haftalık Bülten Üyeliği',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['notify_comments', 'notify_favorites', 'notify_followers', 'subscribe_monthly', 'subscribe_weekly'],
                'boolean',
            ],
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            $user = User::find()->select(['id', 'updated_at'])->where(['id' => Yii::$app->user->id])->one();

            if ($user === null) {
                throw new \Exception('Failed to find user object.');
            }

            $attributes = [];

            if ($this->notify_comments !== null) {
                $attributes['notify_comments'] = $this->notify_comments;
            }

            if ($this->notify_favorites !== null) {
                $attributes['notify_favorites'] = $this->notify_favorites;
            }

            if ($this->notify_followers !== null) {
                $attributes['notify_followers'] = $this->notify_followers;
            }

            if ($this->subscribe_monthly !== null) {
                $attributes['subscribe_monthly'] = $this->subscribe_monthly;
            }

            if ($this->subscribe_weekly !== null) {
                $attributes['subscribe_weekly'] = $this->subscribe_weekly;
            }

            if (count($attributes) > 0) {
                 $user->setAttributes($attributes, false);

                if (!$user->save()) {
                    throw new \Exception('Failed to update user object.');
                }
            }

            return [
                'id' => Yii::$app->user->id,
                'updated_at' => Yii::$app->formatter->asDatetime($user->getAttribute('updated_at')),
            ];
        }

        return false;
    }
}
