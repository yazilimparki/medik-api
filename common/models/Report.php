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

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Report extends ActiveRecord
{
    const OBJECT_TYPE_COMMENT = 0;
    const OBJECT_TYPE_MEDIA = 1;
    const OBJECT_TYPE_USER = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function instantiate($row)
    {
        switch ($row['object_type']) {
            case self::OBJECT_TYPE_COMMENT:
                return new CommentReport();
            case self::OBJECT_TYPE_MEDIA:
                return new MediaReport();
            case self::OBJECT_TYPE_USER:
                return new UserReport();
            default:
                return new self;
        }
    }

    public function getReporter()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id'])->from(['reporter' => 'user']);
    }
}
