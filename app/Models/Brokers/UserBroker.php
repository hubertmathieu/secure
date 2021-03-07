<?php namespace Models\Brokers;

use stdClass;
use Zephyrus\Security\Cryptography;

class UserBroker extends Broker
{

    public function selectAllNames($userId)
    {
        $sql = "SELECT * FROM \"user\" WHERE user_id <> ?";
        $this->query($sql, [$userId]);
        return $this->select($sql, [$userId]);
    }

    public function insert(stdClass $user)
    {
        $sql = "INSERT INTO \"user\" (firstname, lastname, email, username) VALUES (?, ?, ?, ?)";
        $this->query($sql, [
            $user->firstname,
            $user->lastname,
            $user->email,
            $user->username
        ]);
        (new AuthenticationBroker())->insert($user, $this->getDatabase()->getLastInsertedId());
        return $this->getDatabase()->getLastInsertedId();
    }

    public function authenticate($email, $password): ?stdClass
    {
        $sql = "SELECT * FROM \"user\" JOIN authentication ON \"user\".user_id = authentication.user_id WHERE authentication.email = ?";
        $user = $this->selectSingle($sql, [$email]);
        if(is_null($user)) {
            return null;
        }
        if (!Cryptography::verifyHashedPassword($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function selectSharedUsers($id)
    {
        $sql = "SELECT \"user\".firstname, \"user\".lastname FROM \"user\" JOIN user_password ON \"user\".user_id = user_password.user_id WHERE user_password.is_owner = ? AND user_password.password_id = ?";
        return $this->select($sql, [
            false,
            $id
        ]);
    }


    public function giveAuthentification($userId, $url) :stdClass
    {
        $sql = "SELECT decrypt(password_content) as password_content, \"user\".email FROM password JOIN user_password ON password.password_id = user_password.password_id JOIN \"user\" ON user_password.user_id = \"user\".user_id WHERE user_password.user_id = ? AND web_site = ?";
        return $this->selectSingle($sql, [
            $userId,
            $url
        ]);
    }
}