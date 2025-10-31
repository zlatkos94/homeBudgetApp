<?php
namespace App\EventListener;

use App\Enum\CategoryEnum;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Entity\User;
use App\Entity\Category;

class UserPostPersistListener
{
    public function postPersist(User $user, LifecycleEventArgs $args): void
    {
        $em = $args->getObjectManager();

        foreach (CategoryEnum::cases() as $case) {
            $category = new Category();
            $category->setUser($user);
            $category->setName(ucfirst($case->value));
            $em->persist($category);
        }
        $em->flush();
    }
}
