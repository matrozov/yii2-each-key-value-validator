# yii2-each-key-value-validator
This extension provides the simple key-value validation for Yii framework 2.0.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/matrozov/yii2-eachKeyValue-validator/v/stable.png)](https://packagist.org/packages/matrozov/yii2-eachKeyValue-validator)
[![Total Downloads](https://poser.pugx.org/matrozov/yii2-eachKeyValue-validator/downloads.png)](https://packagist.org/packages/matrozov/yii2-eachKeyValue-validator)
[![License](https://poser.pugx.org/matrozov/yii2-eachKeyValue-validator/license)](https://packagist.org/packages/matrozov/yii2-eachKeyValue-validator)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
php composer.phar require --prefer-dist matrozov/yii2-eachKeyValue-validator
```

or add

```
"matrozov/yii2-eachKeyValue-validator": "dev-master"
```

to the require section of your composer.json.

## Usage


```php
class MyClass extends \yii\base\Model
{
    public $data;
    
    public function rules()
    {
        return [
            [['data'], EachKeyValueValidator::class, 'rules' => [
                [['key', 'value'], 'string'], // Validate, what key and value must be string.
            ]],
        ];
    }
}

$model = new MyModel();
$model->data = [
    'myKey' => 2,
];

$model->validate();
print_r($model->errors);
```
You got error: `Data:value must be a string`!

You can assign variable validation for `key` and `value` pseudo-field.
