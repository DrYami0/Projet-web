<?php
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Model/Report.php';

class ReportC
{
    public function addReport($report)
    {
        $db = Config::getConnexion();

        try {
            $req = $db->prepare("INSERT INTO reports (description, reporterID, nomj, gid, status, pid) VALUES (:description, :reporterID, :nomj, :gid, 0, NULL)");
            $result = $req->execute([
                "description" => $report->getDescription(),
                "reporterID" => $report->getReporterID(),
                "gid" => $report->getGid(),
                "nomj" => $report->getNomj(),
            ]);
            
            if (!$result) {
                throw new Exception('Failed to insert report into database.');
            }
            
            return $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Error inserting report: ' . $e->getMessage());
        }
    }

    public function displayReports($uid = null)
    {
        $db = Config::getConnexion();
        
        if ($uid === null) {
            $sql = "SELECT rid, description, reporterID, nomj, gid, status, pid FROM reports GROUP BY rid ORDER BY rid ASC";
        } else {
            $sql = "SELECT rid, description, reporterID, nomj, gid, status, pid FROM reports WHERE reporterID = :uid GROUP BY rid ORDER BY rid ASC";
        }
        
        try {
            if ($uid === null) {
                $liste = $db->query($sql);
            } else {
                $req = $db->prepare($sql);
                $req->bindValue(':uid', $uid);
                $req->execute();
                $liste = $req;
            }
            return $liste;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function editReport($report, $rid)
    {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare(
                "UPDATE reports SET description=:description, reporterID=:reporterID, nomj=:nomj, gid=:gid, status=:status, pid=:pid WHERE rid=:rid"
            );
            $req->bindValue(':description', $report->getDescription());
            $req->bindValue(':reporterID', $report->getReporterID());
            $req->bindValue(':nomj', $report->getNomj());
            $req->bindValue(':gid', $report->getGid());
            $req->bindValue(':status', $report->getStatus());
            $req->bindValue(':pid', $report->getPid());
            $req->bindValue(':rid', $rid);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getReportById($rid)
    {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare("SELECT * FROM reports WHERE rid = :rid");
            $req->bindValue(':rid', $rid);
            $req->execute();
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteReport($rid)
    {
        $db = Config::getConnexion();
        try {
            $req = $db->prepare("DELETE FROM reports WHERE rid = :rid");
            $req->bindValue(':rid', $rid);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}