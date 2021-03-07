<?php namespace Models\Brokers;


class CreditBroker extends Broker
{
    public function insert(\stdClass $credit, $userId): int
    {
        $sql = "INSERT INTO credit_card (user_id, firstname, lastname, card_number, cvv, expiration, web_site) VALUES (?, ?, ?, encrypt(?), ?, ?, ?)";
        $this->query($sql, [
            $userId,
            $credit->firstname,
            $credit->lastname,
            $credit->creditNumber,
            $credit->cvv,
            $credit->expiration,
            $credit->website
        ]);
        return $this->getDatabase()->getLastInsertedId();
    }

    public function selectAll($userId)
    {
        $sql = "SELECT credit_card.card_id, firstname, lastname, decrypt(card_number) as card_number, cvv, expiration, web_site, user_id FROM \"credit_card\" WHERE ? = user_id";
        $this->query($sql, [$userId]);
        return $this->select($sql, [$userId]);
    }

    public function findById($id)
    {
        $sql = "SELECT credit_card.card_id, firstname, lastname, decrypt(card_number) as card_number, cvv, expiration, web_site, user_id  FROM credit_card WHERE card_id = ?";
        $this->query($sql, [$id]);
        return $this->selectSingle($sql, [$id]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM credit_card WHERE card_id = ?";
        $this->query($sql, [$id]);
    }
}