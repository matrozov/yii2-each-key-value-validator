<?php
namespace matrozov\yii2eachKeyValueValidator;

use Yii;
use yii\base\DynamicModel;
use yii\validators\Validator;

class EachKeyValueValidator extends Validator
{
    public $rules = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * @param \yii\base\Model $model
     * @param string          $attribute
     *
     * @throws
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            $this->addError($model, $attribute, $this->message, []);
            return;
        }

        $filtered = [];

        $rules = [];

        foreach ($this->rules as $rule) {
            $fields = [];

            foreach ((array)$rule[0] as $field) {
                $fields[] = $attribute . ':' . $field;
            }

            $rules[] = array_merge([$fields], array_slice($rule, 1));
        }

        foreach ($value as $key => $val) {
            $attributes = [
                $attribute . ':key'   => $key,
                $attribute . ':value' => $val,
            ];

            $dynModel = DynamicModel::validateData($attributes, $rules);

            $filtered[$dynModel[$attribute . ':key']] = $dynModel[$attribute . ':value'];

            foreach ($dynModel->errors as $errors) {
                foreach ($errors as $error) {
                    $this->addError($model, $attribute, $error);
                }
            }
        }

        $model->$attribute = $filtered;
    }
}