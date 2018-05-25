<?php
/**
 * Bookmarks.
 *
 * @link http://epi.chojna.info.pl
 * @author EPI UJ <epi@uj.edu.pl>
 * @copyright (c) 2017
 */
namespace Model;

/**
 * Class Bookmarks.
 */
class BookmarkModel
{
    /**
     * Bookmarks.
     *
     * @var array $bookmarks
     */
    protected $bookmarks = [
        1 => [
            'id' => 1,
            'title' => 'PHP manual',
            'url'   => 'http://php.net',
            'tags'  => [
                'PHP',
                'manual',
            ],
        ],
        2 => [
            'id' => 2,
            'title' => 'Silex',
            'url'   => 'http://silex.sensiolabs.org',
            'tags'  => [
                'PHP',
                'framework',
                'Silex',
            ],
        ],
        3 => [
            'id' => 3,
            'title' => 'Learn Git Branching',
            'url'   => 'http://learngitbranching.js.org',
            'tags'  => [
                'tools',
                'Git',
                'VCS',
                'tutorials',
            ],
        ],
        4 => [
            'id' => 4,
            'title' => 'PhpStorm',
            'url'  => 'https://www.jetbrains.com/phpstorm',
            'tags' => [
                'tools',
                'IDE',
                'PHP',
            ],
        ],
        5 => [
            'id' => 5,
            'title' => 'Twig',
            'url'  => 'http://twig.sensiolabs.org',
            'tags' => [
                'tools',
                'templates',
                'Twig',
                'Silex',
                'PHP',
            ],
        ],
    ];

    /**
     * Find all bookmarks.
     *
     * @return array Result
     */
    public function findAll()
    {
        return $this->bookmarks;
    }

    /**
     * Find bookmark by id.
     *
     * @param integer $id Bookmark id
     *
     * @return array Result
     */
    public function findOneById($id)
    {
        $bookmark = $this->bookmarks[$id];

        if (isset($bookmark) && count($bookmark)) {
            return $bookmark;
        }

        return [];
    }
}
