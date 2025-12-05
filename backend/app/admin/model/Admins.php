<?php
namespace app\admin\model;

class Admins extends \app\common\model\Admins
{
    public function listAdmins($where=[],$field='*',$orderby='',$limit=10,$page=1){
        try{
            $list = $this->getList($where,$field,$orderby,$limit,$page);
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
        return $list;
    }
}