<?php
namespace api\models;

use common\models\Category;
use common\models\Follow;
use common\models\Image;
use common\models\Media;
use Intervention\Image\ImageManager;
use League\Flysystem\AdapterInterface;
use Parse\ParseClient;
use Parse\ParsePush;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class MediaForm extends Model
{
    public $caption;
    public $categories;
    public $images;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'caption' => 'Açıklama Metni',
            'categories' => 'Kategori',
            'images' => 'Dosya',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['caption', 'trim'],
            [['caption', 'categories', 'images'], 'required'],
            ['caption', 'string', 'max' => 250],
            [
                ['categories', 'images'],
                'filter',
                'filter' => function ($value) {
                    return array_map('intval', (array)$value);
                },
            ],
            [['categories', 'images'], 'each', 'rule' => ['integer']],
            [
                'categories',
                'exist',
                'targetClass' => Category::className(),
                'targetAttribute' => 'id',
                'filter' => ['not', ['type' => Category::TYPE_ANATOMY, 'parent_id' => 0]],
                'allowArray' => true,
            ],
            [
                'images',
                'exist',
                'targetClass' => Image::className(),
                'targetAttribute' => 'id',
                'filter' => ['user_id' => Yii::$app->user->id, 'media_id' => 0],
                'allowArray' => true,
            ],
        ];
    }

    public function create()
    {
        if ($this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $media = new Media();

                $media->setAttributes([
                    'public_id' => strtolower(Yii::$app->security->generateRandomString(32)),
                    'user_id' => Yii::$app->user->id,
                    'type' => count($this->images) > 1 ? Media::TYPE_MULTIPLE : Media::TYPE_SINGLE,
                    'caption' => $this->caption,
                    'verified' => 0,
                    'image_count' => 1, // fixme: image count tutmuyor???
                ], false);

                if ($media->save(false) === false) {
                    throw new \Exception('Failed to create media object.');
                }

                foreach ((array)$this->images as $image_id) {
                    $image = Image::find()->select('id')->where(['id' => $image_id])->one();

                    if ($image !== null) {
                        $media->link('images', $image);
                    }
                }

                foreach ((array)$this->categories as $category_id) {
                    $category = Category::find()->select('id')->where(['id' => $category_id])->one();

                    if ($category !== null) {
                        $media->link('categories', $category);
                    }
                }

                $transaction->commit();

                /*
                $followers = Follow::find()
                    ->select(['follower_id'])
                    ->where(['following_id' => Yii::$app->user->id])
                    ->asArray()
                    ->all();

                if (count($followers) > 0) {
                    ParseClient::initialize(
                        Yii::$app->params['parse']['applicationId'],
                        Yii::$app->params['parse']['restApiKey'],
                        Yii::$app->params['parse']['masterKey']
                    );

                    $channels = ArrayHelper::getColumn($followers, function ($element) {
                        return 'u' . $element['id'];
                    });

                    ParsePush::send([
                        'channels' => $channels,
                        'data' => [
                            'badge' => 'Increment',
                            'alert' => sprintf('%s yeni bir fotoğraf yükledi.', Yii::$app->user->identity->username),
                        ],
                    ]);
                }
                */

                if (YII_ENV === 'prod') {
                    $body = sprintf('%s isimli kullanıcı yeni bir fotoğraf gönderdi.', Yii::$app->user->identity->username);

                    try {
                        Yii::$app->mailer->compose()
                            ->setFrom([Yii::$app->params['fromAddress'] => Yii::$app->params['fromName']])
                            ->setTo('support@example.com')
                            ->setSubject('[Example] Yeni fotoğraf')
                            ->setTextBody($body)
                            ->setHtmlBody($body)
                            ->send();
                    } catch (\Swift_TransportException $e) {
                        // do nothing
                    }
                }

                return [
                    'id' => $media->getPrimaryKey(),
                    'created_at' => Yii::$app->formatter->asDatetime($media->getAttribute('created_at')),
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }

        return false;
    }

    public function update($id)
    {
        /**
         * @var $media \yii\db\ActiveRecord
         */
        $media = Media::findOne([
            'id' => $id,
            'user_id' => Yii::$app->user->id,
        ]);

        if ($media === null) {
            throw new NotFoundHttpException('Media object not found.');
        }

        $this->scenario = 'update';

        if ($this->validate()) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $media->setAttributes([
                    'caption' => $this->caption,
                    'type' => count($this->images) == 1 ? Media::TYPE_SINGLE : Media::TYPE_MULTIPLE,
                ], false);

                if ($media->save(false) === false) {
                    throw new \Exception('Failed to update media object.');
                }

                /**
                 * Eger once imajlari yukleyip id'lerini alacaksak
                 */
                foreach ($this->images as $image_id) {
                    $image = Image::find()->select('id')->where(['id' => $image_id])->one();

                    if ($image !== null) {
                        $media->link('images', $image);
                    }
                }

                foreach ($this->categories as $category_id) {
                    $category = Category::find()->select('id')->where(['id' => $category_id])->one();

                    if ($category !== null) {
                        $media->link('categories', $category);
                    }
                }

                $transaction->commit();

                return [
                    'id' => $media->getPrimaryKey(),
                    'updated_at' => Yii::$app->formatter->asDatetime($media->getAttribute('updated_at')),
                ];
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }

        return false;
    }
}
