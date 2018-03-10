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

use common\models\Token;
use common\models\User;
use Yii;
use yii\base\Exception;
use yii\base\Model;

class TokenForm extends Model
{
    public $email;
    public $password;
    public $client_type;
    public $client_id;

    private $_user = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'trim'],
            [['email', 'password', 'client_type', 'client_id'], 'required'],
            ['email', 'email'],
            ['client_type', 'in', 'range' => ['iphone', 'ipad', 'android', 'web']],
            ['client_id', 'match', 'pattern' => '/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/'],
            ['password', 'validatePassword'],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $user_id = $user->getPrimaryKey();

            $token = Token::findOne([
                'user_id' => $user_id,
                'client_type' => $this->client_type,
                'client_id' => $this->client_id,
            ]);

            if (is_null($token)) {
                $token = new Token();
                $token->setAttributes([
                    'client_type' => $this->client_type,
                    'client_id' => $this->client_id,
                ], false);
                $token->generateToken();

                $user->link('tokens', $token);
            }

            return [
                'id' => $token->getPrimaryKey(),
                'token' => $token->getAttribute('token'),
                'user' => [
                    'id' => $user_id,
                    'verified' => $user->getAttribute('verified') > 0,
                ],
                'created_at' => Yii::$app->formatter->asDatetime($token->getAttribute('created_at')),
            ];
        }

        return false;
    }

    /**
     * @return \common\models\User|null
     */
    public function getUser()
    {
        if (is_null($this->_user)) {
            $this->_user = User::findOne(['email' => $this->email]);
        }

        return $this->_user;
    }

    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors() === false) {
            $user = $this->getUser();

            if (is_null($user) || $user->validatePassword($this->$attribute) === false) {
                $this->addError($attribute, Yii::t('app', 'Incorrect e-mail address or password.'));
            }
        }
    }
}
