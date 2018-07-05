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
use Doctrine\DBAL\DBALException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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
     * Find one record.
     *
     * @param string $id Element id
     *
     * @return array|mixed Result
     */
    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('r.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
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
        $result = $queryBuilder->execute()->fetch();

        return $result['id'];
    }

    /**
     * Save function.
     *
     * @param array $user User
     */
    public function save($user)
    {
        $this->db->beginTransaction();

        try {
            $userRole['role_id'] = $user['role_id'];

            if (isset($user['id']) && ctype_digit((string) $user['id'])) {
                // update record

                $userRole['user_id'] = $user['id'];
                $userId = $user['id'];

                unset($user['id']);
                dump($userRole);

                $this->db->update('user_role', $userRole, ['user_id' => $userId]);
            } else {
                // add new record

                $this->db->insert('user', $user);
                $userId = $this->db->lastInsertId();
                $userRole['user_id'] = $userId;

                $this->db->insert('user_role', $userRole);
            }
            $this->db->commit();
        }
        catch (UsernameNotFoundException $exception) {
            throw $exception;
        }
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('r.id', 'r.name', 'ur.role_id')
            ->from('role', 'r')
            ->innerJoin('r', 'user_role', 'ur', 'r.id = ur.role_id');
    }
}
