<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

class UserCrudController extends AbstractCrudController
{
    // public static function getEntityFqcn(): string
    // {
    //     return User::class;
    // }

    
    // public function configureFields(string $pageName): iterable
    // {
    //     return [
    //         TextField::new('email'),
    //         TextField::new('name'),
    //         TextField::new('alignment'),
    //         TextField::new('password')->setFormType(PasswordType::class)
    //     ];
    // }

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * UserCrudController constructor.
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->passwordEncoder = $passwordEncoder;
    }


    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        // Add field to reset password of user
        $password = TextField::new('clearpassword')
            ->setLabel("Nouveau mot de passe")
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('empty_data', '')
            ->setRequired(false)
            ->setHelp('Si vous e voulez pas modifier le mot de passe, laissez ce champ vide')
            ->hideOnIndex();

        // Add Fields to edit
        return [
            TextField::new('email'),
            TextField::new('name'),
            $password,
        ];
    }


    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // set new password with encoder interface
        if (method_exists($entityInstance, 'setPassword')) {

            $clearPassword = trim($this->get('request_stack')->getCurrentRequest()->request->all()['User']['clearpassword']);

            // save password only if is set a new clearpass
            if ( !empty($clearPassword) ) {
                $encodedPassword = $this->passwordEncoder->encodePassword($this->getUser(), $clearPassword);
                $entityInstance->setPassword($encodedPassword);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $qb = $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        // If the user is client or superHero (not admin role), display only his profil data
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $qb->andWhere('entity.id = :id');
            $qb->setParameter('id', $this->getUser()->getId());
        }

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                $that = $this;
                return $action->displayIf(static function ($entity) use ($that) {
                    // If user has admin role, display the DELETE button
                    return in_array('ROLE_ADMIN', $that->getUser()->getRoles());
            });
        });
    }
    
}
