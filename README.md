<p>
  <a href="http://fecshop.appfront.fancyecommerce.com/">
    <img src="http://img.appfront.fancyecommerce.com/custom/logo.png">
  </a>
</p>
<br/>



Fecshop Enterprise 


> Fecshop Enterprise 的数据库操作，由直接使用php连接数据库的方式，
> 改成了通过Go语言提供的Api获取数据，GoLang语言更适合做底层
> ，这样应对高并发，以及做分库分表等操作会更便捷。

安装
--------

```
composer require --prefer-dist fancyecommerce/fecshop_enterprise 
```

or 在根目录的`composer.json`中添加

```
"fancyecommerce/fecshop_enterprise": "~1.0.3"
```

然后执行

```
composer update
```

 





