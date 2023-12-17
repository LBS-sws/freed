<?php

return array(
    'drs'=>array(//日報表系統
        'webroot'=>'http://192.168.0.5/swoper',
        'name'=>'Daily Report',
        'icon'=>'fa fa-pencil-square-o',
    ),
    'acct'=>array(//會計系統
        'webroot'=>'http://192.168.0.5/acct',
        'name'=>'Accounting',
        'icon'=>'fa fa-money',
    ),
    'ops'=>array(//營運系統
        'webroot'=>'http://192.168.0.5/operation',
        'name'=>'Operation',
        'icon'=>'fa fa-gears',
    ),
    'hr'=>array(//人事系統
        'webroot'=>'http://192.168.0.5/hr',
        'name'=>'Personnel',
        'icon'=>'fa fa-users',
    ),
    'sp'=>array(//學分系統
        'webroot'=>'http://192.168.0.5/integral',
        'name'=>'Integral',
        'icon'=>'fa fa-cubes',
    ),
    'ch'=>array(//慈善分系統
        'webroot'=>'http://192.168.0.5/charity',
        'name'=>'Charity',
        'icon'=>'fa fa-cubes',
    ),
    'quiz'=>array(//測驗系統
        'webroot'=>'http://192.168.0.5/examina',
        'name'=>'Examina',
        'icon'=>'fa fa-leaf',
    ),
    'invest'=>array(//投資管理系統
        'webroot'=>'http://192.168.0.5/invest',
        'name'=>'Investment',
        'icon'=>'fa fa-balance-scale',
    ),
    'sal'=>array(//銷售系統
        'webroot'=>'http://192.168.0.5/sales',
        'name'=>'Sales',
        'icon'=>'fa fa-suitcase',
    ),
    'uvv'=>array(//U系統
        'webroot'=>'http://192.168.0.5/uvv',
        'name'=>'UVV',
        'icon'=>'fa fa-ravelry',
    ),
    'freed'=>array(//项目进展
        'webroot'=>'http://192.168.0.5/freed',
        'name'=>'Project progress',
        'icon'=>'fa fa-bug',
    ),
    'nu'=>array(
        'webroot'=>'https://dms.lbsapps.cn/nu',
        'name'=>'New United',
        'icon'=>'fa fa-suitcase',
        'param'=>'/admin',
        'script'=>'goNewUnited',
    ),
    'onlib'=>array(
        'webroot'=>'https://onlib.lbsapps.com/seeddms',
        'script'=>'remoteLoginOnlib',
        'name'=>'Online Library',
        'icon'=>'fa fa-book',
        'external'=>array(
            'layout'=>'onlib',
            'update'=>'saveOnlib',		//function defined in UserFormEx.php
            'fields'=>'fieldsOnlib',
        ),
    ),
    /*
        'apps'=>array(
            'webroot'=>'https://app.lbsgroup.com.tw/web',
            'script'=>'remoteLoginTwApp',
            'name'=>'Apps System',
            'icon'=>'fa fa-rocket',
            'external'=>true,
        ),
    */
);

?>
