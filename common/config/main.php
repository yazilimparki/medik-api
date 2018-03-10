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

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=example_api',
            'username' => 'username',
            'password' => 'password',
            'charset' => 'utf8',
            'on afterOpen' => function ($event) {
                $event->sender->createCommand('SET time_zone = \'+00:00\'')->execute();
            },
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 0,
        ],
        'filesystem' => [
            'class' => 'creocoder\flysystem\AwsS3Filesystem',
            'key' => 'Your AWS key',
            'secret' => 'Your AWS secret',
            'bucket' => YII_ENV === 'dev' ? 'static-dev.example.com' : 'static.example.com',
            'region' => 'eu-central-1',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.example.com',
                'port' => 587,
                'username' => 'username',
                'password' => 'password',
                'encryption' => 'tls',
            ],
        ],
    ],
];
