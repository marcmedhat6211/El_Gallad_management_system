<?php

namespace PN\Bundle\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RefreshTokenRepository extends EntityRepository {

    public function checkTokenValidity() {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT id FROM `user_refresh_token` WHERE last_check_validity < CURDATE() - INTERVAL 2 DAY OR last_check_validity IS NULL "
                . "ORDER BY last_check_validity ASC "
                . "LIMIT 100";
        $statement = $connection->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();
        $return = [];
        foreach ($result as $r) {
            $return[] = $this->find($r['id']);
        }
        return $return;
    }

}
