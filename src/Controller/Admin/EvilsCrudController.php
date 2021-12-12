<?php

namespace App\Controller\Admin;

use App\Entity\Evils;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class EvilsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Evils::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        // Add Fields to edit
        return [
            TextField::new('name'),
            AssociationField::new('task', 'Mission')->setFormTypeOption('attr', ['required' => 'required'])->setRequired(true),
        ];
    }
    
}
