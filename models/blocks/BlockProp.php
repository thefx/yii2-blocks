<?php

namespace thefx\blocks\models\blocks;

use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use thefx\blocks\models\blocks\queries\BlockPropQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%block_prop}}".
 *
 * @property int $id
 * @property int $block_id
 * @property string $title
 * @property string $type
 * @property int $public
 * @property int $multi
 * @property int $required
 * @property int $sort
 * @property string $code
 * @property int $in_filter
 * @property string $hint
 * @property Block $block
 * @property BlockPropElem[] $elements
 * @property BlockItemPropAssignments[] $assignments
 * @property int $relative_block_item
 * @property int $relative_block_cat
 * @property int $redactor
 * @property string $upload_path
 * @property string $watermark_path
 * @property string $web_path
 * @property string $default_value [varchar(255)]
 *
 * @mixin SaveRelationsBehavior
 */
class BlockProp extends ActiveRecord
{
//    use SaveRelationsTrait; // Optional

    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_LIST = 'list';
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';
    const TYPE_GALLERY = 'gallery';
    const TYPE_RELATIVE_BLOCK_ITEM = 'relative_block_item';
    const TYPE_RELATIVE_BLOCK_CAT = 'relative_block_cat';
    const TYPE_SWITCHER = 'switcher';

    public function getTypes()
    {
        return [
            self::TYPE_STRING => 'Строка',
            self::TYPE_INT => 'Число',
            self::TYPE_TEXT => 'Текст',
            self::TYPE_LIST => 'Список',
            self::TYPE_FILE => 'Файл',
            self::TYPE_IMAGE => 'Фото',
            self::TYPE_SWITCHER => 'Переключатель',
//            self::TYPE_GALLERY => 'Галлерея',
            self::TYPE_RELATIVE_BLOCK_ITEM => 'Связанный блок (элемент)',
            self::TYPE_RELATIVE_BLOCK_CAT => 'Связанный блок (группа)',
        ];
    }

    public function getTypeName(): string
    {
        return $this->getTypes()[$this->type];
    }

    public function getBlocksList(): array
    {
        $blocks = Block::find()->select(['id', 'title'])->all();

        return ArrayHelper::map($blocks, 'id', 'title');
    }

    public function isRequired(): bool
    {
        return $this->required === 1;
    }

    public function isMulti(): bool
    {
        return $this->multi === 1;
    }

    public function isString(): bool
    {
        return $this->type === self::TYPE_STRING;
    }

    public function isInteger(): bool
    {
        return $this->type === self::TYPE_INT;
    }

    public function isFile(): bool
    {
        return $this->type === self::TYPE_FILE;
    }

    public function isImage(): bool
    {
        return $this->type === self::TYPE_IMAGE;
    }

    public function isList(): bool
    {
        return $this->type === self::TYPE_LIST;
    }

//    public function beforeValidate()
//    {
//        var_dump($this->validators);
//        die;

//        $validator = Validator::createValidator('file', $this, $this->attributeName,  [
//            'mimeTypes' => 'image/*',
////                'extensions'=>$this->extensions,
//        ]);
//        $validator->validateAttribute($this, $this->attributeName);

//        switch ($this->type) {
//            case self::TYPE_STRING:
//                $this->setScenario('type_string');
//                break;
//            case 1:
//                echo "i равно 1";
//                break;
//            case 2:
//                echo "i равно 2";
//                break;
//            default:
//                echo "i не равно 0, 1 или 2";
//        }
//        return parent::beforeValidate();
//    }

    public function assignPropElement(BlockPropElem $element)
    {
        $assignments = $this->elements;

        if ($element->isNewRecord) {
            $assignments[] = $element;
            $this->elements = $assignments;
            return;
        }
        foreach ($assignments as $k => $assignment) {
            if ($assignment->isExists($element->getAttribute('id'))) {
                $assignments[$k] = $element;
                $this->elements = $assignments;
                return;
            }
        }
    }

    public function revokePropElements()
    {
        $this->elements = [];
    }

    public function getBlock(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Block::class, ['id' => 'block_id']);
    }

    public function getElements(): \yii\db\ActiveQuery
    {
        return $this->hasMany(BlockPropElem::class, ['block_prop_id' => 'id'])->orderBy('sort ASC, id DESC');
    }

    public function getAssignments(): \yii\db\ActiveQuery
    {
        return $this->hasMany(BlockItemPropAssignments::class, ['prop_id' => 'id']);
    }

    public function getAssignBlockItemList(): array
    {
        $items = BlockItem::find()
            ->select(['id', 'title'])
            ->where(['block_id' => $this->relative_block_item])
            ->andWhere(['public' => 1])
            ->orderBy('sort DESC, id DESC')
            ->all();

        return ArrayHelper::map($items, 'id', static function (BlockItem $row) { return '[' . $row->id . '] ' . $row->title; });
    }

    public function getAssignBlockCatList(): array
    {
        $items = BlockCategory::find()
            ->select(['id', 'title', 'depth'])
            ->where(['block_id' => $this->relative_block_cat])
            ->andWhere(['public' => 1])
            ->orderBy('lft')
            ->all();

        return ArrayHelper::map($items, 'id', static function (BlockCategory $row) { return '[' . $row->id . '] ' . str_repeat('.', $row->depth) . $row->title; });
    }

    ####################

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%block_prop}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['block_id', 'code', 'sort'], 'required'],
            [['block_id', 'public', 'multi', 'required', 'sort', 'in_filter', 'relative_block_item', 'relative_block_cat', 'redactor'], 'integer'],
            [['title', 'type', 'code', 'hint', 'default_value'], 'string', 'max' => 255],
            [['upload_path', 'watermark_path', 'web_path'], 'string'],
            [['redactor'], 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'block_id' => 'Блок',
            'title' => 'Название',
            'type' => 'Тип',
            'public' => 'Активно',
            'multi' => 'Множественное',
            'required' => 'Обязательно',
            'sort' => 'Сортировка',
            'code' => 'Код',
            'in_filter' => 'В фильтре',
            'hint' => 'Подсказка',
            'relative_block_item' => 'Связанный блок (элемент)',
            'relative_block_cat' => 'Связанный блок (группа)',
            'upload_path' => 'Путь для загрузки (если отличается от стандартного)',
            'watermark_path' => 'Путь для фонового изображения (если отличается от стандартного)',
            'web_path' => 'Url папки загрузки (если отличается от стандартного)',
            'redactor' => 'Редактор',
            'default_value' => 'Значение по умолчанию (Для Переключателя)',
        ];
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => SaveRelationsBehavior::class,
                'relations' => ['elements'],
            ],
        ];
    }

    public function transactions(): array
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     * @return BlockPropQuery the active query used by this AR class.
     */
    public static function find(): BlockPropQuery
    {
        return new BlockPropQuery(static::class);
    }
}
