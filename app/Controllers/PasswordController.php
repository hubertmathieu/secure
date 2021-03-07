<?php namespace Controllers;


use Models\Brokers\PasswordBrocker;
use Models\Brokers\UserBroker;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Security\Cryptography;





class PasswordController extends Controller
{

    public function initializeRoutes()
    {
        $this->get("/shared", "shared");
        $this->get("/manager", "manage");
        $this->get("/add-password", "addPassword");
        $this->get("/favorite", "favorite");
        $this->get("/manager/{id}", "read");
        $this->get("/delete/{id}", "askDelete");
        $this->get("/delete/{id}/confirmation", "deletePassword");

        $this->post("/ajax", "ajax");
        $this->post("/manager/{id}", "modifyPassword");
        $this->post("/add-password", "insertPassword");
    }

    public function ajax()
    {
        $id = $_POST["id"];
        $url = $_POST["url"];
        $key = $_POST["key"];
        $userId = Cryptography::decrypt($id, $key);
        return $this->json((new UserBroker())->giveAuthentification($userId, $url));
    }

    public function shared()
    {
        $userId = Session::getInstance()->read("id");
        $passwords = (new PasswordBrocker())->selectShared($userId);

        return $this->render('shared', [
            'title'=> "Mots de passe partagés avec moi",
            'passwords' => $passwords
        ]);
    }

    public function manage()
    {
        $userId = Session::getInstance()->read("id");
        $passwords = (new PasswordBrocker())->selectPasswords($userId);

        return $this->render('manager', [
            'title'=>"Gestion des mots de passes",
            'passwords' => $passwords
        ]);
    }

    public function addPassword()
    {
        return $this->render('add-password', ['title'=>"Ajout d'un mot de passe"]);
    }

    public function favorite() {
        $userId = Session::getInstance()->read("id");
        $favoritePasswords = (new PasswordBrocker())->selectFavoritePasswords($userId);

        return $this->render('favorite', [
            'title'=>"Favoris",
            'passwords' => $favoritePasswords
        ]);
    }

    public function read($id) {
        $userId = Session::getInstance()->read("id");
        $password = (new PasswordBrocker())->findById($id);
        $user = (new UserBroker())->selectAllNames($userId);

        if (is_null($password)) {
            Flash::error("Mot de passe innexistant");
            return $this->redirect("/manager");
        }

        return $this->render('password', [
            'title' => $password->web_site,
            'password' => $password,
            'users' => $user
        ]);
    }

    public function modifyPassword($id)
    {
        $isFavorite = false;
        $sharedUser = $this->request->getParameter('sharedUser');
        $form = $this->buildForm();
        $this->validatePasswordForm($form);

        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/password/" + $id);
        }
        if ($form->getValue('isFavorite') != null) {
            $isFavorite = true;
        }
        if ($sharedUser != null) {
            (new PasswordBrocker())->sharePassword($sharedUser, $id);
        }
        (new PasswordBrocker())->update($form->buildObject(), $id, $isFavorite);

        return $this->redirect("/manager");
    }

    public function insertPassword()
    {
        $form = $this->buildForm();
        $this->validatePasswordForm($form);
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/add-password");
        }

        $userId = Session::getInstance()->read("id");
        (new PasswordBrocker())->insert($form->buildObject(), $userId);
        Flash::success("Le mot de passe a été ajouté!");

        return $this->redirect("/manager");
    }

    function validatePasswordForm($form)
    {
        $form->validate('password', Rule::notEmpty("Le champ du mot de passe est vide"));
        $form->validate('website', Rule::regex("(^http[s]?:\/{2})|(^www)|(^\/{1,2})$", "L'url doit être valide"));
    }

    public function askDelete($id)
    {
        $password = (new PasswordBrocker())->findById($id);
        $password->password_content = substr($password->password_content, 0, 1);

        return $this->render('deletePassword', [
            'password' => $password
        ]);
    }

    public function deletePassword($passwordId)
    {
        $userId = Session::getInstance()->read("id");
        (new PasswordBrocker())->delete($passwordId, $userId);
        Flash::error("Le mot de passe a été supprimé");

        return $this->redirect("/manager");
    }

}