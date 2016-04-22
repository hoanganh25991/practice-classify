<?php
namespace BackEnd\Controller;

use App\Config\UniMedia;
use BackEnd\Database\UserTable;
use BackEnd\Form\LoginFilter;
use BackEnd\Form\LoginForm;
use BackEnd\Service\Encrypt;
use BackEnd\Service\UniSession;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractActionController{
    const USER = "USER";

    protected $serviceManager;

    public function __construct(ServiceManager $serviceManager){
        $this->serviceManager = $serviceManager;
    }

    /**
     * check login from user
     * @return ViewModel
     */
    public function loginAction(){
        $view = new ViewModel();
        /** @var Request $request */
        $request = $this->getRequest();
        /*
        * LOGIN FORM
        * both for request GET, POST
        */

        /*
         * GET
         */
        $loginForm = new LoginForm("loginForm");
        $loginFilter = new LoginFilter();
        $loginForm->setInputFilter($loginFilter);
        $view->setVariable("loginForm", $loginForm);
        if($request->isGet()){
            return $view;
        }
        /*
         * POST
         */
        if($request->isPost()){
            /** @var ParametersInterface $data */
            $data = $request->getPost();

            $loginForm->setData($data);
            /*
             * validate by loginForm
             */
            if($loginForm->isValid()){

                /*
                 * check auth, right/wrong
                 */
                $email = $data->get('email');
                /** @var UserTable $userTable */
                $userTable = $this->serviceManager->get("UserTableQuery");

                /** @var array|false $user */
                $user = $userTable->findWhere(array('email' => $email));

                /*
                 * if not find out user, return with message
                 */
                if(!$user){
//                    $loginForm->setMessages(array("email" => "may be your email is wrong"));
                    return $view;
                }

                $isRightPassword = false;
                $encryptPass = Encrypt::hash($data->get('password'));
                if($encryptPass === $user['password']){
                    $isRightPassword = true;
                }
                /*
                 * luu user vao session
                 */
                /*
                 * @warn cach nay khien cho viec lay user ra la khong chac chan return type
                 * logic cua viec lay 1 cai gi do ra can nhat quan
                 * co warn >>> check de
                 * query tu DB return cai gi phai doc thu roi moi xai dc
                 */
                if($isRightPassword){
                    /*
                     * save user into session
                     * 1. viet raw, goi session ra viet, OK
                     * 2. lan sau muon access, goi session ra, get
                     * 3. viet rieng 1 lop, LOGINED_USER
                     * class nay giup handle viec lay user ra
                     * chi la logined user
                     * USER class Model dung de lam gi?
                     * $user["id"], $user["email"], $user["avatar"]
                     * .... acces INFO from user
                     * >>>>get out
                     * >>>>map info
                     * >>>>chi viec query get(), set() la done
                     * lien quan den user
                     * user->saveLoginedUser
                     * user->getLoginedUser
                     * o 1 noi nao do can acces vao LoginedUser >>> lay ra
                     * nhung info $user co duoc, luu vao dau?
                     * $user, luc nao cung can READ tu SESSION
                     * read ra xong roi, handle logic
                     * LoginedUser, luu tat ca cac noi "isAllowed"
                     * lan sau vao loop qua phat la xong, chay tren RAM, SessionArrayStorage (~cache memory)
                     * USERTABLE la khac nua, no la noi handle request den DB
                     * USERSESSION, la noi handle request from SESSION @@
                     * USER model, nhung 1 restrict type, cho phep lien he voi $user khi return la cai gi
                     * khi return co A, B, C, hay array gi cung duoc
                     * nhung do logic minh bo vao trong User, nen biet chac la ra cai gi
                     * khong ra duoc thi minh tao NULL de handle
                     * USER 1 cai modal nhung handle tat ca viec get set @@ la DUNG/SAI
                     *
                     */
                    /*
                     * USER_HANDLER
                     */
                    $uniSession = new UniSession();
                    $uniSession->set(UniSession::USER, self::USER, $user);
                    var_dump($user);
                    $this->redirect()->toUrl('/');
                }
               /*
                * user with wrong info
                */
            }
            /*
            * login from not valid
            */

        }
//        //add variablesContainer to viewModel
//        $variablesContainer = array();
//        $variablesContainer["controller"] = 'BackEnd\Controller\AuthController\loginAction';
//
//        $variablesContainer['loginForm'] = $loginForm;
        /*
         * a fallback when POST check fail
         */
        $view->setVariable("loginForm", $loginForm);
        return $view;
//        $view = new ViewModel($variablesContainer);
//        return $view;
    }

    public function joinAction(){
        $view = new ViewModel();
        /** @var Request $request */
        $request = $this->getRequest();
        /*
        * LOGIN FORM
        * both for request GET, POST
        */

        /*
         * GET
         */
        $loginForm = new LoginForm("loginForm");
        $loginFilter = new LoginFilter();
        $loginForm->setInputFilter($loginFilter);
        $view->setVariable("loginForm", $loginForm);
        if($request->isGet()){
            return $view;
        }

        if($request->isPost()){
            /** @var ParametersInterface $data */
            $data = $request->getPost();

            $loginForm->setData($data);
            /*
             * validate by loginForm
             */
            if($loginForm->isValid()){
                /*
                 * save user into db
                 */
                $userTable = new UserTable($this->serviceManager);

                $user = array();
                $user["email"] = $data->get("email");
                $user["password"]= $data->get("password");

                $result = $userTable->insert($user);
                /*
                 * result = true, insert success
                 */
                if($result){
                    /*
                     * save to session
                     */
                    $uniSession = new UniSession();
                    $uniSession->set(UniSession::USER, self::USER, $user);
                    $this->redirect()->toUrl("/");
                }
            }
        }

//        $variablesContainer = array();
//        $variablesContainer["controller"] =
//            'BackEnd\Controller\AuthController\joinAction';
//        $view = new ViewModel($variablesContainer);

        /*
         * as a fallback
         */
        $view->setVariable("loginForm", $loginForm);
        return $view;
    }

    public function logoutAction(){
        $uniSession = new UniSession();
        $uniSession->remove(UniSession::USER, self::USER);
//        var_dump("unset user from \$sessionContainer");
        return $this->redirect()->toUrl('/login');
    }
}