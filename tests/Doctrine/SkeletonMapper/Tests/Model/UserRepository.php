<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Tests\ObjectRepository;

class UserRepository extends ObjectRepository
{
    public function findUserWithProfileData($id)
    {
        $sql = <<<EOF
SELECT
    u.*,
    p._id AS profileId,
    p.name as profileName
FROM
    users u,
    profiles p
WHERE
    u.profileId = p._id
    AND u._id = ?
EOF;

        $objectData = $this->objectDataRepository
            ->getConnection()
            ->executeQuery($sql, array($id))
            ->fetch();

        if ($objectData) {
            return $this->getOrCreateObject($objectData);
        }
    }
}
