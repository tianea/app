<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 10.06.18
 * Time: 21:28
 */

namespace Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Utils\Paginator;

/**
 * class UserRepository.
 **/
class UserRepository
{
    /**
     * Number of items per page.
     *
     * const int NUM_ITEMS
     */
    const NUM_ITEMS = 15;

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
     * Loads user by login.
     *
     * @param string $login User login
     * @throws UsernameNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function loadUserByLogin($login)
    {
        try {
            $user = $this->getUserByLogin($login);

            if (!$user || !count($user)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            $roles = $this->getUserRoles($user['user_id']);

            if (!$roles || !count($roles)) {
                throw new UsernameNotFoundException(
                    sprintf('Username "%s" does not exist.', $login)
                );
            }

            return [
                'login' => $user['login'],
                'password' => $user['password'],
                'roles' => $roles,
            ];
        } catch (DBALException $exception) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        } catch (UsernameNotFoundException $exception) {
            throw $exception;
        }
    }

    /**
     * Gets user data by login.
     *
     * @param string $login User login
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserByLogin($login)
    {
        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('u.user_id', 'u.login', 'u.password')
                ->from('user_role', 'u')
                ->where('u.login = :login')
                ->setParameter(':login', $login, \PDO::PARAM_STR);

            return $queryBuilder->execute()->fetch();
        } catch (DBALException $exception) {
            return [];
        }
    }


    /**
     * Gets user roles by User ID.
     *
     * @param integer $userId User ID
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array Result
     */
    public function getUserRoles($userId)
    {
        $roles = [];

        try {
            $queryBuilder = $this->db->createQueryBuilder();
            $queryBuilder->select('r.name')
                ->from('user_role', 'u')
                ->innerJoin('u', 'role', 'r', 'u.role_id = r.id')
                ->where('u.user_id = :id')
                ->setParameter(':id', $userId, \PDO::PARAM_INT);
            $result = $queryBuilder->execute()->fetchAll();

            if ($result) {
                $roles = array_column($result, 'name');
            }

            return $roles;
        } catch (DBALException $exception) {
            return $roles;
        }
    }


    /**
     * Fetch all records.
     *
     * @return array Result
     */
    public function findAll()
    {
        $queryBuilder = $this->queryAll();

        return $queryBuilder->execute()->fetchAll();
    }

    /**
     * Get records paginated.
     *
     * @param int $page Current page number
     *
     * @return array Result
     */
    public function findAllPaginated($page = 1)
    {
        $countQueryBuilder = $this->queryAll()
            ->select('COUNT(DISTINCT u.id) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->queryAll(), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(static::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
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
        $queryBuilder->where('u.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * @param string $userLogin
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    public function findUserIdByLogin($userLogin)
    {
        $queryBuilder = $this->queryAll();

        $queryBuilder->select('us.id')
            ->from('user', 'us')
            ->where('us.login = :login')
                ->setParameter(':login', $userLogin, \PDO::PARAM_STR);
        $result = $queryBuilder->execute()->fetch();
        $userId = $result['id'];

        return $userId;
    }

    /**
     * @param Application $app
     * @param $user
     */
    public function save(Application $app, $user)
    {

        $this->db->beginTransaction();

        $roleRepository = new RoleRepository($this->db);

        try {
            $userRole['password'] = $app['security.encoder.bcrypt']->encodePassword($user['password'], '');
            $userRole['login'] = $user['login'];
            $userRole['role_id'] = $roleRepository->findIdByName('ROLE_USER');

            unset($user['password']);

            if (isset($user['id']) && ctype_digit((string)$user['id'])) {
                // update record

                $userRole['user_id'] = $user['id'];

                $userId = $user['id'];

                unset($user['id']);

                $this->db->update('user', $user, ['id' => $userId]);
                $this->db->update('user_role', $userRole, ['user_id' => $userId]);
            } else {
                // add new record

                $this->db->insert('user', $user);
                $userId = $this->db->lastInsertId();

                $userRole['user_id'] = $userId;
            }
        } catch (UsernameNotFoundException $exception) {
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

        return $queryBuilder->select('u.id', 'u.login', 'u.name', 'u.age', 'u.gender', 'u.email', 'u.description', 'ur.user_id')
            ->from('user', 'u')
            ->innerJoin('u', 'user_role', 'ur', 'u.id = ur.user_id');
    }

}