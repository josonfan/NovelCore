<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserMessage;
use think\exception\ValidateException;

class MessageService
{
    public static function list(int $userId, int $page = 1, int $limit = 20, ?int $type = null, ?int $isRead = null): array
    {
        $page = max(1, $page);
        $limit = min(100, max(1, $limit));        
        $raw = [
            'to_uid' => $userId,
        ];
        if ($type !== null && $type > 0) {
            $raw['type'] = $type;
        }
        if ($isRead !== null && in_array($isRead, [1, 2], true)) {
            $raw['is_read'] = $isRead;
        }
        $where = formatWhere($raw);
        $fields = 'id,from_uid,to_uid,title,message,type,source_info,is_read,created_at,updated_at';
        $orderby = 'created_at desc, id desc';
        $m = new UserMessage();
        $list = $m->getList($where, $fields, $orderby, $limit, $page);
        foreach ($list['list'] ?? [] as $key => $item) {
            $list['list'][$key] = self::multiLanguage($item);
        }
        return $list;
    }
    /**
     * 多语言处理
     *
     * @param integer $userId
     * @param integer $id
     * @return array
     */
    public static function multiLanguage(array $item): array
    {
        $title = json_decode($item['title'] ?? '{}', true);
        $message = json_decode($item['message'] ?? '{}', true);
        $item['title'] = lang($title['name'], $title['vars'] ?? []);
        $item['message'] = lang($message['name'], $message['vars'] ?? []);
        $item['source_info'] = json_decode($item['source_info'] ?? '{}', true);
        return $item;
    }
    /**
     * 获取消息详情
     * @param int $userId 用户ID
     * @param int $id 消息ID
     * @return array 消息详情
     */
    public static function info(int $userId, int $id): array
    {
        if ($id <= 0) {
            throw new ValidateException('参数错误');
        }
        $uid = $userId;
        // $uid = 3;
        $m = new UserMessage();
        $info = $m->infoById($id, 'id,from_uid,to_uid,title,message,type,source_info,is_read,created_at,updated_at');
        if (empty($info) || (string)($info['to_uid'] ?? '') !== (string)$uid) {
            throw new ValidateException('参数错误');
        }
        if ((int)($info['is_read'] ?? 2) !== 1) {
            $m->writeById($id, ['is_read' => 1]);
            $info['is_read'] = 1;
        }
        $info = self::multiLanguage($info);
        if (!empty($info['from_uid'])) {
            $from_user_info = UserService::info((int)$info['from_uid'],'id,nickname,avatar');
            unset($from_user_info['id']);
            $info['from_user_info'] = $from_user_info;
        }
        return $info;
    }

    public static function unreadCount(int $userId): int
    {
        $uid = (string) getUidByID($userId);
        return (int) UserMessage::where('to_uid', $uid)->where('is_read', 2)->count('id');
    }
    /**
     * 创建消息
     * @param int $from_uid 发送者ID
     * @param int $to_uid 接收者ID
     * @param int $type 消息类型 1系统消息 2互动消息 3作品更新 4安全提醒
     * @param array $title 标题 多语言 json{"name":"follow_title","vars":{"nickname":"可开"}}
     * @param array $message 消息内容 多语言 json{"name":"follow_message","vars":{"nickname":"可开"}}
     * @param array $sourceInfo 来源信息 json {"type":"数据表名","ids":215,"extend":[{"id":215,"type":3,"resource_id":8666}]}
     * @return int 消息ID
     */
    public static function create(int $from_uid, int $to_uid, int $type, array $title, array $message, array $sourceInfo = []): int
    {
        
        $m = new UserMessage();
        $id = $m->writeById(0,[
            'from_uid' => $from_uid,
            'to_uid' => $to_uid,
            'title' => json_encode($title, JSON_UNESCAPED_UNICODE),
            'message' => json_encode($message, JSON_UNESCAPED_UNICODE),
            'type' => $type,
            'source_info' => json_encode($sourceInfo, JSON_UNESCAPED_UNICODE),
            'is_read' => 2,
        ]);
        return (int)$id;
    }
}

