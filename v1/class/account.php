<?php

class Account {

    public $user = NULL;
    function __construct($name)
    {
        $user = self::find_by("account", $name);
        if ($user)
        {
            $this->user = (object)$user->{0};
            $this->user->meta = (object)self::get_meta_all($name);
        }
    }

    public static function Register($account, $email, $password, &$error, &$errorcode)
    {
        if (self::find_by("account", $account))
        {
            $error = "Account already exists";
            $errorcode = "ACCOUNT_EXISTS";
            return 0; 
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            $error = "That email address is not valid";
            $errorcode = "INVALID_EMAIL";
            return 0; 
        }
        
        $err = NULL;
        if (test_password_strength($password, $err) < get_config("minimum password strength"))
        {
            $error = $err;
            $errorcode = "WEAK_PASSWORD";
            return 0;
        }
        if (!is_valid_account_name($account))
        {
            $error = "Account name contains illegal chars or is too long";
            $errorcode = "BAD_ACCOUNT_NAME";
            return 0;
        }
        $conn = sqlnew();
        $stmt = $conn->prepare("INSERT INTO userv_account (account_name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute(["name" => $account, "email" => $email, "password" => $password]);
        return $stmt->rowCount();
    }

    public static function identify($account = NULL, $email = NULL, $password)
    {
        $account = strtolower($account);
        if ($account)
        {
            $search = "account_name";
            $term = $account;
        }
        elseif ($email)
        {
            $search = "email";
            $term = $email;
        }
        $conn = sqlnew();
        $stmt = $conn->prepare("SELECT * FROM userv_account WHERE LOWER($search) = :term LIMIT 1");
        $stmt->execute(["term" => $term]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$results || empty($results))
            return false;
        if (password_verify($password, $results[0]['password']))
            return new Account($results[0]['account_name']);
        return false;
    }
    /**
     * Two types:
     * "account"
     * "email"
     */
    public static function find_by($type, $lookup)
    {
        if (!$type || ($type != "account" && $type != "email"))
            return null;

        $conn = sqlnew();
        if ($type == "account")
        {
            $stmt = $conn->prepare("SELECT * FROM userv_account WHERE LOWER(account_name) = :name LIMIT 1");
            $stmt->execute(["name" => strtolower($lookup)]);
            if ($stmt->rowCount())
            {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return (object)$results;
            }
        }
        return null;
    }
    public static function list()
    {
        $conn = sqlnew();
        $sql = "SELECT * FROM userv_account";
        $ret = $conn->query($sql);
        return $ret->fetchAll(PDO::FETCH_ASSOC) ?? null;
    }

    public static function add_meta($account, $key, $value)
    {
        $conn = sqlnew();
        $sql = "INSERT INTO userv_account_meta (user_id, meta_name, meta_value) VALUES (:account, :key, :value)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(["account" => strtolower($account), "key" => $key, "value" => $value]);
        return $stmt->rowCount();
    }
    public static function del_meta($account, $key, $value)
    {
        $conn = sqlnew();
        $sql = "DELETE FROM userv_account_meta WHERE LOWER(user_id) = :account AND meta_name = :key AND LOWER(meta_value) = :value";
        $stmt = $conn->prepare($sql);
        $stmt->execute(["account" => strtolower($account), "key" => $key, "value" => strtolower($value)]);
        return $stmt->rowCount();
    }
    public static function get_meta_all($account)
    {
        $conn = sqlnew();
        $sql = "SELECT * FROM userv_account_meta WHERE LOWER(user_id) = :account";
        $stmt = $conn->prepare($sql);
        $stmt->execute(["account" => strtolower($account)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? null;
    }

    public static function get_meta_by_key($account, $key)
    {
        $conn = sqlnew();
        $sql = "SELECT * FROM userv_account_meta WHERE LOWER(user_id) = :account AND meta_name = :key";
        $stmt = $conn->prepare($sql);
        $stmt->execute(["account" => strtolower($account), "key" => $key]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? null;
    }

    function __toString()
    {
        return $this->user ? json_encode($this->user) : json_encode(["error" => "User not found"]);
    }
}

function test_password_strength($password, &$error) {
    // Define the criteria for password strength
    $minLength = 8;
    $minUppercase = 1;
    $minLowercase = 1;
    $minDigits = 1;
    $minSpecialChars = 1;
    $specialChars = '!@#$%^&*()-_+=~`[]{}|;:,.<>?';
    $minAllowed = get_config("minimum password strength");
    // Check password length
    $length = strlen($password);
    if ($length < $minLength) {
        $error = "Password should be at least $minLength characters long.";
        if ($minAllowed >= 1)
            return 0;
    }

    // Check for uppercase letters
    if (preg_match_all('/[A-Z]/', $password) < $minUppercase) {
        $error = "Password should contain at least $minUppercase uppercase letter(s).";
        if ($minAllowed >= 2)
            return 1;
    }

    // Check for lowercase letters
    if (preg_match_all('/[a-z]/', $password) < $minLowercase) {
        $error = "Password should contain at least $minLowercase lowercase letter(s).";
        if ($minAllowed >= 2)
            return 2;
    }

    // Check for digits
    if (preg_match_all('/\d/', $password) < $minDigits) {
        $error = "Password should contain at least $minDigits digit(s).";
        if ($minAllowed >= 3)
            return 3;
    }

    // Check for special characters
    if (preg_match_all('/[' . preg_quote($specialChars, '/') . ']/', $password) < $minSpecialChars) {
        $error = "Password should contain at least $minSpecialChars special character(s).";
        if ($minAllowed >= 4)
            return 4;
    }

    // Password meets the criteria
    return 5;
}

function is_valid_account_name($nick) {
    // Regular expression pattern for valid IRC nickname
    $pattern = '/^[A-Za-z0-9[\]\\`_^{|}-]{1,32}$/';

    // Check if the nickname matches the pattern
    return preg_match($pattern, $nick);
}