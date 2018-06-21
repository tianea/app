<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.06.18
 * Time: 21:00
 */

namespace Repository;

use Doctrine\DBAL\Connection;
use Utils\Paginator;

/**
 * Class QuestionRepository
 **/
class QuestionRepository
{
    /**
     * Number of items per page.
     *
     * const int NUM_ITEMS
     */
    const NUM_ITEMS = 10;

    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * QuestionRepository constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @return mixed
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
            ->select('COUNT(DISTINCT o.id) AS total_results')
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
     * @return array
     */
    public function findOneById($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('o.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetch();

        return !$result ? [] : $result;
    }

    /**
     * @param array $openQuestion OpenQuestion
     *
     * @param int   $surveyId     SurveyId
     *
     * @return int
     */
    public function save($openQuestion, $surveyId)
    {
        if (isset($openQuestion['id']) && ctype_digit((string) $openQuestion['id'])) {
            // update record
            $id = $openQuestion['id'];
            unset($openQuestion['id']);

            $this->db->update('open_question', $openQuestion, ['id' => $id]);
        } else {
            // add new record
            $openQuestion['survey_id'] = $surveyId;

            $this->db->insert('open_question', $openQuestion);
        }
    }

    /**
     * Find all questions by survey id.
     *
     * @return mixed
     */
    public function findAllBySurveyId($id)
    {
        /*$queryBuilder = $this->queryAll();

        $queryBuilder->select('op.id', 'op.content', 'op.survey_id')
            ->from('open_question', 'op')
            ->where('op.survey_id = s.id');

        return $queryBuilder->execute();*/

        $queryBuilder = $this->queryAll();
        $queryBuilder->where('o.survey_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
    }

    public function findLinkedBySurveyId($surveyId){
        $queryBuilder = $this->db->createQueryBuilder()
            ->select('s.id')
            ->from('survey', 's')
            ->where('s.id = :survey_id')
            ->setParameter(':survey_id', $surveyId, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return isset($result) ? array_column($result, 'content') : [];
    }

    /**
     * Query all records.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder Result
     */
    protected function queryAll()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        return $queryBuilder
            ->select('o.id', 'o.content', 'o.survey_id', 's.id', 's.name', 's.description', 's.user_id')
            ->from('open_question', 'o')
            ->innerJoin('o', 'survey', 's', 'o.survey_id=s.id');
    }


}