<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.06.18
 * Time: 22:34
 */

namespace Repository;



/**
 * class RoleRepository
 */
class RoleRepository
{
    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    public function findIdByName($name)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder
            ->select('ur.id')
            ->where('ur.name = :name')
            ->setParameter(':name', $name, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('ur.id', 'ur.name')
            ->from('user_role', 'ur');
    }
}