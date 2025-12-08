<?php
namespace app\admin\service;

use app\admin\model\Novels;
use app\admin\model\NovelTags;
use app\admin\model\Tags;
use think\exception\ValidateException;

class NovelsService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new Novels();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

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

    public static function create(array $data)
    {
        try {
            validate(\app\admin\validate\Novel::class)->scene('create')->check($data);
            if (empty($data['novel_uuid'])) {
                $data['novel_uuid'] = self::uuid();
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

    public static function delete(int $id): bool
    {
        $m = new Novels();
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        $m->setCacheData($m->getCacheKey($id), null);
        NovelTags::where('novel_id', $id)->delete();
        return $ok;
    }

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
