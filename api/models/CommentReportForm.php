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
use common\models\CommentReport;
use Yii;
use yii\base\Exception;
use yii\base\Model;

class CommentReportForm extends Model
{
    public $comment_id;
    public $text;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => 'Yorum',
            'text' => 'Rapor Metni',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['text', 'trim'],
            [['comment_id', 'text'], 'required'],
            ['comment_id', 'integer'],
            ['comment_id', 'exist', 'targetClass' => Comment::className(), 'targetAttribute' => 'id'],
            ['text', 'string', 'max' => 250],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $report = new CommentReport();
            $report->setAttributes([
                'object_id' => $this->comment_id,
                'text' => $this->text,
                'user_id' => Yii::$app->user->id,
            ], false);

            if ($report->save() === false) {
                throw new Exception('Failed to create report object.');
            }

            if (YII_ENV === 'prod') {
                try {
                    Yii::$app->mailer->compose()
                        ->setFrom([Yii::$app->params['fromAddress'] => Yii::$app->params['fromName']])
                        ->setTo('support@example.com')
                        ->setSubject('[Example] Yorum raporu')
                        ->setTextBody('Yeni bir yorum raporu geldi.')
                        ->setHtmlBody('Yeni bir yorum raporu geldi.')
                        ->send();
                } catch (\Swift_TransportException $e) {
                    // do nothing
                }
            }

            return [
                'id' => $report->getPrimaryKey(),
                'created_at' => Yii::$app->formatter->asDatetime($report->getAttribute('created_at')),
            ];
        }

        return false;
    }
}
