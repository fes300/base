<?php

namespace Appzero\Controller;

use Symfony\Component\Security\Core\User\User;

class Admin {

    function __construct($app) {
        $this->app = $app;
    }

    function build() {
        $app = $this->app;

        $admin = $app['controllers_factory'];

        $admin->get('/', function() use($app){
            $user = $app['user']->getUsername();
            return $app->render('admin/admin.twig', ['user'=>$user, 'page'=>'admin']);
        })->secure('IS_AUTHENTICATED_REMEMBERED');

        return $admin;
    }
}
