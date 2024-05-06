<?php

return array(
    'System Setting'=>array(//系统设置
        'access'=>'SS',
        'icon'=>'fa-gear',
        'items'=>array(
            'menu setting'=>array(//菜单项目设置
                'access'=>'SS03',
                'url'=>'/menuSet/index',
            ),
        ),
    ),
    'Comprehensive statistics'=>array(//综合统计
        'access'=>'SA',
        'icon'=>'fa-gavel',
        'items'=>array(
            'Project analyze'=>array(//项目分析
                'access'=>'SA01',
                'url'=>'/statisticProAll/index',
            ),
        ),
    ),
);
