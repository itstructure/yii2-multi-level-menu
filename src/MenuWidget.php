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
 * @property string $itemContainerTag Item container html tag.
 * @property array $itemContainerOptions Item container html options.
 * @property string $itemTemplate Item template to display widget elements.
 * @property array $itemTemplateParams Addition item template params.
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
     * @var string
     */
    public $itemTemplate;

    /**
     * Addition item template params.
     * @var array
     */
    public $itemTemplateParams = [];

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
        if (null === $this->dataProvider){
            throw  new InvalidConfigException('Parameter dataProvider is not defined.');
        }

        if (null === $this->itemTemplate || !is_string($this->itemTemplate)){
            throw  new InvalidConfigException('Item template is not defined.');
        }

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
        for ($i=0; $i < count($models); $i++) {
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
     * @return string
     */
    private function renderItems(array $items): string
    {
        if (count($items) == 0){
            return '';
        }

        $outPut = '';

        /** @var array $item */
        foreach ($items as $item) {
            $contentLi = $this->render($this->itemTemplate, ArrayHelper::merge([
                'data' => $item['data']
            ], $this->itemTemplateParams));

            if (isset($item['items'])){
                $contentLi .= $this->renderItems($item['items']);
            }
            $outPut .= Html::tag($this->itemContainerTag, $contentLi, $this->itemContainerOptions);
        }

        return Html::tag($this->mainContainerTag, $outPut, $this->mainContainerOptions);
    }
}
