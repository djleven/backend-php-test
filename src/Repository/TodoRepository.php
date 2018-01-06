<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;

class TodoRepository extends EntityRepository
{

    public function getUserTodos($user_id)
    {

        $queryBuilder =  $this->_em->createQueryBuilder();
        $query = $queryBuilder
            ->select('t')
            ->from('Entity\Todo', 't')
            ->andWhere('t.user_id = :user_id')
            ->setParameter('user_id', $user_id);
        $result = $query->getQuery()->getResult();
        return $result;
    }

}
