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

use common\models\Comment;
use common\models\Vote;
use Yii;
use yii\base\Model;

class VoteForm extends Model
{
    public $comment_id;
    public $score;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => 'Yorum',
            'score' => 'Oy',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment_id', 'score'], 'required'],
            [['comment_id'], 'integer'],
            ['comment_id', 'exist', 'targetClass' => Comment::className(), 'targetAttribute' => 'id'],
            ['score', 'in', 'range' => ['up', 'down']],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $vote = Vote::findOne([
                'comment_id' => $this->comment_id,
                'user_id' => Yii::$app->user->id,
            ]);

            $this->score = $this->score == 'up' ? 1 : -1;

            if ($vote) {
                $vote->setAttributes([
                    'score' => $this->score,
                ], false);
            } else {
                $vote = new Vote();
                $vote->setAttributes([
                    'comment_id' => $this->comment_id,
                    'user_id' => Yii::$app->user->id,
                    'score' => $this->score,
                ], false);
            }

            if ($vote->save()) {
                $comment = Comment::find()->select(['score'])->where(['id' => $this->comment_id])->one();

                return [
                    'id' => $vote->getPrimaryKey(),
                    'comment' => [
                        'id' => $this->comment_id,
                        'score' => $comment->getAttribute('score'),
                    ],
                    'created_at' => Yii::$app->formatter->asDatetime($vote->getAttribute('created_at')),
                ];
            }
        }

        return false;
    }
}
