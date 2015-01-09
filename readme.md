##QiniuCli
>speed up your development

Have fun with qiniu cdn.

###Features

- upload

- remove

- status

- refresh

###How

Fill out your config file.(mv config.example.php config.php)

`php vendor/bin/robo upload ($param)`

`php vendor/bin/robo remove $param`

`php vendor/bin/robo removeAll`

`php vendor/bin/robo status ($param)`

`php vendor/bin/robo refresh $param`

###Dependence

[Robo](http://robo.li/started.html)

[sweetCli](https://xuqingfeng.github.io/doc/sweetCli/)
>my another project :)

[qiniu-php](https://github.com/qiniu/php-sdk/tags)

###Install
>via composer

```javascript
{
    "require": {
        "codegyre/robo": "*",
        "sweet/cli": "0.1.*"
    }
}
```

###License

GPLv3



