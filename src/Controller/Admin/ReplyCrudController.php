<?php

namespace App\Controller\Admin;

use App\Entity\Reply;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ReplyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reply::class;
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
