<?php

namespace Itstructure\MultiLevelMenu;

use yii\db\ActiveRecord;
use yii\helpers\{Html, ArrayHelper};
use yii\base\{Widget, InvalidConfigException};

/**
 * Class MenuWidget.
 * Multilevel menu widget.
 *
 * @property string $menuId Init level menu html tag id.
 * @property string $primaryKeyName Primary key name.
 * @property string $parentKeyName Relation key name.
 * @property string $mainContainerTag Main container html tag.
 * @property array $mainContainerOptions Main container html options.
 * @property string $itemContainerTag Item container html tag.
 * @property array $itemContainerOptions Item container html options.
 * @property string|array $itemTemplate Item template to display widget elements.
 * @property array $itemTemplateParams Addition item template params.
 * @property ActiveRecord[] $data Data records.
 *
 * @package Itstructure\MultiLevelMenu
 *
 * @author Andrey Girnik <girnikandrey@gmail.com>
 */
class MenuWidget extends Widget
{
    /**
     * Init level menu html tag id.
     * @var string
     */
    public $menuId;

    /**
     * Primary key name.
     * @var string
     */
    public $primaryKeyName = 'id';

    /**
     * Relation key name.
     * @var string
     */
    public $parentKeyName = 'parentId';

    /**
     * Main container html tag.
     * @var string
     */
    public $mainContainerTag = 'ul';

    /**
     * Main container html options.
     * @var array
     */
    public $mainContainerOptions = [];

    /**
     * Item container html tag.
     * @var string
     */
    public $itemContainerTag = 'li';

    /**
     * Item container html options.
     * @var array
     */
    public $itemContainerOptions = [];

    /**
     * Item template to display widget elements.
     * @var string|array
     */
    public $itemTemplate;

    /**
     * Addition item template params.
     * @var array
     */
    public $itemTemplateParams = [];

    /**
     * Data records.
     * @var ActiveRecord[]
     */
    public $data;

    /**
     * Starts the output widget of the multi level view records according with the menu type.
     * @throws InvalidConfigException
     */
    public function run()
    {
        $this->checkConfiguration();

        return $this->renderItems($this->groupLevels($this->data));
    }

    /**
     * Check whether a particular record can be used as a parent.
     * @param ActiveRecord $mainModel
     * @param int $newParentId
     * @param string $primaryKeyName
     * @param string $parentKeyName
     * @return bool
     */
    public static function checkNewParentId(ActiveRecord $mainModel, int $newParentId, string $primaryKeyName = 'id', string $parentKeyName = 'parentId'): bool
    {
        $parentRecord = $mainModel::find()->select([$primaryKeyName, $parentKeyName])->where([
            $primaryKeyName => $newParentId
        ])->one();

        if ($mainModel->{$primaryKeyName} === $parentRecord->{$primaryKeyName}){
            return false;
        }

        if (null === $parentRecord->{$parentKeyName}){
            return true;
        }

        return static::checkNewParentId($mainModel, $parentRecord->{$parentKeyName});
    }

    /**
     * Reassigning child objects to their new parent after delete the main model record.
     * @param ActiveRecord $mainModel
     * @param string $primaryKeyName
     * @param string $parentKeyName
     */
    public static function afterDeleteMainModel(ActiveRecord $mainModel, string $primaryKeyName = 'id', string $parentKeyName = 'parentId'): void
    {
        $mainModel::updateAll([$parentKeyName => $mainModel->{$parentKeyName}], ['=', $parentKeyName, $mainModel->{$primaryKeyName}]);
    }

    /**
     * Check for configure.
     * @throws InvalidConfigException
     */
    private function checkConfiguration()
    {
        if (null === $this->itemTemplate){
            throw  new InvalidConfigException('Item template is not defined.');
        }

        if (is_array($this->itemTemplate) && !isset($this->itemTemplate['levels'])){
            throw  new InvalidConfigException('If item template is array, that has to contain levels key.');
        }
    }

    /**
     * Group records in to sub levels according with the relation to parent records.
     * @param array $models
     * @throws InvalidConfigException
     * @return array
     */
    private function groupLevels(array $models): array
    {
        if (count($models) == 0){
            return [];
        }

        $items = [];

        /** @var ActiveRecord $item */
        $modelsCount = count($models);
        for ($i=0; $i < $modelsCount; $i++) {
            $item = $models[$i];

            if (!($item instanceof ActiveRecord)){
                throw  new InvalidConfigException('Record with '.$i.' key must be an instance of ActiveRecord.');
            }

            $items[$item->{$this->primaryKeyName}]['data'] = $item;
        }

        /** @var ActiveRecord $data */
        foreach($items as $row) {
            $data = $row['data'];
            $parentKey = !isset($data->{$this->parentKeyName}) || empty($data->{$this->parentKeyName}) ? 0 : $data->{$this->parentKeyName};
            $items[$parentKey]['items'][$data->{$this->primaryKeyName}] = &$items[$data->{$this->primaryKeyName}];
        }

        return $items[0]['items'];
    }

    /**
     * Base render.
     * @param array $items
     * @param int $level
     * @return string
     */
    private function renderItems(array $items, int $level = 0): string
    {
        if (count($items) == 0){
            return '';
        }

        $outPut = '';

        /** @var array $item */
        foreach ($items as $item) {
            $contentLi = $this->render($this->levelAttributeValue('itemTemplate', $level), ArrayHelper::merge([
                'data' => $item['data']
            ], $this->levelAttributeValue('itemTemplateParams', $level)));

            if (isset($item['items'])){
                $contentLi .= $this->renderItems($item['items'], $level + 1);
            }
            $outPut .= Html::tag($this->itemContainerTag, $contentLi, $this->levelAttributeValue('itemContainerOptions', $level));
        }

        $mainContainerOptions = $this->levelAttributeValue('mainContainerOptions', $level);

        if ($level == 0 && null !== $this->menuId){
            $mainContainerOptions = ArrayHelper::merge($mainContainerOptions, ['id' => $this->menuId]);
        }

        return Html::tag($this->mainContainerTag, $outPut, $mainContainerOptions);
    }

    /**
     * Get attribute values in current level.
     * @param string $attributeName
     * @param int $level
     * @throws InvalidConfigException
     * @return mixed
     */
    private function levelAttributeValue(string $attributeName, int $level)
    {
        $attributeValue = $this->{$attributeName};

        if (is_string($attributeValue)){
            return $attributeValue;
        }

        if (is_array($attributeValue) && !isset($attributeValue['levels'])){
            return $attributeValue;
        }

        if (is_array($attributeValue) && isset($attributeValue['levels'])){

            $countLevels = count($attributeValue['levels']);

            if ($countLevels == 0){
                throw new InvalidConfigException('Level values are not defined for attribute '.$attributeName.'.');
            }

            return isset($attributeValue['levels'][$level]) ? $attributeValue['levels'][$level] : $attributeValue['levels'][($countLevels-1)];
        }

        throw new InvalidConfigException('Attribute '.$attributeName.' is not defined correctly.');
    }
}
