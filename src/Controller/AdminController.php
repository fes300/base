<?php

namespace App-zero\Controller;

use App-zero\Repository\WebsiteRepository;
use App-zero\Repository\UserRepository;
use App-zero\Model\WebsiteModel as Website;
use App-zero\Model\UserModel;
use Symfony\Component\Security\Core\User\User;
use App-zero\Model\ImageModel as Image;
use App-zero\Library\S3Library as S3;

class AdminController {

    function __construct($app) {
        $this->app = $app;
    }

    function build() {
        $app = $this->app;

        $admin = $app['controllers_factory'];

        $admin->get('/', function ()  use ($app) {
            $userRepo = new UserRepository($app['db']);
            $webWebsiteRepository = new WebsiteRepository($app['db']);
            $user = $userRepo->getByUsername($app['user']->getUsername());
            if ($app['security.authorization_checker']->isGranted('ROLE_ADMIN')) {
                $websites = $webWebsiteRepository->getAllWebsites();
                $users = $userRepo->getAll();
                return $app['twig']->render('admin/siteList.twig', ['websites'=>$websites, 'users'=>$users, 'user'=>$user]);
            }else{
                $websites = $webWebsiteRepository->getUserWebsites($user->domains);
                return $app['twig']->render('admin/customerAdmin.twig', ['user'=>$user, 'websites'=>$websites]);
            };
        })->secure('IS_AUTHENTICATED_REMEMBERED');

        $admin->get('/site/create', function ()  use ($app) {
            return $app['twig']->render('admin/siteCreate.twig');
        })->secure('ROLE_ADMIN');

        $admin->get('/users', function ()  use ($app) {
            $usersRepo = new UserRepository($app['db']);
            $users = $usersRepo->getAll();
            return $app['twig']->render('admin/users.twig', ['users'=>$users]);
        })->secure('ROLE_ADMIN');

        $admin->post('/user/create', function ()  use ($app) {
            $userRepo = new UserRepository($app['db']);

            if($userRepo->isUsernameTaken($_POST['username'])==false){
                $user = new User($_POST['username'], $_POST['password'], [$_POST['role']], true, true, true, true);
                $encoder = $app['security.encoder_factory']->getEncoder($user);
                $encodedPassword = $encoder->encodePassword($_POST['password'], $user->getSalt());
                $_POST['password'] = $encodedPassword;
                $newUser = new UserModel($_POST);

                if($uuid=$userRepo->insert($newUser))
                return $uuid;
            }
            return 'usernameTaken';
        })->secure('ROLE_ADMIN');

        $admin->post('/manageUserActivation', function ()  use ($app) {
            $usersRepo = new UserRepository($app['db']);
            return $usersRepo->activate($_POST);
        })->secure('ROLE_ADMIN');

        $admin->post('/changeMyInfo', function() use($app){
            $usersRepo = new UserRepository($app['db']);
            $_POST = json_decode(json_encode($_POST));
            $oldUser = $usersRepo->getbyUuid($_POST->uuid);

            if($usersRepo->isUsernameTaken($_POST->username)==false || $oldUser->username == $_POST->username){
                if(!empty($_POST->password)){
                    $user = new User($_POST->username, $_POST->password, [$oldUser->role], true, true, true, true);
                    $encoder = $app['security.encoder_factory']->getEncoder($user);
                    $newPasswordEncoded = $encoder->encodePassword($_POST->password, $user->getSalt());
                    $_POST->password = $newPasswordEncoded;
                } else {
                    $_POST->password = $oldUser->password;
                }

                if($response = $usersRepo->update($_POST))
                return $response;
            };

            return 'usernameTaken';
        })->secure('IS_AUTHENTICATED_REMEMBERED');

        $admin->post('/site/create', function ()  use ($app) {
            $website = new Website($_POST);
            $WebsiteRepository = new WebsiteRepository($app['db']);
            $WebsiteRepository->insert($website);
            return $app->redirect('/admin/');
        })->secure('ROLE_ADMIN');

        $admin->get('/site/edit/{domain}', function ($domain)  use ($app) {
            if ($app['security.authorization_checker']->isGranted('ROLE_USER')) {
                $this->checkDomainAutorization($domain, $app);
            }

            $S3 = New S3();
            $picturesRepo = $S3->getObjects("app-zero-storage", 'websites/'.$domain.'/')->toArray();
            if (empty($picturesRepo['Contents'])) {
                $pictures = [];
            } else {
                $pictures = $picturesRepo['Contents'];
            }

            $texturesRepo = $S3->getObjects("app-zero-storage", 'textures/')->toArray();
            if (empty($texturesRepo['Contents'])) {
                $textures = [];
            } else {
                $textures = $texturesRepo['Contents'];
            }

            $WebsiteRepository = new WebsiteRepository($app['db']);
            $website = $WebsiteRepository->getByDomain($domain);
            return $app['twig']->render('admin/siteEdit.twig',
                [
                    'website'=>$website,
                    'pictures'=>$pictures,
                    'textures'=>$textures,
                    'fontFamilies'=> [
                        [
                            'title' => 'Serif',
                            'description' => 'Font che esprimono più classicità',
                            'fonts' => [
                                'Vollkorn', 'Lora', 'Tinos', 'Kreon', 'PT Serif', 'Droid Serif'
                            ],
                            'fallbacks' => ['serif']
                        ],
                        [
                            'title' => 'Sans Serif',
                            'description' => 'Font più consoni al web',
                            'fonts' => [
                                'Lato', 'Montserrat', 'Open Sans', 'Source Sans Pro', 'Roboto'
                            ],
                            'fallbacks' => ['Arial', 'sans-serif']
                        ],
                        [
                            'title' => 'Slab Serif',
                            'description' => 'Font più tecnologico-avanguardisti',
                            'fonts' => [
                                'Merriweather', 'Josefin Slab', 'Roboto Slab', 'Kelly Slab', 'Coda'
                            ],
                            'fallbacks' => ['Georgia', 'serif']
                        ],
                        [
                            'title' => 'Handmade',
                            'description' => 'Font da usare in casi particolari',
                            'fonts' => [
                                'Overlock', 'Pacifico', 'Sigmar One', 'Lobster', 'Handlee'
                            ],
                            'fallbacks' => ['Comic Sans', 'cursive']
                        ]
                    ]
                ]
            );
        })->secure('IS_AUTHENTICATED_REMEMBERED');

        $admin->post('/site/update/{domain}', function ($domain)  use ($app) {
            if ($app['security.authorization_checker']->isGranted('ROLE_USER')) {
                $this->checkDomainAutorization($domain);
            }
            $websiteRepository = new WebsiteRepository($app['db']);
            $website = $websiteRepository->getByDomain($domain);
            $website->content = json_decode($_POST['websiteContent']);
            $websiteRepository->update($website);
            return $app->redirect('/admin/site/edit/'.$website->domain);
        })->secure('IS_AUTHENTICATED_REMEMBERED');

        $admin->post('/site/restore/{domain}', function ($domain)  use ($app) {
            if ($app['security.authorization_checker']->isGranted('ROLE_USER')) {
                $this->checkDomainAutorization($domain);
            }
            $websiteRepository = new WebsiteRepository($app['db']);
            $website = $websiteRepository->getByDomain($domain);
            $website->content = $website->backup;
            $websiteRepository->update($website);
            return $app->redirect('/admin/site/edit/'.$website->domain);
        })->secure('IS_AUTHENTICATED_REMEMBERED');

        $admin->post('/site/uploadPictures/{domain}', function ($domain)  use ($app) {
            if ($app['security.authorization_checker']->isGranted('ROLE_USER')) {
                $this->checkDomainAutorization($domain);
            }
            $S3 = New S3();
            for ($iii = 0; $iii < count($_FILES['pictures']['name']); $iii++) {
                $filePath = $_FILES['pictures']['tmp_name'][$iii];
                $fileName = $_FILES['pictures']['name'][$iii];
                if (!file_exists($filePath)) {
                    throw new Exception("File does not exist: $filePath");
                }

                $extension = $this->getExtension($fileName);

                //if the file uploaded is an image
                if ($extension == "jpeg" || $extension == "jpg" || $extension == "png") {
                    $image = new Image($filePath);
                    $image->origName = $_FILES['pictures']['name'][$iii];

                    //picture resize
                    if ($image->origWidth > Image::MAX_WIDTH || $image->origHeight > Image::MAX_HEIGHT) {
                        if ($image->origWidth >= $image->origHeight) {
                            $image->resizeTo(Image::MAX_WIDTH, null, 'maxwidth');
                        } else if ($image->origWidth < $image->origHeight) {
                            $image->resizeTo(null, Image::MAX_HEIGHT, 'maxheight');
                        }

                        if ($image->extension == "jpeg" || $image->extension == "jpg") {
                            imagejpeg($image->newContent, $image->path);
                        }
                        if ($image->extension == "png") {
                            imagepng($image->newContent, $image->path);
                        }
                    }

                    //pictures optimization
                    if ($image->extension == "jpeg" || $image->extension == "jpg") {
                        exec("jpegoptim " . $image->path);
                    }

                    if ($image->extension == "png") {
                        //exec("pngquant --quality=85 --ext='' --force " . $filePath);
                        //exec("optipng " . $image->path);
                    }

                    $imageFile = file_get_contents($image->path);
                    $S3->upload('app-zero-storage', 'websites/'.$domain.'/'.$image->origName, $imageFile);
                } else if ($extension == "pdf"){
                    $fileContent = file_get_contents($filePath);
                    $S3->uploadPDF('app-zero-storage', 'websites/'.$domain.'/'.$fileName, $fileContent);
                }
            }
            return $app->redirect('/admin/site/edit/'.$domain);
        })->secure('IS_AUTHENTICATED_REMEMBERED');

        $admin->get('/site/deletePicture/{domain}/{fileName}', function ($domain, $fileName)  use ($app) {
            if ($app['security.authorization_checker']->isGranted('ROLE_USER')) {
                $this->checkDomainAutorization($domain);
            }
            $S3 = New S3();
            $S3->delete('app-zero-storage', 'websites/'.$domain.'/'.$fileName);
            return $app->redirect('/admin/site/edit/'.$domain);
        })->secure('IS_AUTHENTICATED_REMEMBERED');

        return $admin;
    }


    //useful functions
    function checkDomainAutorization($domain, $app){
        $userRepo = new UserRepository($app['db']);
        $user = $userRepo->getByUsername($app['user']->getUsername());
        $myDomains = $user->domains;
        if(!in_array($domain, $myDomains)){
             $app->abort(403, "You are not allowed to modify this website.");
        };
    }

    public function getExtension($string) {
        $iii = strrpos($string,".");
        if (!$iii) {
            return false;
        }
        $length = strlen($string) - $iii;
        $extension = substr($string, $iii + 1, $length);

        return $extension;
    }

}
