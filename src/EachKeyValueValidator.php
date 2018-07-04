<?php
namespace matrozov\yii2eachKeyValueValidator;

use Yii;
use yii\base\DynamicModel;
use yii\validators\Validator;

class EachKeyValueValidator extends Validator
{
    const SEPARATOR = ':';

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
                $fields[] = $attribute . self::SEPARATOR . $field;
            }
            
            $method = $rule[1];

            if ($model->hasMethod($method)) {
                $rule[1] = function ($attribute, $param, $validator) use ($model, $method) {
                    call_user_func([$model, $method], $attribute, $param, $validator);
                };
            }

            $rules[] = array_merge([$fields, $rule[1]], array_slice($rule, 2));
        }

        foreach ($value as $key => $val) {
            $attributes = [
                $attribute . self::SEPARATOR . 'key'   => $key,
                $attribute . self::SEPARATOR . 'value' => $val,
            ];

            $dynModel = DynamicModel::validateData($attributes, $rules);

            $filtered[$dynModel[$attribute . self::SEPARATOR . 'key']] = $dynModel[$attribute . self::SEPARATOR . 'value'];

            foreach ($dynModel->errors as $errors) {
                foreach ($errors as $error) {
                    $this->addError($model, $attribute, $error);
                }
            }
        }

        $model->$attribute = $filtered;
    }

    /**
     * @param mixed $value
     *
     * @return array|null
     * @throws
     */
    public function validateValue($value)
    {
        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            return [$this->message, []];
        }

        foreach ($value as $key => $val) {
            $attributes = [
                'key'   => $key,
                'value' => $val,
            ];

            $dynModel = DynamicModel::validateData($attributes, $this->rules);

            if ($dynModel->hasErrors()) {
                return [reset(reset($dynModel->errors)), []];
            }
        }

        return null;
    }
}