<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\enterprise\services\cms\article;

//use fecshop\models\mysqldb\cms\Article;
use Yii;
use yii\base\InvalidValueException;
use fecshop\services\cms\article\ArticleInterface;
use fecshop\services\Service;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class ArticleApi extends Service implements ArticleInterface
{
    public $ver = '/v1';
    public $numPerPage = 20;
    protected $_articleModelName = '\fecshop\models\mysqldb\cms\Article';
    protected $_articleModel;
    
    public function init(){
        parent::init();
        list($this->_articleModelName,$this->_articleModel) = Yii::mapGet($this->_articleModelName);  
        
    }
    /**
     *  language attribute.
     */
    protected $_lang_attr = [
        'title',
        'meta_description',
        'content',
        'meta_keywords',
    ];

    public function getPrimaryKey()
    {
        $apiKey = $this->ver.'/cms/articles/primaryKey';
        $primaryKey = Yii::$service->goApi->getCurlJsonDeData($apiKey, 'get');
        return $primaryKey;
    }

    public function getByPrimaryKey($primaryKey)
    {
        if ($primaryKey) {
            $apiKey = '/v1/cms/articles/oneById';
            $one = Yii::$service->goApi->getCurlJsonDeData($apiKey, 'get', ['id' => $primaryKey]);
            if (isset($one['url_key'])){
                $one['content'] = unserialize($one['content']);
                $one['title'] = unserialize($one['title']);
                $one['meta_keywords'] = unserialize($one['meta_keywords']);
                $one['meta_description'] = unserialize($one['meta_description']);
                return $one;
            }
        }
        
    }
    
    /**
     * @property $urlKey | String ,  对应表的url_key字段
     * 根据url_key 查询得到article model
     */
    public function getByUrlKey($urlKey)
    {
        if ($urlKey) {
            $apiKey = '/v1/cms/articles/oneByUrlKey';
            $one = Yii::$service->goApi->getCurlJsonDeData($apiKey, 'get', ['url_key' => '/'.$urlKey]);
            if (isset($one['url_key'])){
                $one['content'] = unserialize($one['content']);
                $one['title'] = unserialize($one['title']);
                $one['meta_keywords'] = unserialize($one['meta_keywords']);
                $one['meta_description'] = unserialize($one['meta_description']);
                return $one;
            }
        }
    }
    
    
    
    

    /*
     * example filter:
     * [
     * 		'numPerPage' 	=> 20,
     * 		'pageNum'		=> 1,
     * 		'orderBy'	=> ['_id' => SORT_DESC, 'sku' => SORT_ASC ],
            'where'			=> [
                ['>','price',1],
                ['<=','price',10]
     * 			['sku' => 'uk10001'],
     * 		],
     * 	'asArray' => true,
     * ]
     */
    public function coll($filter = '')
    {
        $apiKey = '/v1/cms/articles/coll';
        //$filter = is_array($filter) ? json_encode($filter) : [];
        //$filter['where'] = [['>','price',1],['<=','price',10]];
        $query = (new yii\db\Query)->from('article');
        $countSql = Yii::$service->goApi->queryCountSql($query, $filter);
        $allSql   = Yii::$service->goApi->queryAllSql($query, $filter);
        
        
        $data = Yii::$service->goApi->getCurlJsonDeData($apiKey, 'post', [
            'countSql' => $countSql,
            'allSql' => $allSql,
        ]);
        $coll = $data['coll'];
        if (!empty($coll)) {
            foreach ($coll as $k => $one) {
                foreach ($this->_lang_attr as $attr) {
                    $one[$attr] = $one[$attr] ? unserialize($one[$attr]) : '';
                }
                $coll[$k] = $one;
            }
        }
        return [
            'coll' => $coll,
            'count'=> $data['count'],
        ];
        
        
        /*
        $query = $this->_articleModel->find();
        $query = Yii::$service->helper->ar->getCollByFilter($query, $filter);
        $coll = $query->all();
        if (!empty($coll)) {
            foreach ($coll as $k => $one) {
                foreach ($this->_lang_attr as $attr) {
                    $one[$attr] = $one[$attr] ? unserialize($one[$attr]) : '';
                }
                $coll[$k] = $one;
            }
        }
        //var_dump($one);
        return [
            'coll' => $coll,
            'count'=> $query->limit(null)->offset(null)->count(),
        ];
        */
    }

    /**
     * @property $one|array
     * save $data to cms model,then,add url rewrite info to system service urlrewrite.
     */
    public function save($one, $originUrlKey)
    {
        $primaryKey = $this->getPrimaryKey();
        //var_dump($one);
        $apiKey = '/v1/cms/articles/save';
        foreach ($this->_lang_attr as $attrName) {
            if (is_array($one[$attrName]) && !empty($one[$attrName])) {
                $one[$attrName] = serialize($one[$attrName]);
            }
        }
        $one['status'] ||  $one['status'] = 1;
        $one['created_user_id'] = \fec\helpers\CUser::getCurrentUserId();
        $data = Yii::$service->goApi->getCurlJsonDeData($apiKey, 'post', $one);
        
        $saveOne = $data['one'] ;
        $lastId  = $data['lastid'];
        $affect  = $data['affect'];
        $primaryVal = $lastId ? $lastId : $one[$primaryKey];
        $originUrl = $originUrlKey.'?'.$primaryKey .'='. $primaryVal;
            
        $originUrlKey = isset($one['url_key']) ? $one['url_key'] : '';
        $defaultLangTitle = Yii::$service->fecshoplang->getDefaultLangAttrVal($one['title'], 'title');
        $urlKey = Yii::$service->url->saveRewriteUrlKeyByStr($defaultLangTitle, $originUrl, $originUrlKey);
        $apiKey = '/cms/articles/updateUrlKeyInfo';
        $one = [
            'url_key'    => $urlKey,
            $primaryKey  => $primaryVal 
        ];
        $data = Yii::$service->goApi->getCurlJsonDeData($apiKey, 'post', $one);
        
        $saveOne['content'] = unserialize($saveOne['content']);
        $saveOne['title'] = unserialize($saveOne['title']);
        $saveOne['meta_keywords'] = unserialize($saveOne['meta_keywords']);
        $saveOne['meta_description'] = unserialize($saveOne['meta_description']);
        return saveOne; 
    
    }
    

    public function remove($ids)
    {
        if (!$ids) {
            Yii::$service->helper->errors->add('remove id is empty');

            return false;
        }
        if (is_array($ids) && !empty($ids)) {
            $innerTransaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($ids as $id) {
                    $model = $this->_articleModel->findOne($id);
                    if (isset($model[$this->getPrimaryKey()]) && !empty($model[$this->getPrimaryKey()])) {
                        $url_key = $model['url_key'];
                        Yii::$service->url->removeRewriteUrlKey($url_key);
                        $model->delete();
                    } else {

                        //throw new InvalidValueException("ID:$id is not exist.");
                        Yii::$service->helper->errors->add("Article Remove Errors:ID $id is not exist.");
                        $innerTransaction->rollBack();

                        return false;
                    }
                }
                $innerTransaction->commit();
            } catch (Exception $e) {
                Yii::$service->helper->errors->add('Article Remove Errors: transaction rollback');
                $innerTransaction->rollBack();

                return false;
            }
        } else {
            $id = $ids;
            $model = $this->_articleModel->findOne($id);
            if (isset($model[$this->getPrimaryKey()]) && !empty($model[$this->getPrimaryKey()])) {
                $innerTransaction = Yii::$app->db->beginTransaction();
                try {
                    $url_key = $model['url_key'];
                    Yii::$service->url->removeRewriteUrlKey($url_key);
                    $model->delete();
                    $innerTransaction->commit();
                } catch (Exception $e) {
                    Yii::$service->helper->errors->add('Article Remove Errors: transaction rollback');
                    $innerTransaction->rollBack();
                }
            } else {
                Yii::$service->helper->errors->add("Article Remove Errors:ID:$id is not exist.");

                return false;
            }
        }

        return true;
    }
}
