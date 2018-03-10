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
use common\models\Verification;
use Yii;
use yii\base\Model;

class VerificationForm extends Model
{
    public $user_id;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Kullanıcı',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'user_id',
                'exist',
                'targetClass' => User::className(),
                'targetAttribute' => 'id',
                'filter' => ['verified' => 0],
            ],
            [
                'user_id',
                'unique',
                'targetClass' => Verification::className(),
                'message' => 'Daha önce yapılmış doğrulama isteğiniz bulunmaktadır.',
            ],
        ];
    }

    public function create()
    {
        $this->user_id = Yii::$app->user->id;

        if ($this->validate()) {
            $verification = new Verification();
            $verification->setAttributes([
                'user_id' => $this->user_id,
            ], false);

            if ($verification->save()) {
                /** @var $user \yii\db\ActiveRecord */
                $user = User::findOne(Yii::$app->user->id);

                /**
                 * @var $message \yii\swiftmailer\Message
                 */
                $message = Yii::$app->mailer->compose([
                    'html' => 'verification-html',
                    'text' => 'verification-text',
                ], [
                    'user' => $user,
                ]);

                $message->getSwiftMessage()->getHeaders()->addTextHeader('X-MC-Tags', 'api,verification');
                $message->setFrom([Yii::$app->params['verificationAddress'] => Yii::$app->params['verificationName']])
                    ->setTo($user->getAttribute('email'))
                    ->setSubject('Example Profil Doğrulama')
                    ->send();

                $body = sprintf('%s isimli kullanıcı doğrulama isteği gönderdi.', Yii::$app->user->identity->username);

                try {
                    Yii::$app->mailer->compose()
                        ->setFrom([Yii::$app->params['fromAddress'] => Yii::$app->params['fromName']])
                        ->setTo('support@example.com')
                        ->setSubject('[Example] Yeni doğrulama isteği')
                        ->setTextBody($body)
                        ->setHtmlBody($body)
                        ->send();
                } catch (\Swift_TransportException $e) {
                    // do nothing
                }

                return [
                    'id' => $verification->getPrimaryKey(),
                    'created_at' => Yii::$app->formatter->asDatetime($verification->getAttribute('created_at')),
                ];
            }
        }

        return false;
    }
}
