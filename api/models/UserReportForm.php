<?php
namespace api\models;

use common\models\User;
use common\models\UserReport;
use Yii;
use yii\base\Exception;
use yii\base\Model;

class UserReportForm extends Model
{
    public $user_id;
    public $text;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Kullanıcı',
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
            [['user_id', 'text'], 'required'],
            ['user_id', 'integer'],
            ['user_id', 'exist', 'targetClass' => User::className(), 'targetAttribute' => 'id'],
            ['text', 'string', 'max' => 250],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $report = new UserReport();
            $report->setAttributes([
                'object_id' => $this->user_id,
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
                        ->setSubject('[Example] Kullanıcı raporu')
                        ->setTextBody('Yeni bir kullanıcı raporu geldi.')
                        ->setHtmlBody('Yeni bir kullanıcı raporu geldi.')
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
