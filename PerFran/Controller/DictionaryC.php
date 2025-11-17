<?php
require_once __DIR__ . '/../config.php'; // absolute path

class DictionaryC
{
    public function addWord($dictionary) {
        $db = config::getConnexion();  
        
        try {
            $req = $db->prepare( "INSERT INTO dictionaries VALUES (NULL, :word, :type, :difficulty)");
            $req->execute([ "word"=> $dictionary->getWord(),
                                    "type"=> $dictionary->getType(),
                                    "difficulty"=> $dictionary->getDifficulty()
                        ]);
        } catch (Exception $e) {
            die( 'Error: ' . $e->getMessage());
        }
    }


    public function displayWords() {
        $sql = "SELECT * FROM dictionaries";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function editWord($dictionary, $wid) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                "UPDATE dictionaries SET word=:word, type=:type, difficulty=:difficulty WHERE wid=:wid"
            );
            $req->bindValue(':word', $dictionary->getWord());
            $req->bindValue(':type', $dictionary->getType());
            $req->bindValue(':difficulty', $dictionary->getDifficulty());
            $req->bindValue(':wid', $wid);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteWord($wid) {
        $sql = "DELETE FROM dictionaries WHERE wid= :wid";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':wid', $wid);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    


}