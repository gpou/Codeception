<?php
namespace Codeception\Lib\Driver;

class MySql extends Db
{
    public function cleanup()
    {
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
        $res = $this->dbh->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE '%TABLE';")->fetchAll();
        foreach ($res as $row) {
            $this->dbh->exec('drop table `' . $row[0] . '`');
        }
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function sqlQuery($query)
    {
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=0;');
        parent::sqlQuery($query);
        $this->dbh->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function select($column, $table, array &$criteria)
    {
        $where = $criteria ? "where %s" : '';
        $query = "select %s from %s $where";
        $params = [];
        foreach ($criteria as $k => $v) {
            $k = $this->getQuotedName($k);
            if ($v === null) {
                $params[] = "$k IS ?";
            } else {
                $params[] = "$k = ?";
            }
        }
        $sparams = implode(' AND ', $params);

        return sprintf(
            $query,
            $this->getQuotedName($column),
            $this->getQuotedName($table),
            $params
        );
    }


    public function getQuotedName($name)
    {
        $reserved = array('count\(', 'count\\s+\(');
        if (preg_match('/('.implode('|', $reserved).').*/USsi', trim($name))) {
            return $name;
        } else {
            return '`' . $name . '`';
        }
    }
}
