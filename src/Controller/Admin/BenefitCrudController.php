<?php

namespace App\Controller\Admin;

use App\Entity\Benefit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class BenefitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Benefit::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
