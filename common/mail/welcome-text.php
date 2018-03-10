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

/* @var $user common\models\User */

$facebookLink = 'Facebook share link';
$twitterLink = 'Twitter share link';
?>

Hoşgeldin <?= $user->getAttribute('username') ?>,

Aramıza katıldığınız için teşekkür ederiz. Paylaştığınız değerli fotoğraflar ve yorumlar ile
medikal görüntü kütüphanemizin gelişmesine yardımcı oluyorsunuz.

Example uygulamasını meslektaş ve arkadaşlarınızla paylaşarak topluluğun gelişmesine yardımcı olabilirsiniz.

Facebook'ta Paylaş: <?= $facebookLink ?>
Twitter'da Paylaş: <?= $twitterLink ?>
