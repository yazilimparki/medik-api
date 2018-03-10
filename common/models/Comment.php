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
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
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
                        'model' => Media::className(),
                        'attribute' => 'comment_count',
                        'foreignKey' => 'media_id',
                    ],
                    [
                        'model' => User::className(),
                        'attribute' => 'comment_count',
                        'foreignKey' => 'user_id',
                    ]
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        CommentReport::deleteAll(['object_id' => $this->getPrimaryKey()]);
        Vote::deleteAll(['comment_id' => $this->getPrimaryKey()]);
    }

    public function getMedia()
    {
        return $this->hasOne(Media::className(), ['id' => 'media_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
