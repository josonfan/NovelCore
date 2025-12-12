<?php
namespace app\common\middleware;

class ServiceWhitelist
{
    public function handle($request, \Closure $next)
    {
        $list = (array)(config('service_comm.whitelist') ?? []);
        $clientIp = self::resolveClientIp($request);
        $allowed = self::isIpAllowed($clientIp, $list);
        if (!$allowed) {
            return json(['code' => 403, 'msg' => '接口未在白名单中'.json_encode($request->header() ), 'data' => []]);
        }
        return $next($request);
    }

    protected static function resolveClientIp($request): string
    {
        $remote = (string)($request->server('remote_addr') ?? '');
        
        $cf = (string)($request->header('cf-connecting-ip') ?? '');
        if ($cf !== '' && self::isValidIp($cf)) return $cf;
        $xff = (string)($request->header('x-forwarded-for') ?? '');
        if ($xff !== '') {
            $parts = preg_split('/\s*,\s*/', $xff);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '' && self::isValidIp($p)) return $p;
            }
        }
        $xri = (string)($request->header('x-real-ip') ?? '');
        if ($xri !== '' && self::isValidIp($xri)) return $xri;
        if (self::isValidIp($remote)) return $remote;
        return '0.0.0.0';
    }

    protected static function isIpAllowed(string $ip, array $list): bool
    {
        foreach ($list as $entry) {
            $entry = trim((string)$entry);
            if ($entry === '') { continue; }
            if (strpos($entry, '/') !== false) {
                if (self::cidrMatch($ip, $entry)) { return true; }
            } else {
                if (strcasecmp($ip, $entry) === 0) { return true; }
            }
        }
        return false;
    }

    protected static function cidrMatch(string $ip, string $cidr): bool
    {
        $parts = explode('/', $cidr);
        if (count($parts) !== 2) return false;
        $net = $parts[0];
        $bits = (int)$parts[1];
        $ipBin = @inet_pton($ip);
        $netBin = @inet_pton($net);
        if ($ipBin === false || $netBin === false || strlen($ipBin) !== strlen($netBin)) return false;
        $bytes = intdiv($bits, 8);
        $rem = $bits % 8;
        if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($netBin, 0, $bytes)) return false;
        if ($rem === 0) return true;
        $mask = chr((0xFF << (8 - $rem)) & 0xFF);
        return (substr($ipBin, $bytes, 1) & $mask) === (substr($netBin, $bytes, 1) & $mask);
    }

    protected static function isValidIp(string $ip): bool
    {
        return @inet_pton($ip) !== false;
    }

    protected static function isTrusted(string $ip, array $list): bool
    {
        foreach ($list as $entry) {
            $entry = trim((string)$entry);
            if ($entry === '') continue;
            if (strpos($entry, '/') !== false) {
                if (self::cidrMatch($ip, $entry)) return true;
            } else {
                if (strcasecmp($ip, $entry) === 0) return true;
            }
        }
        return false;
    }
}
