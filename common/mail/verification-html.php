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
?>

<p>Merhaba <strong><?= Html::encode($user->getAttribute('username')) ?></strong>,</p>

<p>
    Sizinle profilinizin doğrulanması talebiniz üzerine iletişime geçiyoruz. Doğrulama sürecini tamamlamak için bazı
    bilgilerinize ihtiyaç duyuyoruz.
</p>
<p>
    <strong>Eğer uzman hekim, hekim, asistan hekim, uzman diş hekimi, diş hekimi, eczacı veya yardımcı sağlık
        personeliyseniz:</strong>
</p>
<ol>
    <li>T.C. kimlik numaranız,</li>
    <li>Baba adınız,</li>
    <li>Doğum tarihiniz (gün/ay/yıl şeklinde),</li>
</ol>
<br>
<p>
    <small><em>Bilgileriniz T.C. Sağlık Bakanlığı Doktor ve Yardımcı Sağlık Personeli Bilgi Bankası üzerinden kontrol
            edilecektir.</em></small>
</p>
<p>
    <strong>Eğer hemşire veya diğer sağlık çalışanıysanız:</strong>
</p>
<ol>
    <li>Ad ve soyadınız,</li>
    <li>Çalıştığınız kuruma ait kimliğinizin veya personel kartınızın taranmış hali,</li>
</ol>
<br>
<p>
    <strong>Eğer öğrenciyseniz:</strong>
</p>
<ol>
    <li>Ad ve soyadınız,</li>
    <li>Üniversite e-posta adresiniz,</li>
    <li>Öğrenci kimlik kartınızın taranmış hali,</li>
</ol>
<br>
<p>
    Bilgilerinizi bu mesajı yanıtlayarak gönderebilirsiniz.
</p>
<p>
    Saygılarımızla,
</p>
<p>
    Example Ekibi
</p>
