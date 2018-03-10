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

use yii\base\Model;
use Yii;

class ContactForm extends Model
{
    public $firstname;
    public $lastname;
    public $email;
    public $message;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'firstname' => 'Ad',
            'lastname' => 'Soyad',
            'email' => 'E-Posta Adresi',
            'message' => 'Mesja',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firstname', 'lastname', 'email', 'message'], 'trim'],
            [['firstname', 'lastname', 'email', 'message'], 'required'],
            ['email', 'email'],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            /**
             * @var $message \yii\swiftmailer\Message
             */
            $message = Yii::$app->mailer->compose([
                'html' => 'contact-form-html',
                'text' => 'contact-form-text',
            ], [
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'message' => $this->message,
            ]);

            $message->getSwiftMessage()->getHeaders()->addTextHeader('X-MC-Tags', 'api,contact-form');
            $message->setFrom([Yii::$app->params['fromAddress'] => Yii::$app->params['fromName']])
                ->setTo('support@example.com')
                ->setSubject('[Example] İletişim Mesajı')
                ->send();

            return [
                'status' => true,
            ];
        }

        return false;
    }
}
