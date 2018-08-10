<?php

namespace Core\Database;

// TODO: Where groups
// TODO: Joins

use Core\Application;
use PDO;

/**
 * Class QueryBuilder
 * @package Core\Database
 */
class QueryBuilder
{
    private $select = [];
    private $from = null;
    private $where = [];
    private $groupBy= [];
    private $orderBy = [];
    private $connection = null;
    private $result = null;
    private $sqlQuery = '';
    private $sqlParameters = [];

    const QB_COLUMN_SYMBOL = '`';
    const QB_VALUE_SYMBOL = '\'';
    const QB_SELECT = 'SELECT';
    const QB_FROM = 'FROM';
    const QB_WHERE = 'WHERE';
    const QB_GROUPBY = 'GROUP BY';
    const QB_ORDERBY = 'ORDER BY';
    const QB_AND = 'AND';
    const QB_OR = 'OR';
    const QB_BEGIN_WHERE_GROUP = '(';
    const QB_END_WHERE_GROUP = ')';


    /**
     * FACTORY
     *
     * @param string|array $from
     * @return QueryBuilder
     */
    public static function factory($from): QueryBuilder
    {
        $qb = new QueryBuilder();
        $qb->from = $from;

        return $qb;
    }

    /**
     * SELECT
     *
     * @param ....
     * @return QueryBuilder
     */
    public function select(): QueryBuilder
    {
        $this->clearQueryData();

        $this->select = func_get_args();
        return $this;
    }

    /**
     * AND WHERE
     *
     * @param string $field
     * @param string $comparison
     * @param string|array $value
     * @return QueryBuilder
     */
    public function where(string $field, string $comparison, $value): QueryBuilder
    {
        $this->clearQueryData();

        $type = '';
        $operator = static::QB_AND;
        $this->where[] = compact('type', 'operator', 'field', 'comparison', 'value');

        return $this;
    }

    /**
     * OR WHERE
     *
     * @param string $field
     * @param string $comparison
     * @param string|array $value
     * @return QueryBuilder
     */
    public function orWhere(string $field, string $comparison, $value): QueryBuilder
    {
        $this->clearQueryData();

        $type = '';
        $operator = static::QB_OR;
        $this->where[] = compact('type', 'operator', 'field', 'comparison', 'value');

        return $this;
    }

    /**
     * AND WHERE GROUP BEGIN
     *
     * @return QueryBuilder
     */
    public function whereGroupBegin(): QueryBuilder
    {
        $this->clearQueryData();

        $this->where[] = [
            'type' => static::QB_BEGIN_WHERE_GROUP,
            'operator' => static::QB_AND,
        ];

        return $this;
    }

    /**
     * OR WHERE GROUP BEGIN
     *
     * @return QueryBuilder
     */
    public function orWhereGroupBegin(): QueryBuilder
    {
        $this->clearQueryData();

        $this->where[] = [
            'type' => static::QB_BEGIN_WHERE_GROUP,
            'operator' => static::QB_OR,
        ];

        return $this;
    }

    /**
     * WHERE GROUP END
     *
     * @return QueryBuilder
     */
    public function whereGroupEnd(): QueryBuilder
    {
        $this->clearQueryData();

        $this->where[] = [
            'type' => static::QB_END_WHERE_GROUP,
        ];

        return $this;
    }

    /**
     * GROUP BY
     *
     * @param string $field
     * @return QueryBuilder
     */
    public function groupBy(string $field): QueryBuilder
    {
        $this->clearQueryData();

        $this->groupBy[] = $field;

        return $this;
    }

    /**
     * ORDER BY
     *
     * @param string $field
     * @return QueryBuilder
     */
    public function orderBy(string $field, string $order = ''): QueryBuilder
    {
        $this->clearQueryData();

        $this->orderBy[] = trim($field . ' ' . $order);

        return $this;
    }

    /**
     * EXECUTE QUERY
     *
     * @return QueryBuilder
     */
    public function execute(): QueryBuilder
    {
        // Clear query data
        $this->clearQueryData();

        // Build query
        $this->buildSelectSection();
        $this->buildFromSection();
        $this->buildWhereSection();
        $this->buildGroupBySection();
        $this->buildOrderBySection();

        //TODO: REMOVE
        //var_dump($this);
        echo '<pre>' . $this->sqlQuery . '</pre>';
        var_dump($this->sqlParameters);
        //TODO: REMOVE

        // Get database connection
        if (!$this->connection) {
            $application = Application::getInstance();
            $this->connection = $application->getDatabaseConnection();
        }

        // Execute query
        $this->result = $this->connection->prepare($this->sqlQuery);
        $this->result->execute($this->sqlParameters);

        return $this;
    }

    /**
     * Return query result as array of objects
     *
     * @param string $groupField
     * @return array
     */
    public function getAll(string $groupField = null, string $column = null): array
    {
        if (!$this->result) {
            return [];
        }

        $result = $this->result->fetchAll(PDO::FETCH_OBJ);
        if ($groupField || $column) {
            $result = array_column($result, $column, $groupField);
        }

        return $result;
    }

    /**
     * BUILD SELECT SECTION
     */
    private function buildSelectSection()
    {
        $select = [];

        foreach($this->select as $key => $param) {
            if (is_array($param)) {
                $name = array_pop($param);
                $field = $this->shield(array_pop($param), true);
                $functionName = array_pop($param);

                if ($functionName) {
                    $select[] = $functionName . '(' . $field . ') AS ' . $name;
                } else {
                    $select[] = $field . ' AS ' . $name;
                }
            } elseif (is_string($param)) {
                $select[] = $this->shield($param, true);
            }
        }

        if (count($select)) {
            $this->sqlQuery = static::QB_SELECT . ' ' . implode(', ', $select);
            return;
        }

        $this->sqlQuery = static::QB_SELECT . ' *';
    }

    /**
     * BUILD FROM SECTION
     */
    private function buildFromSection()
    {
        if (is_array($this->from)) {
            $field = $this->shield(array_shift($this->from), true);
            $name = array_shift($this->from);

            $this->sqlQuery .= "\n" . static::QB_FROM . ' ' . $field . ' AS ' . $name;

        } elseif (is_string($this->from)) {

            $this->sqlQuery .= "\n" . static::QB_FROM . ' '
                . static::QB_COLUMN_SYMBOL . $this->shield($this->from, true) . static::QB_COLUMN_SYMBOL;
        }
    }

    /**
     * BUILD WHERE SECTION
     */
    private function buildWhereSection()
    {
        $where = '';
        $firstOperand = true;
        $paramNo = count($this->sqlParameters);

        foreach($this->where as $param) {
            if ($param['type'] === static::QB_END_WHERE_GROUP) {
                $where .= static::QB_END_WHERE_GROUP;
                $firstOperand = false;
                continue;
            }

            $operator = $param['operator'];
            if (!$firstOperand) {
                $where .= ' ' . $operator . ' ';
            }
            $firstOperand = false;

            if ($param['type'] === static::QB_BEGIN_WHERE_GROUP) {
                $where .= static::QB_BEGIN_WHERE_GROUP;
                $firstOperand = true;
                continue;
            }

            $field = $this->shield($param['field'], true);
            $comparsion = $param['comparison'];
            $value = $param['value'];

            $sqlParamName = ':param' . (++$paramNo);
            $where .= $field . ' ' . $comparsion  . ' ' . $sqlParamName;
            $this->sqlParameters[$sqlParamName] = $value;
        }

        if (!$where) {
            return;
        }

        $this->sqlQuery .= "\n" . static::QB_WHERE . ' ' . $where;
    }

    /**
     * BUILD GROUP BY SECTION
     */
    private function buildGroupBySection()
    {
        if (count($this->groupBy)) {
            $this->sqlQuery .= "\n" . static::QB_GROUPBY . ' ' . implode(', ', $this->groupBy);
        }
    }

    /**
     * BUILD ORDER BY SECTION
     */
    private function buildOrderBySection()
    {
        if (count($this->orderBy)) {
            $this->sqlQuery .= "\n" . static::QB_ORDERBY . ' ' . implode(', ', $this->orderBy);
        }
    }

    /**
     * SCREENING OF PARAMETERS
     *
     * @param string $param
     * @param bool $isField
     * @return string
     */
    private function shield(string $param, bool $isField = false): string
    {
        if (!$isField) {
            return  static::QB_VALUE_SYMBOL . addslashes($param) . static::QB_VALUE_SYMBOL;
        }

        $param = addslashes($param);
        $param = str_replace('.', static::QB_COLUMN_SYMBOL . '.' . static::QB_COLUMN_SYMBOL, $param);
        return static::QB_COLUMN_SYMBOL . $param . static::QB_COLUMN_SYMBOL;
    }

    /**
     * Clear query data
     */
    private function clearQueryData()
    {
        $this->result = null;
        $this->sqlParameters = [];
    }

}