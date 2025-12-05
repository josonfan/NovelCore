<?php

return [
	'alias' => [
        'AdminAuth' => \app\admin\middleware\JwtAuth::class,
        'RbacAuth' => \app\admin\middleware\RbacAuth::class,
	],
];
