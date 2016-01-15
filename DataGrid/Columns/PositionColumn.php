<?php

require_once dirname(__FILE__) . '/NumericColumn.php';



/**
 * Representation of positioning data grid column, that provides moving entries up or down.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
class PositionColumn extends NumericColumn
{

	/** @var array */
	public $moves = [];

	/** @var string  signal handler of move action */
	public $destination;

	/** @var bool */
	public $useAjax;

	/** @var int */
	protected $min;

	/** @var int */
	protected $max;



	/**
	 * Checkbox column constructor.
	 *
	 * @param  string  column's textual caption
	 * @param  string  destination or signal to handler which do the move rutine
	 * @param  array   textual labels for generated links
	 * @param  bool    use ajax? (add class self::$ajaxClass into generated link)
	 * @return void
	 */
	public function __construct($caption = NULL, $destination = NULL, array $moves = NULL, $useAjax = TRUE)
	{
		parent::__construct($caption, 0);

		$this->useAjax = $useAjax;

		if (empty($moves)) {
			$this->moves['up'] = 'Move up';
			$this->moves['down'] = 'Move down';
		} else {
			$this->moves = $moves;
		}

		// try set handler if is not set
		if ($destination === NULL) {
			$this->destination = $this->getName . 'Move!';
		} else {
			$this->destination = $destination;
		}

		$this->monitor('DataGrid');
	}



	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 *
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($dataGrid)
	{
		if ($dataGrid instanceof DataGrid) {
			$dataSource = clone $dataGrid->dataSource;
			$dataSource->orderBy([]);
			$this->min = (int) $dataSource->select($this->getName())->orderBy($this->getName(), 'ASC')->fetchSingle();
			$this->max = (int) $dataSource->select($this->getName())->orderBy($this->getName(), 'DESC')->fetchSingle();
		}

		parent::attached($dataGrid);
	}



	/**
	 * Formats cell's content.
	 *
	 * @param  mixed
	 * @param  DibiRow|array
	 * @return string
	 */
	public function formatContent($value, $data = NULL)
	{
		$control = $this->getDataGrid(TRUE)->lookup('Nette\Application\UI\Control', TRUE);
		$uplink = $control->link($this->destination, ['key' => $value, 'dir' => 'up']);
		$downlink = $control->link($this->destination, ['key' => $value, 'dir' => 'down']);

		$up = Nette\Utils\Html::el('a')->title($this->moves['up'])->href($uplink)->add(Nette\Utils\Html::el('span')->class('up'));
		$down = Nette\Utils\Html::el('a')->title($this->moves['down'])->href($downlink)->add(Nette\Utils\Html::el('span')->class('down'));

		if ($this->useAjax) {
			$up->class(self::$ajaxClass);
			$down->class(self::$ajaxClass);
		}

		// disable top up & top bottom links
		if ($value == $this->min) {
			$up->href(NULL);
			$up->class('inactive');
		}
		if ($value == $this->max) {
			$down->href(NULL);
			$down->class('inactive');
		}

		$positioner = Nette\Utils\Html::el('span')->class('positioner')->add($up)->add($down);

		return $positioner . $value;
	}
}
