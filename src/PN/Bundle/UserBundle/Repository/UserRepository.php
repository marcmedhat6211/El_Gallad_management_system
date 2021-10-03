<?php

namespace PN\Bundle\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PN\Bundle\UserBundle\Entity\User;
use PN\ServiceBundle\Utils\Date;
use PN\Utils\SQL;
use PN\Utils\Validate;

class UserRepository extends EntityRepository {

    public function findByApiKey($apiKey) {
        return $this->findOneBy(array('apiKey' => $apiKey));
    }

    /**
     * check verify email after 5 days
     * @param type $userId
     * @return bool
     */
    public function isVerifyEmail($userId) {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT id FROM `usr` WHERE id=:userId AND  confirmation_token IS NOT NULL and password_requested_at IS NULL AND DATEDIFF(NOW(), created) >=5";
        $statement = $connection->prepare($sql);
        $statement->bindValue("userId", $userId);
        $statement->execute();
        $result = $statement->fetchColumn();
        if (!$result) {
            return TRUE;
        }
        return FALSE;
    }

    public function removedFavoriteCategoriesByUser($userId) {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "DELETE FROM `user_category` WHERE user_id=:userId";
        $statement = $connection->prepare($sql);
        $statement->bindValue("userId", $userId);
        $statement->execute();
    }

    public function getCreditByUser($userId) {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "SELECT survey_points.collected_points- offer_points.redeemed_point AS credit FROM ( "
                . "SELECT IFNULL(SUM(collected_points),0) collected_points FROM `survey_has_user` WHERE user_id=:userId AND completed_datetime IS NOT NULL "
                . ") AS survey_points, "
                . "( "
                . "SELECT IFNULL(SUM(price),0) redeemed_point FROM `offer_has_user` WHERE user_id=:userId "
                . ") AS offer_points";
        $statement = $connection->prepare($sql);
        $statement->bindValue("userId", $userId);
        $statement->execute();
        return $statement->fetchColumn();
    }

    public function getUsersHasRefreshToken() {
        $connection = $this->getEntityManager()->getConnection();

        $sql = "SELECT u.id FROM user_refresh_token rt "
                . "LEFT JOIN usr u ON rt.user_id=u.id "
                . "WHERE u.enabled =:enabled AND u.push_notification=1 AND u.deleted IS NULL "
                . "GROUP BY u.id";
        $statement = $connection->prepare($sql);
        $statement->bindValue("enabled", true);
        $statement->execute();
        $filterResult = $statement->fetchAll();
        $result = array();

        foreach ($filterResult as $key => $r) {
            $result[] = $this->find($r['id']);
        }
        return $result;
    }

    /**
     * @param string $role
     *
     * @return array
     */
    public function findByRole($role, $deleted = FALSE) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
                ->from($this->_entityName, 'u')
                ->where('u.roles LIKE :roles')
                ->andWhere('u.deleted = :deleted')
                ->setParameter('roles', '%"' . $role . '"%')
                ->setParameter('deleted', $deleted);

        return $qb->getQuery()->getResult();
    }


    private function getStatement()
    {
        return $this->createQueryBuilder('u');
    }

    private function filterWhereClause(QueryBuilder $statement, \stdClass $search)
    {
        if (isset($search->string) AND Validate::not_null($search->string)) {
            $statement->andWhere('u.id LIKE :searchTerm '
                .'OR u.fullName LIKE :searchTerm '
                .'OR u.email LIKE :searchTerm '
                .'OR u.phone LIKE :searchTerm '
            );
            $statement->setParameter('searchTerm', '%'.trim($search->string).'%');
        }

        if (isset($search->role) AND Validate::not_null($search->role)) {
            if ($search->role == User::ROLE_DEFAULT) {
                $statement->andWhere("u.roles = :role");
                $statement->setParameter("role", "a:0:{}");
            } else {
                $roles = (!is_array($search->role)) ? [$search->role] : $search->role;
                $roleClause = null;
                $i = 0;
                foreach ($roles as $value) {
                    if ($i > 0) {
                        $roleClause .= " OR ";
                    }
                    $roleClause .= " u.roles LIKE :role".$i;
                    $statement->setParameter('role'.$i, '%'.trim($value).'%');
                    $i++;
                }
                $statement->andWhere($roleClause);
            }

        }
        if (isset($search->ids) AND is_array($search->ids) and count($search->ids) > 0) {
            $statement->andWhere('u.id in (:ids)');
            $statement->setParameter('ids', $search->ids);
        }

        if (isset($search->enabled) AND (is_bool($search->enabled) or in_array($search->enabled, [0, 1]))) {
            $statement->andWhere('u.enabled = :enabled');
            $statement->setParameter('enabled', $search->enabled);
        }
        if (isset($search->subscriptionNewsletter) AND (is_bool($search->subscriptionNewsletter) or in_array($search->subscriptionNewsletter, [0, 1]))) {
            $statement->andWhere('u.subscriptionNewsletter = :subscriptionNewsletter');
            $statement->setParameter('subscriptionNewsletter', $search->subscriptionNewsletter);
        }
        if (isset($search->regDateFrom) AND Validate::not_null($search->regDateFrom)) {
            $convertedDate = Date::convertDateFormat($search->regDateFrom, Date::DATE_FORMAT3, Date::DATE_FORMAT2);
            $statement->andWhere("DATEDIFF(u.created, :regDateFrom) >= 0");
            $statement->setParameter('regDateFrom', $convertedDate);
        }
        if (isset($search->regDateTo) AND Validate::not_null($search->regDateTo)) {
            $convertedDate = Date::convertDateFormat($search->regDateTo, Date::DATE_FORMAT3, Date::DATE_FORMAT2);
            $statement->andWhere("DATEDIFF(u.created, :regDateTo) <= 0");
            $statement->setParameter('regDateTo', $convertedDate);
        }

        if (isset($search->deleted) AND in_array($search->deleted, array(0, 1))) {
            if ($search->deleted == 1) {
                $statement->andWhere('u.deleted IS NOT NULL');
            } else {
                $statement->andWhere('u.deleted IS NULL');
            }
        }
    }

    private function filterOrder(QueryBuilder $statement, \stdClass $search)
    {
        $sortSQL = [
            'u.id',
            'u.fullName',
            'u.email',
            'u.phone',
            'u.lastLogin',
            'u.created',
            'u.enabled',
        ];

        if (isset($search->ordr) AND Validate::not_null($search->ordr)) {
            $dir = $search->ordr['dir'];
            $columnNumber = $search->ordr['column'];
            if (isset($columnNumber) AND array_key_exists($columnNumber, $sortSQL)) {
                $statement->orderBy($sortSQL[$columnNumber], $dir);
            }
        } else {
            $statement->orderBy($sortSQL[0], "DESC");
        }
    }

    private function filterPagination(QueryBuilder $statement, $startLimit = null, $endLimit = null)
    {
        if ($startLimit === null OR $endLimit == null) {
            return false;
        }
        $statement->setFirstResult($startLimit)
            ->setMaxResults($endLimit);
    }

    private function filterCount(QueryBuilder $statement)
    {
        $statement->select("COUNT(DISTINCT u.id)");
        $statement->setMaxResults(1);

        $count = $statement->getQuery()->getOneOrNullResult();
        if (is_array($count) and count($count) > 0) {
            return (int)reset($count);
        }

        return 0;
    }

    public function filter($search, $count = false, $startLimit = null, $endLimit = null)
    {
        $statement = $this->getStatement();
        $this->filterWhereClause($statement, $search);

        if ($count == true) {
            return $this->filterCount($statement);
        }
        $statement->groupBy('u.id');
        $this->filterPagination($statement, $startLimit, $endLimit);
        $this->filterOrder($statement, $search);

        return $statement->getQuery()->execute();
    }

}
