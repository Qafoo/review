<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Chart;

/**
 * Line chart base class
 *
 * @version $Revision$
 */
class LineChart extends \ezcGraphLineChart
{
    /**
     * Construct pie chart
     *
     * Construct pie chart and assign common setting to fit it to arbit layout
     * settings
     *
     * @return void
     */
    public function __construct( $title )
    {
        parent::__construct();

        // Use specific arbit palette
        $this->palette = new ChartPalette();

        // Use 2D renderer by default for line charts
        $this->renderer = new \ezcGraphRenderer2d();

        // More beautiful formatting for legend
        $this->renderer->options->dataBorder             = 0;
        $this->renderer->options->legendSymbolGleam      = .3;
        $this->renderer->options->legendSymbolGleamSize  = .9;
        $this->renderer->options->legendSymbolGleamColor = '#FFFFFF';

        // Line chart formatting options
        $this->options->fillLines              = 255;
        $this->renderer->options->shortAxis    = true;
        $this->renderer->options->axisEndStyle = \ezcGraph::NO_SYMBOL;

        // Include SVG font for more precise text rendering
        $this->options->font = __DIR__ . '/font.svg';

        // Line chart specific options
        $this->options->lineThickness = 2;

        $this->options->highlightSize = 12;
        $this->options->highlightFont->background = '#eeeeef80';
        $this->options->highlightFont->border = '#babdb6';
        $this->options->highlightFont->borderWidth = 1;

        $this->legend->position = \ezcGraph::BOTTOM;
        $this->legend->borderWidth = 1;

        $this->title->borderWidth = 1;
        $this->title = $title;

        $this->background->image    = __DIR__ . '/chart_background.png';
        $this->background->repeat   = \ezcGraph::NO_REPEAT;
        $this->background->position = \ezcGraph::CENTER | \ezcGraph::MIDDLE;

        $this->xAxis = new \ezcGraphChartElementDateAxis();
        $this->xAxis->axisLabelRenderer = new \ezcGraphAxisCenteredLabelRenderer();

        $this->yAxis->axisLabelRenderer = new \ezcGraphAxisCenteredLabelRenderer();
        $this->yAxis->font->maxFontSize = 10;
    }
}

