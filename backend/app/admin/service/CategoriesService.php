<?php
namespace app\admin\service;

use app\admin\model\Categories;
use think\exception\ValidateException;

class CategoriesService
{
    protected static function slug(string $name): string
    {
        $base = $name;
        if (class_exists('\\Transliterator')) {
            $tr = \Transliterator::create('Han-Latin; Latin-ASCII; Lower()');
            if ($tr) {
                $base = (string)$tr->transliterate($base);
            }
        }
        $san = strtolower($base);
        $san = preg_replace('/[^a-z0-9]+/', '-', $san);
        $san = trim((string)$san, '-');
        if ($san === '' || strlen($san) < 3) {
            $gb = @iconv('UTF-8', 'GBK//IGNORE', (string)$name);
            $letters = '';
            if ($gb !== false) {
                for ($i = 0; $i < strlen($gb); $i++) {
                    $ord = ord($gb[$i]);
                    if ($ord > 160 && $i + 1 < strlen($gb)) {
                        $sec = ord($gb[$i + 1]);
                        $code = ($ord - 160) * 100 + ($sec - 160);
                        $letters .= self::mapInitial($code);
                        $i++;
                    } else {
                        $ch = $gb[$i];
                        if (preg_match('/[a-zA-Z0-9]/', $ch)) {
                            $letters .= strtolower($ch);
                        }
                    }
                }
            }
            $san = preg_replace('/[^a-z0-9]+/', '-', $letters);
            $san = trim((string)$san, '-');
        }
        $base = $san;
        if ($base === '') {
            return 'cat-' . bin2hex(random_bytes(8));
        }
        $slug = $base;
        $i = 1;
        while (\think\facade\Db::name('categories')->where('slug', $slug)->find()) {
            $slug = $base . '-' . $i;
            $i++;
            if ($i > 10) {
                $slug = $base . '-' . bin2hex(random_bytes(4));
                break;
            }
        }
        return $slug;
    }

    protected static function mapInitial(int $code): string
    {
        $map = [
            ['a', 45217, 45252],
            ['b', 45253, 45760],
            ['c', 45761, 46317],
            ['d', 46318, 46825],
            ['e', 46826, 47009],
            ['f', 47010, 47296],
            ['g', 47297, 47613],
            ['h', 47614, 48118],
            ['j', 48119, 49061],
            ['k', 49062, 49323],
            ['l', 49324, 49895],
            ['m', 49896, 50371],
            ['n', 50372, 50613],
            ['o', 50614, 50621],
            ['p', 50622, 50905],
            ['q', 50906, 51386],
            ['r', 51387, 51445],
            ['s', 51446, 52217],
            ['t', 52218, 52697],
            ['w', 52698, 52979],
            ['x', 52980, 53688],
            ['y', 53689, 54480],
            ['z', 54481, 55289],
        ];
        foreach ($map as $m) {
            if ($code >= $m[1] && $code <= $m[2]) return $m[0];
        }
        return '';
    }
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort_order desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new Categories();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new Categories();
        return $m->infoById($id, $field);
    }

    public static function create(array $data)
    {
        try {
            if (empty($data['slug'])) {
                $data['slug'] = self::slug((string)($data['name'] ?? ''));
            }
            validate(\app\admin\validate\Category::class)->scene('create')->check($data);
            $m = new Categories($data);
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
            validate(\app\admin\validate\Category::class)->scene('update')->check($data);
            $m = new Categories();
            $ok = $m->writeById($id, $data);
            return $ok;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function delete(int $id): bool
    {
        $m = new Categories();
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        \think\facade\Cache::delete(env('DATABASE.PREFIX', 'blad_') . 'categories_' . $id);
        return $ok;
    }

    public static function toggle(int $id, int $isActive): bool
    {
        $m = new Categories();
        return $m->writeById($id, ['is_active' => $isActive]);
    }
}
