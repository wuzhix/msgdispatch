<?php

return array(
    //存储消息redis
    'email_redis_key' => 'oa:msg:email',    //redis的key
    'rtx_redis_key' => 'oa:msg:rtx',        //redis的key
    'wechat_redis_key' => 'oa:msg:wechat',    //redis的key
    'redis_ip' => '127.0.0.1',        //redis的ip
    'redis_port' => 6379,            //redis的port
    'redis_db' => 0,                //redis的db
    'requirepass' => 'password',    //redis的密码

    'db_ip' => '127.0.0.1',     //数据库ip
    'db_port' => '27017',       //数据库端口
    'db_name' => 'oa_msg',      //数据库名称
    'db_email_collection' => 'email', //集合名称
    'db_rtx_collection' => 'rtx', //集合名称
    'db_wechat_collection' => 'wechat', //集合名称

    'send_reply' => 3,      //发送重试时，最大总共发送次数
);
