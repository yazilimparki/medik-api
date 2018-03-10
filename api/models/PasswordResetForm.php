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

class PasswordResetForm extends Model
{
    public $email;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-Posta Adresi',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
        ];
    }

    public function requestReset()
    {
        if ($this->validate()) {
            /**
             * @var $user \common\models\User
             */
            $user = User::findOne(['email' => $this->email]);

            if ($user) {
                $token = md5($user->getAttribute('id') . $user->getAttribute('updated_at'));

                /**
                 * @var $message \yii\swiftmailer\Message
                 */
                $message = Yii::$app->mailer->compose([
                    'html' => 'reset-password-html',
                    'text' => 'reset-password-text',
                ], [
                    'user' => $user,
                    'token' => $token,
                ]);

                $message->getSwiftMessage()->getHeaders()->addTextHeader('X-MC-Tags', 'api,reset-password');
                $message->setFrom([Yii::$app->params['fromAddress'] => Yii::$app->params['fromName']])
                    ->setTo($user->getAttribute('email'))
                    ->setSubject('Parolanızı Sıfırlayın')
                    ->send();
            }

            return true;
        }

        return false;
    }
}
