<?php

namespace App\Controller\Admin;

use App\Entity\Input;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class InputCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Input::class;
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
