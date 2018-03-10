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

use common\models\Activity;
use common\models\Follow;
use common\models\User;
use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParsePush;
use Yii;
use yii\base\Model;

class FollowForm extends Model
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
            ['user_id', 'required'],
            ['user_id', 'integer'],
            ['user_id', function ($attribute, $params) {
                if ($this->$attribute == Yii::$app->user->id) {
                    $this->addError(
                        $attribute,
                        Yii::t('yii', '{attribute} is invalid.', ['attribute' => $this->getAttributeLabel($attribute)])
                    );
                }
            }],
            ['user_id', 'exist', 'targetClass' => User::className(), 'targetAttribute' => 'id'],
            [
                'user_id',
                'unique',
                'targetClass' => Follow::className(),
                'targetAttribute' => 'following_id',
                'filter' => ['follower_id' => Yii::$app->user->id],
                'message' => 'Bu kullanıcı zaten takip edilmektedir.',
            ],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $follow = new Follow();
            $follow->setAttributes([
                'follower_id' => Yii::$app->user->id,
                'following_id' => $this->user_id,
            ], false);

            if ($follow->save()) {
                if ($this->user_id != Yii::$app->user->id) {
                    $activity = new Activity();
                    $activity->setAttributes([
                        'user_id' => $this->user_id,
                        'type' => Activity::TYPE_FOLLOW,
                        'source_id' => Yii::$app->user->id,
                        'object_id' => $this->user_id,
                        'object_type' => Activity::OBJECT_TYPE_USER,
                    ], false);
                    $activity->save();

                    try {
                        ParseClient::initialize(
                            Yii::$app->params['parse']['applicationId'],
                            Yii::$app->params['parse']['restApiKey'],
                            Yii::$app->params['parse']['masterKey']
                        );

                        ParsePush::send([
                            'channels' => ['u' . $this->user_id],
                            'data' => [
                                'badge' => 'Increment',
                                'alert' => sprintf('%s sizi takip etmeye başladı.', Yii::$app->user->identity->username),
                            ],
                        ]);
                    } catch (ParseException $e) {
                        // do nothing
                    }
                }

                return [
                    'id' => $follow->getPrimaryKey(),
                    'created_at' => Yii::$app->formatter->asDatetime($follow->getAttribute('created_at')),
                ];
            }
        }

        return false;
    }
}
