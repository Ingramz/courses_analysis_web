<?php
namespace BIS;

class MaterialLDA {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
	public static function getMaterialTopicsExpanded($db, $courseId) {
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
		
		$sql = 'SELECT * from materialtopicinfo where materialtopicinfo.topic IN (?)';
		$query = $db->executeQuery($sql, array($topics), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
		foreach ($query->fetchAll() as $row) {
            $data[$row['topic']]['tname'] = $row['name'] . "(" . $row['topic'] . ")";
		}
				
		return $data;
	}
	
	public static function getMaterialTopics($db, $courseId) {
		$lectures = array();
		$topics = array();
		$lecture_info = array();
		
		$sql = 'SELECT materialtopic.topic, materialtopic.weight, lecture.name as lname, lecture.url, lecture.id as lid, materialtopicinfo.name as tname FROM materialtopic INNER JOIN lecture ON materialtopic.lecture_id = lecture.id INNER JOIN course ON lecture.course_id = course.id INNER JOIN materialtopicinfo ON materialtopic.topic = materialtopicinfo.topic WHERE course.id = ? ORDER BY materialtopic.topic ASC, materialtopic.weight DESC';
		$query = $db->executeQuery($sql, array($courseId));
        foreach ($query->fetchAll() as $row) {
			$lectures[$row['lid']][$row['topic']] = $row['weight'];
            $topics[$row['topic']]['tname'] = $row['tname'];
			$lecture_info[$row['lid']] = array('name' => $row['lname'], 'link' => $row['url']);
        }
		
		if(empty($topics)){
			return array('lecture_names' => array(), 'lecture_urls' => array(), 'topics' => array());
		}
		
		$topic_ids = array_keys($topics);
		$other_id = max($topic_ids) + 1; // Find other topic ID, should be last
		$topics[$other_id]['tname'] = "Other";  // Initialize 'Other' topic
		foreach ($lectures as $key => $val) {
			foreach ($topic_ids as $t) {
				if (!array_key_exists($t, $val)) {
					$lectures[$key][$t] = 0.0;
				}
			}
			$lectures[$key][$other_id] = 100 - array_sum(array_values($val));
		}
		
		//Create distribution by topics
		foreach ($lectures as $lec_id => $top) {
			foreach ($top as $t_id => $t_weight) {
				$topics[$t_id]['lectures'][$lec_id] = array('weight' => floatval($t_weight), 'name' => $lecture_info[$lec_id]['name']);
			}
		}
		
		$data = array();
		//Sort by lecture ID, leave only weights
		foreach ($topics as $t_id => $val) {
			usort($topics[$t_id]['lectures'], 'BIS\MaterialLDA::sortByName');
			$new_tname = ($t_id == $other_id) ? $val['tname'] : $val['tname'] . "(" . $t_id . ")";
			$data['topics'][] = array('name' => $new_tname , 'data' => array_column($val['lectures'], 'weight') );	
			
		}
		
		usort($lecture_info, 'BIS\MaterialLDA::sortByName');
		foreach ($lecture_info as $l_id => $l_info) {
			$data['lecture_names'][] = $l_info['name'];
			$data['lecture_urls'][$l_info['name']] = $l_info['link'];
		}
		
		return $data;
	}
	
	private static function sortByName($a, $b) {
		return strcmp($a['name'], $b['name']);
	}
	
}