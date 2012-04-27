<?php
/**
 * This file is part of Titio
 *
 * @version $Revision$
 */

namespace Qafoo\Review;
use Qafoo\RMF;

require __DIR__ . '/../main/Qafoo/Review/bootstrap.php';
$dic = new DIC\Base();
$dic->environment = 'development';

$dic->mysqli;

$dispatcher = new RMF\Dispatcher\Simple(
    new RMF\Router\Regexp( array(
        '(^/$)' => array(
            'GET'  => function() {
                return "Hello world!";
            },
        ),
    ) ),
    new RMF\View\HtmlJson(
        new RMF\View\Json()
    )
);

$request = new RMF\Request\HTTP();
$request->addHandler( 'body', new RMF\Request\PropertyHandler\PostBody() );
$request->addHandler( 'session', new RMF\Request\PropertyHandler\Session() );

$dispatcher->dispatch( $request );

