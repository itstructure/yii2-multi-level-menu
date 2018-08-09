Yii2 Multilevel menu widget
==============

1 Introduction
----------------------------

[![Latest Stable Version](https://poser.pugx.org/itstructure/yii2-multi-level-menu/v/stable)](https://packagist.org/packages/itstructure/yii2-multi-level-menu)
[![Latest Unstable Version](https://poser.pugx.org/itstructure/yii2-multi-level-menu/v/unstable)](https://packagist.org/packages/itstructure/yii2-multi-level-menu)
[![License](https://poser.pugx.org/itstructure/yii2-multi-level-menu/license)](https://packagist.org/packages/itstructure/yii2-multi-level-menu)
[![Total Downloads](https://poser.pugx.org/itstructure/yii2-multi-level-menu/downloads)](https://packagist.org/packages/itstructure/yii2-multi-level-menu)
[![Build Status](https://scrutinizer-ci.com/g/itstructure/yii2-multi-level-menu/badges/build.png?b=master)](https://scrutinizer-ci.com/g/itstructure/yii2-multi-level-menu/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/itstructure/yii2-multi-level-menu/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/itstructure/yii2-multi-level-menu/?branch=master)

This widget is designed to display a multilevel menu, in which there can be nested submenus. Used for Yii2 framework.

The widget uses data from the **database**, in which there are, in addition to the primary keys, also the parent keys.

Data from the **database** is taken from an active model, which instance of **yii\db\ActiveRecord**.

2 Dependencies
----------------------------

- php >= 7.1
- composer
- Yii2 = 2.*

3 Installation
----------------------------

Via composer:

```composer require "itstructure/yii2-multi-level-menu": "^3.2.3"```

or in section **require** of composer.json file set the following:
```
"require": {
    "itstructure/yii2-multi-level-menu": "^3.2.3"
}
```
and command ```composer install```, if you install yii2 project extensions first,

or command ```composer update```, if all yii2 project extensions are already installed.

## 4 Usage

### 4.1 Usage in view template

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

Example of ```itemTemplate``` file:

```php
use yii\helpers\{Url, Html};
/* @var app\models\Page $data */
```
```html
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

### 4.2 Database table structure example

```Table "pages"```

```php
| id  | parentId | title | ... |
|-----|----------|-------|-----|
|  1  |   NULL   | page1 | ... |
|  2  |   NULL   | page2 | ... |
|  3  |     1    | page3 | ... |
|  4  |     1    | page4 | ... |
|  5  |     4    | page5 | ... |
|  6  |     4    | page6 | ... |
|  7  |     3    | page7 | ... |
|  8  |     3    | page8 | ... |
|  9  |   NULL   | page9 | ... |
|  10 |   NULL   | page10| ... |
| ... |    ...   |  ...  | ... |
```

License
----------------------------

Copyright © 2018 Andrey Girnik girnikandrey@gmail.com.

Licensed under the [MIT license](http://opensource.org/licenses/MIT). See LICENSE.txt for details.
