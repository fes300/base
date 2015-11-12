<?php

require_once __DIR__.'/../vendor/autoload.php';

// controllers
use Appzero\Controller\AdminController;
use Appzero\Controller\WebsiteController;

// repositories
use Appzero\Repository\MandrillApi;
use Appzero\Repository\UserRepository;

//Symfony components
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//Extend Silex
use Silex\Application;
class MyApplication extends Application
{
    use Application\TwigTrait;
    use Application\SecurityTrait;
};

//Create own route
use Silex\Route;
class MyRoute extends Route
{
    use Route\SecurityTrait;
};

// setup
$app = new MyApplication();
$app['debug'] = true;
$app['route_class'] = 'MyRoute';

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$defaultDbConfiguration = array(
    'driver'    => 'pdo_pgsql',
    'host'      => 'localhost',
    'dbname'    => 'app-zero',
    'user'      => 'app-zero',
    'password'  => '123',
    'charset'   => 'utf8',
);

$dbUrl = getenv("DATABASE_URL");
$isOnHeroku = !empty($dbUrl);
if ($isOnHeroku) {
    $app['debug'] = false;
    $dbUrl = parse_url($dbUrl);
    $defaultDbConfiguration['host'] = $dbUrl['host'];
    $defaultDbConfiguration['user'] = $dbUrl['user'];
    $defaultDbConfiguration['password'] = $dbUrl['pass'];
    $defaultDbConfiguration['dbname'] = substr($dbUrl['path'],1);
}

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $defaultDbConfiguration,
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

//security
$userRepo = new UserRepository($app['db']);
$usersData = $userRepo->getUsersForSilex();

$app->register(
    new Silex\Provider\SecurityServiceProvider(), array(
       'security.firewalls' =>  array(
           'admin' => array(
               'pattern' => '\badmin\b',
               'anonymous' => false,
               'http' => true,
               'form' => array(
                   'login_path' => '/login',
                   'check_path' => '/admin/login_check',
                   'always_use_default_target_path' => true,
                   'default_target_path' => '/admin/'
               ),
               'logout' => array(
                   'logout_path' => '/admin/logout',
                   'invalidate_session' => true
               ),
               'remember_me' => array(
                    'key' => '44pinc3d229ebqbvqa6bpvlhp3'
                ),
               'users' => $usersData
            )
        )
    )
);

$app->register(
    new Silex\Provider\RememberMeServiceProvider()
);

$app->get('/login', function( Request $request) use ($app) {
    return $app->render('login.html', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

// controllers
$app->post('/sendMail', function ()  use ($app) {
    $mandrill = new Mandrill('tf3QppbIhIQY7z-ZhdBd9Q');
    $mandrillFunctions = new MandrillApi($app['db']);

    $template_content = $mandrillFunctions->getContent($_POST);
    $message = $mandrillFunctions->sendMessage($_POST);
    $template_name = 'App-zeroContactForm';
    $async = false;
    $ip_pool = 'Main Pool';
    $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool);

    if(!empty($_POST['email'])){
        $mailFrom = $_POST['mailto'];
        $_POST['mailto'] = $_POST['email'];
        $_POST['email'] = $mailFrom;
        $message = $mandrillFunctions->sendMessage($_POST);
        $template_name = 'App-zeroRequestReceived';
        $template_content = $mandrillFunctions->getContentForCustomer($_POST);
        $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool);
    }
    return $app->redirect('/'.$pageName);
});

$adminController = new AdminController($app);
$app->mount('/admin', $adminController->build());

$websiteController = new WebsiteController($app);
$app->mount('/', $websiteController->build());

return $app;
