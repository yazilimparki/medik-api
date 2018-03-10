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

use Intervention\Image\ImageManager;
use League\Flysystem\AdapterInterface;
use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class UserPictureForm extends Model
{
    public $file;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => 'Profil Fotoğrafı',
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
                'maxSize' => 20000000,
                'skipOnEmpty' => false,
            ],
        ];
    }

    public function create()
    {
        /**
         * @var $user \yii\db\ActiveRecord
         */
        $user = User::findOne(Yii::$app->user->id);

        if (empty($user)) {
            throw new NotFoundHttpException('User object not found.');
        }

        $this->file = UploadedFile::getInstanceByName('file');

        if ($this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();
            $unlink = [];

            try {
                $picture_file_base = strtolower(Yii::$app->security->generateRandomString(32));
                $picture_file_extension = strtolower(str_replace('jpeg', 'jpg', $this->file->extension));
                $picture_file_name = $picture_file_base . '.' . $picture_file_extension;
                $old_file_base = $user->getAttribute('picture_file_base');
                $old_file_extension = $user->getAttribute('picture_file_extension');

                $manager = new ImageManager(['driver' => 'gd']);
                $image = $manager->make($this->file->tempName)->orientate();

                Yii::$app->filesystem->put('profiles/' . $picture_file_name, $image->encode(), [
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                ]);

                $image->fit(150);

                Yii::$app->filesystem->put('profiles/' . $picture_file_base . '_150.jpg', $image->encode('jpg'), [
                    'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                    'StorageClass' => 'REDUCED_REDUNDANCY',
                ]);

                if (!empty($old_file_base)) {
                    if (Yii::$app->filesystem->has('profiles/' . $old_file_base . '.' . $old_file_extension)) {
                        Yii::$app->filesystem->delete('profiles/' . $old_file_base . '.' . $old_file_extension);
                    }

                    if (Yii::$app->filesystem->has('profiles/' . $old_file_base . '_150.jpg')) {
                        Yii::$app->filesystem->delete('profiles/' . $old_file_base . '_150.jpg');
                    }
                }

                $unlink[] = $picture_file_base . '_150.jpg';
                $unlink[] = $picture_file_name;

                $user->setAttributes([
                    'picture_file_base' => $picture_file_base,
                    'picture_file_extension' => $picture_file_extension,
                    'picture_storage' => STORAGE_AMAZON,
                ], false);

                if ($user->save(false) === false) {
                    throw new \Exception('Failed to update user object.');
                }

                $transaction->commit();

                return [
                    'id' => $user->getPrimaryKey(),
                    'updated_at' => Yii::$app->formatter->asDatetime($user->getAttribute('updated_at')),
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();

                foreach ($unlink as $file) {
                    if (Yii::$app->filesystem->has('profiles/' . $file)) {
                        Yii::$app->filesystem->delete('profiles/' . $file);
                    }
                }
            }
        }

        return false;
    }

    public function delete()
    {
        /**
         * @var $user \yii\db\ActiveRecord
         */
        $user = User::findOne(Yii::$app->user->id);

        if (empty($user)) {
            throw new NotFoundHttpException('User object not found.');
        }

        $picture_file_base = $user->getAttribute('picture_file_base');

        if (!empty($picture_file_base)) {
            $files = [
                $picture_file_base . '_150.jpg',
                $picture_file_base . '.' . $user->getAttribute('picture_file_extension'),
            ];

            $user->setAttributes([
                'picture_file_base' => '',
                'picture_file_extension' => '',
                'picture_storage' => 0,
            ], false);

            $user->save(false);

            foreach ($files as $file) {
                if (Yii::$app->filesystem->has('profiles/' . $file)) {
                    Yii::$app->filesystem->delete('profiles/' . $file);
                }
            }
        }

        return true;
    }
}
