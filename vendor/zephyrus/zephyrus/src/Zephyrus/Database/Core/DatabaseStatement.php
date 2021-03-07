<?php namespace Zephyrus\Database\Core;

use PDOStatement;
use stdClass;
use Zephyrus\Application\Configuration;

class DatabaseStatement
{
    const TYPE_INTEGER = ['LONGLONG', 'LONG', 'INTEGER', 'INT4'];
    const TYPE_BOOLEAN = ['TINY', 'BOOL'];
    const TYPE_FLOAT = ['NEWDECIMAL', 'FLOAT', 'DOUBLE', 'DECIMAL', 'NUMERIC'];

    /**
     * @var PDOStatement
     */
    private $statement = null;

    /**
     * @var string
     */
    private $allowedHtmlTags = "";

    /**
     * @var array
     */
    private $fetchColumnTypes = [];

    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
        if (Configuration::getDatabaseConfiguration('convert_type', false)) {
            $this->initializeTypeConversion();
        }
    }

    /**
     * Return the next row from the current result set obtained from the last
     * executed query. Automatically strip slashes that would have been stored
     * in database as escaping.
     *
     * @return stdClass|null
     */
    public function next(): ?stdClass
    {
        $row = $this->statement->fetch(\PDO::FETCH_OBJ);
        if ($row === false) {
            return null;
        }
        if (!empty($this->fetchColumnTypes)) {
            $this->convertRowTypes($row);
        }
        $this->sanitizeOutput($row);
        return $row;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->statement->rowCount();
    }

    /**
     * @return string
     */
    public function getAllowedHtmlTags()
    {
        return $this->allowedHtmlTags;
    }

    /**
     * @param string $allowedHtmlTags
     */
    public function setAllowedHtmlTags($allowedHtmlTags)
    {
        $this->allowedHtmlTags = $allowedHtmlTags;
    }

    /**
     * @return bool
     */
    public function isDirectHtmlTagsAllowed()
    {
        return !empty($this->allowedHtmlTags);
    }

    /**
     * @param string $tag
     */
    public function addAllowedHtmlTag($tag)
    {
        $this->allowedHtmlTags .= $tag;
    }

    /**
     * Clear allowed tags.
     */
    public function disallowDirectHtmlTags()
    {
        $this->allowedHtmlTags = "";
    }

    private function convertRowTypes(&$row)
    {
        foreach (get_object_vars($row) as $column => $value) {
            if (isset($this->fetchColumnTypes[$column]) && !is_null($value) && is_callable($this->fetchColumnTypes[$column])) {
                $row->{$column} = $this->fetchColumnTypes[$column]($row->{$column});
            }
        }
    }

    private function sanitizeOutput(&$row)
    {
        foreach (get_object_vars($row) as $column => $value) {
            if (!is_null($value) && is_string($value)) {
                $row->{$column} = $this->sanitize($value);
            }
        }
    }

    private function sanitize($value)
    {
        return strip_tags($value, $this->allowedHtmlTags);
    }

    private function initializeTypeConversion()
    {
        for ($i = 0; $i < $this->statement->columnCount(); ++$i) {
            try {
                $meta = $this->statement->getColumnMeta($i);
                $this->fetchColumnTypes[$meta['name']] = $this->getMetaCallback(strtoupper($meta['native_type']));
            } catch (\Exception $exception) {
                // With DBMS SQLite, if a query has no result, it cannot use the getColumnMeta method as this will
                // throw an out of range exception even if the columnCount returns the correct result. Must be a bug
                // within PDO statement with SQLite. To avoid any problem, an empty catch will make sure to ignore
                // such error as anyway no conversion will be necessary with empty results.
            }
        }
    }

    private function getMetaCallback(string $pdoType): ?string
    {
        if (in_array($pdoType, self::TYPE_INTEGER)) {
            return "intval";
        }
        // Boolean type doesnt exists in SQLITE
        // @codeCoverageIgnoreStart
        if (in_array($pdoType, self::TYPE_BOOLEAN)) {
            return "boolval";
        }
        // @codeCoverageIgnoreEnd
        if (in_array($pdoType, self::TYPE_FLOAT)) {
            return "floatval";
        }
        return null;
    }
}
