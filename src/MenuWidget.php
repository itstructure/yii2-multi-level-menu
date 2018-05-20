<?php

namespace Itstructure\MultiLevelMenu;

use yii\db\ActiveRecord;
use yii\helpers\{Html, ArrayHelper};
use yii\base\{Widget, InvalidConfigException};
use yii\data\ActiveDataProvider;

/**
 * Class MenuWidget.
 * Multilevel menu widget.
 *
 * @property string $primaryKeyName Primary key name.
 * @property string $parentKeyName Relation key name.
 * @property string $mainContainerTag Main container html tag.
 * @property array $mainContainerOptions Main container html options.
 * @property string $subMainContainerTag Sub main container html tag.
 * @property array $subMainContainerOptions Sub main container html options.
 * @property string $itemContainerTag Item container html tag.
 * @property array $itemContainerOptions Item container html options.
 * @property string $subItemContainerTag Sub item container html tag.
 * @property array $subItemContainerOptions Sub item container html options.
 * @property string $itemTemplate Item template to display widget elements.
 * @property array $itemTemplateParams Addition item template params.
 * @property string $subItemTemplate Sub item template to display widget elements.
 * @property array $subItemTemplateParams Addition sub item template params.
 * @property ActiveDataProvider $dataProvider Data provider records.
 *
 * @package Itstructure\MultiLevelMenu
 *
 * @author Andrey Girnik <girnikandrey@gmail.com>
 */
class MenuWidget extends Widget
{
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
     * Sub main container html tag.
     * @var string
     */
    public $subMainContainerTag = 'ul';

    /**
     * Sub main container html options.
     * @var array
     */
    public $subMainContainerOptions = [];

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
     * Sub item container html tag.
     * @var string
     */
    public $subItemContainerTag = 'li';

    /**
     * Sub item container html options.
     * @var array
     */
    public $subItemContainerOptions = [];

    /**
     * Item template to display widget elements.
     * @var string
     */
    public $itemTemplate;

    /**
     * Addition item template params.
     * @var array
     */
    public $itemTemplateParams = [];

    /**
     * Sub item template to display widget elements.
     * @var string
     */
    public $subItemTemplate;

    /**
     * Addition sub item template params.
     * @var array
     */
    public $subItemTemplateParams = [];

    /**
     * Data provider records.
     * @var ActiveDataProvider
     */
    private $dataProvider;

    /**
     * Starts the output widget of the multi level view records according with the menu type.
     * @throws InvalidConfigException
     */
    public function run()
    {
        $this->checkConfiguration();

        /** @var ActiveRecord[] $models */
        $models = array_values($this->dataProvider->getModels());
        $models = $this->groupLevels($models);

        return $this->renderItems($models);
    }

    /**
     * Set data provider.
     * @param ActiveDataProvider $dataProvider
     */
    public function setDataProvider(ActiveDataProvider $dataProvider): void
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Group records in to sub levels according with the relation to parent records.
     * @param array $models
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
     * @param bool $initLevel
     * @return string
     */
    private function renderItems(array $items, bool $initLevel = true): string
    {
        if (count($items) == 0){
            return '';
        }

        $outPut = '';

        /** @var array $item */
        foreach ($items as $item) {
            $contentLi = $this->render($this->currentItemTemplate($initLevel), ArrayHelper::merge([
                'data' => $item['data']
            ], $this->currentItemTemplateParams($initLevel)));

            if (isset($item['items'])){
                $contentLi .= $this->renderItems($item['items'], false);
            }
            $outPut .= Html::tag($this->currentItemContainerTag($initLevel), $contentLi, $this->currentItemContainerOptions($initLevel));
        }

        return Html::tag($this->currentMainContainerTag($initLevel), $outPut, $this->currentMainContainerOptions($initLevel));
    }

    /**
     * @param bool $initLevel
     * @return string
     */
    private function currentItemTemplate(bool $initLevel = true): string
    {
        return $initLevel ? $this->itemTemplate : $this->subItemTemplate;
    }

    /**
     * @param bool $initLevel
     * @return array
     */
    private function currentItemTemplateParams(bool $initLevel = true): array
    {
        return $initLevel ? $this->itemTemplateParams : $this->subItemTemplateParams;
    }

    /**
     * @param bool $initLevel
     * @return string
     */
    private function currentMainContainerTag(bool $initLevel = true): string
    {
        return $initLevel ? $this->mainContainerTag : $this->subMainContainerTag;
    }

    /**
     * @param bool $initLevel
     * @return array
     */
    private function currentMainContainerOptions(bool $initLevel = true): array
    {
        return $initLevel ? $this->mainContainerOptions : $this->subMainContainerOptions;
    }

    /**
     * @param bool $initLevel
     * @return string
     */
    private function currentItemContainerTag(bool $initLevel = true): string
    {
        return $initLevel ? $this->itemContainerTag : $this->subItemContainerTag;
    }

    /**
     * @param bool $initLevel
     * @return array
     */
    private function currentItemContainerOptions(bool $initLevel = true): array
    {
        return $initLevel ? $this->itemContainerOptions : $this->subItemContainerOptions;
    }

    /**
     * Check for configure.
     * @throws InvalidConfigException
     */
    private function checkConfiguration()
    {
        if (null === $this->dataProvider){
            throw  new InvalidConfigException('Parameter dataProvider is not defined.');
        }

        if (null === $this->itemTemplate || !is_string($this->itemTemplate)){
            throw  new InvalidConfigException('Item template is not defined.');
        }

        if (null === $this->subItemTemplate || !is_string($this->subItemTemplate)){
            $this->subItemTemplate = $this->itemTemplate;
        }
    }
}
