<?php

namespace Core\Database;

//TODO: MAGIC factory
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
    private $connection = null;
    private $result = null;
    private $sqlQuery = '';
    private $sqlParameters = [];

    const QB_COLUMN_SYMBOL = '`';
    const QB_VALUE_SYMBOL = '\'';
    const QB_SELECT = 'SELECT';
    const QB_FROM = 'FROM';
    const QB_WHERE = 'WHERE';
    const QB_AND = 'AND';
    const QB_OR = 'OR';

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

        $operator = static::QB_AND;
        $this->where[] = compact('operator', 'field', 'comparison', 'value');

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

        $operator = static::QB_OR;
        $this->where[] = compact('operator', 'field', 'comparison', 'value');

        return $this;
    }

    /**
     * EXECUTE QUERY
     *
     * @return QueryBuilder
     */
    public function execute(): QueryBuilder
    {
        $this->buildSQLQuery();
        //TODO: REMOVE
        echo '<pre>' . $this->sqlQuery . '</pre>';
        var_dump($this->sqlParameters);

        if (!$this->connection) {
            $application = Application::getInstance();
            $this->connection = $application->getDatabaseConnection();
        }

        $this->result = $this->connection->prepare($this->sqlQuery);
        $this->result->execute($this->sqlParameters);

        //TODO: REMOVE
        //var_dump($this);

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
     * BUILD SQL QUERY
     */
    private function buildSQLQuery()
    {
        $this->clearQueryData();

        $this->buildSelectSection();
        $this->buildFromSection();
        $this->buildWhereSection();
    }

    /**
     * BUILD SELECT SECTION
     */
    private function buildSelectSection()
    {
        $select = [];

        foreach($this->select as $key => $param) {
            if (is_array($param)) {
                $field = $this->shield(array_shift($param), true);
                $name = $this->shield(array_shift($param));
                $select[] = $field . ' AS ' . $name;
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
                . static::QB_COLUMN_SYMBOL . $this->shield($this->from) . static::QB_COLUMN_SYMBOL;
        }
    }

    /**
     * BUILD WHERE SECTION
     */
    private function buildWhereSection()
    {
        $where = '';
        $paramNo = count($this->sqlParameters);

        foreach($this->where as $key => $param) {
            $operator = $param['operator'];
            $field = $this->shield($param['field'], true);
            $comparsion = $param['comparison'];
            $value = $param['value'];

            if ($key > 0) {
                $where .= ' ' . $operator . ' ';
            }

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