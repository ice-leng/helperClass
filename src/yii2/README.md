# yii2 

```php
    
    支持vue js element.ui
    返回 yii2 model rule 规制
    
    public function createFromValidate(ActiveRecord $activeRecord, array $params = [], $scenario = '')
    {
        if( !empty($scenario) ){
            $activeRecord->scenario = $scenario;
        }
        
        /**
         * CreateFromValidate constructor.
         *
         * @param Model $model       表单对象 如果有场景的 记得 加上哈
         * @param array $validateUrl 需要做ajax请求的路由地址 [ type => http://xxxxx.com ]
         */
        
        $data = new ElemeVueJsFormValidate( $activeRecord, $params);
        return $data->createValidate();
    }
    

```