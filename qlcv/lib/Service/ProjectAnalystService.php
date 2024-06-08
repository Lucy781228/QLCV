<?php
namespace OCA\QLCV\Service;

use OCP\IDBConnection;

class ProjectAnalystService
{
    private $db;

    public function __construct(IDBConnection $db)
    {
        $this->db = $db;
    }

    public function getProjectIds($startDate, $endDate, $userId)
    {
        $query = $this->db->getQueryBuilder();
        $query
            ->select("project_id", "project_name")
            ->from("qlcv_project")
            ->where(
                $query
                    ->expr()
                    ->eq("user_id", $query->createNamedParameter($userId))
            );
        if ($startDate !== null) {
            $query->andWhere(
                $query
                    ->expr()
                    ->gte(
                        "start_date",
                        $query->createNamedParameter($startDate)
                    )
            );
        }

        if ($endDate !== null) {
            $query->andWhere(
                $query
                    ->expr()
                    ->lte("end_date", $query->createNamedParameter($endDate))
            );
        }

        $query->orderBy("start_date", "ASC");

        return $query->execute()->fetchAll();
    }

    public function countWorksPerProject($startDate, $endDate, $userId)
    {
        $projects = $this->getProjectIds($startDate, $endDate, $userId);
        $result = [];
    
        foreach ($projects as $project) {
            $projectResult = [
                "project_id" => $project["project_id"],
                "project_name" => $project["project_name"],
                "all_works" => 0,
                "done_work" => 0,
                "high" => 0,
                "normal" => 0,
                "low" => 0
            ];
    
            $projectResult["all_works"] = $this->countWorksByStatus($project["project_id"], null);
            $projectResult["done_work"] = $this->countWorksByStatus($project["project_id"], 3);
            $projectResult["high"] = $this->countWorksByLabel($project["project_id"], "Cao");
            $projectResult["normal"] = $this->countWorksByLabel($project["project_id"], "Trung bình");
            $projectResult["low"] = $this->countWorksByLabel($project["project_id"], "Thấp");
    
            $result[] = $projectResult;
        }
    
        return $result;
    }
    
    private function countWorksByStatus($projectId, $status)
    {
        $query = $this->db->getQueryBuilder();
        $query
            ->select($query->func()->count("*", "work_count"))
            ->from("qlcv_work")
            ->where(
                $query->expr()->eq(
                    "project_id",
                    $query->createNamedParameter($projectId)
                )
            );
    
        if ($status !== null) {
            $query->andWhere(
                $query->expr()->eq(
                    "status",
                    $query->createNamedParameter($status)
                )
            );
        }
    
        $count = $query->execute()->fetch();
        return $count["work_count"];
    }

    private function countWorksByLabel($projectId, $label)
    {
        $query = $this->db->getQueryBuilder();
        $query
            ->select($query->func()->count("*", "work_count"))
            ->from("qlcv_work")
            ->where(
                $query->expr()->eq(
                    "project_id",
                    $query->createNamedParameter($projectId)
                )
            )
            ->andWhere(
                $query->expr()->eq(
                    "label",
                    $query->createNamedParameter($label)
                ));
    
        $count = $query->execute()->fetch();
        return $count["work_count"];
    }
}
