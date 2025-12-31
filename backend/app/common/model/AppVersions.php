<?php

namespace app\common\model;

class AppVersions extends BaseModel
{
    protected $name = 'app_versions';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;
}
