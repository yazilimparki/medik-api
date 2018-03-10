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
use common\models\Comment;
use common\models\Media;
use common\models\User;
use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParsePush;
use Yii;
use yii\base\Model;

class CommentForm extends Model
{
    public $media_id;
    public $text;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'media_id' => 'Medya',
            'text' => 'Yorum Metni',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['text', 'trim'],
            [['media_id', 'text'], 'required'],
            ['media_id', 'integer'],
            ['media_id', 'exist', 'targetClass' => Media::className(), 'targetAttribute' => 'id'],
            ['text', 'string', 'max' => 250],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $comment = new Comment();
            $comment->setAttributes([
                'user_id' => Yii::$app->user->id,
                'media_id' => $this->media_id,
                'text' => $this->text,
            ], false);

            if ($comment->save()) {
                $media = Media::find()
                    ->select(['user_id'])
                    ->where(['id' => $this->media_id])
                    ->one();

                if ($media->getAttribute('user_id') != Yii::$app->user->id) {
                    $activity = new Activity();
                    $activity->setAttributes([
                        'user_id' => $media->getAttribute('user_id'),
                        'type' => Activity::TYPE_COMMENT,
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
                                'alert' => sprintf('%s gönderinize yeni bir yorum yazdı.', Yii::$app->user->identity->username),
                            ],
                        ]);
                    } catch (ParseException $e) {
                        // do nothing
                    }

                    try {
                        $user = User::find()
                            ->select(['username', 'email'])
                            ->where(['id' => $media->getAttribute('user_id')])
                            ->one();

                        /**
                         * @var $message \yii\swiftmailer\Message
                         */
                        $message = Yii::$app->mailer->compose([
                            'html' => 'new-comment-html',
                            'text' => 'new-comment-text',
                        ], [
                            'user' => $user,
                            'commenter' => Yii::$app->user->identity->username,
                            'text' => $this->text,
                        ]);

                        $message->setFrom([Yii::$app->params['fromAddress'] => Yii::$app->params['fromName']])
                            ->setTo($user->getAttribute('email'))
                            ->setSubject('Gönderinize yeni bir yorum yazıldı')
                            ->send();
                    } catch (\Swift_TransportException $e) {
                        // do nothing
                    }
                }

                // todo: subscribe to media updates

                return [
                    'id' => $comment->getPrimaryKey(),
                    'created_at' => Yii::$app->formatter->asDatetime($comment->getAttribute('created_at')),
                ];
            }
        }

        return false;
    }
}
