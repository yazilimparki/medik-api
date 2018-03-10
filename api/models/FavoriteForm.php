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
use common\models\Favorite;
use common\models\Media;
use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParsePush;
use Yii;
use yii\base\Model;

class FavoriteForm extends Model
{
    public $media_id;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'media_id' => 'Medya',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['media_id', 'required'],
            ['media_id', 'integer'],
            ['media_id', 'exist', 'targetClass' => Media::className(), 'targetAttribute' => 'id'],
            /*
             * Ahmet'in istegi uzerine favorite ettiyse tekrar etmis gibi response donecek.
            [
                'media_id',
                'unique',
                'targetClass' => Favorite::className(),
                'filter' => ['user_id' => Yii::$app->user->id],
            ],
            */
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $favorite = Favorite::find()
                ->select([
                    'id',
                    'created_at',
                ])
                ->where([
                    'media_id' => $this->media_id,
                    'user_id' => Yii::$app->user->id,
                ])
                ->one();

            if ($favorite === null) {
                $favorite = new Favorite();
                $favorite->setAttributes([
                    'user_id' => Yii::$app->user->id,
                    'media_id' => $this->media_id,
                ], false);

                if ($favorite->save()) {
                    $media = Media::find()
                        ->select(['user_id'])
                        ->where(['id' => $this->media_id])
                        ->one();

                    if ($media->getAttribute('user_id') != Yii::$app->user->id) {
                        $activity = new Activity();
                        $activity->setAttributes([
                            'user_id' => $media->getAttribute('user_id'),
                            'type' => Activity::TYPE_FAVORITE,
                            'source_id' => Yii::$app->user->id,
                            'object_id' => $this->media_id,
                            'object_type' => Activity::OBJECT_TYPE_MEDIA,
                        ], false);
                        $activity->save();

                        try {
                            ParseClient::initialize(
                                Yii::$app->params['parse']['applicationId'],
                                Yii::$app->params['parse']['restApiKey'],
                                Yii::$app->params['parse']['masterKey']
                            );

                            ParsePush::send([
                                'channels' => ['u' . $media->getAttribute('user_id')],
                                'data' => [
                                    'badge' => 'Increment',
                                    'alert' => sprintf('%s gönderini favorilerine ekledi.', Yii::$app->user->identity->username),
                                ],
                            ]);
                        } catch (ParseException $e) {
                            // do nothing
                        }
                    }

                    // todo: subscribe to media updates

                    return [
                        'id' => $favorite->getAttribute('id'),
                        'created_at' => Yii::$app->formatter->asDatetime($favorite->getAttribute('created_at')),
                    ];
                }
            } else {
                return [
                    'id' => $favorite->getAttribute('id'),
                    'created_at' => Yii::$app->formatter->asDatetime($favorite->getAttribute('created_at')),
                ];
            }
        }

        return false;
    }
}
