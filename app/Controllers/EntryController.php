<?php


namespace Controllers;


use Models\Brokers\PasswordBrocker;
use Models\Brokers\UserBroker;
use phpDocumentor\Reflection\Types\Array_;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Security\Cryptography;

class EntryController extends Controller
{

    public function initializeRoutes()
    {
        $this->get("/index", "index");
        $this->get("/login", "login");
        $this->get("/inscription", "inscription");



        $this->post("/login", "authenticateUser");
        $this->post("/inscription", "insertUser");
    }



    public function index()
    {
        return $this->render('index', ['title'=>"Secure"]);
    }

    public function login()
    {
        return $this->render('login', ['title'=>"Connexion"]);
    }

    public function inscription()
    {
        return $this->render('inscription', ['title'=>"Inscription"]);
    }

    public function authenticateUser()
    {
        $form  = $this->buildForm();
        $this->validateConnexionForm($form);
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/login");
        }

        $user = (new UserBroker())->authenticate(
            $this->request->getParameter('email'),
            $this->request->getParameter('password')
        );

        if (is_null($user)) {
            Flash::error("Mauvaises informations");
            return $this->redirect("/login");
        }

        Session::getInstance()->set('user', $user);
        Session::getInstance()->set("id", $user->user_id);
        Flash::success("Vous vous êtes connecté avec succès! \nBienvenue " .$user->firstname);
        return $this->redirect("/manager");
    }

    function validateConnexionForm($form)
    {
        $form->validate('email', Rule::email("Votre adresse couriel doit être valide."));
        $form->validate('password', Rule::notEmpty("Vous devez entrer un mot de passe."));
    }

    public function insertUser()
    {
        $form = $this->buildForm();
        $this->validateInscriptionForm($form);
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/inscription");
        }

        (new UserBroker())->insert($form->buildObject());
        Flash::success("Vous vous êtes inscrit avec succès!");
        return $this->redirect("/login");
    }

    function validateInscriptionForm($form)
    {
        $form->validate('lastname', Rule::name("Vous devez avoir un nom."));
        $form->validate('firstname', Rule::name("Vous devez avoir un prénom."));
        $form->validate('username', Rule::notEmpty("Vous devez avoir un nom d'utilisateur."));
        $form->validate('email', Rule::email("Votre adresse couriel doit être valide."));
        $form->validate('password', Rule::passwordCompliant("Votre mot de passe doit être valide."));
    }
}