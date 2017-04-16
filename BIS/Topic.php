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
            $data[$row['topic']]['id'] = $row['topic'];
        }
		
		foreach (self::topicCourses($db) as $key => $item) {
            $data[$key]['courses'] = $item;
        }

        $sql = 'SELECT * FROM `CourseTopicInfo`';
        $query = $db->executeQuery($sql);
        foreach ($query->fetchAll() as $row) {
            $data[$row['topic']]['name'] = $row['name'];
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
		
        $sql = 'SELECT * FROM coursetopicinfo WHERE topic IN (?)';
		$query = $db->executeQuery($sql, array($topicIds), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
        foreach ($query->fetchAll() as $row) {
            $data[$row['topic']]['name'] = $row['name'];
        }		
		
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
    
    public static function courseTopicWeights($db, $courseId) {
        $data = array();
        $sum = 0;

        foreach (self::course($db, $courseId) as $topicId => $item) {
            $weight = floatval($item['weight']);
            $sum += $weight;
            $data[] = array('T' . $topicId, $weight);
        }

        if ( $sum < 100) {
            $data[] = array('Other', 100 - $sum);
        }
        return $data;
    }
    
    public static function courseTopics($db) {
        $data = array();
        
        $courseIds = Course::getIdListOrderedByName($db);
        $sql = 'SELECT * FROM `coursetopic`';
        foreach ($db->fetchAll($sql) as $row) {
            $data[] = array(intval($row['topic']), intval($courseIds[$row['course_id']]), floatval($row['weight']));
        }
        return $data;
    }
	
	public static function topicCourses($db) {
        $data = array();
        
        $courseNames = Course::getAllNamesById($db);
        $sql = 'SELECT * FROM `coursetopic` ORDER BY topic, weight DESC';
        foreach ($db->fetchAll($sql) as $row) {
            $data[$row['topic']][] = $courseNames[$row['course_id']] . '(' . $row['weight'] . '%)';
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
    
    public static function getAllWords($db) {
        $data = array();
        foreach (self::all($db) as $key => $item) {
            $data[$key][] = implode(', ', $item['words']);
        }
        return $data;
    }
}
