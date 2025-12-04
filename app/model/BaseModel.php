<?php
declare(strict_types=1);

namespace app\model;

use app\exception\BusinessException;
use think\Model;

/**
 * 模型通用基类：统一主键、时间戳、公共 Scope 及便捷查询方法。
 */
abstract class BaseModel extends Model
{
    /**
     * 使用全局默认连接。
     * @var string
     */
    protected $connection = null;

    /**
     * 主键字段名称。
     * @var string
     */
    protected $pk = 'id';

    /**
     * 启用时间戳自动写入，统一字段为 created_at / updated_at。
     * @var bool|string
     */
    protected $autoWriteTimestamp = 'datetime';

    /**
     * 创建时间字段名。
     * @var string|false
     */
    protected $createTime = 'created_at';

    /**
     * 更新时间字段名。
     * @var string|false
     */
    protected $updateTime = 'updated_at';

    /**
     * 已发布 Scope 占位，可按业务扩展。
     */
    public function scopePublished($query)
    {
        return $query;
    }

    /**
     * 启用状态 Scope，占位可扩展具体状态字段。
     */
    public function scopeActive($query)
    {
        return $query;
    }

    /**
     * 根据主键查询并在缺失时抛业务异常。
     *
     * @param mixed $id 主键值
     * @return static
     * @throws BusinessException
     */
    public static function findOrFail($id)
    {
        $model = static::find($id);
        if (!$model) {
            throw new BusinessException('数据不存在', 404);
        }

        return $model;
    }
}
