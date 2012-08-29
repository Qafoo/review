<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\Chart;

/**
 * Chart palette in Qafoo style
 *
 * @version $Revision$
 */
class ChartPalette extends \ezcGraphPalette
{
    /**
     * Axiscolor
     *
     * @var \ezcGraphColor
     */
    protected $axisColor = '#2E3436';

    /**
     * Color of grid lines
     *
     * @var \ezcGraphColor
     */
    protected $majorGridColor = '#2E3436B0';

    /**
     * Color of minor grid lines
     *
     * @var \ezcGraphColor
     */
    protected $minorGridColor = '#2E3436E0';

    /**
     * Array with colors for datasets
     *
     * @var array
     */
    protected $dataSetColor = array(
        '#97BF0D',
        '#3465A4',
        '#4E9A06',
        '#CC0000',
        '#EDD400',
        '#75505B',
        '#F57900',
        '#204A87',
        '#C17D11',
    );

    /**
     * Array with symbols for datasets
     *
     * @var array
     */
    protected $dataSetSymbol = array(
        \ezcGraph::NO_SYMBOL,
    );

    /**
     * Name of font to use
     *
     * @var string
     */
    protected $fontName = 'sans-serif';

    /**
     * Fontcolor
     *
     * @var \ezcGraphColor
     */
    protected $fontColor = '#2E3436';

    /**
     * Backgroundcolor for chart
     *
     * @var \ezcGraphColor
     */
    protected $chartBackground = '#FFFFFF';

    /**
     * Padding in elements
     *
     * @var integer
     */
    protected $padding = 1;

    /**
     * Margin of elements
     *
     * @var integer
     */
    protected $margin = 1;

    /**
     * Backgroundcolor for elements
     *
     * @var \ezcGraphColor
     */
    protected $elementBackground = '#e6f0c580';

    /**
     * Bordercolor for elements
     *
     * @var \ezcGraphColor
     */
    protected $elementBorderColor = '#cade8480';

    /**
     * Borderwidth for elements
     *
     * @var integer
     * @access protected
     */
    protected $elementBorderWidth = 1;
}

