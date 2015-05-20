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
        return $summary;
    }
}
