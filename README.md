# Dcat 配置项
后台配置项，将一些配置项变为后台管理员可操作
```php
#获取配置项
sys_config('config', '');
sys_config('arr.name', '无名');
如果未找到,且未设置默认值,则返回null
支持JSON数组用.的方式来获取数组的键值
```

