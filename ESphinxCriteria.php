<?php
/**
 * This file contains filesource of class ESphinxCriteria
 */

/**
 * Class ESphinxCriteria
 *
 * @author mitallast <mitallast@gmail.com>
 * @version 0.1
 * @since 0.1
 * @package 
 * @property-read
 */
class ESphinxCriteria extends CComponent
{
	# Sphinx match mode types
	const MATCH_ALL = 0;
	const MATCH_ANY = 1;
	const MATCH_PHRASE = 2;
	const MATCH_BOOLEAN = 3;
	const MATCH_EXTENDED = 4;
	const MATCH_FULLSCAN = 5;
	const MATCH_EXTENDED2 = 6;
	static $matchModes = array(0,1,2,3,4,5,6);
	# Sphinx rank mode types
	const RANK_PROXIMITY_BM25 = 0;
	const RANK_BM25 = 1;
	const RANK_NONE = 2;
	const RANK_WORDCOUNT = 3;
	const RANK_PROXIMITY = 4;
	const RANK_MATCHANY  = 5;
	const RANK_FIELDMASK = 5;
	static $rankModes = array(0,1,2,3,4,5);
	# Sphinx sort mode types
	const SORT_RELEVANCE = 0;
	const SORT_ATTR_DESC = 1;
	const SORT_ATTR_ASC = 2;
	const SORT_TIME_SEGMENTS = 3;
	const SORT_EXTENDED = 4;
	const SORT_EXPR = 5;
	static $sortModes = array(0,1,2,3,4,5);
	# Sphinx group mode types
	const GROUPBY_DAY = 0;
	const GROUPBY_WEEK = 1;
	const GROUPBY_MONTH = 2;
	const GROUPBY_YEAR = 3;
	const GROUPBY_ATTR = 4;
	const GROUPBY_ATTRPAIR = 5;
	static $groupModes = array(0,1,2,3,4,5);
	/**
	 * Sphinx match mode
	 * @var int
	 */
	public $matchMode;
	/**
	 * Sphinx rank mode
	 * @var int
	 */
	public $rankMode;
	/**
	 * Sphinx sort mode
	 * @var int
	 */
	public $sortMode;
	/**
	 * Select column SQL-like list.
	 * @var string $select
	 */
	public $select;

	/**
	 * @var int defaults to null, no limit
	 */
	public $limit;
	/**
	 * @var int defaults to null, no offset
	 */
	public $offset;
	/**
	 * @var int defaults to 0, default matches
	 */
	public $max_matches=0;
	/**
	 * @var int defaults to 0, defaults cutoff
	 */
	public $cutoff=0;
	/**
	 * @var string $sort sort expression sql-like
	 */
	public $sort;
	/**
	 * @var int $idMax filters maximum id
	 */
	private $idMax;
	/**
	 * @var int $idMin filters minimum id
	 */
	private $idMin;

	/**
	 * @var array hash of field weights: array(field=>weight)
	 */
	private $fieldWeights = array();
	/**
	 * @var array hash of index weights: array(index=>weight)
	 */
	private $indexWeights = array();
	/**
	 * @var array $include include attribute values list filter
	 */
	private $include = array();
	/**
	 * @var array $exclude exclude attribute values list filter
	 */
	private $exclude = array();
	/**
	 * @var array $inRange include attribute value range array(10, 20)
	 */
	private $inRange = array();
	/**
	 * @var array $outRange exclude attribute value range array(10, 20)
	 */
	private $outRange = array();
	/**
	 * @var string $groupBy
	 */
	private $groupBy;
	/**
	 * @var int $groupFunc
	 */
	private $groupFunc;
	/**
	 * @var string $groupSort defaults to "@group desc"
	 */
	private $groupSort = "@group desc";

	/**
	 * Set field weight. If weight isset, it will be rewrited.
	 * @param string $field
	 * @param int $weight
	 */
	public function setFieldWeight($field, $weight)
	{
		$this->fieldWeights[(string)$field] = (int)$weight;
	}
	/**
	 * Set field weights. All setted earlier weights will be cleared.
	 * @param array $weights hash of field weight array(field=>weight)
	 */
	public function setFieldWeights(array $weights)
	{
		$this->fieldWeights = array();
		foreach ( $weights as $field => $weight )
			$this->setFieldWeight ($field, $weight);
	}
	/**
	 * Get setted field weights
	 * @return array hash of $field=>$weight
	 * @see setFieldWeight
	 * @see setFieldWeights
	 */
	public function getFieldWeights()
	{
		return $this->fieldWeights;
	}
	/**
	 * Set index weight. If weight isset, it will be rewrited.
	 * @param string $index
	 * @param int $weight
	 */
	public function setIndexWeight($index, $weight)
	{
		$this->indexWeights[(string)$index] = (int)$weight;
	}
	/**
	 * Set index weights. All setted earlier weights will be cleared.
	 * @param array $weights hash of field weight array(field=>weight)
	 */
	public function setIndexWeights(array $weights)
	{
		$this->indexWeights = array();
		foreach ( $weights as $index => $weights )
			$this->setIndexWeight ($index, $weights);
	}
	/**
	 * Get setted index weights
	 * @return array hash of $index=>$weight
	 * @see setIndexWeight
	 * @see setIndexWeights
	 */
	public function getIndexWeights()
	{
		return $this->indexWeights;
	}
	/**
	 * Add condition filter by field value in list values.
	 * @param string $field
	 * @param array $values
	 */
	public function setInCondition($field, array $values)
	{
		$field = strtolower(trim($field));
		$this->include[$field] = $values;
	}
	/**
	 * Get setted include filter of attribute values list
	 * <code>
	 * array(
	 *   "attributeName" => array( 1, 2, 3, 4)
	 * )
	 * </code>
	 * @return array
	 * @see setInCondition
	 */
	public function getInConditions()
	{
		return $this->include;
	}/**
	 * Add condition filter by field value not in list values.
	 * @param string $field
	 * @param array $values
	 */
	public function setNotInCondition($field, array $values)
	{
		$field = strtolower(trim($field));
		$this->exclude[$field] = $values;
	}
	/**
	 * Get setted exclude filter of attribute values list
	 * <code>
	 * array(
	 *   "attributeName" => array( 1, 2, 3, 4)
	 * )
	 * </code>
	 * @return array
	 * @see setNotInCondition
	 */
	public function getNotInConditions()
	{
		return $this->exclude;
	}
	/**
	 * Add filter by field value in range (between $min and $max).
	 * @param string $field
	 * @param int $min
	 * @param int $max
	 */
	public function setInRange($field, $min, $max)
	{
		$field = strtolower(trim($field));
		$this->inRange[$field] = array(
			"min" => (int)$min,
			"max" => (int)$max,
		);
	}
	/**
	 * Get setted in ranges
	 * <code>
	 * array(
	 *   "field" => array(
	 *     "min" => 0
	 *     "max" => 100
	 *	  )
	 * )
	 * </code>
	 * @return array
	 */
	public function getInRanges()
	{
		return $this->inRange;
	}
	/**
	 * Add filter by field value not in range (between $min and $max).
	 * @param string $field
	 * @param int $min
	 * @param int $max
	 */
	public function setNotInRange($field, $min, $max)
	{
		$field = strtolower(trim($field));
		$this->outRange[$field] = array(
			"min" => (int)$min,
			"max" => (int)$max,
		);
	}
	/**
	 * Get setted out ranges
	 * <code>
	 * array(
	 *   "field" => array(
	 *     "min" => 0
	 *     "max" => 100
	 *	  )
	 * )
	 * </code>
	 * @return array
	 */
	public function getNotInRanges()
	{
		return $this->outRange;
	}
	/**
	 * Set filter by model id range
	 * @param int $min
	 * @param int $max
	 * @see getIdMax
	 * @see getIdMin
	 */
	public function setIdRange($min, $max)
	{
		$this->idMin = (int)$min;
		$this->idMax = (int)$max;
	}
	/**
	 * Get maximum id in range
	 * @return int
	 * @see getIdMax
	 * @see setIdRange
	 */
	public function getIdMax()
	{
		return $this->idMax;
	}
	/**
	 * Get minimum id in range
	 * @return int
	 * @see getIdMin
	 * @see setIdRange
	 */
	public function getIdMin()
	{
		return $this->idMin;
	}
	/**
	 * Check is id range setted
	 * @return bool
	 */
	public function getIsIdRangeSetted()
	{
		return is_int($this->idMax) && is_int($this->idMin);
	}
	/**
	 * Set group field and function
	 * @param string $field
	 * @param int $type defaults to GROUP_ATTR
	 * @param string $sort defaults to "@group desc"
	 * @see getGroupBy
	 * @see getGroupFunc
	 * @see getGroupSort
	 * @see getIsGroupSetted
	 */
	public function setGroup($field, $type = 4, $sort = "@group desc")
	{
		$this->groupBy = (string)$field;
		$this->groupFunc = (int)$type;
		$this->groupSort = (string)$sort;
	}
	/**
	 * Get group attribute
	 * @return string|null string if setted, else null
	 * @see setGroup
	 */
	public function getGroupBy()
	{
		return $this->groupBy;
	}
	/**
	 * Get group function type
	 * @return int|null int if setted, else null
	 * @see setGroup
	 */
	public function getGroupFunc()
	{
		return $this->groupFunc;
	}
	/**
	 * @return string|null string if setted, else null
	 * @see setGroup
	 */
	public function getGroupSort()
	{
		return $this->groupSort;
	}
	/**
	 * Check is grouping setted
	 * @return bool
	 * @see setGroup
	 */
	public function getIsGroupSetted()
	{
		return strlen($this->groupBy) > 0;
	}
	/**
	 * Check is limit setted
	 * @return bool true if is limited
	 */
	public function getIsLimited()
	{
		return (int)$this->limit > 0;
	}
	/**
	 * @param int $limit
	 * @param int $offset
	 * @return void
	 */
	public function setLimit($limit, $offset = 0)
	{
		$this->limit = (int)$limit;
		$this->offset = (int)$offset;
	}
	/**
	 * @param  $attribute
	 * @param string $mode
	 * @return void
	 */
	public function setSort($attribute, $mode = 'asc')
	{
		$this->sortMode = self::SORT_EXTENDED;
		$this->sort = $attribute ." ".$mode;
	}
}