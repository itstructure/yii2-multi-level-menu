Yii2 Multilevel menu widget
==============

## Introduction

[![Latest Stable Version](https://poser.pugx.org/itstructure/yii2-multi-level-menu/v/stable)](https://packagist.org/packages/itstructure/yii2-multi-level-menu)
[![Latest Unstable Version](https://poser.pugx.org/itstructure/yii2-multi-level-menu/v/unstable)](https://packagist.org/packages/itstructure/yii2-multi-level-menu)
[![License](https://poser.pugx.org/itstructure/yii2-multi-level-menu/license)](https://packagist.org/packages/itstructure/yii2-multi-level-menu)
[![Total Downloads](https://poser.pugx.org/itstructure/yii2-multi-level-menu/downloads)](https://packagist.org/packages/itstructure/yii2-multi-level-menu)
[![Build Status](https://scrutinizer-ci.com/g/itstructure/yii2-multi-level-menu/badges/build.png?b=master)](https://scrutinizer-ci.com/g/itstructure/yii2-multi-level-menu/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/itstructure/yii2-multi-level-menu/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/itstructure/yii2-multi-level-menu/?branch=master)

This widget is designed to display a multilevel menu, in which there can be nested submenus. Used for Yii2 framework.

The widget uses data from the **database**, in which there are, in addition to the primary keys, also the parent keys.

Data from the **database** is taken from an active model, which instance of **yii\db\ActiveRecord**.

![Multi level menu example scheme](https://github.com/itstructure/yii2-multi-level-menu/blob/master/ML_menu_en.jpg)

## Requirements

- php >= 7.1
- composer

## Installation

Via composer:

`composer require itstructure/yii2-multi-level-menu ~3.2.9`

## Usage

### Usage in view template

Base application config must be like in example below:

```php
use Itstructure\MultiLevelMenu\MenuWidget;
```

```php
echo MenuWidget::widget([
    'menuId' => 'multi-level-menu',
    'data' => array_values($dataProvider->getModels()),
    'itemTemplate' => '@app/views/MultiLevelMenu/main.php'
]);
```

Example of `itemTemplate` file:

```php
use yii\helpers\{Url, Html};
/* @var app\models\Page $data */
```

```php
<span>
    <?php echo Html::a(
        Html::encode($data->title),
        Url::to(['view', 'id' => $data->id])
    ) ?>
</span>
```

Example when there are some properties for nesting levels:

```php
use Itstructure\MultiLevelMenu\MenuWidget;
```

```php
echo MenuWidget::widget([
    'menuId' => 'multi-level-menu',
    'data' => array_values($dataProvider->getModels()),
    'itemTemplate' => '@app/views/MultiLevelMenu/main.php'
    'mainContainerOptions' => [
        'class' => 'list-group'
    ],
    'itemContainerOptions' => [
        'levels' => [
            ['class' => 'list-group-item'],
            ['class' => 'list-group-item list-group-item-success'],
            ['class' => 'list-group-item list-group-item-warning'],
        ]
    ],
]);
```

Example when there are some properties as callable function:

```php
use Itstructure\MultiLevelMenu\MenuWidget;
```

```php
echo MenuWidget::widget([
    'menuId' => 'multi-level-menu',
    'data' => array_values($dataProvider->getModels()),
    'itemTemplate' => '@app/views/MultiLevelMenu/main.php'
    'mainContainerOptions' => function () {
        return [
            'class' => $level == 0 ? 'nav navbar-nav navbar-right' : 'dropdown-menu'
        ];
    },
    'itemTemplateParams' => function ($level, $item) {
        return [
            'linkOptions' => isset($item['items']) && count($item['items']) > 0 ? [
                'class' => 'dropdown-toggle',
                'data-toggle' => 'dropdown',
                'aria-haspopup' => 'true',
                'aria-expanded' => 'false',
            ] : [],
        ];
    },
    'itemContainerOptions' => function ($level, $item) {
        return $level == 0 ? [
            'class' => isset($item['items']) && count($item['items']) > 0 ? 'nav-item dropdown' : 'nav-item'
        ] : [
            'class' => isset($item['items']) && count($item['items']) > 0 ? 'dropdown-item dropdown' : 'dropdown-item'
        ];
    }
]);
```


### Database table structure example

`Table "pages"`

    | id  | parentId |   title    | ... |
    |-----|----------|------------|-----|
    |  1  |   NULL   |   item 1   | ... |
    |  2  |   NULL   |   item 2   | ... |
    |  3  |   NULL   |   item 3   | ... |
    |  4  |   NULL   |   item 4   | ... |
    |  5  |   NULL   |   item 5   | ... |
    |  6  |     2    |  item 2.1  | ... |
    |  7  |     2    |  item 2.2  | ... |
    |  8  |     7    | item 2.2.1 | ... |
    |  9  |     7    | item 2.2.2 | ... |
    |  10 |     7    | item 2.2.3 | ... |
    | ... |    ...   |     ...    | ... |

## License

Copyright © 2018-2023 Andrey Girnik girnikandrey@gmail.com.

Licensed under the [MIT license](http://opensource.org/licenses/MIT). See LICENSE.txt for details.
