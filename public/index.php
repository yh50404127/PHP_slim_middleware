<?php
use Psr\Http\Message\ServerRequestInterface as Request;        //正常Request
use Psr\Http\Server\RequestHandlerInterface as RequestHandler; //中間件要用的Request

use Psr\Http\Message\ResponseInterface as Response;		       //正常Response
use Slim\Psr7\Response as MiddlewareResponse;                  //中間件要用的Response

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

//中間件 輸入(正常Request)和(中間件要用的Request) 輸出(中間件要用的Response)
$beforeMiddleware = function (Request $request, RequestHandler $handler) {
    $response = $handler->handle($request);  //跳到下一級的中間件
    $existingContent = (string) $response->getBody();

    $response = new MiddlewareResponse();    //新建一個空的響應
    $response->getBody()->write('BEFORE ' . $existingContent);
 
    return $response;
};

$afterMiddleware = function (Request $request, RequestHandler $handler) {
    $response = $handler->handle($request);  //跳到下一級的中間件
    $response->getBody()->write(' AFTER');
    return $response;
};

//增加中間件
$app->add($beforeMiddleware);
$app->add($afterMiddleware);

//要把響應換成中間件的響應
$app->get('/', function (Request $request, MiddlewareResponse $response) {
    $response->getBody()->write("Hello, world~~~"); //在後面增加資料不會刪除前面的
    return $response;
});

$app->get('/hello/{name}', function (Request $request, MiddlewareResponse $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->run();