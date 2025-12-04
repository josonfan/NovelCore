<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\exception\BusinessException;
use app\model\Novel;
use app\model\Tag;
use think\Request;

/**
 * 小说列表与详情接口，外部 ID 使用 novel_uuid。
 */
class NovelController extends BaseController
{
    /**
     * 小说列表。
     */
    public function index(Request $request)
    {
        $categoryId = (int) $request->get('category_id', 0);
        $tagId      = (int) $request->get('tag_id', 0);
        $page       = max(1, (int) $request->get('page', 1));
        $pageSize   = min(50, max(1, (int) $request->get('page_size', 10)));
        $order      = $request->get('order', 'newest');

        $query = Novel::with(['author', 'category'])
            ->order($this->buildOrder($order));

        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        if ($tagId > 0) {
            // 通过中间表过滤标签；仅使用 uuid 对外暴露。
            $query->join('novel_tags', 'novel_tags.novel_id = novels.id')
                ->where('novel_tags.tag_id', $tagId)
                ->group('novels.id');
        }

        $paginator = $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);

        $novels = [];
        $storage = app(\app\service\StorageService::class);
        foreach ($paginator->items() as $novel) {
            $tags = $this->extractTags($novel->tags_json);
            $novels[] = [
                // 对外 ID 使用 novel_uuid
                'id'           => $novel->novel_uuid,
                'title'        => $novel->title,
                'author_name'  => $novel->author->nickname ?? $novel->author->username ?? '',
                'category'     => $novel->category->name ?? '',
                'tags'         => $tags,
                // 封面 URL 通过 StorageService 处理，未来可切换 CDN/域名池
                'cover'        => $storage->getPublicUrl((string) $novel->cover),
                'intro'        => $novel->intro,
                'status'       => (int) $novel->status,
                'is_vip'       => (int) $novel->is_vip,
                'word_count'   => (int) $novel->word_count,
                'updated_at'   => $novel->updated_at,
            ];
        }

        return json_success([
            'total'     => $paginator->total(),
            'page'      => $paginator->currentPage(),
            'page_size' => $paginator->listRows(),
            'novels'    => $novels,
        ], '获取成功');
    }

    /**
     * 小说详情（按 uuid 查询，必要时兼容自增 id）。
     */
    public function show(string $novelId)
    {
        $novel = Novel::with(['author', 'category', 'chapters' => function ($query) {
            $query->order('sort_order', 'asc')->limit(20);
        }])
            ->where('novel_uuid', $novelId)
            ->find();

        if (!$novel && ctype_digit($novelId)) {
            $novel = Novel::with(['author', 'category'])->find((int) $novelId);
        }

        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $storage = app(\app\service\StorageService::class);

        $tags = $this->extractTags($novel->tags_json);
        $chapters = [];
        foreach ($novel->chapters as $chapter) {
            $chapters[] = [
                // 对外 ID 使用 chapter_uuid
                'id'         => $chapter->chapter_uuid,
                'title'      => $chapter->title,
                'is_free'    => (int) $chapter->is_free,
                'is_vip'     => (int) $chapter->is_vip,
                'word_count' => (int) $chapter->word_count,
                'sort_order' => (int) $chapter->sort_order,
            ];
        }

        return json_success([
            'id'           => $novel->novel_uuid,
            'title'        => $novel->title,
            'author_name'  => $novel->author->nickname ?? $novel->author->username ?? '',
            'category'     => $novel->category->name ?? '',
            'tags'         => $tags,
            'cover'        => $storage->getPublicUrl((string) $novel->cover),
            'intro'        => $novel->intro,
            'status'       => (int) $novel->status,
            'is_vip'       => (int) $novel->is_vip,
            'word_count'   => (int) $novel->word_count,
            'like_count'   => (int) $novel->like_count,
            'fav_count'    => (int) $novel->fav_count,
            'view_count'   => (int) $novel->view_count,
            'created_at'   => $novel->created_at,
            'updated_at'   => $novel->updated_at,
            'chapter_count'=> $novel->chapters()->count(),
            'chapters_preview' => $chapters,
        ], '获取成功');
    }

    /**
     * 列表排序构建。
     */
    protected function buildOrder(string $order): array
    {
        return match ($order) {
            'popular' => ['view_count' => 'desc', 'fav_count' => 'desc', 'id' => 'desc'],
            default   => ['created_at' => 'desc', 'id' => 'desc'],
        };
    }

    /**
     * 从 tags_json 提取标签名数组。
     */
    protected function extractTags($tagsJson): array
    {
        if (is_array($tagsJson)) {
            return array_values($tagsJson);
        }
        return [];
    }

}
