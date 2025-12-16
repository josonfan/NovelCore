<?php
namespace app\admin\service;

use app\admin\model\Chapters;
use app\admin\model\ChapterContents;
use think\exception\ValidateException;

class ChaptersService
{
    /**
     * 章节列表
     * @param array $where 过滤条件
     * @param string $field 字段列表
     * @param string $orderby 排序
     * @param int $limit 每页数量
     * @param int $page 页码
     * @return array
     */
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort_order asc, id asc', int $limit = 10, int $page = 1): array
    {
        $m = new Chapters();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    /**
     * 章节详情
     * @param int $id 主键ID
     * @param string $field 字段列表
     * @return mixed
     */
    public static function detail(int $id, string $field = '*')
    {
        $m = new Chapters();
        $info = $m->infoById($id, $field);
        if (empty($info)) {
            return $info;
        }
        $cc = new ChapterContents();
        $row = $cc->infoById($id, 'content');
        $info['content'] = $row['content'] ?? null;
        return $info;
    }

    public static function content(int $id): ?string
    {
        $cc = new ChapterContents();
        $row = $cc->infoById($id, 'content');
        if (empty($row) || !isset($row['content'])) {
            return null;
        }
        return (string)$row['content'];
    }

    /**
     * 创建章节（可选正文）
     * @param array $data 章节数据（含 content 可选）
     * @return Chapters
     */
    public static function create(array $data)
    {
        try {
            validate(\app\admin\validate\Chapter::class)->scene('create')->check($data);
            $content = (string)($data['content'] ?? '');
            unset($data['content']);
            if (empty($data['chapter_uuid'])) {
                $data['chapter_uuid'] = self::uuid();
            }
            $m = new Chapters($data);
            $m->created_at = time();
            $m->writeById((int)$m['id'], $m->toArray());
            if ($content !== '') {
                $chapterId = (int)\think\facade\Db::name('chapters')->where('chapter_uuid', (string)$data['chapter_uuid'])->value('id');
                if ($chapterId > 0) {
                    $cc = new ChapterContents();
                    $cc->where('chapter_id', $chapterId)->delete();
                    $cc->writeById(0, ['chapter_id' => $chapterId, 'content' => $content]);
                }
            }
            return $m;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected static function uuid(): string
    {
        do {
            $uuid = bin2hex(random_bytes(8));
            $exists = \think\facade\Db::name('chapters')->where('chapter_uuid', $uuid)->value('id');
        } while ($exists);
        return $uuid;
    }

    /**
     * 更新章节（可同时更新正文）
     * @param int $id 主键ID
     * @param array $data 更新数据（content 可选）
     * @return bool
     */
    public static function update(int $id, array $data): bool
    {
        try {
            validate(\app\admin\validate\Chapter::class)->scene('update')->check($data);
            $content = null;
            if (array_key_exists('content', $data)) {
                $content = (string)$data['content'];
                unset($data['content']);
            }
            $m = new Chapters();
            $ok = $m->writeById($id, $data);
            if ($content !== null) {                
                $cc = new ChapterContents();
                $cc->where('chapter_id', $id)->delete();
                $cc->writeById(0, ['chapter_id' => $id, 'content' => $content]);
            }
            return $ok;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 删除章节（含正文）
     * @param int $id 主键ID
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $m = new Chapters();
        $ok = $m->deleteById($id);
        if ($ok) {
            (new ChapterContents())->deleteById($id);
        }
        return $ok;
    }
}
