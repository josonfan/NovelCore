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
        return $m->infoById($id, $field);
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
            $m->save();
            $m->primeCacheById((int)$m['id'], $m->toArray());
            if ($content !== '') {
                $cc = new ChapterContents(['chapter_id' => (int)$m['id'], 'content' => $content]);
                $cc->save();
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
        $d = random_bytes(16);
        $d[6] = chr(ord($d[6]) & 0x0f | 0x40);
        $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
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
                $cc->writeById($id, ['content' => $content]);
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
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        \think\facade\Cache::delete(env('DATABASE.PREFIX', 'blad_') . 'chapters_' . $id);
        $cc = new ChapterContents();
        $cc->where('chapter_id', $id)->delete();
        return $ok;
    }
}
