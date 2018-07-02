<?php
/**
 * Answer repository.
 *
 * @copyright (c) 2018 Monika Kwiecień
 *
 * @link http://cis.wzks.uj.edu.pl/~15_kwiecien/web/surveys/
 */

namespace Repository;

use Doctrine\DBAL\Connection;

/**
 * Class AnswerRepository
 */
class AnswerRepository
{
    /**
     * Doctrine DBAL connection.
     *
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * AnswerRepository constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Save function.
     *
     * @param array $answer     Answer
     * @param int   $questionId questionId
     *
     * @return int
     */
    public function save($answer, $questionId)
    {
        if (isset($answer['id']) && ctype_digit((string) $answer['id'])) {
            // update record
            $id = $answer['id'];
            unset($answer['id']);
            unset($answer['name']);
            unset($answer['description']);
            unset($answer['user_id']);

            $this->db->update('open_question_answer', $answer, ['id' => $id]);
        } else {
            // add new record
            $answer['open_question_id'] = $questionId;

            $this->db->insert('open_question_answer', $answer);
        }
    }

    /**
     * Find all answers by question id.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function findAllByQuestionId($id)
    {
        $queryBuilder = $this->queryAll();
        $queryBuilder->where('a.open_question_id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $queryBuilder->execute()->fetchAll();

        return !$result ? [] : $result;
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
            ->select('a.open_question_id', 'a.answer')
            ->from('open_question_answer', 'a')
            ->innerJoin('a', 'open_question', 'o', 'a.open_question_id=o.id');
    }
}
