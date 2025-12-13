<?php
require_once __DIR__ . '/../../config.php';

class TypeC
{
    // Create a new type
    public function addType($type) {
        $db = config::getConnexion();  
        
        try {
            $req = $db->prepare("INSERT INTO types (type, difficulty) 
                                VALUES (:type, :difficulty)");
            $req->execute([
                "type" => $type->getType(),
                "difficulty" => $type->getDifficulty()
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Display all types
    public function displayTypes() {
        $sql = "SELECT * FROM types";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Edit type
    public function editType($type, $wid) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                "UPDATE types SET type=:type, difficulty=:difficulty WHERE wid=:wid"
            );
            $req->bindValue(':type', $type->getType());
            $req->bindValue(':difficulty', $type->getDifficulty());
            $req->bindValue(':wid', $wid);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // Delete type
    public function deleteType($wid) {
        $sql = "DELETE FROM types WHERE wid= :wid";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':wid', $wid);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    
    // Get types by difficulty
    public function getTypesByDifficulty($difficulty) {
        $sql = "SELECT * FROM types WHERE difficulty = :difficulty";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->bindValue(':difficulty', $difficulty);
            $req->execute();
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>