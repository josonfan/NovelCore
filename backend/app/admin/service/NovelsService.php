<?php
namespace app\admin\service;

use app\admin\model\Novels;
use app\admin\model\NovelTags;
use app\admin\model\Tags;
use think\facade\Db;
use think\exception\ValidateException;

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
            }
            $res['list'] = $list;
        }
        return $res;
    }

    /**
     * 小说详情
     * @param int $id 主键ID
     * @param string $field 字段列表
     * @return mixed
     */
    public static function detail(int $id, string $field = '*')
    {
        $m = new Novels();
        return $m->infoById($id, $field);
    }

    protected static function uuid(): string
    {
        $d = random_bytes(16);
        $d[6] = chr(ord($d[6]) & 0x0f | 0x40);
        $d[8] = chr(ord($d[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
    }

    protected static function slug(): string
    {
        return 'novel-' . bin2hex(random_bytes(8));
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
            if (empty($data['novel_uuid'])) {
                $data['novel_uuid'] = self::uuid();
            }
            if (empty($data['slug'])) {
                $data['slug'] = self::slug();
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
        $m = new Novels();
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        $m->setCacheData($m->getCacheKey($id), null);
        NovelTags::where('novel_id', $id)->delete();
        return $ok;
    }

    /**
     * 绑定标签
     * @param int $novelId 小说ID
     * @param array $tagIds 标签ID数组
     * @return bool
     */
    public static function bindTags(int $novelId, array $tagIds): bool
    {
        NovelTags::where('novel_id', $novelId)->delete();
        foreach ($tagIds as $tid) {
            if (!$tid) continue;
            $nt = new NovelTags(['novel_id' => $novelId, 'tag_id' => (int)$tid]);
            $nt->save();
        }
        return true;
    }
}
