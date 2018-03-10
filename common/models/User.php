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

namespace common\models;

use filsh\yii2\oauth2server\models\OauthAccessTokens;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use OAuth2\Storage\UserCredentialsInterface;

class User extends ActiveRecord implements IdentityInterface, UserCredentialsInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return self::find()
            ->innerJoinWith('tokens', false)
            ->where(['oauth_access_tokens.access_token' => $token])
            ->one();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->getAttribute('authentication_key');
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * UserCredentialsInterface
     *
     * @inheritdoc
     */
    public function checkUserCredentials($username, $password)
    {
        $user = self::find()
            ->select('password')
            ->where(['username' => $username])
            ->orWhere(['email' => $username])
            ->one();

        return $user && Yii::$app->security->validatePassword($password, $user->getAttribute('password'));
    }

    /**
     * UserCredentialsInterface
     *
     * @inheritdoc
     */
    public function getUserDetails($username)
    {
        $user = self::find()
            ->select('id')
            ->where(['username' => $username])
            ->orWhere(['email' => $username])
            ->one();

        if ($user) {
            return ['user_id' => $user->getAttribute('id')];
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (isset($this->real_name)) {
                $this->real_name = preg_replace('/\s+/', ' ', trim($this->real_name));
                // todo: buyuk kucuk I i harfi olayi
                $this->real_name = mb_convert_case($this->real_name, MB_CASE_TITLE, 'utf-8');
            }

            return true;
        }

        return false;
    }

    public function setPassword($password)
    {
        $this->setAttribute('password', Yii::$app->security->generatePasswordHash($password));
    }

    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['user_id' => 'id']);
    }

    public function getFavorites()
    {
        return $this->hasMany(Media::className(), ['id' => 'media_id'])->viaTable('favorite', ['user_id' => 'id']);
    }

    public function getMedia()
    {
        return $this->hasMany(Media::className(), ['user_id' => 'id']);
    }

    public function getProfession()
    {
        return $this->hasOne(Profession::className(), ['id' => 'profession_id']);
    }

    public function getSpeciality()
    {
        return $this->hasOne(Speciality::className(), ['id' => 'speciality_id']);
    }

    public function getSubscriptions()
    {
        return $this->hasMany(Subscription::className(), ['user_id' => 'id']);
    }

    public function getTokens()
    {
        return $this->hasMany(OauthAccessTokens::className(), ['user_id' => 'id']);
    }

    public function getDevices()
    {
        return $this->hasMany(Device::className(), ['user_id' => 'id']);
    }

    public function getVerification()
    {
        return $this->hasOne(Verification::className(), ['user_id' => 'id']);
    }

    public function getScreen_name()
    {
        return empty($this->real_name) ? $this->getAttribute('username') : $this->getAttribute('real_name');
    }

    public function getScreen_speciality()
    {
        return self::getScreenSpeciality(
            isset($this->profession) && isset($this->profession->id) ? $this->profession->id : null,
            isset($this->profession) && isset($this->profession->title) ? $this->profession->title : null,
            isset($this->speciality) && isset($this->speciality->title) ? $this->speciality->title : null
        );
    }

    public static function getScreenSpeciality($profession_id, $profession_title, $speciality_title)
    {
        if (empty($profession_id) || empty($profession_title)) {
            return '';
        }

        if (empty($speciality_title)) {
            return $profession_title;
        }

        if ($speciality_title == 'Diğer') {
            return $profession_title;
        }

        switch ($profession_id) {
            case 1: // Hekim
                if (strstr($speciality_title, 'Hekim')) {
                    $suffix = '';
                }
                else {
                    $suffix = ' Hekimi';
                }
                break;
            case 2: // Asistan
                $suffix = ' Asistanı';
                break;
            case 3: // Hemşire
                if (strstr($speciality_title, 'Hemşire')) {
                    $suffix = '';
                }
                $suffix = ' Hemşiresi';
                break;
            case 6: // Ögrenci
                $suffix = ' Öğrencisi';
                break;
            default:
                $suffix = '';
                break;
        }

        return $speciality_title . $suffix;
    }
}
