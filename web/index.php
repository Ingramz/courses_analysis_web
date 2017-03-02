<?php
define('MAX_ROWS', 100);
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
        'corpus_words' => json_encode(BIS\CorpusWord::topWords($app['db'], MAX_ROWS)),
    ));
});

$app->get('/about', function() use ($app) {
    return $app['twig']->render('about.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
        'lda_loglikelihood' => json_encode(BIS\LdaLoglikelihood::all($app['db'])),
		'summary' => BIS\CorpusWord::summary($app['db']),
		'max_iter' => BIS\LdaLoglikelihood::getIterationCount($app['db']),
    ));
});

$app->get('/corpus', function(Silex\Application $app) {
    return $app['twig']->render('corpus.twig', array(
        'rows' => MAX_ROWS,
        'corpus_words' => json_encode(BIS\CorpusWord::topWords($app['db'], MAX_ROWS)),
        'courses' => BIS\Course::getAll($app['db']),
        'summary' => BIS\CorpusWord::summary($app['db']),
    ));
});

$app->get('/corpus/{courseId}', function(Silex\Application $app, $courseId) {
    return $app['twig']->render('corpus.twig', array(
        'rows' => MAX_ROWS,
        'corpus_words' => json_encode(BIS\CourseWord::topWords($app['db'], MAX_ROWS, $courseId)),
        'courses' => BIS\Course::getAll($app['db']),
        'course'  => BIS\Course::getRecord($app['db'], $courseId),
        'summary' => BIS\CourseWord::summary($app['db'], $courseId),
        'topics' => BIS\Topic::course($app['db'], $courseId),
        'topic_weights' => json_encode(BIS\Topic::courseTopicWeights($app['db'], $courseId)),
		'topic_words' => json_encode(BIS\LectureLDA::getAllWords($app['db'], $courseId)),
        'map_lectures' => json_encode(BIS\LectureLDA::allLectureNames($app['db'], $courseId)),
        'map_topics' => json_encode(BIS\LectureLDA::allTopicNames($app['db'], $courseId)),
        'map_data' => json_encode(BIS\LectureLDA::allLectureTopics($app['db'], $courseId)),
		'lecture_url' => json_encode(BIS\LectureLDA::allLectureHyperlinks($app['db'], $courseId)),
		'material_topics' =>BIS\MaterialLDA::getMaterialTopics($app['db'], $courseId),
	));
})
->assert('courseId', '\d+');

$app->get('/topics', function(Silex\Application $app) {
    return $app['twig']->render('topics.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
        'topics' => BIS\Topic::all($app['db']),
    ));
});

$app->get('/course_topics', function(Silex\Application $app) {
    return $app['twig']->render('course_topics.twig', array(
        'courses' => BIS\Course::getAll($app['db']),
        'topics' => BIS\Topic::all($app['db']),
        'topic_words' => json_encode(BIS\Topic::getAllWords($app['db'])),
        'map_courses' => json_encode(BIS\Course::getAllNames($app['db'])),
        'map_topics' => json_encode(BIS\Topic::getAllNames($app['db'])),
        'map_data' => json_encode(BIS\Topic::courseTopics($app['db'])),
    ));
});


$app->run();