<?php
/**
 * Created by PhpStorm.
 * User: ice.leng(lengbin@geridge.com)
 * Date: 16/8/15
 * Time: 下午5:23
 */
namespace lengbin\helper\yii2;

use yii\base\Model;
use yii\validators\InlineValidator;

class CreateFromValidate
{

    private $_model;
    private $_validateUrls;
    private $_hasMinMax  = ['string', 'integer'];
    private $_exclude    = ['filter', 'file', 'image'];
    private $_string     = 'string';
    private $_integer    = 'integer';
    private $_number     = 'number';
    private $_url        = 'url';
    private $_in         = 'in';
    private $_match      = 'match';
    private $_notMessage = ['unique', 'exist', 'trim'];
    private $_compare    = 'compare';

    /**
     * CreateFromValidate constructor.
     *
     * @param Model $model       表单对象 如果有场景的 记得 加上哈
     * @param array $validateUrl 需要做ajax请求的路由地址 [ type => http://xxxxx.com ]
     */
    public function __construct(Model $model, array $validateUrl = [])
    {
        $this->_model = $model;
        $this->_validateUrls = $validateUrl;
    }

    /**
     * 信息生成
     *
     * @param       $msg
     * @param array $data
     *
     * @return string
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    private function _setMessage($msg, $data = [])
    {
        $replace = ['{attribute}' => ''];
        $replace = array_merge($replace, $data);
        $data = strtr($msg, $replace);
        if (mb_substr($data, 0, 1) === '的') {
            $data = mb_substr($data, 1);
        }
        return $data;
    }

    /**
     * @param $type
     * @param $rule
     * @param $validator
     *
     * @return array
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    private function _hasMinMaxValidate($type, $rule, $validator)
    {
        $data = [];
        if( isset( $rule['min'] ) ){
            $data['min'] = $rule['min'];
            $replace = ['{min, number}' => $data['min'], '{min}' => $data['min']];
            $msg = $this->_string == $type ? $validator->tooShort : $validator->tooSmall;
            $data['message'] = ( isset( $rule['message'] ) && !empty( $rule['message'] ) ) ? $rule['message'] : $this->_setMessage( $msg, $replace );
        }
        if( isset( $rule['max'] ) ){
            $data['max'] = $rule['max'];
            $replace = ['{max, number}' => $data['max'], '{max}' => $data['max']];
            $msg = $this->_string == $type ? $validator->tooLong : $validator->tooBig;
            $data['message'] = ( isset( $rule['message'] ) && !empty( $rule['message'] ) ) ? $rule['message'] : $this->_setMessage( $msg, $replace );
        }

        if( isset( $rule['length'] ) ){
//            $data['length'] = $rule['length'];
//            $replace = ['{length, number}' => $data['length']];
//            $data['message'] = ( isset( $rule['message'] ) && !empty( $rule['message'] ) ) ? $rule['message'] : $this->_setMessage( $validator->notEqual, $replace );
            $rule = array_merge($rule, [
                'min' => $rule['length'][0],
                'max' => $rule['length'][1],
            ]);
            unset($rule['length']);
        }
        if( isset( $rule['min'] ) && isset( $rule['max'] ) ){
            $data['min'] = $rule['min'];
            $data['max'] = $rule['max'];
            $replace = [
                '{min, number}' => $data['min'],
                '{min}'         => $data['min'],
                '{max, number}' => $data['max'],
                '{max}'         => $data['max'],
            ];
            $msg = $this->_string == $type ? $validator->tooShort : $validator->tooSmall;
            $msg .= $this->_string == $type ? $validator->tooLong : $validator->tooBig;
            $data['message'] = ( isset( $rule['message'] ) && !empty( $rule['message'] ) ) ? $rule['message'] : $this->_setMessage( $msg, $replace );
        }
        return $data;
    }

    /**
     * 生成 规则 正则, 提示语言
     *
     * @param int   $index
     * @param array $rule
     *
     * @return array
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    private function _generateRule($index, $rule)
    {
        $data = [];
        $type = $rule[1];
        $inlineValidatorClassName = InlineValidator::className();
        $validators = $this->_model->getActiveValidators();
        $validator = $validators[ $index ];
        // default message
        $msg = isset( $rule['message'] ) ? $rule['message'] : $validator->message;
        $data['message'] = $this->_setMessage( $msg );
        //规则
        if( property_exists( $validator, 'pattern' ) && $type != $this->_url ){
            $data['rule'] = $validator->pattern;
        }else{
            switch( $type ){
                case $this->_integer:
                    $data['rule'] = $validator->integerPattern;
                    break;
                case $this->_number:
                    $data['rule'] = $validator->numberPattern;
                    break;
                case $this->_url:
                    if(isset($rule['defaultScheme'])){
                        $data['rule'] = str_replace( '{schemes}', $rule['defaultScheme'], $validator->pattern );
                        $data['defaultScheme'] = $rule['defaultScheme'];
                    }
                    break;
                case $this->_match:
                    $data['rule'] = $rule['pattern'];
                    break;
                default :
                    $data['rule'] = $type;
                    break;
            }
        }
        // 自定义的 方法 / 自定义类
        if( $validator->className() == $inlineValidatorClassName || $validator->className() == $type ){
            if( $validator->className() == $type ){
                $names = explode( "\\", $type );
                $type = array_pop( $names );
            }
            $data['rule'] = $type;
            unset( $data['message'] );
        }
        //不需要返回message
        if( in_array( $type, $this->_notMessage ) ) unset( $data['message'] );
        // 针对  string, int 字数验证
        if( in_array( $type, $this->_hasMinMax ) && ( isset( $rule['min'] ) || isset( $rule['max'] ) || isset( $rule['length'] ) ) ){
            $data = $this->_hasMinMaxValidate( $type, $rule, $validator );
        }
        // 针对 number 验证
        if( $type == $this->_number ){
            $validator->integerOnly = false;
            $data['message'] = $this->_setMessage( $validator->message );
        }
        // 针对 in 验证
        if( $type == $this->_in ) $data['range'] = $rule['range'];
        // 针对 对比 验证

        if( $type == $this->_compare ) {
            if( isset($rule['compareAttribute']) ){
                $data['compare'] = $rule['compareAttribute'];
            }
            if( isset($rule['compareValue']) && isset($rule['operator']) ){
                $data['rule'] = $this->_string;
                switch ( $rule['operator'] ){
                    case '>=':
                        $data['max'] = $rule['compareValue'];
                        $data['message'] = "只能包含至少{$data['max']}个字符。";
                        break;
                    case '>':
                        $data['max'] = $rule['compareValue'] + 1;
                        $data['message'] = "只能包含至少{$data['max']}个字符。";
                        break;
                    case '<=':
                        $data['min'] = $rule['compareValue'];
                        $data['message'] = "只能包含至多{$data['min']}个字符。";
                        break;
                    case '<':
                        $data['min'] = $rule['compareValue'] - 1;
                        $data['message'] = "只能包含至多{$data['min']}个字符。";
                        break;
                    case '=':
                        $data['max'] = $rule['compareValue'] + 1;
                        $data['min'] = $rule['compareValue'] - 1;
                        $data['message'] = "应该包含至少{$data['min']}个字符, 只能包含至多{$data['max']}个字符。";
                        break;
                }
            }
        }
        // 根据类型 添加 validateUrl
        if( isset( $this->_validateUrls[ $type ] ) ) $data['validateUrl'] = $this->_validateUrls[ $type ];
        return $data;
    }

    /**
     * 获得有效场景字段翻译
     *
     * @param array $fields
     *
     * @return array
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    private function _getValidAttributeLabels(array $fields)
    {
        $fields = array_flip( $fields );
        $fields = array_keys( $fields );
        $labels = [];
        foreach( $fields as $f ){
            $m = '';
            if( isset( $this->_model->attributeLabels()[ $f ] ) ){
                $m = $this->_model->attributeLabels()[ $f ];
            }
            $labels[ $f ] = $m;
        }
        return $labels;
    }

    /**
     * 生成验证 规则
     * trim,required,compare,需要 前端校验
     *
     * @return mixed
     *
     * @auth ice.leng(lengbin@geridge.com)
     * @issue
     */
    public function createValidate()
    {
        $fields = [];
        $key = -1;
        foreach( $this->_model->rules() as $rule ){
            $on = '';
            if( isset( $rule['on'] ) ) $on = is_array( $rule['on'] ) ? $rule['on'] : [$rule['on']];
            // 场景不同 过滤掉
            if( !empty( $on ) && !in_array( $this->_model->scenario, $on ) ) continue;
            $key++;
            $type = $rule[1];
            // 排除 定义的 验证
            if( in_array( $type, $this->_exclude ) ) continue;
            $field = is_array($rule[0]) ? $rule[0] : [$rule[0]];
            $fields = array_merge( $fields, $field );
            $data = ['field' => $rule[0], 'rule' => $type, 'type' => $type];
            $rule = array_merge( $data, $this->_generateRule( $key, $rule ) );
            $rules['validates'][] = $rule;
        }
        $rules['labels'] = $this->_getValidAttributeLabels( $fields );
//        $rules['_csrf'] = [
//            'name'  => \Yii::$app->request->csrfParam,
//            'value' =>\Yii::$app->request->csrfToken,
//        ];
        $rules['formName'] = $this->_model->tableName();
        return $rules;
    }

}