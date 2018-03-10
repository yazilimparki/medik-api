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

use yii\helpers\Html;

/* @var $user common\models\User */
/* @var $token string */

$link = 'http://app.example.com/reset-password/' . $token;
?>

<p>Merhaba <?= Html::encode($user->getAttribute('username')) ?>,</p>

<p>Aşağıdaki butona tıklayarak yeni parolanızı oluşturabilirsiniz.</p>

<p><?= Html::a('Yeni Parola Oluştur', $link, ['class' => 'btn-primary']) ?></p>
