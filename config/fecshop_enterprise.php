<?php
/**
 * FecShop file.
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

return [
    /**
     * 下面是第三方扩展库包的配置方式
     */
    // 这个是扩展extensions的总开关，true代表打开
    'enable' => true, 
    // 各个入口的配置
    'app' => [
        // 1.公用层
        'common' => [
            // 在公用层的开关，设置成false后，公用层的配置将失效
            'enable' => true,
            // 公用层的具体配置下载下面
            'config' => [
                'services' => [
                    'cms' => [
                       // 子服务
                        'childService' => [
                            'article' => [
                                //'class'            => 'fecshop\enterprise\services\cms\Article',
                                'storagePath' => 'fecshop\enterprise\services\cms\article',
                                'storage' => 'ArticleApi', // ArticleMysqldb or ArticleMongodb.
                            ],

                            //'staticblock' => [
                            //    'class'    => 'fecshop\services\cms\StaticBlock',
                            //    'storage'    => 'StaticBlockMongodb', // mysqldb or mongodb.
                            //],
                        ],
                    ],
                    'goApi' => [
                        'class'     => 'fecshop\enterprise\services\GoApi',
                        'token'     => '4Dr4%i2mX&BP^7lFTd%0!1IX2^zx26F8',
                        'apiHost'   => '120.24.37.249:3000',
                    ]
                ],
            ],
        ],
        
    ],
    
];









