<?php

use Juanpiorg\Rtx\RTXApiClient;

/**
 *
 * 用这个worker实现rtx发送服务
 * 每次从redis取一条数据，处理成功后写入数据库
 * 数据表根据月份分开创建
 *
 * @author walkor <walkor@workerman.net>
 */
class RtxWorker
{
    protected $redis = null;
    protected $manager = null;
    static public $config = null;

    /**
     * 该worker进程开始服务的时候会触发一次
     * @return bool
     */
    public function start()
    {
        //echo getmypid()." start initRedis\n";
        $this->initRedis();
        //echo getmypid()." start initMongo\n";
        $this->initMongo();
        $redis_key = $this->getConf('rtx_redis_key');
        while (true) {
            $data = $this->redis->lPop($redis_key);
            if (empty($data)) {
                usleep(300);
                continue;
            }
            $this->dealRtx($data);
        }
        $this->end();
        return true;
    }

    /** 获取配置信息
     * @param $str
     * @return mixed
     */
    protected function getConf($str)
    {
        return isset(self::$config[$str]) ? self::$config[$str] : '';
    }

    /** 初始化redis
     *
     */
    protected function initRedis()
    {
        $parameters = array(
            'host'     => $this->getConf('redis_ip'),
            'port'     => $this->getConf('redis_port'),
            'database' => $this->getConf('redis_db')
        );
        $this->redis = new Predis\Client($parameters);
    }

    /** 初始化mongo
     *
     */
    protected function initMongo()
    {
        $host = $this->getConf('db_ip');
        $port = $this->getConf('db_port');
        $this->manager = new MongoDB\Driver\Manager("mongodb://{$host}:{$port}");    // 连接到mongodb
    }

    /** 回收资源
     *
     */
    protected function end()
    {
        $this->redis->close();
        $this->manager->close();
    }

    /** 处理邮件
     * @param $rtx_info
     * @return bool
     */
    protected function dealRtx($rtx_info)
    {
        //echo $rtx_info."\n";
        $data = json_decode($rtx_info, true);
        if (empty($data)) {
            return false;
        }
        list($usec, $sec) = explode(" ", microtime());
        $msec = round($usec*1000);
        $data['date'] = new \MongoDB\BSON\UTCDateTime(time()*1000+$msec+8*60*60*1000);
        $fails = '';
        $result = RTXApiClient::sendNotify($data['to'], $data['title'], $data['body'], $data['autoclose'], $fails);
        if ($result) {
            //echo getmypid()."send successful\n";
            //写入数据库
            $data['status'] = 'success';
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert($data);
            $db_name = $this->getConf('db_name');
            $db_collection = $this->getConf('db_rtx_collection');
            $this->manager->executeBulkWrite("{$db_name}.{$db_collection}", $bulk);
            unset($bulk);
        } else {
            if (!isset($data['send_reply'])) {
                $data['send_reply'] = 1;
                $this->redis->rPush($this->getConf('rtx_redis_key'), json_encode($data));
            } else {
                if (++$data['send_reply'] < $this->getConf('send_reply')) {
                    $this->redis->rPush($this->getConf('rtx_redis_key'), json_encode($data));
                } else {
                    //echo getmypid()." send failed {$fails}\n";
                    $data['status'] = 'fail';
                    $data['error'] = $fails;
                    $bulk = new MongoDB\Driver\BulkWrite;
                    $bulk->insert($data);
                    $db_name = $this->getConf('db_name');
                    $db_collection = $this->getConf('db_rtx_collection');
                    $this->manager->executeBulkWrite("{$db_name}.{$db_collection}", $bulk);
                    unset($bulk);
                }
            }
        }
        return true;
    }
}
