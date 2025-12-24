<?php
namespace app\controller;

use app\service\UploadService;
use storage\StorageClient;
use think\exception\ValidateException;

class Upload extends Common
{
    /**
     * 文件上传
     */
    public function file()
    {
        $siteId = (int)$this->request->param('site_id', 0, 'intval');
        $dir = (string)$this->request->param('dir', 'uploads', 'strval');
        $file = $this->request->file('file');
        if (!$file) {
            return $this->ajaxReturn(422, '缺少文件');
        }
        $savename = date('Ymd') . '/' . uniqid('', true);
        $ext = strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION));
        $key = trim($dir, '/') . '/' . $savename . ($ext ? ('.' . $ext) : '');
        $path = $file->getPathname();
        $cli = StorageClient::forSite($siteId > 0 ? $siteId : null);
        $allowed = $cli->allowedSuffixes();
        if (!empty($allowed) && $ext && !in_array($ext, $allowed, true)) {
            return $this->ajaxReturn(422, '不允许的文件后缀', ['ext' => $ext, 'allowed' => $allowed]);
        }
        $res = $cli->uploadFile($key, $path);
        return $this->ajaxReturn(200, '上传成功', $res);
    }
    
}