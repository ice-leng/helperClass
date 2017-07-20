<?php
/**
 * 饿了么 vue js 的 form validate 生成帮助类
 * User: lengbin
 * Date: 2017/2/23
 * Time: 上午10:57
 */

namespace lengbin\helper\yii2;


class ElemeVueJsFormValidate extends CreateFromValidate
{

    private $_notSupportValidate = [
        'trim',
    ];
    private $_supportValidate = [
        'required',
        'string',
        'number',
        'email',
        'integer',
        'url',
        'in',
        'match',
    ];
    private $_required = [];

    /**
     * 重构
     *
     * @param $rule
     *
     * @return array
     */
    private function _refactoring($rule)
    {
        $data = [];
        switch ($rule['type']) {
            case 'required':
                $data = [
                    'required' => true,
                    'message'  => $rule['message'],
                ];
                $field = [];
                foreach ($rule['field'] as $f){
                    $field[$f] = true;
                }
                $this->_required = array_merge($this->_required, $field);
                break;
            case 'string':
                if (isset($rule['min'])) {
                    $data = [
                        'type'    => 'string',
                        'min'     => $rule['min'],
                        'message' => $rule['message'],
                    ];
                }
                if (isset($rule['max'])) {
                    $data = [
                        'type'    => 'string',
                        'max'     => $rule['max'],
                        'message' => $rule['message'],
                    ];
                }
                if (isset($rule['min']) && isset($rule['max'])) {
                    $data = [
                        'type'    => 'string',
                        'min'     => $rule['min'],
                        'max'     => $rule['max'],
                        'message' => $rule['message'],
                    ];
                }
                break;
            case 'integer':
                if (isset($rule['min'])) {
                    $data = [
                        'type'    => 'integer',
                        'min'     => $rule['min'],
                        'message' => $rule['message'],
                    ];
                } else if (isset($rule['max'])) {
                    $data = [
                        'type'    => 'integer',
                        'max'     => $rule['max'],
                        'message' => $rule['message'],
                    ];
                }else if (isset($rule['min']) && isset($rule['max'])) {
                    $data = [
                        'type'    => 'integer',
                        'min'     => $rule['min'],
                        'max'     => $rule['max'],
                        'message' => $rule['message'],
                    ];
                }else{
                    $data = [
                        'type'    => 'integer',
                        'message' => $rule['message'],
                    ];
                }

                break;
            case 'number':
                $data = [
                    'type'    => 'number',
                    'message' => $rule['message'],
                ];
                break;
            case 'email':
                $data = [
                    'type'    => 'email',
                    'message' => $rule['message'],
                ];
                break;
            case 'in' :
                $data = [
                    'type'    => 'array',
                    'message' => $rule['message'],
                ];
                break;
            case 'url':
                $data = [
                    'type'    => 'url',
                    'message' => $rule['message'],
                ];
                break;
            default:
                $data = [
                    'type'    => 'string',
                    'pattern' => $rule['rule'],
                    'message' => $rule['message'],
                ];
                break;
        }
        return $data;
    }

    public function createValidate()
    {
        $data = $fields = [];
        $rules = parent::createValidate();
        foreach ($rules['validates'] as $rule) {
            if (in_array($rule['type'], $this->_notSupportValidate)) {
                continue;
            }
            if (!in_array($rule['type'], $this->_supportValidate)) {
                continue;
            }
            foreach ($rule['field'] as $field) {
                if (isset($data[$field])) {
                    array_push($data[$field], $this->_refactoring($rule));
                } else {
                    $data[$field][] = $this->_refactoring($rule);
                }

            }
        }
        $rules['validates'] = $data;
        foreach ($rules['labels'] as $field => $label) {
            $fields[$field] = '';
        }
        $rules['model'] = $fields;
        $rules['required'] = $this->_required;
        return $rules;
    }

}