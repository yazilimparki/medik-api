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

namespace app\models;

use common\models\Activity;
use common\models\Category;
use common\models\City;
use common\models\Device;
use common\models\Follow;
use common\models\Speciality;
use common\models\Subscription;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;

class UserForm extends Model
{
    public $username;
    public $password;
    public $email;
    public $speciality_id;
    public $real_name;
    public $city_id;
    public $bio;
    public $institution;
    public $web;

    /**
     * @inheritdoc
     */
    public function attributeLabel()
    {
        return [
            'username' => 'Kullanıcı Adı',
            'password' => 'Parola',
            'email' => 'E-Posta Adresi',
            'speciality_id' => 'Uzmanlık Alanı',
            'real_name' => 'Ad Soyad',
            'city_id' => 'Şehir',
            'bio' => 'Biyografi',
            'institution' => 'Kurum',
            'web' => 'Web Adresi',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'email', 'real_name', 'bio', 'institution', 'web'], 'trim'],

            ['username', 'string', 'length' => [4, 20]],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9-]+$/'],
            ['email', 'email'],

            [['speciality_id', 'city_id'], 'integer'],
            ['speciality_id', 'exist', 'targetClass' => Speciality::className(), 'targetAttribute' => 'id'],
            ['city_id', 'exist', 'targetClass' => City::className(), 'targetAttribute' => 'id'],
            ['password', 'string', 'min' => 6],

            /**
             * Create only.
             */
            [['username', 'password', 'email', 'speciality_id'], 'required', 'on' => 'create'],
            ['username', 'unique', 'targetClass' => User::className(), 'on' => 'create'],
            ['email', 'unique', 'targetClass' => User::className(), 'on' => 'create'],

            /**
             * Update only.
             */
            [
                'username',
                'unique',
                'targetClass' => User::className(),
                'filter' => ['not', ['id' => Yii::$app->user->id]],
                'on' => 'update',
            ],
            [
                'email',
                'unique',
                'targetClass' => User::className(),
                'filter' => ['not', ['id' => Yii::$app->user->id]],
                'on' => 'update',
            ],
            ['real_name', 'string', 'length' => [6, 50]],
            ['bio', 'string', 'max' => 250],
            ['institution', 'string', 'max' => 250],
            ['web', 'url', 'defaultScheme' => 'http'],
        ];
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            'create' => ['username', 'password', 'email', 'speciality_id'],
            'update' => ['username', 'password', 'email', 'speciality_id', 'real_name', 'city_id', 'bio', 'institution', 'web'],
        ]);
    }

    public function create()
    {
        $this->setScenario('create');

        if ($this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $attributes = [
                    'username' => $this->username,
                    'email' => $this->email,
                    'notify_comments' => 1,
                    'notify_favorites' => 1,
                    'notify_followers' => 1,
                    'subscribe_monthly' => 1,
                    'subscribe_weekly' => 1,
                ];

                if (!empty($this->speciality_id)) {
                    $speciality = Speciality::find()
                        ->select('profession_id')
                        ->where(['id' => $this->speciality_id])
                        ->one();

                    $attributes['profession_id'] = $speciality->getAttribute('profession_id');
                    $attributes['speciality_id'] = $this->speciality_id;
                }

                $user = new User();
                $user->setAttributes($attributes, false);
                $user->setPassword($this->password);

                if ($user->save() === false) {
                    throw new \Exception('Failed to create user object.');
                }

                /**
                 * Subscribe to all categories.
                 * ActiveRecord `link` method does not work with multiple models.
                 */
                $categories = Category::find()
                    ->select('id')
                    ->where(['not', ['type' => Category::TYPE_ANATOMY, 'parent_id' => 0]])
                    ->all();

                foreach ($categories as $category) {
                    $subscription = new Subscription();
                    $subscription->setAttributes([
                        'category_id' => $category->getAttribute('id'),
                    ], false);

                    $user->link('subscriptions', $subscription);
                }

                /**
                 * We will follow you.
                 */
                $follower = new Follow();
                $follower->setAttributes([
                    'follower_id' => 1,
                    'following_id' => $user->getPrimaryKey(),
                ], false);

                $follower->save(false);

                $activity = new Activity();
                $activity->setAttributes([
                    'user_id' => $user->getPrimaryKey(),
                    'type' => Activity::TYPE_FOLLOW,
                    'source_id' => 1,
                    'object_id' => $user->getPrimaryKey(),
                    'object_type' => Activity::OBJECT_TYPE_USER,
                ], false);

                $activity->save(false);

                $device = new Device();

                /**
                 * Quick and silly way to determine user device type.
                 * todo: improve
                 */
                if (strstr(Yii::$app->request->userAgent, 'okhttp/2.5.0')) {
                    $device->setAttributes([
                        'user_id' => $user->getPrimaryKey(),
                        'name' => 'Android',
                    ], false);
                } else if (strstr(Yii::$app->request->userAgent, 'example/com.example')) {
                    $device->setAttributes([
                        'user_id' => $user->getPrimaryKey(),
                        'name' => 'iPhone',
                    ], false);
                } else {
                    $device->setAttributes([
                        'user_id' => $user->getPrimaryKey(),
                        'name' => 'Unknown',
                    ], false);
                }

                $user->link('devices', $device);

                $transaction->commit();

                /**
                 * @var $message \yii\swiftmailer\Message
                 */
                $message = Yii::$app->mailer->compose([
                    'html' => 'welcome-html',
                    'text' => 'welcome-text',
                ], [
                    'user' => $user,
                ]);

                try {
                    $message->getSwiftMessage()->getHeaders()->addTextHeader('X-MC-Tags', 'api,welcome');
                    $message->setFrom([Yii::$app->params['fromAddress'] => Yii::$app->params['fromName']])
                        ->setTo($user->getAttribute('email'))
                        ->setSubject('Example Servisine Hoşgeldiniz')
                        ->send();
                } catch (\Swift_TransportException $e) {
                    // do nothing
                }

                return [
                    'id' => $user->getPrimaryKey(),
                    'verified' => false,
                    'created_at' => Yii::$app->formatter->asDatetime($user->getAttribute('created_at')),
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }

        return false;
    }

    public function update()
    {
        /**
         * @var $user \common\models\User;
         */
        $user = User::findOne(Yii::$app->user->id);

        if (empty($user)) {
            throw new NotFoundHttpException('User object not found.');
        }

        $this->setScenario('update');

        if ($this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $attributes = [];

                if (!empty($this->username)) {
                    $attributes['username'] = $this->username;
                }

                if (!empty($this->password)) {
                    $attributes['password'] = Yii::$app->security->generatePasswordHash($this->password);
                }

                if (!empty($this->email)) {
                    $attributes['email'] = $this->email;
                }

                if (!empty($this->speciality_id)) {
                    $speciality = Speciality::find()
                        ->select('profession_id')
                        ->where(['id' => $this->speciality_id])
                        ->one();

                    $attributes['profession_id'] = $speciality->getAttribute('profession_id');
                    $attributes['speciality_id'] = $this->speciality_id;
                }

                /* Focking android app sending empty data */

                if (!empty($this->real_name)) {
                    $attributes['real_name'] = $this->real_name;
                }

                if (!empty($this->city_id)) {
                    $attributes['city_id'] = $this->city_id;
                }

                if (!empty($this->bio)) {
                    $attributes['bio'] = $this->bio;
                }

                if (!empty($this->institution)) {
                    $attributes['institution'] = $this->institution;
                }

                if (!empty($this->web)) {
                    $attributes['web'] = $this->web;
                }

                if (!empty($attributes)) {
                    $user->setAttributes($attributes, false);

                    if ($user->save() === false) {
                        throw new \Exception('Failed to update user object.');
                    }
                }

                $transaction->commit();

                return [
                    'id' => $user->getPrimaryKey(),
                    'updated_at' => Yii::$app->formatter->asDatetime($user->getAttribute('updated_at')),
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }

        return false;
    }
}
