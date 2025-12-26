<?php
namespace app\admin\service;

use app\admin\model\Novels;
use app\admin\model\NovelTags;
use app\admin\model\Tags;
use think\facade\Db;
use think\exception\ValidateException;
use storage\StorageClient;


class NovelsService
{
    /**
     * 小说列表
     * @param array $where 过滤条件
     * @param string $field 字段列表
     * @param string $orderby 排序
     * @param int $limit 每页数量
     * @param int $page 页码
     * @return array
     */
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new Novels();
        $res = $m->getList($where, $field, $orderby, $limit, $page);
        $list = (array)($res['list'] ?? []);
        if (count($list) > 0) {
            $ids = [];
            foreach ($list as $row) { $ids[] = (int)$row['id']; }
            $links = (new NovelTags())->whereIn('novel_id', $ids)->field('novel_id,tag_id')->select()->toArray();
            $tagIds = [];
            foreach ($links as $ln) { $tagIds[] = (int)$ln['tag_id']; }
            $tagIds = array_values(array_unique($tagIds));
            $tagMap = [];
            if (count($tagIds) > 0) {
                $tm = new Tags();
                foreach ($tagIds as $tid) {
                    $info = $tm->infoById((int)$tid, 'id,name,type');
                    if (!empty($info)) {
                        $tagMap[(int)$info['id']] = [
                            'id' => (int)$info['id'],
                            'name' => (string)$info['name'],
                            'type' => (string)$info['type'],
                        ];
                    }
                }
            }
            $group = [];
            foreach ($links as $ln) {
                $nid = (int)$ln['novel_id'];
                $tid = (int)$ln['tag_id'];
                if (!isset($group[$nid])) $group[$nid] = [];
                if (isset($tagMap[$tid])) { $group[$nid][] = $tagMap[$tid]; }
            }
            foreach ($list as $i => $row) {
                $nid = (int)$row['id'];
                $list[$i]['tags'] = $group[$nid] ?? [];
                $cli = StorageClient::forSite();
                $list[$i]['cover'] = $cli->getPublicUrl((string) ($list[$i]['cover'] ?? ''));
            }
            $res['list'] = $list;
        }
        return $res;
    }

    /**
     * 小说详情
     * @param int $id 主键ID
     * @param string $field 字段列表
     * @return array
     */
    public static function detail(int $id, string $field = '*'): array
    {
        $m = new Novels();
        $info = $m->infoById($id, $field);
        if (!empty($info)) {
            $cli = StorageClient::forSite();
            $info['cover'] = $cli->getPublicUrl((string) ($info['cover'] ?? ''));
            $links = (new NovelTags())->where('novel_id', $id)->field('tag_id')->select()->toArray();
            $tagIds = [];
            foreach ($links as $ln) { $tagIds[] = (int)$ln['tag_id']; }
            $tagIds = array_values(array_unique($tagIds));
            $tagMap = [];
            if (count($tagIds) > 0) {
                $tm = new Tags();
                foreach ($tagIds as $tid) {
                    $tm_info = $tm->infoById((int)$tid, 'id,name,type');
                    if (!empty($tm_info)) {
                        $tagMap[(int)$tm_info['id']] = [
                            'id' => (int)$tm_info['id'],
                            'name' => (string)$tm_info['name'],
                            'type' => (string)$tm_info['type'],
                        ];
                    }
                }
            }
            $result = array_values($tagMap);
            $info['tags'] = $result ?? [];
        }
        return $info;
    }
    /**
     * 生成唯一UUID
     * @return string
     */
    protected static function uuid(): string
    {
        do {
            $uuid = bin2hex(random_bytes(8));
            $exists = Db::name('novels')->where('novel_uuid', $uuid)->value('id');
        } while ($exists);
        return $uuid;
    }
    /**
     * 生成唯一Slug
     * @return string
     */
    protected static function slug($uuid): string
    {
        $slug = 'novel-' .$uuid;
        return $slug;
    }

    /**
     * 创建小说
     * @param array $data 小说数据
     * @return Novels
     */
    public static function create(array $data)
    {
        try {
            validate(\app\admin\validate\Novel::class)->scene('create')->check($data);
            if (!empty($data['cover'])) {
                $data['cover'] = StorageClient::forSite()->filterDomain((string)$data['cover']);
            }
            if (empty($data['novel_uuid'])) {
                $data['novel_uuid'] = self::uuid();
            }
            if (empty($data['slug'])) {
                $novel_uuid = $data['novel_uuid']??self::uuid();
                $data['slug'] = self::slug($novel_uuid);
            }
            $m = new Novels($data);
            $m->created_at = time();
            $m->writeById((int)$m['id'], $m->toArray());
            return $m;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 更新小说
     * @param int $id 主键ID
     * @param array $data 更新数据
     * @return bool
     */
    public static function update(int $id, array $data): bool
    {
        try {            
            validate(\app\admin\validate\Novel::class)->scene('update')->check($data);
            if (!empty($data['cover'])) {
                $data['cover'] = StorageClient::forSite()->filterDomain((string)$data['cover']);
            }
            $m = new Novels();
            return $m->writeById($id, $data);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function audit(int $id, int $status, string $remark, int $adminId): bool
    {
        try {
            validate(\app\admin\validate\NovelAudit::class)->scene('audit')->check(['id' => $id, 'audit_status' => $status, 'audit_remark' => $remark]);
            $m = new Novels();
            $ok = $m->writeById($id, [
                'audit_status' => $status,
                'audit_remark' => $remark,
                'audit_admin_id' => $adminId,
                'audit_at' => date('Y-m-d H:i:s'),
            ]);
            if ($ok) {
                try {
                    $log = new \app\common\model\NovelAuditLog([
                        'novel_id' => $id,
                        'admin_id' => $adminId,
                        'status' => $status,
                        'remark' => $remark,
                    ]);
                    $log->save();
                } catch (\Throwable $e) {}
            }
            return $ok;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 删除小说
     * @param int $id 主键ID
     * @return bool
     */
    public static function delete(int $id): bool
    {
        try {
            
            Db::startTrans();
            $m = new Novels();
            $ok = $m->deleteById($id);
            if ($ok) {                
                $tagLinks = NovelTags::where('novel_id', $id)->field('id')->select()->toArray();     
                trace($tagLinks);           
                foreach ($tagLinks as $ln) {
                    (new NovelTags())->deleteById((int)$ln['id']);
                }
            }
            Db::commit();
            return $ok;
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }    
    /**
     * 绑定标签
     * @param int $novelId 小说ID
     * @param array $tagIds 标签ID数组
     * @return bool
     */
    public static function bindTags(int $novelId, array $tagIds): bool
    {
        $rows = NovelTags::where('novel_id', $novelId)->field('id')->select()->toArray();
        foreach ($rows as $r) {
            (new NovelTags())->deleteById((int)$r['id']);
        }
        foreach ($tagIds as $tid) {
            if (!$tid) continue;
            (new NovelTags())->writeById(0, ['novel_id' => $novelId, 'tag_id' => (int)$tid]);
        }
        $tags_json = Tags::where('id', 'in', $tagIds)->column('id as tag_id,name');
        $tags_json = json_encode($tags_json, JSON_UNESCAPED_UNICODE);
        (new Novels())->writeById($novelId, ['tags_json' => $tags_json]);
        return true;
    }
}
