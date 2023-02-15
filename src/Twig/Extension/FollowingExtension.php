<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\FollowingRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Following;

class FollowingExtension extends AbstractExtension
{
    protected $doctrine;
    function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('followingStatus', [$this, 'followingStatusFunction']),
        ];
    }
    public function followingStatusFunction($user, $followed)
    {
        $entityManager = $this->doctrine->getManager();
        $following_repository = $entityManager->getRepository(Following::class);
        $user_followed = $following_repository->findOneBy([
            'users' => $user,
            'followed' => $followed,
        ]);

        if ($user_followed != null && !empty($user_followed) && is_object($user_followed)) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }
}
