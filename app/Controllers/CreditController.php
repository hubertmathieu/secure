<?php namespace Controllers;


use Models\Brokers\CreditBroker;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;

class CreditController extends Controller
{

    public function initializeRoutes()
    {
        $this->get("/credit-card", "creditCard");
        $this->get("/add-credit", "addCredit");
        $this->get("/deleteCredit/{id}", "askDeleteCredit");
        $this->get("/deleteCredit/{id}/confirmation", "deleteCredit");

        $this->post("/add-credit", "insertCredit");

    }

    public function creditCard()
    {
        $userId = Session::getInstance()->read("id");
        $credits = (new CreditBroker())->selectAll($userId);
        foreach ($credits as $credit) {
            $credit->card_number = substr($credit->card_number, -4);
        }
        return $this->render('credit-card', ['title' => "Cartes de crédit", 'credits' => $credits]);
    }

    public function addCredit()
    {
        return $this->render('add-credit', ['title' => "Ajout d'une carte de crédit"]);
    }

    public function insertCredit()
    {
        $form = $this->buildForm();
        $this->validateCreditForm($form);
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/add-credit");
        }

        $userId = Session::getInstance()->read("id");
        (new CreditBroker())->insert($form->buildObject(), $userId);
        Flash::success("La carte de crédit a été ajoutée avec succès!");

        return $this->redirect("/credit-card");
    }

    function validateCreditForm($form)
    {
        $form->validate('firstname', Rule::notEmpty("Vous devez entrer un prénom"));
        $form->validate('lastname', Rule::notEmpty("Vous devez entrer un nom"));
        $form->validate('website', Rule::notEmpty("Vous devez entrer un site web"));
        $form->validate('creditNumber', Rule::regex("^(?:4[0-9]{12}(?:[0-9]{3})?|[25][1-7][0-9]{14}|6(?:011|5[0-9][0-9])[0-9]{12}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|(?:2131|1800|35\d{3)\d{11})$", "Mauvais # de carte"));
        $form->validate('expiration', Rule::regex("^(0[1-9]|1[0-2])\/{0,1}[0-9]{2}$", "Mauvaise expiration"));
        $form->validate('cvv', Rule::regex("^[0-9]{3}$", "Mauvais cvv"));
    }

    public function askDeleteCredit($creditId)
    {
        $credit = (new CreditBroker())->findById($creditId);
        $credit->card_number = substr($credit->card_number, -4);
        return $this->render('deleteCredit', [
            'credit' => $credit
        ]);
    }

    public function deleteCredit($creditId)
    {
        (new CreditBroker())->delete($creditId);
        Flash::error("La carte de crédit a été supprimer");

        return $this->redirect("/credit-card");
    }
}