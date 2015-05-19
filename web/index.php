<?php
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
        'name' => 'kala',
    ));
});

$app->get('/about', function() use ($app) {
    return $app['twig']->render('about.twig', array());
});

$app->get('/contact', function() use ($app) {
    return $app['twig']->render('contact.twig', array());
});

$app->get('/corpus', function() use ($app) {
    $maxRows = 40;
    $sql = 'SELECT `word`, `count` FROM `corpusword` ORDER BY `count` DESC LIMIT ?';
    $colors = BIS\RandomColor::get($maxRows);
    $data = array();
    foreach ($app['db']->fetchAll($sql, array($maxRows)) as $i => $row) {
        $data[] = array(
            'label' => $row['word'],
            'value' => intval($row['count']),
            'color' => $colors[$i]
        );
    }
    // return $app->json($data);
    return $app['twig']->render('corpus.twig', array(
        'rows' => $maxRows,
        'data' => json_encode($data),
    ));
});

$app->get('/course', function() use ($app) {
    $sql = 'SELECT * FROM course ORDER BY ?';
    $data = $app['db']->fetchAll($sql, array('name'));
    
    return $app->json($data);
});

$app->run();