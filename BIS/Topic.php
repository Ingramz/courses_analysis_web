<?php
namespace BIS;

class Topic {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
    public static function all($db) {
        $data = array();
        $sql = 'SELECT * FROM `topicword` ORDER BY `topic` ASC, `weight` DESC';
        $query = $db->executeQuery($sql);
        foreach ($query->fetchAll() as $row) {
            $data[$row['topic']]['words'][] = $row['word'];
        }
        foreach ($data as $id => $item) {
            $data[$id]['id'] = $id;
            // $data[$id]['words'] = implode(', ', $item['words']);
        }
        return $data;
    }
    
    public static function course($db, $courseId) {
        $data = array();
        // total amount of words for corpus
        $sql = 'SELECT * FROM `coursetopic` WHERE `course_id` = ? ORDER BY `weight` DESC';
        foreach ($db->fetchAll($sql, array($courseId)) as $row) {
            $data[$row['topic']] = $row;
        }
        
        $topicIds = array_keys($data);
        $sql = 'SELECT * FROM `topicword` WHERE `topic` IN (?) ORDER BY `topic` ASC, `weight` DESC';
        $query = $db->executeQuery($sql, array($topicIds), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
        foreach ($query->fetchAll() as $row) {
            $data[$row['topic']]['words'][] = $row['word'];
        }

        foreach ($data as $id => $item) {
            $data[$id]['words'] = implode(', ', $item['words']);
        }
        
        return $data;
    }
    
    public static function courseTopics($db) {
        $data = array();
        // $sql = 'SELECT `course_id`, `topic`, MAX(`weight`) AS weight FROM `coursetopic` GROUP BY `course_id` ORDER BY `course_id`, `topic`';
        $sql = 'SELECT * FROM `coursetopic`';
        foreach ($db->fetchAll($sql) as $row) {
            $data[] = array(intval($row['topic']), intval($row['course_id'] - 1), floatval($row['weight']));
        }
        return $data;
    }
    
    public static function getAllNames($db) {
        $data = array();
        $sql = 'SELECT DISTINCT(`topic`) AS `topic` FROM `topicword` ORDER BY `topic`';
        foreach ($db->fetchAll($sql) as $row) {
            $data[] = 'T' . $row['topic'];
        }
        return $data;
    }
}
