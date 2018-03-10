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

namespace api\models;

use common\models\Image;
use Intervention\Image\ImageManager;
use League\Flysystem\AdapterInterface;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ImageForm extends Model
{
    public $file;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => 'Dosya',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'file',
                'file',
                'checkExtensionByMimeType' => false,
                'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                'maxSize' => 50000000,
                'skipOnEmpty' => false,
            ],
        ];
    }

    public function create()
    {
        $this->file = UploadedFile::getInstanceByName('file');

        if ($this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            $unlink = [];

            try {
                $image_file_base = strtolower(Yii::$app->security->generateRandomString(32));
                $image_file_extension = strtolower(str_replace('jpeg', 'jpg', $this->file->extension));
                $image_file_name = $image_file_base . '.' . $image_file_extension;

                $imageManager = new ImageManager(['driver' => 'gd']);
                $uploadedImage = $imageManager->make($this->file->tempName)->orientate();
                $image_width = $uploadedImage->width();
                $image_height = $uploadedImage->height();

                Yii::$app->filesystem->put('media/' . $image_file_name, $uploadedImage->encode(), [
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                ]);

                $uploadedImage->backup()->fit(150);

                Yii::$app->filesystem->put('media/' . $image_file_base . '_150.jpg', $uploadedImage->encode('jpg'), [
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                ]);

                $uploadedImage->reset()->resize(300, null, function ($constraint) {
                    /** @var $constraint \Intervention\Image\Constraint */
                    $constraint->aspectRatio();
                });

                Yii::$app->filesystem->put('media/' . $image_file_base . '_300.jpg', $uploadedImage->encode('jpg'), [
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                ]);

                $uploadedImage
                    ->insert(dirname(__DIR__) . '/web/img/watermark-text.png', 'center')
                    ->insert(dirname(__DIR__) . '/web/img/watermark-logo.png', 'bottom-left', 5, 5);

                Yii::$app->filesystem->put('media/public/' . $image_file_base . '_300.jpg', $uploadedImage->encode('jpg'), [
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                ]);

                $unlink[] = $image_file_base . '_150.jpg';
                $unlink[] = $image_file_base . '_300.jpg';
                $unlink[] = 'public/' . $image_file_base . '_300.jpg';
                $unlink[] = $image_file_name;

                $image = new Image();

                $image->setAttributes([
                    'user_id' => Yii::$app->user->id,
                    'media_id' => 0, // mysql strict mode (doesn't have a default value)
                    'image_file_base' => $image_file_base,
                    'image_file_extension' => $image_file_extension,
                    'image_storage' => STORAGE_AMAZON,
                    'image_width' => $image_width,
                    'image_height' => $image_height,
                ], false);

                if ($image->save(false) === false) {
                    throw new \Exception('Failed to create image object.');
                }

                $transaction->commit();

                return [
                    'id' => $image->getPrimaryKey(),
                    'created_at' => Yii::$app->formatter->asDatetime($image->getAttribute('created_at')),
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();

                foreach ($unlink as $file) {
                    if (Yii::$app->filesystem->has('media/' . $file)) {
                        Yii::$app->filesystem->delete('media/' . $file);
                    }
                }
            }
        }

        return false;
    }
}
