<?php
define('MAX_ROWS', 40);
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('BIS', __DIR__. '/..');

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__ . '/../db/courses.sqlite',
    ),
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app->get('/', function() use ($app) {
    return $app['twig']->render('index.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
    ));
});

$app->get('/about', function() use ($app) {
    return $app['twig']->render('about.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
    ));
});

$app->get('/corpus', function(Silex\Application $app) {
    $sql = 'SELECT `word`, `count` FROM `corpusword` ORDER BY `count` DESC LIMIT ?';
    $colors = BIS\RandomColor::get(MAX_ROWS);
    $data = array();
    foreach ($app['db']->fetchAll($sql, array(MAX_ROWS)) as $i => $row) {
        $data[] = array(
            'label' => $row['word'],
            'value' => intval($row['count']),
            'color' => $colors[$i]
        );
    }
    // return $app->json($data);
    return $app['twig']->render('corpus.twig', array(
        'rows' => MAX_ROWS,
        'data' => json_encode($data),
        'courses' => BIS\Course::getAll($app['db']),
        'summary' => BIS\CorpusWord::summary($app['db']),
    ));
});

$app->get('/corpus/{courseId}', function(Silex\Application $app, $courseId) {
    $colors = BIS\RandomColor::get(MAX_ROWS);
    $sql = 'SELECT `word`, `count` FROM `courseword` WHERE `course_id` = ? ORDER BY `count` DESC LIMIT ?';
    $data = array();
    foreach ($app['db']->fetchAll($sql, array($courseId, MAX_ROWS)) as $i => $row) {
        $data[] = array(
            'label' => $row['word'],
            'value' => intval($row['count']),
            'color' => $colors[$i]
        );
    }

    return $app['twig']->render('corpus.twig', array(
        'rows' => MAX_ROWS,
        'data' => json_encode($data),
        'courses' => BIS\Course::getAll($app['db']),
        'course'  => BIS\Course::getRecord($app['db'], $courseId),
        'summary' => BIS\CourseWord::summary($app['db'], $courseId),
        'topics' => BIS\Topic::course($app['db'], $courseId)
    ));
})
->assert('courseId', '\d+');

$app->get('/topics', function(Silex\Application $app) {
    return $app['twig']->render('topics.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
        'topics' => BIS\Topic::all($app['db']),
    ));
});

// $app->get('/course', function() use ($app) {
//     $sql = 'SELECT * FROM course ORDER BY ?';
//     $data = $app['db']->fetchAll($sql, array('name'));
//
//     return $app->json($data);
// });

$app->run();