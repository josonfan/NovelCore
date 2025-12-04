<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Category;

/**
 * 分类列表接口，使用 uuid 体系的外部 ID。
 */
class CategoryController extends BaseController
{
    /**
     * 获取启用分类列表，按 sort_order 倒序。
     */
    public function index()
    {
        $categories = Category::where('is_active', 1)
            ->order('sort_order', 'desc')
            ->field(['id', 'name', 'slug', 'seo_title', 'seo_keywords', 'seo_description'])
            ->select();

        return json_success(['categories' => $categories], '获取成功');
    }
}
