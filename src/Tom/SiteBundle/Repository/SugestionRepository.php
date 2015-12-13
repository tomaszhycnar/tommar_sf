<?php

namespace Tom\SiteBundle\Repository;


class SugestionRepository extends \Doctrine\ORM\EntityRepository
{
    
    public function getPublishedSugestion($id){
        $qb = $this->getQueryBuilder(array(
            'status' => 'read'
        ));
        
        $qb->andWhere('s.id = :id')
                ->setParameter('id', $id);
        
        return $qb->getQuery()->getOneOrNullResult();
    }


    public function getQueryBuilder(array $params = array()){
        
        $qb = $this->createQueryBuilder('s');
        
        if(!empty($params['status'])){
            if('read' == $params['status']){
                $qb->where('s.updateDate <= :currDate AND s.updateDate IS NOT NULL')
                        ->setParameter('currDate', new \DateTime());
            }else if('removed' == $params['status']){
                $qb->where('s.updateDate > :currDate OR s.updateDate IS NULL')
                        ->setParameter('currDate', new \DateTime());
            }
        }
        
        if(!empty($params['orderBy'])){
            $orderDir = !empty($params['orderDir']) ? $params['orderDir'] : NULL;
            $qb->orderBy($params['orderBy'], $orderDir);
        }

        
        if(!empty($params['search'])){
            $searchParam = '%'.$params['search'].'%';
            $qb->andWhere('s.title LIKE :searchParam OR s.content LIKE :searchParam')
                    ->setParameter('searchParam', $searchParam);
        }
        
        if(!empty($params['sugestionLike'])){
            $sugestionLike = '%'.$params['sugestionLike'].'%';
            $qb->andWhere('s.title LIKE :sugestionLike')
                    ->setParameter('sugestionLike', $sugestionLike);
        }
                
        return $qb;
    }
    
    
    public function getStatistics(){
        $qb = $this->createQueryBuilder('s')
                        ->select('COUNT(s)');
        
        
        $all = (int)$qb->getQuery()->getSingleScalarResult();
        
        $published = (int)$qb->andWhere('s.updateDate <= :currDate AND s.updateDate IS NOT NULL')
                            ->setParameter('currDate', new \DateTime())
                            ->getQuery()
                            ->getSingleScalarResult();
        
        return array(
            'all' => $all,
            'read' => $published,
            'removed' => ($all - $published)
        );
    }
    
}
