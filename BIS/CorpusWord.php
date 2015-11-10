<?php
namespace BIS;

class CorpusWord {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
    public static function summary($db) {
        $summary = array();
        // total amount of words for corpus
        $sql = 'SELECT COUNT(*) AS "words", SUM(`count`) AS "total" FROM `corpusword`';
        $data = $db->fetchAssoc($sql, array());
        $summary['words'] = $data['words'];
        $summary['total'] = $data['total'];
        
        // total amount of documents
        $sql = 'SELECT COUNT(*) AS "documents" FROM `lecture`';
        $data = $db->fetchAssoc($sql, array());
        $summary['documents'] = $data['documents'];
		
		//total number of courses
		$sql = 'SELECT COUNT(*) AS num_courses FROM `course`';
		$data = $db->fetchAssoc($sql);
		$summary['num_courses'] = $data['num_courses'];
		
        return $summary;
    }
    
    public static function topWords($db, $maxCount) {
        $data = array();
        $sql = 'SELECT * FROM `corpusword` ORDER BY `count` DESC LIMIT ?';
        foreach ($db->fetchAll($sql, array($maxCount)) as $row) {
            $data[] = array($row['word'], intval($row['count']));
            $maxCount = max($maxCount, $row['count']);
        } 

        foreach ($data as $key => $item) {
            $data[$key][1] = round(($item[1] / $maxCount) * 100, 0);
        }
        return $data;
    }
}
