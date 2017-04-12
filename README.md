#邮件&短信服务功能说明
本系统是基于workerman3.0版本实现，workerman官方网站：http://www.workerman.net/
#启动服务
php start.php start		//debug启动

php start.php start -d	//release启动

#停止服务
php start.php stop

#查看服务状态
php start.php status

##邮件服务
邮件功能流程为先向redis写入数据，然后多进程异步从redis读取数据进行发送，发送结果写入数据库。

多进程配置项和启动文件：Applications\Email\start.php

redis数据结构：

    $data = json_encode(
                array(
                    'from' => $from, 
                    'to' => $to,
                    'title' => $title, 
                    'body' => $body
                )
            );
			
####参数说明：
> * from ：发送者邮箱
> * to ：接收者邮箱
> * title ：邮件标题
> * body ：邮件正文

在Applications\Email目录下通过php start.php start -d单独启动邮件服务
