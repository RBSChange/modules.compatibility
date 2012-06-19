<?php
/**
 * @deprecated
 */
interface f_chart_DataTableProducer
{
	/**
	 * @deprecated
	 */
	function getDataTable($params = null);
}

/**
 * @deprecated
 */
class f_chart_Axis
{
	/**
	 * @var f_chart_Range
	 */
	private $range;
	/**
	 * @var f_chart_AxisStyle
	 */
	private $style;

	/**
	 * @deprecated
	 */
	function __construct($range, $style = null)
	{
		$this->range = $range;
		$this->style = $style;
	}

	/**
	 * @deprecated
	 */
	function getRange()
	{
		return $this->range;
	}

	/**
	 * @deprecated
	 */
	function getStyle()
	{
		return $this->style;
	}


}

/**
 * @deprecated
 */
class f_chart_AxisStyle
{
	/**
	 * @deprecated
	 */
	const ALIGN_LEFT = -1;
	/**
	 * @deprecated
	 */
	const ALIGN_CENTERED = 0;
	/**
	 * @deprecated
	 */
	const ALIGN_RIGHT = 1;
	/**
	 * @deprecated
	 */
	const DRAW_LINES = "l";
	/**
	 * @deprecated
	 */
	const DRAW_TICK_MARKS = "t";
	/**
	 * @deprecated
	 */
	const DRAW_LINES_AND_TICK_MARKS = "lt";

	private $color;
	private $fontSize;
	private $alignement;
	private $drawControl;
	private $tickMarkColor;

	/**
	 * @deprecated
	 */
	function __construct($color, $size = null, $alignement = null, $drawControl = null, $tickMarkColor = null)
	{
		$this->color = $color;
		$this->size = $size;
		$this->alignement = $alignement;
		$this->drawControl = $drawControl;
		$this->tickMarkColor = $tickMarkColor;
	}

	/**
	 * @deprecated
	 */
	function getColor()
	{
		return $this->color;
	}
	
	/**
	 * @deprecated
	 */
	function getSize()
	{
		return $this->size;
	}
	
	/**
	 * @deprecated
	 */
	function getAlignement()
	{
		return $this->alignement;
	}
	
	/**
	 * @deprecated
	 */
	function getDrawControl()
	{
		return $this->drawControl;
	}
	
	/**
	 * @deprecated
	 */
	function getTickMarkColor()
	{
		return $this->tickMarkColor;
	}
}

/**
 * @deprecated
 */
class f_chart_Range
{
	/**
	 * @var Float
	 */
	private $start;
	/**
	 * @var Float
	 */
	private $end;
	/**
	 * @var Float
	 */
	private $interval;

	/**
	 * @deprecated
	 */
	function __construct($start, $end, $interval = null)
	{
		$this->start = $start;
		$this->end = $end;
		$this->interval = $interval;
	}

	/**
	 * @deprecated
	 */
	function getStart()
	{
		return $this->start;
	}
	/**
	 * @deprecated
	 */
	function getEnd()
	{
		return $this->end;
	}
	/**
	 * @deprecated
	 */
	function getInterval()
	{
		return $this->interval;
	}
	/**
	 * @deprecated
	 */
	function getQueryString($index)
	{
		$q =  $index.",".$this->start.",".$this->end;
		if ($this->interval !== null)
		{
			$q .= ",".$this->interval;
		}
		return $q;
	}
}
/**
 * @deprecated
 */
class f_chart_DataTable
{
	/**
	 * @deprecated
	 */
	const STRING_TYPE = 0;
	/**
	 * @deprecated
	 */
	const NUMBER_TYPE = 1;

	/**
	 * @var array<String, Integer>
	 */
	private $columns;

	private $values;

	/**
	 * @deprecated
	 */
	function addColumn($label, $type = self::NUMBER_TYPE, $color = null)
	{
		$this->columns[] = array($label, $type, $color);
	}

	/**
	 * @deprecated
	 */
	function addRows($rowCount)
	{
		$valuesSize = count($this->values);
		for ($i = 0; $i < $rowCount; $i++)
		{
			$this->values[$valuesSize+$i] = array();
		}
	}

	/**
	 * @deprecated
	 */
	function setValue($row, $col, $value)
	{
		$this->values[$row][$col] = $value;
	}

	/**
	 * @deprecated
	 */
	function setRowValues($row, $values)
	{
		$this->values[$row] = $values;
	}

	/**
	 * @deprecated
	 */
	function setColValues($col, $values, $type = self::NUMBER_TYPE, $label = null, $color = null)
	{
		foreach ($values as $row => $value)
		{
			$this->columns[$col] = array($label, $type, $color);
			$this->values[$col][$row+1] = $values;
		}
	}
	/**
	 * @deprecated
	 */
	function getValues()
	{
		return $this->values;
	}
	/**
	 * @deprecated
	 */
	function getColumns()
	{
		return $this->columns;
	}

	/**
	 * @deprecated
	 */
	function getRowCount()
	{
		return count($this->values);
	}

	/**
	 * @deprecated
	 */
	function getColCount()
	{
		return count($this->columns);
	}
	/**
	 * @deprecated
	 */
	function asString()
	{
		return serialize($this->columns).serialize($this->values);
	}
}

/**
 * @deprecated
 */
abstract class f_chart_Visualization
{
	/**
	 * @var array<String, mixed>
	 */
	protected $options;

	/**
	 * @var f_chart_DataTable
	 */
	protected $data;

	/**
	 * @deprecated
	 */
	function __construct($data, $options = null)
	{
		$this->data = $data;
		if ($options !== null)
		{
			$this->options = array_merge(self::getDefaultOptions(), $options);
		}
		else
		{
			$this->options = self::getDefaultOptions();
		}
	}

	// protected methods
	abstract static protected function getDefaultOptions();

	/**
	 * @deprecated
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
	}

	/**
	 * @deprecated
	 */
	public function getOption($name, $defaultValue = null)
	{
		if (isset($this->options[$name]))
		{
			return $this->options[$name];
		}
		return $defaultValue;
	}

	/**
	 * @deprecated
	 */
	public function hasOption($name)
	{
		return isset($this->options[$name]);
	}

	/**
	 * @deprecated
	 */
	function getDataTable()
	{
		return $this->data;
	}
}

/**
 * @deprecated
 */
class f_chart_Table extends f_chart_Visualization
{
	private static $defaultOptions;

	/**
	 * @deprecated
	 */
	function __construct($data, $options = null)
	{
		$this->data = $data;
		if ($options !== null)
		{
			$this->options = array_merge(self::getDefaultOptions(), $options);
		}
		else
		{
			$this->options = self::getDefaultOptions();
		}
	}
	/**
	 * @deprecated
	 */
	function setTitle($title)
	{
		$this->setOption("title", $title);
	}
	/**
	 * @deprecated
	 */
	function getTitle()
	{
		return $this->getOption("title");
	}
	/**
	 * @deprecated
	 */
	function getHTML()
	{
		$columns = $this->data->getColumns();
		echo "<table class=\"".$this->options["class"]."\"";
		if (isset($this->options["style"]))
		{
			echo " style=\"".$this->options["style"]."\"";
		}
		echo ">";
		$title = $this->getTitle();
		if ($title !== null)
		{
			echo "<caption>";
			echo nl2br($title);
			echo "</caption>";
		}
		echo "<thead><tr>";
		foreach ($columns as $column)
		{
			echo "<th scope=\"col\" class=\"col\">".$column[0]."</th>";
		}
		echo "</tr></thead>";
		$values = $this->data->getValues();
		$rowCount = count($values);
		$colCount = count($columns);
		echo "<tbody>";
		for ($row = 0; $row < $rowCount; $row++)
		{
			echo "<tr class=\"row-".($row%2)."\">";
			for ($col = 0; $col < $colCount; $col++)
			{
				$colType = $columns[$col][1];
				if ($colType == f_chart_DataTable::NUMBER_TYPE)
				{
					echo "<td>";
					echo $values[$row][$col];
					echo "</td>";
				}
				elseif ($colType == f_chart_DataTable::STRING_TYPE)
				{
					echo "<th scope=\"row\" class=\"row\">";
					echo $values[$row][$col];
					echo "</th>";
				}
			}
			echo "</tr>";
		}
		echo "</tbody>";
		echo "</table>";
	}

	// protected methods
	protected static function getDefaultOptions()
	{
		if (self::$defaultOptions === null)
		{
			self::$defaultOptions = array("class" => "normal chart");
		}
		return self::$defaultOptions;
	}
}

/**
 * @deprecated
 */
class f_chart_Grid
{
	private $xAxisStepSize;
	private $yAxisStepSize;
	private $lineSegmentLength = 3;
	private $blankSegmentLength = 2;
	private $xOffset = 0;
	private $yOffset = 0;

	/**
	 * @deprecated
	 */
	function __construct($xAxisStepSize = 20, $yAxisStepSize = 20)
	{
		$this->xAxisStepSize= $xAxisStepSize;
		$this->yAxisStepSize= $yAxisStepSize;
	}

	/**
	 * @deprecated
	 */
	function setLineSegmentLength($length)
	{
		$this->lineSegmentLength = $length;
		return $this;
	}

	/**
	 * @deprecated
	 */
	function setBlankSegmentLength($length)
	{
		$this->blankSegmentLength = $length;
		return $this;
	}
	/**
	 * @deprecated
	 */
	function setXOffset($offset)
	{
		$this->xOffset = $offset;
		return $this;
	}
	/**
	 * @deprecated
	 */
	function setYOffset($offset)
	{
		$this->yOffset = $offset;
		return $this;
	}
	/**
	 * @deprecated
	 */
	function getQueryString()
	{
		return "&chg=".$this->xAxisStepSize.",".$this->yAxisStepSize.",".
		$this->lineSegmentLength.",".$this->blankSegmentLength.",".
		$this->xOffset.",".$this->yOffset;
	}
}

/**
 * @deprecated
 */
abstract class f_chart_Chart extends f_chart_Visualization
{
	/**
	 * @deprecated
	 */
	const LEGEND_RIGHT = 'right';
	/**
	 * @deprecated
	 */
	const LEGEND_LEFT = 'left';
	/**
	 * @deprecated
	 */
	const LEGEND_TOP = 'top';
	/**
	 * @deprecated
	 */
	const LEGEND_BOTTOM = 'bottom';
	/**
	 * @deprecated
	 */
	const LEGEND_NONE = 'none';

	const LEGEND_ORIENT_VERTICAL = 'vertical';
	/**
	 * @deprecated
	 */
	const LEGEND_ORIENT_HORIZONTAL = 'horizontal';

	private static $defaultOptions;

	private static $googleChartProvider;
	/**
	 * @deprecated
	 */
	function setWidth($width)
	{
		$this->setOption("width", $width);
	}
	/**
	 * @deprecated
	 */
	function getWidth()
	{
		return $this->getOption("width");
	}
	/**
	 * @deprecated
	 */
	function setHeight($height)
	{
		$this->setOption("height", $height);
	}
	/**
	 * @deprecated
	 */
	function getHeight()
	{
		return $this->getOptions("height");
	}
	/**
	 * @deprecated
	 */
	function setLegendOrient($legendOrient)
	{
		$this->setOption("legendOrient", $legendOrient);
	}
	/**
	 * @deprecated
	 */
	function getLegendOrient()
	{
		return $this->getOption("legendOrient");
	}
	/**
	 * @deprecated
	 */
	function setLegendPosition($legendPosition)
	{
		$this->setOption("legendPosition", $legendPosition);
	}
	/**
	 * @deprecated
	 */
	function getLegendPosition()
	{
		return $this->getOption("legendPosition");
	}
	/**
	 * @deprecated
	 */
	function setTitle($title)
	{
		$this->setOption("title", $title);
	}
	/**
	 * @deprecated
	 */
	function getTitle()
	{
		return $this->getOption("title");
	}
	/**
	 * @deprecated
	 */
	function setTitleColor($color)
	{
		$this->setOption("titleColor", $color);
	}
	/**
	 * @deprecated
	 */
	function getTitleColor()
	{
		return $this->getOption("titleColor");
	}
	/**
	 * @deprecated
	 */
	function setTitleSize($size)
	{
		$this->setOption("titleSize", $size);
	}
	/**
	 * @deprecated
	 */
	function getTitleSize()
	{
		$this->getOption("titleSize");
	}
	/**
	 * @deprecated
	 */
	function setLeftMargin($margin)
	{
		$this->setOption("leftMargin", $margin);
	}
	/**
	 * @deprecated
	 */
	function setRightMargin($margin)
	{
		$this->setOption("rightMargin", $margin);
	}
	/**
	 * @deprecated
	 */
	function setTopMargin($margin)
	{
		$this->setOption("topMargin", $margin);
	}
	/**
	 * @deprecated
	 */
	function setBottomMargin($margin)
	{
		$this->setOption("bottomMargin", $margin);
	}
	/**
	 * @deprecated
	 */
	function setLegendWidth($width)
	{
		$this->setOption("legendWidth", $width);
	}
	/**
	 * @deprecated
	 */
	function setLegendHeight($height)
	{
		$this->setOption("legendHeight", $height);
	}

	/**
	 * @deprecated
	 */
	function setGrid($grid)
	{
		$this->setOption("grid", $grid);
	}
	/**
	 * @deprecated
	 */
	function getQueryString()
	{
		$q = "";

		// Size
		$q .= "chs=".$this->getOption("width")."x".$this->getOption("height");

		// Legend position
		if ($this->getLegendOrient() == self::LEGEND_ORIENT_VERTICAL)
		{
			switch ($this->getLegendPosition())
			{
				case self::LEGEND_BOTTOM: $q .= "&chdlp=bv"; break;
				case self::LEGEND_TOP: $q .= "&chdlp=tv"; break;
				case self::LEGEND_RIGHT: $q .= "&chdlp=r"; break;
				case self::LEGEND_LEFT: $q .= "&chdlp=l"; break;
			}
		}
		elseif ($this->getLegendOrient() == self::LEGEND_ORIENT_HORIZONTAL)
		{
			switch ($this->getLegendPosition())
			{
				case self::LEGEND_BOTTOM: $q .= "&chdlp=b"; break;
				case self::LEGEND_TOP: $q .= "&chdlp=t "; break;
			}
		}

		// Title
		$q .= "&chtt=".urlencode(str_replace(array("\r\n", "\n"), "|", $this->getTitle()));
		if ($this->getTitleColor() !== null || $this->getTitleSize() !== null)
		{
			$q .= "&chts=".$this->getTitleColor();
			if ($this->getTitleSize() !== null)
			{
				$q .= ",".$this->getTitleSize();
			}
		}

		// Data. http://code.google.com/apis/chart/formats.html
		$columns = $this->data->getColumns();
		$columnsCount = count($columns);
		$values = $this->data->getValues();
		$rowCount = count($values);

		$q .= "&chd=t:"; // TODO: encode for the better queryString length
		for ($col = 0; $col < $columnsCount; $col++)
		{
			$column = $columns[$col];
			if ($column[1] === f_chart_DataTable::NUMBER_TYPE)
			{
				$rowValues = array();
				for ($row = 0; $row < $rowCount; $row++)
				{
					// TODO: encode for the better queryString length
					$rowValues[] = $values[$row][$col];
				}
				$q .= join(",", $rowValues);
				if ($col+1 < $columnsCount)
				{
					$q .= "|";
				}
			}
		}

		// Margins
		$chma = "&chma=".$this->getOption("leftMargin").",".
		$this->getOption("rightMargin").",".
		$this->getOption("topMargin").",".
		$this->getOption("bottomMargin");
		$legendWidth = $this->getOption("legendWidth");
		$legendHeight = $this->getOption("legendHeight");
		if ($legendWidth !== null || $legendHeight !== null)
		{
			$chma .= "|".$legendWidth.",".$legendHeight;
		}
		$q .= $chma;

		// Grid
		$grid = $this->getOption("grid");
		if ($grid !== null)
		{
			$q .= $grid->getQueryString();
		}

		return $q;
	}

	private static function getGoogleChartProvider()
	{
		if (self::$googleChartProvider === null)
		{
			self::$googleChartProvider = Framework::getConfigurationValue("charts/googleChartProvider");
		}
		return self::$googleChartProvider;
	}
	
	/**
	 * @deprecated
	 */
	function getUrl()
	{
		$md5 = md5(self::getGoogleChartProvider().$this->data->asString().serialize($this->options));
		$key = "";
		for ($i = 0; $i < strlen($md5); $i++)
		{
			$key .= $md5[$i];
			if ($i % 2)
			{
				$key .= "/";
			}
		}
		$title = f_util_StringUtils::isEmpty($this->getTitle()) ? "chart" : $this->getTitle();
		$key .= $title.".png";

		$path = f_util_FileUtils::buildWebCachePath("charts", $key);
		$cacheTime = $this->getOption("cacheTime", 0);
		if (!file_exists($path) || (filemtime($path)+$cacheTime) < time())
		{
			f_util_FileUtils::writeAndCreateContainer($path, file_get_contents($this->getDirectUrl()), f_util_FileUtils::OVERRIDE);
		}
		return LinkHelper::getRessourceLink("/cache/www/charts/".$key)->getUrl();
	}
	
	/**
	 * @deprecated
	 */
	function getDirectUrl()
	{
		return self::getGoogleChartProvider()."?".$this->getQueryString();
	}

	// protected methods
	protected static function getDefaultOptions()
	{
		if (self::$defaultOptions === null)
		{
			self::$defaultOptions = array("width" => "400", "height" => "240",
				"legendPosition" => self::LEGEND_RIGHT, "legendOrient" => self::LEGEND_ORIENT_VERTICAL,
				"leftMargin" => 30, "rightMargin" => 30, "topMargin" => 30, "bottomMargin" => 30);
		}
		return self::$defaultOptions;
	}
}

/**
 * @deprecated
 */
abstract class f_chart_2AxisChart extends f_chart_Chart
{
	private static $defaultOptions;
	protected $rotated = false;

	/**
	 * @deprecated
	 */
	function __construct($data, $options = null)
	{
		$this->data = $data;
		if ($options !== null)
		{
			$this->options = array_merge(self::getDefaultOptions(), $options);
		}
		else
		{
			$this->options = self::getDefaultOptions();
		}
	}

	/**
	 * @deprecated
	 */
	function setBottomAxis($axis)
	{
		$this->setOption("bottomAxis", $axis);
	}

	/**
	 * @deprecated
	 */
	function getBottomAxis()
	{
		return $this->getOption("bottomAxis");
	}

	/**
	 * @deprecated
	 */
	function setLeftAxis($axis)
	{
		$this->setOption("leftAxis", $axis);
	}

	/**
	 * @deprecated
	 */
	function getLeftAxis()
	{
		$leftAxis = $this->getOption("leftAxis");
		if ($leftAxis === null)
		{
			list($min, $max) = $this->getMinMax();
			$min -= abs($min * 0.1);
			$max += abs($max * 0.1);
			$leftAxis = new f_chart_Axis(new f_chart_Range($min, $max));
			$this->setOption("leftAxis", $leftAxis);
		}
		return $leftAxis;
	}

	protected function getMinMax()
	{
		$values = $this->data->getValues();
		$columns = $this->data->getColumns();
		$rowCount = $this->data->getRowCount();
		$min = null;
		$max = null;
		for ($col = 0; $col < $this->data->getColCount(); $col++)
		{
			$column = $columns[$col];
			if ($column[1] === f_chart_DataTable::NUMBER_TYPE)
			{
				$this->getColMinMax($col, $values, $rowCount, $min, $max);
			}
		}
		return array($min, $max);
	}

	protected function getColMinMax($col, $values, $rowCount, &$min, &$max)
	{
		for ($row = 0; $row < $rowCount; $row++)
		{
			$value = $values[$row][$col];
			if ($min === null)
			{
				$min = $value;
				$max = $value;
			}
			else
			{
				if ($min > $value)
				{
					$min = $value;
				}
				if ($max < $value)
				{
					$max = $value;
				}
			}
		}
	}
	
	/**
	 * @deprecated
	 */
	function getQueryString()
	{
		$q = parent::getQueryString();

		$columns = $this->data->getColumns();
		$columnsCount = count($columns);
		$values = $this->data->getValues();
		$rowCount = count($values);

		// Legend values = dataset labels
		// + Legend colors
		$labels = array();
		$colors = array();
		for ($col = 0; $col < $columnsCount; $col++)
		{
			$column = $columns[$col];
			if ($column[1] == f_chart_DataTable::NUMBER_TYPE)
			{
				$labels[] = urlencode($column[0]);
				if (isset($column[2]))
				{
					$colors[] = $column[2];
				}
			}
		}
		if (!empty($labels))
		{
			$q .= "&chdl=".join("|", $labels);
		}
		if (!empty($colors))
		{
			$q .= "&chco=".join(",", $colors);
		}

		// Axis
		if ($this->rotated)
		{
			$chxt = "&chxt=y,x";
		}
		else
		{
			$chxt = "&chxt=x,y";
		}

		// Ranges labels. http://code.google.com/apis/chart/labels.html#axis_range
		$chds = "&chds=";
		$chxr = "&chxr=";

		if ($columns[0][1] === f_chart_DataTable::STRING_TYPE)
		{
			$chxl = "&chxl=0:|";
			$labels = array();
			for ($row = 0; $row < $rowCount; $row++)
			{
				$labels[] = urlencode($values[$row][0]);
			}
			if ($this->rotated)
			{
				$labels = array_reverse($labels);
			}
			$chxl .= join("|", $labels);
			$q .= $chxl."|2:|".$columns[0][0];
			if ($this->rotated)
			{
				$chxt .= ",y";
			}
			else
			{
				$chxt .= ",x";
			}

			$q .= "&chxp=2,50";
		}
		else
		{
			$range = $this->getBottomAxis()->getRange();
			$chxr .= $range->getQueryString(0);
			$chxr .= "|";
		}

		$leftAxis = $this->getLeftAxis();
		if ($leftAxis !== null)
		{
			$range = $leftAxis->getRange();
			$chxr .= $range->getQueryString(1);
			$chds .= $range->getStart().",".$range->getEnd();
		}

		// Range scale. http://code.google.com/apis/chart/formats.html#scaled_values
		$q .= $chds;
		$q .= $chxr;
		$q .= $chxt;

		return $q;
	}

	// protected methods
	protected static function getDefaultOptions()
	{
		if (self::$defaultOptions === null)
		{
			self::$defaultOptions = array_merge(parent::getDefaultOptions(), array());
		}
		return self::$defaultOptions;
	}
}

/**
 * @deprecated
 */
class f_chart_LineChart extends f_chart_2AxisChart
{
	/**
	 * @deprecated
	 */
	function getQueryString()
	{
		$q = parent::getQueryString();
		// Type
		$q .= "&cht=lc";
		return $q;
	}
}
/**
 * @deprecated
 */
class f_chart_PieChart extends f_chart_Chart
{
	private static $defaultOptions;

	/**
	 * @deprecated
	 */
	function __construct($data, $options = null)
	{
		$this->data = $data;
		if ($options !== null)
		{
			$this->options = array_merge(self::getDefaultOptions(), $options);
		}
		else
		{
			$this->options = self::getDefaultOptions();
		}
	}
	/**
	 * @deprecated
	 */
	function set3d()
	{
		$this->setOption("3d", true);
	}
	/**
	 * @deprecated
	 */
	function setOrientation($angleInRadian)
	{
		$this->setOption("orientation", $angleInRadian);
	}
	/**
	 * @deprecated
	 */
	function setMasterColor($color)
	{
		$this->setOption("masterColor", $color);
	}
	/**
	 * @deprecated
	 */
	function setColors($colors)
	{
		$this->setOption("colors", $colors);
	}

	/**
	 * @deprecated
	 */
	function getQueryString()
	{
		$q = parent::getQueryString();
		if ($this->hasOption("3d"))
		{
			$q .= "&cht=p3";
		}
		else
		{
			$q .= "&cht=p";
		}

		$orientation = $this->getOption("orientation");
		if ($orientation !== null)
		{
			$q .= "&chp=".$orientation;
		}

		$columns = $this->data->getColumns();
		$rowCount = $this->data->getRowCount();
		$values = $this->data->getValues();
		if ($columns[0][1] === f_chart_DataTable::STRING_TYPE)
		{
			$chl = "&chl=";
			$labels = array();
			for ($row = 0; $row < $rowCount; $row++)
			{
				$labels[] = urlencode($values[$row][0]);
			}
			$chl .= join("|", $labels);
			$q .= $chl;
		}
		$colors = $this->getOption("colors", $this->getOption("masterColor"));
		if ($colors !== null)
		{
		    $colorsFormated = "";
		    if (is_array($colors))
		    {
			$colorsFormated = implode("|", $colors);
		    }
		    else
		    {
			$colorsFormated = $colors;
		    }
		    $q .= "&chco=".$colorsFormated;
		}

		return $q;
	}

	// protected methods
	protected static function getDefaultOptions()
	{
		if (self::$defaultOptions === null)
		{
			self::$defaultOptions = array_merge(parent::getDefaultOptions(), array());
		}
		return self::$defaultOptions;
	}
}
/**
 * @deprecated
 */
class f_chart_BarChart extends f_chart_2AxisChart
{
	/**
	 * @deprecated
	 */
	const ORIENTATION_VERTICAL = 1;
	/**
	 * @deprecated
	 */
	const ORIENTATION_HORIZONTAL = 2;

	/**
	 * @deprecated
	 */
	const BAR_WIDTH_AUTO = "a";
	/**
	 * @deprecated
	 */
	const BAR_WIDTH_RELATIVE = "r";

	private static $defaultOptions;


	/**
	 * @deprecated
	 */
	function __construct($data, $options = null)
	{
		$this->data = $data;
		if ($options !== null)
		{
			$this->options = array_merge(self::getDefaultOptions(), $options);
		}
		else
		{
			$this->options = self::getDefaultOptions();
		}
	}

	/**
	 * @deprecated
	 */
	function setStacked()
	{
		$this->setOption("stacked", true);
	}

	/**
	 * @deprecated
	 */
	function setOrientation($orientation)
	{
		$this->setOption("orientation", $orientation);
		$this->rotated = $orientation === self::ORIENTATION_HORIZONTAL;
	}

	/**
	 * @deprecated
	 */
	function setBarWidth($width)
	{
		$this->setOption("barWidth", $width);
	}

	/**
	 * @deprecated
	 */
	function setBarSpace($space)
	{
		$this->setOption("barSpace", $space);
	}

	/**
	 * @deprecated
	 */
	function setGroupSpace($space)
	{
		$this->setOption("groupSpace", $space);
	}

	/**
	 * @deprecated
	 */
	function getQueryString()
	{
		$q = parent::getQueryString();

		// Type
		$orientation = $this->getOption("orientation");
		$cht = "&cht=b";
		$cht .= ($orientation === self::ORIENTATION_HORIZONTAL) ? "h" : "v";
		$cht .= ($this->getOption("stacked")) ? "s" : "g";
		$q .= $cht;

		// Bar width & spacing
		$barWidth = $this->getOption("barWidth");
		if ($barWidth !== null)
		{
			$chbh = "&chbh=".$barWidth;
			$barSpace = $this->getOption("barSpace");
			$groupSpace = $this->getOption("groupSpace");
			if ($barSpace !== null || $groupSpace !== null)
			{
				$chbh .= ",".$barSpace.",".$groupSpace;
			}
			$q .= $chbh;
		}

		return $q;
	}

	/**
	 * @deprecated
	 */
	function getLeftAxis()
	{
		$leftAxis = $this->getOption("leftAxis");
		if ($leftAxis === null)
		{
			list($min, $max) = $this->getMinMax();
			$min -= abs($min * 0.1);
			$max += abs($max * 0.1);
			$leftAxis = new f_chart_Axis(new f_chart_Range($min, $max));
			$this->setOption("leftAxis", $leftAxis);
		}
		return $leftAxis;
	}

	protected function getMinMax()
	{
		if (!$this->getOption("stacked"))
		{
			list($min, $max) = parent::getMinMax();
		}
		else
		{
			$values = $this->data->getValues();
			$columns = $this->data->getColumns();
			$rowCount = $this->data->getRowCount();
			$min = 0;
			$max = 0;
			for ($col = 0; $col < $this->data->getColCount(); $col++)
			{
				$column = $columns[$col];
				if ($column[1] === f_chart_DataTable::NUMBER_TYPE)
				{
					$colMin = $colMax = null;
					$this->getColMinMax($col, $values, $rowCount, $colMin, $colMax);
					$min += $colMin;
					$max += $colMax;
				}
			}

		}
		if ($min > 0) $min = 0;
		return array($min, $max);
	}

	// protected methods
	protected static function getDefaultOptions()
	{
		if (self::$defaultOptions === null)
		{
			self::$defaultOptions = array_merge(parent::getDefaultOptions(),
			array(
			 	"orientation" => self::ORIENTATION_VERTICAL,
			 	"stacked" => false,
			 	"barWidth" => self::BAR_WIDTH_AUTO, 
			 	"barSpace" => 4, "groupSpace" => 8));
		}
		return self::$defaultOptions;
	}
}