<?php

namespace App\Repository;

use App\Entity\Editor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Editor>
 */
class EditorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Editor::class);
    }

    public function findAllWithPagination(int $page, int $limit)
        {
            $queryBuilder = $this->createQueryBuilder('e')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            return $queryBuilder->getQuery()->getResult();
        }
}
