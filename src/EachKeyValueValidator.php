<?php
namespace matrozov\yii2eachKeyValueValidator;

use Yii;
use yii\base\DynamicModel;
use yii\validators\Validator;

class EachKeyValueValidator extends Validator
{
    public $rules = [];

    public $messageField = '{attribute}.{message}';

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

        foreach ($value as $key => $val) {
            $attributes = [
                'key'   => $key,
                'value' => $val,
            ];

            $dynModel = DynamicModel::validateData($attributes, $this->rules);

            $filtered[$dynModel['key']] = $dynModel['value'];

            foreach ($dynModel->errors as $errors) {
                foreach ($errors as $error) {
                    $this->addError($model, $attribute, $this->messageField, ['message' => $error]);
                }
            }
        }

        $model->$attribute = $filtered;
    }
}