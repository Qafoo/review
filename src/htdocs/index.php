<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;
use Qafoo\RMF;

require __DIR__ . '/../main/Qafoo/Review/bootstrap.php';
$dic = new DIC\Base();

$dic->mysqli;

$dispatcher = new RMF\Dispatcher\Simple(
    new RMF\Router\Regexp( array(
        '(^/$)' => array(
            'GET'  => array( $dic->reviewController, 'showOverview' ),
        ),
        '(^/show/(?P<analyzer>[A-Za-z_]+))' => array(
            'GET'  => array( $dic->reviewController, 'showAnalyzer' ),
        ),
        '(^/source/annotate$)' => array(
            'POST'  => array( $dic->sourceController, 'annotate' ),
        ),
        '(^/source/?(?P<path>.*)$)' => array(
            'GET'  => array( $dic->reviewController, 'showSource' ),
        ),
    ) ),
    $dic->view
);

$request = new RMF\Request\HTTP();
$request->addHandler( 'body', new RMF\Request\PropertyHandler\PostBody() );
$request->addHandler( 'session', new RMF\Request\PropertyHandler\Session() );

$dispatcher->dispatch( $request );

