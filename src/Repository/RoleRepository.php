<?php
/**
 * Role repository.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * class RoleRepository
 */
class RoleRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * UserRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Finds id by role name
     *
     * @param string $name name
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    public function findIdByName($name)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->select('r.id')
            ->where('r.name = :name')
            ->setParameter(':name', $name, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * Query all records.
     *
     * @return $this
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('r.id', 'r.name')
            ->from('role', 'r');
    }
}