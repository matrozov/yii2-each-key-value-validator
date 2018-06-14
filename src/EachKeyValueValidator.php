<?php
namespace matrozov\yii2eachKeyValueValidator;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\validators\Validator;

class EachKeyValueValidator extends Validator
{
    public $keyRules   = [];
    public $valueRules = [];

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
     * @param array|Validator $rule
     * @param null $model
     *
     * @return Validator
     * @throws
     */
    private function getValidator($model, $rule)
    {
        if ($rule instanceof Validator) {
            return $rule;
        }
        elseif (is_array($rule) && isset($rule[0])) {
            return Validator::createValidator($rule[0], $model, $this->attributes, array_slice($rule, 1));
        }

        throw new InvalidConfigException('Invalid validation rule: a rule must be an array specifying validator type.');
    }

    /**
     * @param null $model
     *
     * @param      $rules
     * @param      $attribute
     * @param      $value
     *
     * @return array|null
     * @throws
     */
    private function validateAttributeKeyValue($model, $attribute, $rules, $value)
    {
        if (empty($rules)) {
            return $value;
        }

        $save = $this->$attribute;
        $this->$attribute = $value;

        foreach ($rules as $rule) {
            $validator = $this->getValidator($model, $rule);

            $validator->validateAttribute($model, $attribute);
        }

        $value = $this->$attribute;
        $this->$attribute = $save;

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            $this->addError($model, $attribute, $this->message, []);\
            return;
        }

        $filtered = [];

        foreach ($value as $key => $val) {
            $key = $this->validateAttributeKeyValue($model, $attribute, $this->keyRules, $key);
            $val = $this->validateAttributeKeyValue($model, $attribute, $this->valueRules, $val);

            $filtered[$key] = $val;
        }

        $model->$attribute = $filtered;
    }

    /**
     * @param $rules
     * @param $value
     *
     * @return array|null
     * @throws
     */
    protected function validateKeyValue($rules, $value)
    {
        if (empty($rules)) {
            return $value;
        }

        $model = new Model();

        foreach ($rules as $rule) {
            $validator = $this->getValidator($model, $rule);

            if (($result = $validator->validateValue($value)) !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validateValue($value)
    {
        if (!is_array($value) && !($value instanceof \ArrayAccess)) {
            return [$this->message, []];
        }

        foreach ($value as $key => $val) {
            if (($result = $this->validateKeyValue($this->keyRules, $key)) !== null) {
                return $result;
            }

            if (($result = $this->validateKeyValue($this->valueRules, $val)) !== null) {
                return $result;
            }
        }

        return null;
    }
}