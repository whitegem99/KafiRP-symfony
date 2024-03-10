<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function createUserFromDiscordOAuth(
        string $discordId,
        string $discordUsername,
        string $email,
        string $discordAvatar,
        string $randomPassword,
        string $accessToken,
        bool $inWhitelist
    ): User
    {
        $user = new User();
        $user
            ->setRoles(['ROLE_USER'])
            ->setDiscordId($discordId)
            ->setUsername($discordId)
            ->setDiscordUsername($discordUsername)
            ->setDiscordAvatar($discordAvatar)
            ->setLastDiscordAccessToken($accessToken)
            ->setInWhitelist($inWhitelist)
            ->setPassword(
                $this->passwordEncoder->encodePassword($user, $randomPassword)
            )
            ->setEmail($email);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function getUserFromDiscordOAuth(
        string $discordId,
        string $discordUsername,
        string $email,
        string $discordAvatar,
        string $accessToken,
        bool $inWhitelist
    ): ?User
    {
        $user = $this->findOneBy([
            'email' => $email
        ]);

        if (!$user) {
            return null;
        }

        if ($user->getDiscordId() !== $discordId) {
            $user->setDiscordId($discordId);
            $user->setDiscordUsername($discordUsername);
            $user->setDiscordAvatar($discordAvatar);
        }

        $user->setLastDiscordAccessToken($accessToken);
        $user->setInWhitelist($inWhitelist);

        $this->entityManager->flush();

        return $user;
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
