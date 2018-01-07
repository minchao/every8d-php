<?php

require __DIR__ . '/../vendor/autoload.php';

use function GuzzleHttp\Psr7\parse_query;
use Symfony\Component\VarDumper;
use Slim\Http\Request;
use Slim\Http\Response;
use Symfony\Component\Console\Output\ConsoleOutput;

$app = new Slim\App();
$app->get('/callback', function (Request $request, Response $response) {
    $output = new ConsoleOutput();
    $output->writeln(
        sprintf(
            '[%s] %s',
            date('D M  j H:i:s Y', time()),
            $request->getUri()
        )
    );

    $querys = parse_query($request->getUri()->getQuery());
    $dumper = new VarDumper\Dumper\CliDumper();
    $cloner = new VarDumper\Cloner\VarCloner();

    $dumper->dump($cloner->cloneVar($querys));

    $response->getBody()->write('Ok');
});

$app->run();
