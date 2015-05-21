<?php
namespace BIS;

class LdaLoglikelihood {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
    public static function all($db) {
        $data = array();
        $sql = 'SELECT * FROM `ldaloglikelihood` ORDER BY `iteration` ASC';
        foreach ($db->fetchAll($sql) as $row) {
            $data[] = array(intval($row['iteration']), floatval($row['loglikelihood']));
        } 
        return $data;
    }
}
