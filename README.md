#配置文件路径
    ```
    config/dd.php
    ```
    
#开启服务端代理

    ##使用传参启动
    ···
        php console.php --u=dd --p=server --pp=base64_encode(json_encode($array))
    ···
    
        ##参数说明
        @pp in param array['port'=>passwd]
        
    ##一般开启代理
    ···
        php console.php --u=dd --p=server --proxy=off
    ···

#开启本地代理
    ···
        php console.php --u=dd --p=local
    ···

    ##设置本地代理的服务端IP
    ···
        php console.php --u=dd --p=local --server=111.33.18.67
    ···