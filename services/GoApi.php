<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\enterprise\services;

//use fecshop\models\mysqldb\cms\Article;
use Yii;
use yii\base\InvalidValueException;
use fecshop\services\Service;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class GoApi extends Service
{
    public $token ;
    public $apiHost ;
    # 1.通过函数访问api，获取数据
	#  JSON格式
	/*
		参数说明：  $url  为API访问的url
					$type 为请求类型，默认为get
					$data 为传递的数组数据
					$timeout 设置超时时间
		返回值：	返回API返回的数据
	*/
	public function getCurlJsonDeData($apiKey,$type="get",$data=array(),$timeout = 10){
        //对空格进行转义
        //$url = str_replace(' ','+',$url);
        $url = 'http://'.$this->apiHost.$apiKey;
       
        if ($type == "get") {
            if (!empty($data) && is_array($data)) {
                $arr = [];
                foreach ($data as $k=>$v) {
                    $arr[] = $k. "=". urlencode($v);
                }
                $str  = implode("&", $arr);
                if (strstr($url, "?")) {
                    $url .= "&".$str;
                }else{
                    $url .= "?".$str;
                }
            }
        }
        $data = json_encode($data);
        $url = urldecode($url);
        //echo $url."<br>"; 
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);  //定义超时10秒钟  
        if($type == "post"){
            // POST数据
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, 
                CURLOPT_HTTPHEADER, 
                [
                'Accept: application/json',
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
                
                ]
                );
            // 把post的变量加上
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'token: '.$this->token ,
        ]);
        //执行并获取url地址的内容
        $output = curl_exec($ch);
        //var_dump($output);
        //释放curl句柄
        curl_close($ch);
        $returnArr = json_decode($output, true);
        if ($returnArr['status'] == 200) {
            $data = $returnArr['data'];
            return $data;
        }
    }
    
    
    /*
     * example filter:
    * [
     * 		'numPerPage' 	=> 20,
     * 		'pageNum'		=> 1,
     * 		'orderBy'	=> ['_id' => SORT_DESC, 'sku' => SORT_ASC ],
     * 		'where'			=> [
                ['>','price',1],
                ['<=','price',10]
     * 			['sku' => 'uk10001'],
     * 		],
     * 	'asArray' => true,
     * ]
     * 查询方面使用的函数，根据传递的参数，进行query
     */
    public function queryCountSql($query, $filter)
    {
        $query->select('COUNT(*)');
        $where = isset($filter['where']) ? $filter['where'] : '';
        if ($where) {
            if (is_array($where)) {
                $i = 0;
                foreach ($where as $w) {
                    $i++;
                    if ($i == 1) {
                        $query->where($w);
                    } else {
                        $query->andWhere($w);
                    }
                }
            }
        }
        $count_sql = $query->createCommand()->getRawSql();
        return $count_sql;
    }
    
    
    /*
     * example filter:
    * [
     * 		'numPerPage' 	=> 20,
     * 		'pageNum'		=> 1,
     * 		'orderBy'	=> ['_id' => SORT_DESC, 'sku' => SORT_ASC ],
     * 		'where'			=> [
                ['>','price',1],
                ['<=','price',10]
     * 			['sku' => 'uk10001'],
     * 		],
     * 	'asArray' => true,
     * ]
     * 查询方面使用的函数，根据传递的参数，进行query
     */
    public function queryAllSql($query, $filter)
    {
        $select     = isset($filter['select']) ? $filter['select'] : '';
        $numPerPage = isset($filter['numPerPage']) ? $filter['numPerPage'] : 20;
        $pageNum    = isset($filter['pageNum']) ? $filter['pageNum'] : 1;
        $orderBy    = isset($filter['orderBy']) ? $filter['orderBy'] : '';
        $where      = isset($filter['where']) ? $filter['where'] : '';
        if (is_array($select) && !empty($select)) {
            $query->select($select);
        } else {
            $query->select('*');
        }
        if ($where) {
            if (is_array($where)) {
                $i = 0;
                foreach ($where as $w) {
                    $i++;
                    if ($i == 1) {
                        $query->where($w);
                    } else {
                        $query->andWhere($w);
                    }
                }
            }
        }
        
        $offset = ($pageNum - 1) * $numPerPage;
        $query->limit($numPerPage)->offset($offset);
        if ($orderBy) {
            $query->orderBy($orderBy);
        }
        $all_sql = $query->createCommand()->getRawSql();
        return $all_sql;
    }
    
    
}