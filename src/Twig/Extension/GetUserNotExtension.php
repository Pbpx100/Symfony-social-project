<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\GetUserNotRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

class GetUserNotExtension extends AbstractExtension
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
            new TwigFilter('filter_name', [GetUserNotRuntime::class, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getTypeId', [$this, 'getTypeIdFunction']),
        ];
    }

    public function getTypeIdFunction($user_id)
    {
        $entityManager = $this->doctrine->getManager();
        $user_repository = $entityManager->getRepository(User::class);
        $user = $user_repository->find($user_id);


        return $user;
    }
}
