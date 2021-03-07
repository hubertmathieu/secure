<?php namespace Models\Brokers;


use Zephyrus\Security\Cryptography;

class AuthenticationBroker extends Broker
{

    public function insert($user, $id)
    {
        $sql = "INSERT INTO \"authentication\" (user_id, email, password) VALUES (?, ?, ?)";
        $this->query($sql, [
            $id,
            $user->email,
            Cryptography::hashPassword($user->password)
        ]);
        return $this->getDatabase()->getLastInsertedId();
    }
}