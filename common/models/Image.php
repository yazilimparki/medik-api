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
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Image extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
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
                        'attribute' => 'image_count',
                        'foreignKey' => 'media_id',
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        $files = [
            'media/' . $this->getAttribute('image_file_base') . '_150.jpg',
            'media/' . $this->getAttribute('image_file_base') . '_300.jpg',
            'media/public/' . $this->getAttribute('image_file_base') . '_300.jpg',
            'media/' . $this->getAttribute('image_file_base') . '.' . $this->getAttribute('image_file_extension'),
        ];

        foreach ($files as $file) {
            if ($this->getAttribute('image_storage') == STORAGE_AMAZON) {
                if (Yii::$app->filesystem->has($file)) {
                    Yii::$app->filesystem->delete($file);
                }
            } else {
                @unlink(Yii::getAlias('@webroot/uploads/', false) . $file);
            }
        }
    }

    public function getMedia()
    {
        return $this->hasOne(Media::className(), ['id' => 'media_id']);
    }
}
