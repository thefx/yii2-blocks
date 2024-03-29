<?php

namespace thefx\blocks\models\blocks;

use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use thefx\blocks\behaviors\Slug;
use thefx\blocks\behaviors\UploadImageBehavior;
use thefx\blocks\behaviors\UploadImageBehavior5;
use thefx\blocks\models\blocks\queries\BlockItemQuery;
use thefx\user\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%block_item}}".
 *
 * @property int $id
 * @property int|null $block_id
 * @property string|null $title
 * @property string|null $path
 * @property string|null $anons
 * @property string|null $text
 * @property string|null $photo
 * @property string|null $photo_preview
 * @property string|null $date
 * @property int $parent_id
 * @property int $public
 * @property int $sort
 * @property string|null $seo_title
 * @property string|null $seo_keywords
 * @property string|null $seo_description
 * @property int|null $create_user
 * @property string|null $create_date
 * @property int|null $update_user
 * @property string|null $update_date
 * @property string|null $article
 * @property float|null $price
 * @property float|null $price_old
 * @property string|null $currency
 * @property string|null $unit
 * @property BlockCategory $category
 * @property BlockProp[] $propAll
 * @property BlockItemPropAssignments[] $propAssignments
 * @property BlockItemPropAssignments[] $propAssignmentsIndexed
 * @property BlockItemPropAssignments[] $propAssignmentsFilter
 * @property Block $block
 * @property BlockProp[] $propsIndexed
 * @property User[] $createUser
 *
 * @mixin SaveRelationsBehavior
 */
class BlockItem extends ActiveRecord
{
    public $propAssignmentsTemp;
    public $photo_preview_crop;
    public $photo_crop;

    public function getPhoto($attribute = 'photo')
    {
        return $this->{$attribute} ? '/upload/blocks/' . $this->{$attribute} : '';
    }

    public function getPhotoMobile($attribute = 'photo')
    {
        return file_exists('/upload/blocks/mobile_' . $this->{$attribute}) ? '/upload/blocks/mobile_' . $this->{$attribute} : null;
    }

    public function getPhotoPreview($attribute = 'photo_preview')
    {
        return $this->{$attribute} ? '/upload/blocks/' . $this->{$attribute} : '';
    }

    public function getEditorPath()
    {
        $id = $this->getPrimaryKey();
        $path = 'block';

        $dir = Yii::getAlias('@webroot/upload/' . $path) . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR;

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }

        return Yii::getAlias('@web/upload/' . $path) . '/';
    }

    public function categoryList()
    {
        /** @var BlockCategory $category */
        $category = BlockCategory::findOne(['id' => $this->parent_id]);
        $categories = BlockCategory::find()->where(['block_id' => $category->block_id])->orderBy('lft')->all();

        return ArrayHelper::map($categories, 'id', static function($row) {
            return str_repeat('-', $row->depth) . ' ' . $row->title;
        });
    }

    public function populateAssignments()
    {
        $assignments = [];

        foreach ($this->propAll as $prop) {
            $isset = false;
            foreach ($this->propAssignments as $assignment) {
                if ($assignment->isForProp($prop->id)) {
                    if ($prop->isMulti() && !$prop->isImage() && !$prop->isFile()) {
                        $assignment->value = explode(';', $assignment->value);
                    }
                    if ($prop->isImage()) {
                        $assignment->value = explode(';', $assignment->value);
                    }
                    $assignments[$prop->id] = $assignment;
                    $isset = true;
                }
            }
            if (!$isset) {
                $newAssignment = new BlockItemPropAssignments([
                    'prop_id' => $prop->id
                ]);
                $newAssignment->populateRelation('prop', $prop);
                $assignments[$prop->id] = $newAssignment;
            }
        }
        $this->propAssignments = $assignments;

        return $this;
    }

    public function getAssignmentByPropId($propId)
    {
        foreach ($this->propAssignments as $propAssignment) {
            if ($propAssignment->prop->id == $propId) {
                return $propAssignment;
            }
        }
        return null;
    }

    public function loadAssignments($data)
    {
        $assignments = $this->propAssignments;

        foreach ($assignments as $model) {
            $i = $model->prop_id;

            $model->setAttributes($data[$model->formName()][$i]);
            if (/*$model->prop->isImage() ||*/ $model->prop->isFile()) {
                $model->value = UploadedFile::getInstances($model, "[$i]value");
            }
            if (is_array($model->value) && $model->prop->isMulti() /*&& !$model->prop->isImage()*/ && !$model->prop->isFile()) {
                $model->value = array_filter(array_map(static function ($data) {
                    return (int) $data;
                }, $model->value));
                $model->value = implode(';', $model->value);
            }
            $model->validate();
        }
        $this->propAssignments = array_filter($assignments, static function ($data) {
            return $data->value != '';
        });
        return $this;
    }

    public function createRelCatHandler()
    {
        $isSave = false;
        foreach ($this->propAll as $i => $prop) {
            if ($prop->type == $prop::TYPE_RELATIVE_BLOCK_CAT && $prop->relative_block_cat != '') {
                $isContinue = false;
                foreach ($this->propAssignments as $propAssignment) {
                    if ($propAssignment->prop_id == $prop->id) { $isContinue = true; break; }
//                    var_dump([$prop->id == $propAssignment->prop_id, $prop->id => $propAssignment->prop_id]);
                }
                if (!$isContinue) {
                    $categoryId = $this->createRelCat($prop->relative_block_cat);
                    $this->addPropAssignment($this->id, $prop->id, $categoryId);
                    $isSave = true;
                }
            }
        }
        if ($isSave) {
            $this->save();
        }
    }

    private function addPropAssignment($block_item_id, $prop_id, $value)
    {
        $assignments = $this->propAssignments;

        $assignments[] = new BlockItemPropAssignments([
            'block_item_id' => $block_item_id,
            'prop_id' => $prop_id,
            'value' => $value,
        ]);

        $this->propAssignments = $assignments;
    }

    private function createRelCat($block_id)
    {
        /** @var Block $block */
        $block = Block::findOne($block_id);

        /** @var BlockCategory $category */
        $category = BlockCategory::find()
            ->where(['block_id' => $block->id])
            ->getRoot()
            ->one();

        $model = BlockCategory::create(
            $block->id,
            $category->id,
            $this->id . '#' . $this->title
        );

        if ($model->validate()) {
            $model->appendTo($category)->save();
            return $model->id;
        }

        die(var_dump($model->getErrors()));
    }

    public function beforeValidate()
    {
        /** @var BlockCategory $category */
        $category = BlockCategory::findOne($this->parent_id);

        /** @var Block $block */
        $block = Block::findOne($category->block_id);

        $this->attachBehaviors([
            'photo_preview' => [
                'class' => UploadImageBehavior5::class,
                    'attributeName' => 'photo_preview',
                    'cropCoordinatesAttrName' => 'photo_preview_crop',
                    'savePath' => "@webroot/upload/{$block->settings->upload_path}/",
                    'generateNewName' => static function () {
                        return uniqid('', false);
                    },
                    'defaultCrop' => [
                        $block->settings->photo_preview_crop_width,
                        $block->settings->photo_preview_crop_height,
                        $block->settings->photo_preview_crop_type
                    ],
//                    'crop' => [
//                        [300, 300, 'min', 'fit'],
//                    ]
                ],
                'photo' => [
                    'class' => UploadImageBehavior5::class,
                    'attributeName' => 'photo',
                    'cropCoordinatesAttrName' => 'photo_crop',
                    'savePath' => "@webroot/upload/{$block->settings->upload_path}/",
                    'generateNewName' => static function () {
                        return uniqid('', false);
                    },
                    'defaultCrop' => [
                        $block->settings->photo_crop_width,
                        $block->settings->photo_crop_height,
                        $block->settings->photo_crop_type
                    ],
                    // только для поселков
//                    'crop' => array_filter($block->id == 12 ? [
//                        [640, 1030, 'mobile', 'widen'],
//                    ] : [])
                ]
            ]
        );

        return parent::beforeValidate();
    }

    public function getPropLabel($code = null)
    {
        return isset($this->propsIndexed[$code]) ? $this->propsIndexed[$code]->title : null;
    }

    public function getPropValue($code = null)
    {
        foreach ($this->propAssignments as $item) {
            if ($item->prop->code === $code) {
                return $item->getValue();
            }
        }
        return null;
    }

    /**
     * Возвращает код элемента (для типа - список)
     * @param null $code
     * @return BlockItem[]|array|string|null
     */
    public function getPropCodeElem($code = null)
    {
        foreach ($this->propAssignments as $item) {
            if ($item->prop->code == $code) {
                return $item->getCode();
            }
        }
        return null;
    }

    public function getPropValueString($code = null)
    {
        return is_array($this->getPropValue($code)) ? implode(', ', $this->getPropValue($code)) : $this->getPropValue($code);
    }

    public function getPropValueStringLinks($code = null)
    {
        $link = $this->getPropValue($code);
        if (is_array($this->getPropValue($code))) {
            $links = [];
            foreach ($this->getPropValue($code) as $id => $name) {
                $links[] = Html::a($name, ['index', strtolower($code) => [$id]]);
            }
            return implode(' ', $links);
        }

        foreach ($this->propAssignments as $item) {
            if ($item->prop->code === $code) {
                $link = Html::a($item->propElement->title, ['index', strtolower($code) => $item->propElement->id]);
            }
        }
        return $link;
    }

    public function getPropAssignmentValue($code = null)
    {
        foreach ($this->propAssignments as $item) {
            if ($item->prop->code === $code) {
                return $item->value;
            }
        }
        return null;
    }

    public function getPath()
    {
        return  $this->block->path . '/' . $this->path;
    }

    #########################

    public function getBlock()
    {
        return $this->hasOne(Block::class, ['id' => 'block_id']);
    }

    public function getPropAll()
    {
        return $this->hasMany(BlockProp::class, ['block_id' => 'block_id']);
    }

    public function getPropsIndexed()
    {
        return $this->hasMany(BlockProp::class, ['block_id' => 'block_id'])->indexBy('code');
    }

    public function getPropAssignments()
    {
        return $this->hasMany(BlockItemPropAssignments::class, ['block_item_id' => 'id']);
    }

    public function getPropAssignmentsIndexed()
    {
        return $this->hasMany(BlockItemPropAssignments::class, ['block_item_id' => 'id'])->indexBy('id');
    }

    public function getPropAssignmentsFilter()
    {
        return $this->hasMany(BlockItemPropAssignments::class, ['block_item_id' => 'id']);
    }

    public function getCategory()
    {
        return $this->hasOne(BlockCategory::class, ['id' => 'parent_id']);
    }

    public function getCreateUser()
    {
        return $this->hasOne(User::class, ['id' => 'create_user']);
    }

    #########################

    public function behaviors()
    {
        return [
            Slug::class,
            [
                'class' => SaveRelationsBehavior::class,
                'relations' => [
                    'propAssignments' /*=> ['cascadeDelete' => true]*/
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%block_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'block_id', 'parent_id', 'sort'], 'required'],
            [['anons', 'text'], 'string'],
            [['date', 'create_date', 'update_date'], 'safe'],
            [['block_id', 'parent_id', 'public', 'sort', 'create_user', 'update_user'], 'integer'],
            [['title', 'path', /*'photo', 'photo_preview',*/ 'photo_crop', 'photo_preview_crop', 'seo_title', 'seo_keywords', 'seo_description'], 'string', 'max' => 255],
            [['photo', 'photo_preview'], 'file', 'mimeTypes' => 'image/*'],

            [['price', 'price_old'], 'double'],
            [['article'], 'string', 'max' => 50],
            [['currency', 'unit'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'block_id' => 'Блок',
            'title' => 'Название',
            'path' => 'Url',
            'anons' => 'Краткое описание',
            'text' => 'Текст',
            'photo' => 'Фото',
            'photo_preview' => 'Фото для анонса',
            'date' => 'Дата',
            'parent_id' => 'Категория',
            'public' => 'Активность',
            'sort' => 'Сортировка',
            'seo_title' => 'Заголовок в браузере',
            'seo_keywords' => 'Ключевые слова',
            'seo_description' => 'Описание',
            'create_user' => 'Создал',
            'create_date' => 'Дата создания',
            'update_user' => 'Редактировал',
            'update_date' => 'Дата обн.',

            'article' => 'Артикул',
            'price' => 'Цена',
            'price_old' => 'Старая цена',
            'currency' => 'Валюта',
            'unit' => 'Ед. измерения',
        ];
    }

    public function getMetaDescription()
    {
        return $this->seo_description ?: mb_substr(strip_tags($this->title), 0, 255);
    }

    public function getMetaKeywords()
    {
        return $this->seo_keywords;
    }

    /**
     * @param $blockId
     * @param $slug
     * @return BlockItem|array
     */
    public static function getBySlug($blockId, $slug)
    {
        return self::find()
            ->with(['propAssignments.prop'])
            ->where(['path' => $slug])
            ->andWhere(['block_id' => $blockId])
            ->active()
            ->one();
    }

    /**
     * @inheritdoc
     * @return BlockItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BlockItemQuery(static::class);
    }

    /**
     * @throws NotFoundHttpException
     * @return self
     */
    public static function findOrFail($id)
    {
        if (($model = self::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
