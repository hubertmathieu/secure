<?php namespace Models\Brokers;

use stdClass;


class PasswordBrocker extends Broker
{
    public function findById($passwordId): ?stdClass
    {

        $sql = "SELECT web_site, decrypt(password_content) as password_content, is_fav, password_id FROM \"password\" WHERE password_id = ?";
        return $this->selectSingle($sql, [$passwordId]);
    }

    public function insert(stdClass $password, int $userId)
    {

        $sql1 = "INSERT INTO \"password\"  (web_site, password_content, is_fav) VALUES (?, encrypt(?), ?)";
        $this->query($sql1, [
            $password->website,
            $password->password,
            false
        ]);
        $lastId = $this->getDatabase()->getLastInsertedId();
        $sql2 = "INSERT INTO user_password (user_id, password_id, is_owner) VALUES (?, ?, ?)";
        $this->query($sql2, [
            $userId,
            $lastId,
            true
        ]);
        return $this->getDatabase()->getLastInsertedId();
    }

    public function selectPasswords($userId): array
    {
        $sql = "SELECT web_site, decrypt(password_content) as password_content, is_fav, password.password_id, user_password.is_owner FROM password JOIN user_password ON password.password_id = user_password.password_id JOIN \"user\" ON user_password.user_id = \"user\".user_id WHERE user_password.user_id = ?";
        //$sql = "SELECT * FROM \"password\" WHERE ? = user_id";
        $this->query($sql, [$userId]);

        return $this->select($sql, [$userId]);
    }

    public function selectFavoritePasswords($userId): array
    {
        $sql = "SELECT web_site, decrypt(password_content) as password_content, is_fav, password.password_id, user_password.is_owner FROM password JOIN user_password ON password.password_id = user_password.password_id JOIN \"user\" ON user_password.user_id = \"user\".user_id WHERE user_password.user_id = ? AND password.is_fav = ? AND user_password.is_owner = ?";
        $this->query($sql, [$userId, 1, 1]);
        return $this->select($sql, [$userId, 1, 1]);
    }

    public function selectPasswordByWebsite($userId, $url): stdClass
    {
        $sql = "SELECT decrypt(password_content) as password_content FROM password JOIN user_password ON password.password_id = user_password.password_id JOIN \"user\" ON user_password.user_id = \"user\".user_id WHERE user_password.user_id = ? AND password.web_site = ?";
        $this->query($sql, [
            $userId,
            $url
        ]);
        return $this->selectSingle($sql, [
            $userId,
            $url
        ]);
    }

    public function selectShared($userId)
    {
        $sql = "SELECT web_site, decrypt(password_content) as password_content, password.password_id FROM password JOIN user_password ON password.password_id = user_password.password_id JOIN \"user\" ON user_password.user_id = \"user\".user_id WHERE user_password.user_id = ? AND user_password.is_owner = ?";
        $this->query($sql, [$userId, 0]);
        return $this->select($sql, [
                $userId,
                0
            ]);
    }

    public function update(stdClass $password, int $id, bool $isFavorite)
    {
        $sql = "UPDATE \"password\" SET is_fav = ?, password_content = encrypt(?), web_site = ? WHERE password_id = ?";
        $this->query($sql, [
            $isFavorite,
            $password->password,
            $password->website,
            $id]);
    }

    public function sharePassword($userId, $passwordId)
    {
        if (is_null($this->alreadyShared($userId, $passwordId))) {
            $sql = "INSERT INTO user_password (user_id, password_id, is_owner) VALUES (?, ?, ?)";
            $this->query($sql, [
                $userId,
                $passwordId,
                false
            ]);
        }
    }

    public function alreadyShared($userId, $passwordId)
    {
        $sql = "SELECT * FROM user_password WHERE password_id = ? AND user_id = ?";
        $this->query($sql, [
            $passwordId,
            $userId
        ]);
        return $this->selectSingle($sql, [
            $passwordId,
            $userId
        ]);
    }

    public function delete($passwordId, $userId)
    {
        if (is_null($this->isOwner($passwordId, $userId))) {
            $sql = "DELETE FROM user_password WHERE user_id = ? AND password_id = ?";
            $this->query($sql, [
                $userId,
                $passwordId
            ]);
        } else {
            $sql = "DELETE FROM user_password WHERE password_id = ?";
            $this->query($sql, [
                $passwordId
            ]);
            $this->query("DELETE FROM password WHERE password_id = ?", [
                $passwordId
            ]);
        }
    }

    public function isOwner($passwordId, $userId)
    {
        $sql = "SELECT * FROM user_password WHERE user_id = ? AND password_id = ? AND is_owner = ?";
        $this->query($sql, [
            $userId,
            $passwordId,
            true
        ]);
        return $this->selectSingle( $sql, [
            $userId,
            $passwordId,
            true
        ]);
    }

    public function selectAuthentication($id, $url)
    {

    }

}