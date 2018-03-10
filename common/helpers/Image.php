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

namespace common\helpers;

class Image
{
    public static function storageBaseUrl($storage)
    {
        if (YII_ENV === 'dev') {
            return 'http://static-dev.example.com';
        }

        switch ($storage) {
            case STORAGE_AMAZON:
                return 'http://static.example.com';
            default:
                return 'https://api.example.com/uploads';
        }
    }

    public static function calculateHeight($fromWidth, $toWidth, $height)
    {
        $ratio = $toWidth / ($fromWidth ?: 1);

        return ceil($height * $ratio);
    }
}
