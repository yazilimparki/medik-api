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

Merhaba <?= Html::encode($user->getAttribute('username')) ?>,

Sizinle profilinizin doğrulanması talebiniz üzerine iletişime geçiyoruz. Doğrulama sürecini tamamlamak için bazı
bilgilerinize ihtiyaç duyuyoruz.

Eğer uzman hekim, hekim, asistan hekim, uzman diş hekimi, diş hekimi, eczacı veya yardımcı sağlık personeliyseniz:

    1. T.C. kimlik numaranız,
    2. Baba adınız,
    3. Doğum tarihiniz (gün/ay/yıl şeklinde),

(Bilgileriniz T.C. Sağlık Bakanlığı Doktor ve Yardımcı Sağlık Personeli Bilgi Bankası üzerinden kontrol edilecektir.)

Eğer hemşire veya diğer sağlık çalışanıysanız:

    1. Ad ve soyadınız,
    2. Çalıştığınız kuruma ait kimliğinizin veya personel kartınızın taranmış hali,

Eğer öğrenciyseniz:

    1. Ad ve soyadınız,
    2. Üniversite e-posta adresiniz,
    2. Öğrenci kimlik kartınızın taratılmış hali,

Bilgilerinizi bu mesajı yanıtlayarak gönderebilirsiniz.

Saygılarımızla,

Example Ekibi
