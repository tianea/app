<?php
/**
 * Survey repository.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */
namespace Repository;

use Doctrine\DBAL\Connection;
use Utils\Paginator;

/**
 * Class SurveyRepository.
 */
class SurveyRepository
{
    /**
     * Number of items per page.
     *
     * const int NUM_ITEMS
     */
    const NUM_ITEMS = 5;

    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * SurveyRepository constructor.
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
            ->select('COUNT(DISTINCT t.id) AS total_results')
            ->setMaxResults(1);

        $paginator = new Paginator($this->queryAll(), $countQueryBuilder);
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage(static::NUM_ITEMS);

        return $paginator->getCurrentPageResults();
    }

    /**
     * Find records by survey id
     *
     * @param string $id Element id
     *
     * @return array
     */
    public function findQuestionsBySurvey($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('t.survey_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
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
        $queryBuilder->where('t.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * Save function.
     *
     * @param array $survey Survey
     *
     * @param int   $userId UserId
     *
     * @return boolean Result
     */
    public function save($survey, $userId)
    {
        if (isset($survey['id']) && ctype_digit((string) $survey['id'])) {
            // update record
            $id = $survey['id'];
            unset($survey['id']);
            unset($survey['login']);

            $this->db->update('survey', $survey, ['id' => $id]);
        } else {
            // add new record
            $survey['user_id'] = $userId;

            $this->db->insert('survey', $survey);
        }
    }

    /**
     * Survey deleting.
     *
     * @param array $survey Survey
     *
     * @return int
     */
    public function delete($survey)
    {
        return $this->db->delete('survey', ['id' => $survey['id']]);
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder->select('t.id', 't.name', 't.description', 't.user_id', 'u.login')
            ->from('survey', 't')
            ->innerJoin('t', 'user', 'u', 't.user_id=u.id');
    }
}