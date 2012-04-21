<?php

/**
 * Description of actions
 *
 * @author panefra
 */
class jcryptionActions extends sfActions {

    public function executeGenerateKeyPair(sfWebRequest $request) {
        // Get user session
        $user = $this->getUser();
        // Set the RSA key length
        $keyLength = 1024;
        // Create a new jCryption object
        $jCryption = new jCryption();
        // Include some RSA keys
        $arrKeys = jCryption::loadGeneratedKeys();
        // Pick a random key from the array
        $keys = $arrKeys[mt_rand(0, 100)];
        // Save the RSA key into session
//        $_SESSION["e"] = array("int" => $keys["e"], "hex" => $jCryption->dec2string($keys["e"], 16));
//        $_SESSION["d"] = array("int" => $keys["d"], "hex" => $jCryption->dec2string($keys["d"], 16));
//        $_SESSION["n"] = array("int" => $keys["n"], "hex" => $jCryption->dec2string($keys["n"], 16));
        $user->setAttribute("e", array("int" => $keys["e"], "hex" => $jCryption->dec2string($keys["e"], 16)));
        $user->setAttribute("d", array("int" => $keys["d"], "hex" => $jCryption->dec2string($keys["d"], 16)));
        $user->setAttribute("n", array("int" => $keys["n"], "hex" => $jCryption->dec2string($keys["n"], 16)));
        // Generate reponse
        $e = $user->getAttribute("e");
        $n = $user->getAttribute("n");
        $arrOutput = array(
            "e" => $e["hex"],
            "n" => $n["hex"],
            "maxdigits" => intval($keyLength * 2 / 16 + 3)
        );
        // Convert the response to JSON, and send it to the client
        $this->getResponse()->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $this->renderText(json_encode($arrOutput));
        return sfView::NONE;
    }

    public function executeDecryptTest(sfWebRequest $request) {
        // Get user session
        $user = $this->getUser();
        // Set the RSA key length
        $keyLength = 1024;
        // Create a new jCryption object
        $jCryption = new jCryption();
        // Get some test data to encrypt, this is an ISO 8601 timestamp
        $toEncrypt = date("c");
        // JSON encode the timestamp, both encrypted and unencrypted
        $this->getResponse()->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $this->renderText(json_encode(array(
                    "encrypted" => AesCtr::encrypt($toEncrypt, $user->getAttribute("key"), 256),
                    "unencrypted" => $toEncrypt
                )));
        return sfView::NONE;
    }

    public function executeHandShake(sfWebRequest $request) {
        // Get user session
        $user = $this->getUser();
        // Set the RSA key length
        $keyLength = 1024;
        // Create a new jCryption object
        $jCryption = new jCryption();
        // Decrypt the client's request
        $d = $user->getAttribute("d");
        $n = $user->getAttribute("n");
//        var_dump($d);
//        var_dump($n);
//        die();
        $key = $jCryption->decrypt($request->getParameter("key"), $d["int"], $n["int"]);
        // Remove the RSA key from the session
        $holder = $user->getAttributeHolder();
        $holder->remove("e");
        $holder->remove("d");
        $holder->remove("n");
//            unset($_SESSION["e"]);
//            unset($_SESSION["d"]);
//            unset($_SESSION["n"]);
        // Save the AES key into the session
//            $_SESSION["key"] = $key;
        $user->setAttribute("key", $key);
        // JSON encode the challenge
//            echo json_encode(array("challenge" => AesCtr::encrypt($key, $key, 256)));
        $this->getResponse()->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $this->renderText(json_encode(array("challenge" => AesCtr::encrypt($key, $key, 256))));
        return sfView::NONE;
    }

    public function executeDecrypt(sfWebRequest $request) {
        $user = $this->getUser();
        // Set the RSA key length
        $keyLength = 1024;
        // Create a new jCryption object
        $jCryption = new jCryption();

        // If the GET parameter "generateKeypair" is set
        if ($request->getParameter("generateKeypair", false)) {

            // Else if the GET parameter "decrypttest" is set
        } elseif ($request->getParameter("decrypttest", false)) {

            // Else if the GET parameter "handshake" is set
        } elseif ($request->getParameter("handshake", false)) {
            
        } else {
            // Decrypt the client's request and send it to the clients(uncrypted)
//            echo json_encode(array("data" => AesCtr::decrypt($_POST['jCryption'], $_SESSION["key"], 256)));
            $this->getResponse()->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
            $this->renderText(json_encode(array("data" => AesCtr::decrypt($request->getParameter("jCryption"), $user->getAttribute("key"), 256))));
            return sfView::NONE;
        }
    }

    public function executeTest(sfWebRequest $request) {
//        $this->setLayout(false);
    }

}

?>