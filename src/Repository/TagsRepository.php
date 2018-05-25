<?php
/**
 * Tags repository.
 */
namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class TagsRepository.
 */
class TagsRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * TagsRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Fetch all records.
     *
     * @return array Result
     */
    public function findAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('id', 'name')
            ->from('si_tags');

        return $queryBuilder->execute()->fetchAll();
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
        $queryBuilder = $this->db->createQueryBuilder();
        $queryBuilder->select('id', 'name')
            ->from('si_tags')
            ->where('id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Number of items per page.
     *
     * const int NUM_ITEMS
     */
    const NUM_ITEMS = 1;
// ...
    /**
     * Get records paginated.
     *
     * @param int $page Current page number
     *
     * @return array Result
     */

    public function findAllPaginated($page = 1)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->setFirstResult(($page - 1) * static::NUM_ITEMS)
            ->setMaxResults(static::NUM_ITEMS);

        $pagesNumber = $this->countAllPages();

        $paginator = [
            'page' => ($page < 1 || $page > $pagesNumber) ? 1 : $page,
            'max_results' => static::NUM_ITEMS,
            'pages_number' => $pagesNumber,
            'data' => $queryBuilder->execute()->fetchAll(),
        ];

        return $paginator;
    }
// ...
    /**
     * Count all pages.
     *
     * @return int Result
     */
    protected function countAllPages()
    {
        $pagesNumber = 1;

        $queryBuilder = $this->queryAll();
        $queryBuilder->select('COUNT(DISTINCT t.id) AS total_results')
            ->setMaxResults(1);

        $result = $queryBuilder->execute()->fetch();

        if ($result) {
            $pagesNumber =  ceil($result['total_results'] / static::NUM_ITEMS);
        } else {
            $pagesNumber = 1;
        }

        return $pagesNumber;
    }
}