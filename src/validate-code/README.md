# php 验证码

```php

session_start();
require './ValidateCode.class.php';

$_vc = new \lengbin\helper\ValidateCode\ValidateCode();
$_vc->doimg();
var_dump($_vc->getCode());
$_SESSION['authnum_session'] = $_vc->getCode();

```
