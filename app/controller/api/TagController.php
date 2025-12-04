<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Tag;
use think\Request;

/**
 * 标签列表接口。
 */
class TagController extends BaseController
{
    /**
     * 获取启用标签，可按类型筛选。
     * 说明：category_id 预留参数，当前表未直接关联分类，仅在注释中标注。
     */
    public function index(Request $request)
    {
        $type = trim((string) $request->get('type', ''));
        // 预留：category_id 参数暂不生效
        $query = Tag::where('is_active', 1);

        if ($type !== '') {
            $query->where('type', $type);
        }

        $tags = $query->order('id', 'desc')
            ->field(['id', 'name', 'slug', 'type'])
            ->select();

        return json_success(['tags' => $tags], '获取成功');
    }
}
