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

use common\models\Media;
use common\models\MediaReport;
use Yii;
use yii\base\Exception;
use yii\base\Model;

class MediaReportForm extends Model
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
            [['media_id', 'text'], 'required'],
            ['media_id', 'integer'],
            ['media_id', 'exist', 'targetClass' => Media::className(), 'targetAttribute' => 'id'],
            ['text', 'string', 'max' => 250],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $report = new MediaReport();
            $report->setAttributes([
                'object_id' => $this->media_id,
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
                        ->setSubject('[Example] Fotoğraf raporu')
                        ->setTextBody('Yeni bir fotoğraf raporu geldi.')
                        ->setHtmlBody('Yeni bir fotoğraf raporu geldi.')
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
