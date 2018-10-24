<?php

namespace tool\cache;

use Upadd\Bin\Cache\getRedis as redis;

use Log;

class MemberCache
{

    private $redis = null;

    private $vi = 'member_';

    public function __construct()
    {
        if ($this->redis === null) {
            $this->redis = new redis();
        }
    }

    public static function init()
    {
        return new static();
    }

    /**
     * 设置详情
     * @param $token
     * @param array $memberData
     */
    public function setDetails($token, $memberData = [])
    {
        if ($token && !empty($memberData)) {
            //设置过期时间为30分钟
            return $this->redis->set($this->vi . $token, json($memberData), (3600 * 24));
        }
    }

    /**
     * 获取时间
     * @param $token
     * @return bool|\json
     */
    public function getDetails($token)
    {
        $this->upDetailsTime($token);
        $memberData = $this->redis->get($this->vi . $token);
        if ($memberData) {
            return json($memberData);
        }
        return false;
    }

    /**
     * 更新详情时间
     * @param $token
     */
    private function upDetailsTime($token)
    {
        $ttl = $this->redis->ttl($token);
        if ($ttl) {
            if ($ttl <= 1000 && $ttl > 10) {
                $is_expire = $this->redis->expire($this->vi . $token, (3600 * 24));
                if ($is_expire == false) {
                    Log::notes('更新时间失败:' . $token, 'redis.log');
                }
            }
        } else {
            Log::notes('没有找到时间戳:' . $token, 'redis.log');
        }
    }

    /**
     * 删除一条
     * @param $token
     * @return bool
     */
    public function del($token)
    {
        return $this->redis->delete($this->vi.$token);
    }

}