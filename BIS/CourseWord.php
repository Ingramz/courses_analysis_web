<?php
namespace BIS;

class CourseWord {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
    public static function summary($db, $id) {
        $summary = array();
        // total amount of words for course
        $sql = 'SELECT COUNT(*) AS "words", SUM(`count`) AS "total" FROM `courseword` WHERE `course_id` = ?';
        $data = $db->fetchAssoc($sql, array($id));
        $summary['words'] = $data['words'];
        $summary['total'] = $data['total'];
        
        // total amount of documents
        $sql = 'SELECT COUNT(*) AS "documents" FROM `lecture` WHERE `lecture`.`course_id` = ?';
        $data = $db->fetchAssoc($sql, array($id));
        $summary['documents'] = $data['documents'];
        $summary['url'] = 'abc';
        return $summary;
    }
    
    public static function topWords($db, $maxCount, $id) {
        $data = array();
        $sql = 'SELECT * FROM `courseword` WHERE `course_id` = ? ORDER BY `count` DESC LIMIT ?';
        foreach ($db->fetchAll($sql, array($id, $maxCount)) as $row) {
            $data[] = array($row['word'], intval($row['count']));
            $maxCount = max($maxCount, $row['count']);
        } 

        foreach ($data as $key => $item) {
            $data[$key][1] = round(($item[1] / $maxCount) * 100, 0);
        }
        return $data;
    }
    
    public static function topWordsLabels($db, $maxCount, $id) {
        $data = array();
        $words = self::topWords($db, $maxCount, $id);
        foreach ($words as $item) {
            $data[] = array('text' => $item[0], 'size' => intval($item[1]));
        }
        return $data;
            
    }
}
