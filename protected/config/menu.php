<?php return array (
  '人事系统' => 
  array (
    'access' => 'HR',
    'icon' => 'fa-bookmark',
    'items' => 
    array (
      'Project manage' => 
      array (
        'access' => 'HR01',
        'url' => '/projectManage/index?index=5&menu_code=HR',
      ),
      'Project analyze' => 
      array (
        'access' => 'HR02',
        'url' => '/analyzeProOne/index?index=5&menu_code=HR',
      ),
      'Username analyze' => 
      array (
        'access' => 'HR03',
        'url' => '/analyzeUserOne/index?index=5&menu_code=HR',
      ),
    ),
  ),
  '营运系统' => 
  array (
    'access' => 'OP',
    'icon' => 'fa-bookmark',
    'items' => 
    array (
      'Project manage' => 
      array (
        'access' => 'OP01',
        'url' => '/projectManage/index?index=6&menu_code=OP',
      ),
      'Project analyze' => 
      array (
        'access' => 'OP02',
        'url' => '/analyzeProOne/index?index=6&menu_code=OP',
      ),
      'Username analyze' => 
      array (
        'access' => 'OP03',
        'url' => '/analyzeUserOne/index?index=6&menu_code=OP',
      ),
    ),
  ),
  'System Setting' => 
  array (
    'access' => 'SS',
    'icon' => 'fa-gear',
    'items' => 
    array (
      'menu setting' => 
      array (
        'access' => 'SS03',
        'url' => '/menuSet/index',
      ),
    ),
  ),
);