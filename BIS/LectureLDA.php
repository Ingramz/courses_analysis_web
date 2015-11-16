<?php
namespace BIS;

class LectureLDA {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
    public static function allLectureNames($db, $courseId) {
		$data = array();
		foreach (self::getLectures($db, $courseId) as $row) {
			$data[] = trim($row['name']);
        }
        return $data;
    }
	
	public static function allLectureHyperlinks($db, $courseId) {
		$data = array();
		foreach (self::getLectures($db, $courseId) as $row) {
			$data[trim($row['name'])] = $row['url'];
        }
        return $data;
    }
	
	public static function allTopicNames($db, $courseId) {
		$data = array();
		$lectureIds = self::getLectureIdsOrderedByName($db, $courseId);
		$sql = 'SELECT DISTINCT(`topic`) AS `topic` FROM `lecturetopic` WHERE `lecture_id` IN (?)  ORDER BY `topic`';
        
		$query = $db->executeQuery($sql, array($lectureIds), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
		foreach ($query->fetchAll() as $row) {
            $data[] = 'T' . $row['topic'];
        }
        return $data;
	}
	
	public static function allLectureTopics($db, $courseId) {
		$data = array();
        $lectureIds = self::getLectureIdsOrderedByName($db, $courseId);
		$lectureKeys = array_keys($lectureIds);
		
        $sql = 'SELECT * FROM `lecturetopic` where `lecture_id` IN (?)';
		$query = $db->executeQuery($sql, array($lectureKeys), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
		foreach ($query->fetchAll() as $row) {
            $data[] = array(intval($row['topic']), intval($lectureIds[$row['lecture_id']]), floatval($row['weight']));
		}
        return $data;
    }
	
	private static function getLectureWords($db, $courseId){
		$data = array();
		$sql = 'SELECT * FROM `lecturetopicword` where course_id = ? ORDER BY `topic` ASC, `weight` DESC';
		$query = $db->executeQuery($sql, array($courseId));
        foreach ($query->fetchAll() as $row) {
            $data[$row['topic']]['words'][] = $row['word'];
            $data[$row['topic']]['id'] = $row['topic'];
        }
        return $data;
	}
	
	public static function getAllWords($db, $courseId) {
        $data = array();
        foreach (self::getLectureWords($db, $courseId) as $key => $item) {
            $data[$key][] = implode(', ', $item['words']);
        }
        return $data;
    }
	
	private static function getLectureIdsOrderedByName($db, $courseId) {
		$data = array();
        $rowCount = 0;
        foreach (self::getLectures($db, $courseId) as $row) {
            $data[$row['id']] = $rowCount;
            $rowCount++;
        }
        return $data;
    }
	
	private static function getLectures($db, $courseId){
		$sql = 'SELECT * FROM `lecture` where course_id = ? ORDER BY `name`';
		$data = $db->fetchAll($sql, array($courseId));
        return $data;
	}
}