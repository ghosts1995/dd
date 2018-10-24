<?php
namespace app\dd\src;

class DdConfig
{

    // 状态相关
    const STAGE_INIT = 0;
    const STAGE_ADDR = 1;
    const STAGE_UDP_ASSOC = 2;
    const STAGE_DNS = 3;
    const STAGE_CONNECTING = 4;
    const STAGE_STREAM = 5;
    const STAGE_DESTROYED = -1;
    // 命令
    const CMD_CONNECT = 1;
    const CMD_BIND = 2;
    const CMD_UDP_ASSOCIATE = 3;
    //https://shadowsocks.org/en/spec/protocol.html 协议说明
    //parse_header 请求地址类型
    //IPv4
    const ADDRTYPE_IPV4 = 1;
    //IPv6
    const ADDRTYPE_IPV6 = 4;
    //Remote DNS
    const ADDRTYPE_HOST = 3;
/*
原始包头格式
+--------------+---------------------+------------------+----------+
| Address Type | Destination Address | Destination Port |   Data   |
+--------------+---------------------+------------------+----------+
|      1       |       Variable      |         2        | Variable |
+--------------+---------------------+------------------+----------+
*/
    // dd OTA 相关
    //https://shadowsocks.org/en/spec/one-time-auth.html
    //OTA hash长度
    const ONETIMEAUTH_BYTES = 10;
    //数据流阶段每个数据包 OTA 所占用长度
    const ONETIMEAUTH_CHUNK_BYTES = 12;
    //OTA data.len 长度
    const ONETIMEAUTH_CHUNK_DATA_LEN = 3;
    //即是0b00010000
    const ADDRTYPE_AUTH = 0x10;
    //即是0b00011111
    const ADDRTYPE_MASK = 0xF;
/*
开启OTA后包头格式
+------+---------------------+------------------+-----------+----------+
| ATYP | Destination Address | Destination Port | HMAC-SHA1 |   Data   |
+------+---------------------+------------------+-----------+----------+
|  1   |       Variable      |         2        |    10     | Variable |
+------+---------------------+------------------+-----------+----------+
开启OTA后 每个数据包格式
+----------+-----------+----------+----
| DATA.LEN | HMAC-SHA1 |   DATA   | ...
+----------+-----------+----------+----
|     2    |     10    | Variable | ...
+----------+-----------+----------+----
*/

}