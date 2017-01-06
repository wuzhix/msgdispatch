#邮件服务功能说明
本系统是基于workerman3.0版本实现，workerman官方网站：http://www.workerman.net/
#启动服务
php start.php start		//debug启动

php start.php start -d	//release启动

#停止服务
php start.php stop

#查看服务状态
php start.php status

##邮件服务：
邮件功能通过phpmailer实现，流程为先向redis写入数据，然后多进程异步从redis读取数据进行发送，发送结果写入数据库。

多进程配置项和启动文件：Applications\Email\start.php

redis数据结构：

    $data = json_encode(
        array(
            'sender' => $sender, 
            'subject' => $subject,
            'content' => $content, 
            'to_email' => $to_email, 
            'stringattachment' => $stringattachment,
            'stringembeddedimage' => $stringembeddedimage,
            'to_name' => $to_name,
            'platform_id' => $platform_id
        )
    );
			
####参数说明
> * sender：发送者名称
> * subject：邮件标题
> * content：邮件正文
> * to_email：收件人邮箱
> * stringattachment：附件
> * stringembeddedimage：可以显示在正文的附件，例如图片
> * to_name：接收者名称
> * platform_id：平台id

在Applications\Email目录下通过php start.php start -d单独启动邮件服务