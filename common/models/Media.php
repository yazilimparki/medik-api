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

use ostapetc\yii2\behaviors\CounterCacheBehavior;
use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParsePush;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Media extends ActiveRecord
{
    const TYPE_SINGLE = 0;
    const TYPE_MULTIPLE = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'media';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => CounterCacheBehavior::className(),
                'counters' => [
                    [
                        'model' => User::className(),
                        'attribute' => 'media_count',
                        'foreignKey' => 'user_id',
                    ]
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['backend'] = ['caption', 'verified'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if ($insert === false && isset($changedAttributes['verified']) && $changedAttributes['verified'] == 0 && $this->getAttribute('verified') == 1) {
            $followers = Follow::find()
                ->select(['follower_id'])
                ->where(['following_id' => $this->getAttribute('user_id')])
                ->asArray()
                ->all();

            if (count($followers) > 0) {
                $channels = ArrayHelper::getColumn($followers, function ($element) {
                    return 'u' . $element['follower_id'];
                });

                $user = User::find()
                    ->select('username')
                    ->where(['id' => $this->getAttribute('user_id')])
                    ->one();

                try {
                    ParseClient::initialize(
                        Yii::$app->params['parse']['applicationId'],
                        Yii::$app->params['parse']['restApiKey'],
                        Yii::$app->params['parse']['masterKey']
                    );

                    ParsePush::send([
                        'channels' => $channels,
                        'data' => [
                            // 'badge' => 'Increment',
                            'alert' => sprintf('%s yeni bir fotoğraf yükledi.', $user->getAttribute('username')),
                        ],
                    ]);
                } catch (ParseException $e) {
                    // do nothing
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        $this->getDb()
            ->createCommand()
            ->delete('category_media', ['media_id' => $this->getPrimaryKey()])
            ->execute();

        $comments = Comment::find()
            ->select('id')
            ->where(['media_id' => $this->getPrimaryKey()])
            ->all();

        foreach ($comments as $comment) {
            $comment->delete();
        }

        Activity::deleteAll(['object_id' => $this->getPrimaryKey(), 'object_type' => Activity::OBJECT_TYPE_MEDIA]);
        Favorite::deleteAll(['media_id' => $this->getPrimaryKey()]);
        MediaReport::deleteAll(['object_id' => $this->getPrimaryKey()]);

        $images = Image::find()
            ->select([
                'id',
                'image_file_base',
                'image_file_extension',
                'image_storage',
            ])
            ->where(['media_id' => $this->getPrimaryKey()])
            ->all();

        foreach ($images as $image) {
            $image->delete();
        }
    }

    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->viaTable('category_media', ['media_id' => 'id']);
    }

    public function getImages()
    {
        return $this->hasMany(Image::className(), ['media_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
