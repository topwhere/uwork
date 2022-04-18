## 📐 勾股DEV
![输入图片说明](https://dev.gougucms.com/storage/202204/banner.jpg)

### ✅ 相关链接
- gitee：https://gitee.com/gougucms/dev.git
- 文档地址：[https://blog.gougucms.com/home/book/detail/bid/7.html](https://blog.gougucms.com/home/book/detail/bid/7.html)
- 项目会不定时进行更新，建议⭐star⭐和👁️watch👁️一份。

### ⭕ 开源项目
1. [![勾股OA](https://img.shields.io/badge/GouguOA-2.0.9-brightgreen.svg)](https://gitee.com/gougucms/office) [开源项目系列：勾股OA —— OA协同办公系统框架](https://gitee.com/gougucms/office)
2. [![勾股CMS](https://img.shields.io/badge/GouguCMS-2.0.18-brightgreen.svg)](https://gitee.com/gougucms/gougucms) [开源项目系列：勾股CMS —— CMS内容管理系统框架](https://gitee.com/gougucms/gougucms)
3. [![勾股BLOG](https://img.shields.io/badge/GouguBLOG-2.0.16-brightgreen.svg)](https://gitee.com/gougucms/blog) [开源项目系列：勾股BLOG —— 个人&工作室博客系统](https://gitee.com/gougucms/blog)
4. [![勾股DEV](https://img.shields.io/badge/GouguDEV-1.4.18-brightgreen.svg)](https://gitee.com/gougucms/dev) [开源项目系列：勾股DEV —— 项目研发管理系统](https://gitee.com/gougucms/dev)

### 📋 系统介绍
勾股DEV是一款专为IT研发团队打造的智能化项目管理与团队协作的工具软件，可以在线管理团队的工作、项目和任务，覆盖从需求提出到研发完成上线整个过程的项目协作。

勾股DEV的产品理念：通过“项目（Project）”的形式把成员、需求、任务、缺陷(BUG)、文档、互动讨论以及各种形式的资源组织在一起，团队成员参与更新任务、文档等内容来推动项目的进度，同时系统利用时间线索和各种动态的报表的形式来自动给成员汇报项目进度。

[![勾股DEV](https://img.shields.io/badge/GougDEV-1.4.18-brightgreen.svg)](https://gitee.com/gougucms/dev/)
[![star](https://gitee.com/gougucms/dev/badge/star.svg?theme=dark)](https://gitee.com/gougucms/dev/stargazers)
[![fork](https://gitee.com/gougucms/dev/badge/fork.svg?theme=dark)](https://gitee.com/gougucms/dev/members)

### ✳️ 演示地址

勾股DEV演示地址：[https://dev.gougucms.com](https://dev.gougucms.com)
   
勾股DEV文档地址：[https://blog.gougucms.com/home/book/detail/bid/7.html](https://blog.gougucms.com/home/book/detail/bid/7.html)

PS：为了给后面的人提供良好的演示体验，体验以查看为主。

**登录账号及密码：**
~~~
   技术总监：gougujishu     123456
   工 程 师：ligong    123456
   产品经理：ouyangchanpin        123456
~~~

### ✴️ 系统特点
- 多产品支持，可添加多产品管理
- 多项目支持，可以多项目同时进行管理
- 可配置的用户角色控制，不同的角色可配置不同的操作权限
- Wiki 形式的文档撰写，Mardown编辑器，工程师使用高效便捷
- 每个项目配置有需求、任务、Wiki文档、动态记录、互动评论、工作记录模块
- 任务时间跟踪机制，项目任务多状态流转，任务成果可见可控。
- 工时登记，团队精细化管理，可统计每个人每天在每个项目做了多少时间
- 任务安排，任务分配指定人，可设置负责人、多协同人员
- 员工的操作记录全覆盖跟踪


### 📚 安装教程

**一、勾股DEV推荐你使用阿里云和腾讯云服务器**

阿里云服务器官方长期折扣优惠地址：

点击访问，(https://www.aliyun.com/activity/daily/bestoffer?userCode=dmrcx154) 

腾讯云服务器官方长期折扣优惠地址：

点击访问，(https://curl.qcloud.com/PPEgI0oV) 


**二、服务器运行环境要求**

~~~
    PHP >= 7.1  
    Mysql >= 5.5.0 (需支持innodb引擎)  
    Apache 或 Nginx  
    PDO PHP Extension  
    MBstring PHP Extension  
    CURL PHP Extension  
    Composer (用于管理第三方扩展包)
~~~

**三、系统安装**

**命令行安装（推荐）**

推荐使用命令行安装，因为采用命令行安装的方式可以和勾股DEV随时保持更新同步。使用命令行安装请提前准备好Git、Composer。

Linux下，勾股DEV的安装请使用以下命令进行安装。  

第一步：克隆勾股CMS到你本地  
    git clone https://gitee.com/gougucms/dev.git

第二步：进入目录  
    cd gougudev  
    
第三步：下载PHP依赖包 
    
composer install  
	
注意：composer的版本最好是2.0.8版本，否则可能下载PHP依赖包失败，composer降级：composer self-update 2.0.8
    
第四步：添加虚拟主机并绑定到项目的public目录  
    
第五步：访问 http://www.yoursite.com/install/index 进行安装

**PS：如需要重新安装，请删除目录里面 config/install.lock 的文件，即可重新安装。**

**四、伪静态配置**

**Nginx**
修改nginx.conf 配置文件 加入下面的语句。
~~~
    location / {
        if (!-e $request_filename){
        rewrite  ^(.*)$  /index.php?s=$1  last;   break;
        }
    }
~~~

**Apache**
把下面的内容保存为.htaccess文件放到应用入 public 文件的同级目录下。
~~~
    <IfModule mod_rewrite.c>
    Options +FollowSymlinks -Multiviews
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php?/$1 [QSA,PT,L]
    </IfModule>
~~~


### ❓ 常见问题

1.  安装失败，可能存在php配置文件禁止了putenv 和 proc_open函数。解决方法，查找php.ini文件位置，打开php.ini，搜索 disable_functions 项，看是否禁用了putenv 和 proc_open函数。如果在禁用列表里，移除putenv proc_open然后退出，重启php即可。

2.  如果安装后打开页面提示404错误，请检查服务器伪静态配置，如果是宝塔面板，网站伪静态请配置使用thinkphp规则。

3.  如果提示当前权限不足，无法写入配置文件config/database.php，请检查database.php是否可读，还有可能是当前安装程序无法访问父目录，请检查PHP的open_basedir配置。

4.  如果composer install失败，请尝试在命令行进行切换配置到国内源，命令如下composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/。

5.  如果composer install失败，请尝试composer降级：composer self-update 2.0.8。

6.  访问 http://www.yoursite.com/install/index ，请注意查看伪静态请配置是否设置了thinkphp规则。

7.  如果遇到无法解决的问题请到QQ群：24641076 反馈交流。

### 🖼️ 截图预览
![输入图片说明](https://dev.gougucms.com/storage/202204/dev1.png)
![输入图片说明](https://dev.gougucms.com/storage/202204/dev2.png)

### ⭐ 开源助力
- 勾股DEV遵循GPL-3.0开源协议发布。 
- 开源的系统少不了大家的参与，如果大家在体验的过程中发现有问题或者BUG，请到Issue里面反馈，谢谢！
- 如果觉得勾股DEV不错，不要吝啬您的赞许和鼓励，请给我们⭐ STAR ⭐吧！

### 👍 支持我们
- If the project is very helpful to you, you can buy the author a cup of coffee☕.
- 如果这个项目对您有帮助，请支持一下我们，可以请我们喝杯咖啡哟☕

|支付宝      |    微信|
| :--------: | :--------:|
| <img src="https://www.gougucms.com/static/home/images/zfb.png" width="300"  align=center />|<img src="https://www.gougucms.com/static/home/images/wx.png" width="300"  align=center />|

