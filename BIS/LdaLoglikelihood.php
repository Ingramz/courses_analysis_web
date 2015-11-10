<?php
namespace BIS;

class LdaLoglikelihood {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
    public static function all($db) {
        $data = array();
        $sql = 'SELECT * FROM `ldaloglikelihood` WHERE `iteration` > 10 ORDER BY `iteration` ASC';
        foreach ($db->fetchAll($sql) as $row) {
            $data[] = array(intval($row['iteration']), floatval($row['loglikelihood']));
        } 
        return $data;
    }
	
	public static function getIterationCount($db){
		$sql = 'SELECT MAX(iteration) as max_iter FROM `ldaloglikelihood`';
		$data = $db->fetchAssoc($sql);
		return $data['max_iter']+10;
	}
}
