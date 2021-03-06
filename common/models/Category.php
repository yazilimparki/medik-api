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

use yii\db\ActiveRecord;

class Category extends ActiveRecord
{
    const TYPE_ANATOMY = 0;
    const TYPE_SPECIALITY = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    public function getChildren()
    {
        return $this->hasMany(static::className(), ['parent_id' => 'id']);
    }

    public function getMedia()
    {
        return $this->hasMany(Media::className(), ['id' => 'media_id'])->viaTable('category_media', ['category_id' => 'id']);
    }

    public function getParent()
    {
        return $this->hasOne(static::className(), ['id' => 'parent_id']);
    }
}
