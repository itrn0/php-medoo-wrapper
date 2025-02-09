<?php

namespace Itrn0\MedooWrapper;

use Generator;
use Itrn0\SqlInterpolator\SqlInterpolator;
use Medoo\Medoo;
use PDO;
use PDOStatement;

class MedooWrapper extends Medoo
{
    /**
     * @inheritDoc
     * @param string|callable(SqlInterpolator $interp):PDOStatement $statement
     * @param array<string, mixed> $map
     * @return PDOStatement
     * @throws MedooWrapperException
     */
    public function query($statement, array $map = []): PDOStatement
    {
        return $this->_query($statement, $map, function ($statement, $map) {
            return parent::query($statement, $map);
        });
    }

    /**
     * @inheritDoc
     * @param string|callable(SqlInterpolator $interp):PDOStatement $statement
     * @param array<string, mixed> $map
     * @param callable|null $callback
     * @return PDOStatement
     * @throws MedooWrapperException
     */
    public function exec($statement, array $map = [], ?callable $callback = null): PDOStatement
    {
        return $this->_query($statement, $map, function ($statement, $map) use ($callback) {
            return parent::exec($statement, $map, $callback);
        });
    }

    /**
     * Fetch a single row from the query result set
     * @param $statement
     * @param array $map
     * @param int $fetchFlags
     * @return mixed
     * @throws MedooWrapperException
     */
    public function fetch($statement, array $map = [], int $fetchFlags = PDO::FETCH_ASSOC)
    {
        return $this->query($statement, $map)->fetch($fetchFlags);
    }

    /**
     * Returns an array containing all of the result set rows
     * @param $statement
     * @param array $map
     * @param int $fetchFlags
     * @return array
     * @throws MedooWrapperException
     */
    public function fetchAll($statement, array $map = [], int $fetchFlags = PDO::FETCH_ASSOC): array
    {
        $result = $this->query($statement, $map);
        $items = $result->fetchAll($fetchFlags);
        if ($items === false) {
            throw new MedooWrapperException("Medoo returned FALSE");
        }
        return $items;
    }

    /**
     * Returns a generator that sequentially returns rows from the query result.
     * @param $statement
     * @param array $map
     * @param int $fetchFlags
     * @return Generator<int, array>
     * @throws MedooWrapperException
     */
    public function fetchGenerator($statement, array $map = [], int $fetchFlags = PDO::FETCH_ASSOC): Generator
    {
        $result = $this->query($statement, $map);
        while (true) {
            $item = $result->fetch($fetchFlags);
            if ($item === false) {
                $errorInfo = $result->errorInfo();
                if (is_array($errorInfo) && $errorInfo[0] !== "00000") {
                    throw new MedooWrapperException("Medoo fetch returns error: " . $errorInfo[2]);
                }
                return;
            }
            yield $item;
        }
    }

    /**
     * @param $statement
     * @param array $map
     * @param callable $callback
     * @return PDOStatement
     * @throws MedooWrapperException
     */
    private function _query($statement, array $map, callable $callback): PDOStatement
    {
        if (is_callable($statement)) {
            $interpolator = new SqlInterpolator();
            $statement = $statement($interpolator);
            $map = $interpolator->getParams();
        }
        $result = $callback($statement, $map);
        if ($result === null) {
            throw new MedooWrapperException('Medoo returned NULL');
        }
        return $result;
    }

}