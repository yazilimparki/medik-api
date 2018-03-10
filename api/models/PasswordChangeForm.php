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

class PasswordChangeForm extends Model
{
    public $current_password;
    public $password;
    public $password_repeat;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'current_password' => 'Parola',
            'password' => 'Yeni Parola',
            'password_repeat' => 'Yeni Parola (Tekrar)',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['current_password', 'password', 'password_repeat'], 'trim'],
            [['current_password', 'password', 'password_repeat'], 'required'],
            ['password', 'string', 'min' => 6],
            ['password', 'compare'],
            [
                'current_password',
                function ($attribute, $params) {
                    // todo: cektirmek yerine user->identify->password olabilir?
                    $user = User::find()->select('password')->where(['id' => Yii::$app->user->id])->one();

                    if (Yii::$app->security->validatePassword($this->$attribute, $user->getAttribute('password')) === false) {
                        $this->addError(
                            $attribute,
                            Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)])
                        );
                    }
                }
            ],
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            /**
             * @var $user \common\models\User
             */
            $user = User::findOne(Yii::$app->user->id);
            $user->setPassword($this->password);

            if ($user->save()) {
                return [
                    'id' => Yii::$app->user->id,
                    'updated_at' => Yii::$app->formatter->asDatetime($user->getAttribute('updated_at')),
                ];
            }
        }

        return false;
    }
}
