<?php

namespace App\Controller\Admin;

use App\Entity\ProductSheet;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductSheetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductSheet::class;
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
