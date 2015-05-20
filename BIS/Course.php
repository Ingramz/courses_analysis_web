<?php
namespace BIS;

class Course {
    private $db;
    
    public function __construct(Silex\Application $app) {
        $this->db = $app['db'];
    }
    
    public static function getAll($db) {
        $sql = 'SELECT * FROM `course` ORDER BY `name`';
        $data = $db->fetchAll($sql);
        return $data;
    }
    
    public static function getRecord($db, $id) {
        $sql = 'SELECT * FROM `course` WHERE `id` = ?';
        $data = $db->fetchAssoc($sql, array($id));
        return $data;
    }
}
