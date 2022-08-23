# yzhanAPI
API 集合，包括短网址，图片处理，IP，定位，OCR，二维码， Emulator 和授权验证等接口  
A collection of APIs, including short URL, image processing, IP, positioning, OCR, QR code, Emulator and authorization verification and other interfaces  
## Terms of Use 使用条款
仅用于临时测试，请勿他用  
For temporary testing only, pls do not use for other purposes
## Usage 用法
### Short URL Restoration 短网址还原 
[url/raw?out_type=json&des=github&url={url}](https://api.yzhan.cyou/url/raw?out_type=json&des=github&url=https://tinyurl.com/5ra9gp)  
#### Requst
- url：{ short url }
- out_type: json / txt
- des: { who is using }
#### Response
```json
{
  "code": 200,
  "msg": "success",
  "data": {
    "url": "https://github.com"
  }
}
```

### Proxy IP 代理 IP 
[ip/proxy?out_type=html&des=github&ip_type={ip_type}](https://api.yzhan.cyou/ip/proxy?ip_type=all&out_type=html&des=github)
#### Requst
- ip_type：all / anonymous / ssl
- out_type: json / html
- des: { who is using }
#### Response
```txt
130.41.15.76:8080
133.18.173.81:8080
133.18.231.31:8080
130.41.55.190:8080
161.97.130.154:3128
130.41.41.175:8080
23.107.176.65:32180
8.219.97.248:80
……
```
### Emulator 模拟器 
[rom/webretro](https://api.yzhan.cyou/apidir/rom/webretro/index.html?system=nes&rom={rom})
#### Requst
- system：nes / autodetect
- rom: { rom url }
#### Response
![Emulator Super Mario BROS.](https://s2.loli.net/2022/08/23/RuyT6zY8AM9FVsn.jpg)