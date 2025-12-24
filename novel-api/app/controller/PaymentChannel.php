<?php
declare(strict_types=1);

namespace app\controller;
use app\service\PaymentChannelService;
use think\exception\ValidateException;

class PaymentChannel extends Common
{
    public function index()
    {
        $page  = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $isHot = $this->request->param('is_hot', null, 'intval');
        $where = [];
        $where['status'] =1;
        if ($isHot !== null) $where['is_hot'] = (int)$isHot;
        $fields = 'id,name,icon_iden,is_default';
        $res = PaymentChannelService::list(formatWhere($where), $fields, 'sort desc, id desc', $limit, $page);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    public function info()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (empty($id)) {
            throw new ValidateException('参数错误');
        }
        $fields = 'id,name,icon_iden,is_default';
        $res = PaymentChannelService::info($id, $fields);
        return $this->ajaxReturn(200, '获取成功', $res);
    }
}