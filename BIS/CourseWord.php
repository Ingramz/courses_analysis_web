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
}
