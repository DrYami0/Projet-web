<?php
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Model/Report.php';

class ReportC
{
    public function addReport($report)
    {
        $db = Config::getConnexion();

        try {
            $req = $db->prepare("INSERT INTO reports (description, reporterID, reportedID, gid, status, aid, pid) VALUES (:description, :reporterID, :reportedID, :gid, :status, :aid, :pid)");
            $req->execute([
                "description" => $report->getDescription(),
                "reporterID" => $report->getReporterID(),
                "reportedID" => $report->getReportedID(),
                "gid" => $report->getGid(),
                "status" => $report->getStatus(),
                "aid" => $report->getAid(),
                "pid" => $report->getPid()
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function displayReports()
    {
        $sql = "SELECT * FROM reports";
        $db = Config::getConnexion();
        try {
            $liste = $db->query($sql);
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
                "UPDATE reports SET description=:description, reporterID=:reporterID, reportedID=:reportedID, gid=:gid, status=:status, aid=:aid, pid=:pid WHERE rid=:rid"
            );
            $req->bindValue(':description', $report->getDescription());
            $req->bindValue(':reporterID', $report->getReporterID());
            $req->bindValue(':reportedID', $report->getReportedID());
            $req->bindValue(':gid', $report->getGid());
            $req->bindValue(':status', $report->getStatus());
            $req->bindValue(':aid', $report->getAid());
            $req->bindValue(':pid', $report->getPid());
            $req->bindValue(':rid', $rid);
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}