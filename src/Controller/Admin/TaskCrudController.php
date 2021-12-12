<?php

namespace App\Controller\Admin;

use App\Entity\Task;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;

class TaskCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Task::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        $fields = [];
        if(!in_array('ROLE_SUPER_HERO', $this->getUser()->getRoles())) {
            $fields = [
                TextField::new('name')->setRequired(true),
                TextField::new('description')->setRequired(true),
                AssociationField::new('priority', 'Priorité de la mission')->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true),
                AssociationField::new('client', 'Client')->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true),
                AssociationField::new('superHero')->setFormTypeOptions([
                    'query_builder' => function (UserRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->andWhere('u.roles = :role')
                            ->setParameter('role', json_encode(array("ROLE_SUPER_HERO")));
                    },
                ])->setRequired(true),
                AssociationField::new('evils', 'Méchants')->setFormTypeOptionIfNotSet('by_reference', false)->setRequired(true),
                DateTimeField::new('deadline', 'Date butoire')->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true),
            ];
        }
        array_push(
            $fields,
            DateTimeField::new('realisationDate', 'Date de réalisation'),
            AssociationField::new('status', 'Statut de la mission')->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true)
        );
        return $fields;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if (['ROLE_USER'] == $this->getUser()->getRoles()) {
            $qb->andWhere('entity.client = :user');
            $qb->setParameter('user', $this->getUser());
        } else if(in_array('ROLE_SUPER_HERO', $this->getUser()->getRoles())) {
            $qb->leftJoin('entity.status', 'status');
            $qb->andWhere('status.name != :name');
            $qb->setParameter('name', 'A valider');
        }

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            $that = $this;
            return $action->displayIf(static function ($entity) use ($that) {
                if(in_array('ROLE_SUPER_HERO', $that->getUser()->getRoles())) {
                    return true;
                } else {
                    return $entity->getStatus()->getName() == 'A valider';
                }
        });
    })
    ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
        return $action->displayIf(static function ($entity) {
                 return in_array($entity->getStatus()->getName(), ['A valider','A faire']);
    });
});
    }
    
}
