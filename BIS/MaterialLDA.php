<?php
namespace BIS;

class MaterialLDA {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
	public static function getMaterialTopics($db, $courseId) {
		$data = array();
		$topics = array();
		$sql = 'SELECT course.id as cid, course.name as cname, course.year, course.semester, materialtopic.topic, materialtopic.weight, lecture.name as lname, lecture.url FROM materialtopic INNER JOIN lecture ON materialtopic.lecture_id = lecture.id INNER JOIN course ON lecture.course_id = course.id WHERE materialtopic.topic in (SELECT materialtopic.topic FROM materialtopic INNER JOIN lecture ON materialtopic.lecture_id = lecture.id INNER JOIN course ON lecture.course_id = course.id WHERE course.id = ?) ORDER BY materialtopic.topic ASC, materialtopic.weight DESC';
		$query = $db->executeQuery($sql, array($courseId));
        foreach ($query->fetchAll() as $row) {
			
			$main = $row['cid'] == $courseId;			
            $data[$row['topic']]['lectures'][] = array( 'lname' => $row['lname'], 'main' => $main, 'link' => $row['url'], 'cname' => $row['cname'] . ' ' . $row['year'] . '/' . $row['semester'] . ' (' . $row['weight'] . '%)');
			$topics[] = $row['topic'];
        }
		
		$topics = array_unique($topics);
		
		$sql = 'SELECT * from materialtopicword where materialtopicword.topic IN (?)';
		$query = $db->executeQuery($sql, array($topics), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
		foreach ($query->fetchAll() as $row) {
            $data[$row['topic']]['words'][] = $row['word'];
		}		
				
		return $data;
	}
	
}